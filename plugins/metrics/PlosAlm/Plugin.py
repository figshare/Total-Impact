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
        Counter="the number of downloads of the PLoS article", 
        PubMed_Central_Usage_Stats="HTML page views, PDF downloads and XML downloads for an article from PubMed Central.", 
        CiteULike="The number of times that a user has bookmarked an article in CiteULike.", 
        Scopus="The citation data reported for an article from Scopus.", 
        PubMed_Central="The citation data reported for an article from PubMed Central", 
        Research_Blogging="This service no longer responds to API requests.", 
        CrossRef="The citation data reported for an article from CrossRef."
    )

    DEBUG = False

    PLOS_API_KEY = "n0ixcSmyvDdRNsq"
    PLOS_API_URL = "http://alm.plos.org/articles/info:doi/%s.xml?history=1&api_key=" + PLOS_API_KEY

    PLOS_DOI_PATTERN = re.compile(r"10.1371/journal.p", re.DOTALL | re.IGNORECASE)

    def get_page(self, doi):
        if not doi:
            return(None)
        url = self.PLOS_API_URL % doi
        if (self.DEBUG):
            print url
        try:
            page = self.get_cache_timeout_response(url)
            if (self.DEBUG):
                print page
        except:
            page = None
        return(page)

    def extract_stats(self, page, doi):
        if not page:
            return(None)        
        (response_header, content) = page
    
        soup = BeautifulStoneSoup(content)
        counts = soup.findAll(count=True)
                
        metrics_dict = {}
        for source in counts:
            try:
                metrics_dict[source["source"]] = source["count"]
            except:
                pass
        return(metrics_dict)
    
    
    def get_metric_values(self, doi):
        page = self.get_page(doi)
        if page:
            response = self.extract_stats(page, doi)    
        else:
            response = None
        return(response)    
    
    def is_plos_doi(self, id):        
        response = (self.PLOS_DOI_PATTERN.search(id) != None)
        return(response)
                            
    def artifact_type_recognized(self, id):
        response = self.is_plos_doi(id)
        return(response)   
        
    def build_artifact_response(self, artifact_id):
        metrics_response = self.get_metric_values(artifact_id)
        metrics_response.update({"type":"article"})
        return(metrics_response)
                
    ## Crossref API doesn't seem to have limits, though we should check every few months to make sure still true            
    def get_artifacts_metrics(self, query):
        response_dict = dict()
        error = "NA"
        time_started = time.time()
        for artifact_id in query:
            if self.artifact_type_recognized(artifact_id):
                artifact_response = self.build_artifact_response(artifact_id)
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
    
        

    