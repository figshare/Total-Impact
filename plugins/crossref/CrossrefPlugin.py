#!/usr/bin/env python
import simplejson
import json
import urllib
import time
import re
import BeautifulSoup
from BeautifulSoup import BeautifulStoneSoup
import nose
from nose.tools import assert_equals
from BasePlugin import BasePluginClass
from BasePlugin import TestBasePluginClass
    
# Conforms to API specified here:  https://github.com/mhahnel/Total-Impact/wiki/Plugin-requirements
# To do automated tests with nosy                
# nosy CrossrefPlugin.py -A \'not skip\'
    
def skip(f):
    f.skip = True
    return f

class CrossrefPluginClass(BasePluginClass):
                
    # each plugin needs to customize this stuff                
    SOURCE_NAME = "CrossRef"
    SOURCE_DESCRIPTION = "An official Digital Object Identifier (DOI) Registration Agency of the International DOI Foundation."
    SOURCE_URL = "http://www.crossref.org/"
    SOURCE_ICON = "http://www.crossref.org/favicon.ico"
    SOURCE_METRICS = dict(  journal="the journal where the paper was published",
                            year="the year of the publication",
                            title="the title of the publication", 
                            authors="the authors of the publication", 
                            doi="the DOI of the publication, if applicable",
                            url="the url of the full text of the publication",
                            pmid="the PubMed identifier of the publication, if applicable")

    DEBUG = False

    DOI_LOOKUP_URL = "http://dx.doi.org/%s"

    def get_page(self, doi):
        ## curl -D - -L -H "Accept: application/unixref+xml" "http://dx.doi.org/10.1126/science.1157784" 
        if not doi:
            return(None)
        url = self.DOI_LOOKUP_URL % doi
        if (self.DEBUG):
            print url
        try:
            page = self.get_cache_timeout_response(url, header_addons={'Accept':'application/unixref+xml'})
            if (self.DEBUG):
                print page
        except:
            page = None
        return(page)

    def extract_stats(self, page, doi):
        # crossref extraction code based on example at https://gist.github.com/931878
        if not page:
            return(None)        
        (response_header, content) = page
    
        soup = BeautifulStoneSoup(content)
        try:
            title = str(soup.title.text)
            if (title == "DOI Not Found"):
                return(None)
        except:
            title = ""

        try:
            year = str(soup.year.text)
        except:
            year = ""        
        
        try:
            journal = str(soup.abbrev_title.text)
        except:
            journal = ""
        
        try:
            authors = ", ".join([str(a.surname.text) for a in soup.findAll(contributor_role="author")])
        except:
            authors = ""
    
        # To get full text, try to follow the doi url then get the final landing page
        doi_initial_url = self.DOI_LOOKUP_URL % doi
        (redirected_header, redirected_page) = self.get_cache_timeout_response(doi_initial_url)
        try:
            url = redirected_header["content-location"]
        except:
            url = ""
       
        response = dict(url=url, title=title, journal=journal, year=year, authors=authors)
        return(response)  
    
    def get_metric_values(self, doi):
        page = self.get_page(doi)
        if page:
            response = self.extract_stats(page, doi)    
        else:
            response = None
        return(response)    
    
    def get_doi_from_pmid(self, pmid):
        url = "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id=%s&retmode=xml&email=%s" % (pmid, self.TOOL_EMAIL)
        (response, xml) = self.get_cache_timeout_response(url)
        try:
            doi = re.search('<ArticleId IdType="doi">(?P<doi>.*?)</ArticleId>', xml).group("doi")
        except:
            doi = ""
        return(doi)

    def get_pmid_from_doi(self, doi):
        url = "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?term=%s&email=%s" % (doi, self.TOOL_EMAIL)
        (response, xml) = self.get_cache_timeout_response(url)
        try:
            pmid = re.search("<Id>(?P<pmid>\d*)</Id>", xml).group("pmid")
        except:
            pmid = ""
        return(pmid)
            
    def artifact_type_recognized(self, id):
        is_recognized = (self.is_crossref_doi(id) or self.is_pmid(id))
        return(is_recognized)   
        
    def build_artifact_response(self, artifact_id):
        if self.is_crossref_doi(artifact_id):
            doi = artifact_id
            pmid = self.get_pmid_from_doi(doi)
        elif self.is_pmid(artifact_id):
            pmid = artifact_id
            doi = self.get_doi_from_pmid(pmid)
        if not self.is_crossref_doi(doi):
            return(None)
        metrics_response = self.get_metric_values(doi)
        if not pmid and not metrics_response:
            return(None)
        response = dict(type="article", pmid=pmid, doi=doi)    
        if metrics_response:
            response.update(metrics_response)
        return(response)
                
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
    
    
class TestMendeleyPluginClass(TestBasePluginClass):

    def setup(self):
        self.plugin = CrossrefPluginClass()
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
    
        

    