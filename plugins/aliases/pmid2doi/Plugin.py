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
import ConfigParser

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
    SOURCE_NAME = "pmid2doi"
    SOURCE_DESCRIPTION = "PubMed comprises more than 21 million citations for biomedical literature from MEDLINE, life science journals, and online books."
    SOURCE_URL = "http://www.ncbi.nlm.nih.gov/pubmed/"
    SOURCE_ICON = "http://www.ncbi.nlm.nih.gov/favicon.ico"
    SOURCE_METRICS = {}
    TOOL_NAME = ""
    TOOL_EMAIL = ""

    DEBUG = False

    PUBMED_ESUMMARY_API_URL = "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi?db=pubmed&id=%s&retmode=xml&tool=%s&email=%s"

    def __init__(self):
        config = ConfigParser.ConfigParser()
        config.readfp(open('../../../config/creds.ini'))
        self.TOOL_NAME = config.get('id', 'name')
        self.TOOL_EMAIL = config.get('id', 'email')

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

    def extract_stats(self, page, list_of_pmids):
        if not page:
            return([])     
        (response_header, content) = page
    
        response = []
        soup = BeautifulStoneSoup(content)
        #print soup.prettify()

        for docsum in soup.findAll("docsum"):
            #print(tag.id.text)
            id = docsum.id.text
            author_list = []
            response_dict = {}
            response_dict.update(pmid=id)
            for item in docsum.findAll("item"):
                if item.get("name") == "DOI":
                    doi = item.text
                    response_dict.update(doi=doi)
                if item.get("name") == "pmc":
                    pmcid = item.text
                    #share_details_url = "http://www.ncbi.nlm.nih.gov/pmc/articles/%s/citedby/?tool=pubmed" %pmcid
                    #response_dict.update(pmcid=pmcid, share_details_url=share_details_url)
                    response_dict.update(pmcid=pmcid)
            response += [(id, response_dict)]

        return(response)
    
    
    def get_dois_from_pmids(self, list_of_pmids):
        string_of_lookups = ",".join(list_of_pmids)
        url = self.PUBMED_ESUMMARY_API_URL % (string_of_lookups, self.TOOL_NAME, self.TOOL_EMAIL)
        page = self.get_page(url)
        if page:
            response = self.extract_stats(page, list_of_pmids)    
        else:
            response = None
        return(response)    
                                
    def artifact_type_recognized(self, id):
        response = self.is_pmid(id)
        return(response)   
        
    def build_artifact_response(self, list_of_pmid_lookups):
        metrics_response = self.get_dois_from_pmids(list_of_pmid_lookups)
        return(metrics_response)

                        
    def get_artifacts_metrics(self, query):
        pmid_lookups = {}
        for artifact_id in query:
            (artifact_id, lookup_id) = self.get_relevant_id(artifact_id, query[artifact_id], ["pmid"])
            if (artifact_id):
                pmid_lookups[lookup_id] = artifact_id
        artifact_response = self.build_artifact_response(pmid_lookups.keys())

        response_dict = dict()
        if artifact_response:
            for (lookup_id, response) in artifact_response:
                response_dict[pmid_lookups[lookup_id]] = response
            
        error = None
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
    
        

    