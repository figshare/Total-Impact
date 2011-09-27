#!/usr/bin/env python
import simplejson
import json
import urllib
import urllib2
import BeautifulSoup
from BeautifulSoup import BeautifulStoneSoup
import time
import re
import nose
from nose.tools import assert_equals
import sys
import os
# This hack is to add current path when running script from command line
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
import BasePlugin
from BasePlugin.BasePlugin import BasePluginClass
from BasePlugin.BasePlugin import TestBasePluginClass

# Permissions: RWX for owner, WX for others.  Set this here so that .pyc are created with these permissions
os.umask(022) 
    
# Conforms to API specified here:  https://github.com/mhahnel/Total-Impact/wiki/Plugin-requirements
# To do automated tests with nosy                
# nosy CrossrefPlugin.py -A \'not skip\'
    
def skip(f):
    f.skip = True
    return f

class PluginClass(BasePluginClass):
                           
    # each plugin needs to customize this stuff                
    SOURCE_NAME = "PlosAlm"
    SOURCE_DESCRIPTION = "PLoS article level metrics."
    SOURCE_URL = "http://www.plos.org/"
    SOURCE_ICON = "http://plos.org/favicon.ico"
    SOURCE_METRICS = dict(  Postgenomic="This service was discontinued by Nature Publishing Group in 2009.", 
        Web_of_Science="The citation data reported for an article from Web of Science.", 
        Bloglines="This service no longer responds to API requests.", 
        Biod="", 
        Nature="The number of blog articles in Nature Blogs that have mentioned an article.", 
        Connotea="The Connotea API does not respond in a timely manner.", 
        PLoS_html_view="the number of downloads of the PLoS HTML article", 
        PLoS_pdf_view="the number of downloads of the PLoS PDF article", 
        PLoS_xml_view="the number of downloads of the PLoS XML article", 
        PMC_abstract="the number of times the abstract has been viewed at PubMed Central (confirm)",
        PMC_supp_data="the number of times the supplementary material has been viewed at PubMed Central (confirm)",
        PMC_unique_ip="the number of unique IP addresses that have viewed the artifact at PubMed Central (confirm)",
        PMC_pdf="the number of times the PDF has been viewed at PubMed Central (confirm)",
        PMC_scanned_page_browse="the number of times the scanned pages have been viewed at PubMed Central, if applicable (confirm)",
        PMC_cited_by="the number of times the article has been cited by other articles in PubMed Central (confirm)", 
        PMC_scanned_summary="the number of times the scanned summary has been viewed at PubMed Central, if applicable (confirm)",
        PMC_full_text="the number of times the full text has been viewed at PubMed Central (confirm)",
        CiteULike="The number of times that a user has bookmarked an article in CiteULike.", 
        Scopus="The citation data reported for an article from Scopus.", 
        PubMed_Central="The citation data reported for an article from PubMed Central", 
        Research_Blogging="This service no longer responds to API requests.", 
        CrossRef="The citation data reported for an article from CrossRef."
    )

    DEBUG = False

    PLOS_API_KEY = "n0ixcSmyvDdRNsq"
    PLOS_ALM_POOLED_API_URL = "http://alm.plos.org/articles/info:doi/%s.xml?history=1&api_key=" + PLOS_API_KEY
    PLOS_ALM_PLOS_USAGE_API_URL = "http://alm.plos.org/articles/info:doi/%s.xml?source=counter&citations=1&api_key=" + PLOS_API_KEY
    PLOS_ALM_PMC_USAGE_API_URL = "http://alm.plos.org/articles/info:doi/%s.xml?source=pmc&citations=1&history=1&api_key=" + PLOS_API_KEY
    PLOS_DOI_PATTERN = re.compile(r"10.1371/journal.p", re.DOTALL | re.IGNORECASE)

    PLOS_HOSTNAME_LOOKUP = dict(pone="plosone", pbio="plosbiology", pmed="plosmedicine", pcbi="ploscompbiol", pgen="plosgenetics", ppat="plospathogens", pntd="plosntds")
    
    def get_page(self, url):
        if not url:
            return(None)
        if (self.DEBUG):
            print url
        try:
            page = self.get_cache_timeout_response(url)
            if (self.DEBUG):
                print page
        except:
            page = None
        return(page)

    def extract_stats_pooled(self, page, doi):
        if not page:
            return(None)        
        (response_header, content) = page
    
        soup = BeautifulStoneSoup(content)
        sources = soup.findAll(source=True)
                
        metrics_dict = {}
        for source in sources:
            if not source["source"] in ["Counter", "PubMed Central Usage Stats"]:
                metrics_dict[source["source"]] = source["count"]

        return(metrics_dict)
    
    def extract_stats_general(self, page, doi, prefix, exclude_fields, aggregator):
        if not page:
            return({})        
        (response_header, content) = page
    
        soup = BeautifulStoneSoup(content)
        #print soup.prettify()
        try:
            details = soup.details.findAll(year=True)
        except AttributeError:
            return({})
            
        metrics_dict = {}
        all_exclude_fields = ["month", "year"] + exclude_fields
        for (metric_name, metric_value) in details[0].attrs:
            if metric_name not in all_exclude_fields:
                metric_values = [int(details[i][metric_name]) for i in range(len(details))]
                metrics_dict[prefix + metric_name] = aggregator(metric_values)
        return(metrics_dict)
                        
    def get_metric_values(self, doi):
        response = {}
        
        # Get main alm stats
        page = self.get_page(self.PLOS_ALM_POOLED_API_URL %doi)
        if page:
            response.update(self.extract_stats_pooled(page, doi))
            
        # Get PLoS html, pdf views
        page = self.get_page(self.PLOS_ALM_PLOS_USAGE_API_URL %doi)
        if page:
            response.update(self.extract_stats_general(page, doi, "PLoS_", [], sum))

        # Get detailed PMC usage
        page = self.get_page(self.PLOS_ALM_PMC_USAGE_API_URL %doi)
        if page:
            response.update(self.extract_stats_general(page, doi, "PMC_", [], sum))

        journal_key = re.search("10.1371/journal.(?P<prefix>[a-z]+).\d+", doi)
        prefix = journal_key.group("prefix")
        plos_host = self.PLOS_HOSTNAME_LOOKUP[prefix]
        show_details_url = "http://www.%s.org/article/metrics/info:doi/%s" %(plos_host, doi)
        response["show_details_url"] = show_details_url
        
        return(response)    
    
    def is_plos_doi(self, id):        
        response = (self.PLOS_DOI_PATTERN.search(id) != None)
        return(response)
                            
    def artifact_type_recognized(self, id):
        response = self.is_plos_doi(id)
        return(response)   
        
    def build_artifact_response(self, artifact_id):
        metrics_response = self.get_metric_values(artifact_id)
        if metrics_response:
            metrics_response.update({"type":"article"})
        return(metrics_response)
                
    def get_artifacts_metrics(self, query):
        response_dict = dict()
        error = None
        time_started = time.time()
        for artifact_id in query:
            (artifact_id, lookup_id) = self.get_relevant_id(artifact_id, query[artifact_id], ["doi"])
            if (artifact_id):
                artifact_response = self.build_artifact_response(lookup_id)
                if artifact_response:
                    response_dict[artifact_id] = artifact_response
            if (time.time() - time_started > self.MAX_ELAPSED_TIME):
                error = "TIMEOUT"
                break
        return(response_dict, error)
    
    
class TestPluginClass(TestBasePluginClass):

    def setup(self):
        self.plugin = PluginClass()
        self.test_parse_input = self.testinput.TEST_INPUT_DOI
    
    ## this changes for every plugin        
    def test_build_artifact_response(self):
        response =  self.plugin.build_artifact_response('10.1371/journal.pcbi.1000361')
        assert_equals(response, {'doi': '10.1371/journal.pcbi.1000361', 'title': 'Adventures in Semantic Publishing: Exemplar Semantic Enhancements of a Research Article', 'url': 'http://www.ploscompbiol.org/article/info%3Adoi%2F10.1371%2Fjournal.pcbi.1000361', 'journal': 'PLoS Comput Biol', 'authors': 'Shotton, Portwin, Klyne, Miles', 'year': '2009', 'pmid': '19381256', 'type': 'article'})

    ## this changes for every plugin        
    def test_get_artifacts_metrics(self):
        response = self.plugin.get_artifacts_metrics(self.test_parse_input)
        assert_equals(response, ({u'10.1371/journal.pcbi.1000361': {'doi': u'10.1371/journal.pcbi.1000361', 'title': 'Adventures in Semantic Publishing: Exemplar Semantic Enhancements of a Research Article', 'url': 'http://www.ploscompbiol.org/article/info%3Adoi%2F10.1371%2Fjournal.pcbi.1000361', 'journal': 'PLoS Comput Biol', 'authors': 'Shotton, Portwin, Klyne, Miles', 'year': '2009', 'pmid': '19381256', 'type': 'article'}}, 'NA'))

    #each plugin should make sure its range of inputs are covered
    def test_run_plugin_doi(self):
        response = self.plugin.run_plugin(simplejson.dumps(self.testinput.TEST_INPUT_DOI))
        assert_equals(len(response), 1077)

    def test_run_plugin_pmid(self):
        response = self.plugin.run_plugin(simplejson.dumps(self.testinput.TEST_INPUT_PMID))
        assert_equals(len(response), 961)

    def test_run_plugin_url(self):
        response = self.plugin.run_plugin(simplejson.dumps(self.testinput.TEST_INPUT_URL))
        assert_equals(len(response), 685)

    def test_run_plugin_invalid_id(self):
        response = self.plugin.run_plugin(simplejson.dumps(self.testinput.TEST_INPUT_DUD))
        assert_equals(len(response), 685)
    
    def test_run_plugin_multiple(self):
        response = self.plugin.run_plugin(simplejson.dumps(self.testinput.TEST_INPUT_ALL))
        assert_equals(len(response), 1710)
    
        

    