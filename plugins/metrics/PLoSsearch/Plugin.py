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
    SOURCE_NAME = "PLoSsearch"
    SOURCE_DESCRIPTION = "PLoS full text search."
    SOURCE_URL = "http://www.plos.org/"
    SOURCE_ICON = "http://a0.twimg.com/profile_images/67542107/Globe_normal.jpg"
    SOURCE_METRICS = dict(mentions="the number of mentions in PLoS article full text")
    PLOS_SEARCH_API_URL = ''
    DEBUG = False

    def __init__(self):
        config = ConfigParser.ConfigParser()
        config.readfp(open('../../../config/creds.ini'))
        key = config.get('apis', 'PLoS_key')
        self.PLOS_SEARCH_API_URL = 'http://api.plos.org/search?q="%s"&api_key=' + key

    def get_page(self, id):
        if not id:
            return(None)
        url = self.PLOS_SEARCH_API_URL % id
        if (self.DEBUG):
            print url
        try:
            page = self.get_cache_timeout_response(url)
            if (self.DEBUG):
                print page
        except:
            page = None
        return(page)

    def extract_stats(self, page, id):
        if not page:
            return(None)        
        (response_header, content) = page
    
        soup = BeautifulStoneSoup(content)
        #print soup.prettify()
        try:
            hits = soup.result['numfound']
        except:
            hits = 0
            
        return({"mentions":hits})
    
    
    def get_metric_values(self, id):
        page = self.get_page(id)
        if page:
            response = self.extract_stats(page, id)    
        else:
            response = {}
        return(response)    
                                
    def artifact_type_recognized(self, id):
        if (self.is_pmid(id) or self.is_url(id) or self.is_mendeley_uuid(id)):
            response = False
        else:
            response = True;
        return(response)   
        
    def is_PDB_ID(self, id):
        if re.search("[A-Za-z0-9]{4}", id):
            return(True)
        else:
            return(False) 

    def is_Genbank_ID(self, id):
        # to do 
        return(False) 

    def is_GEO_ID(self, id):
        if re.search("G[A-Z{2}.\d+", id):
            return(True)
        else:
            return(False) 

    def is_ArrayExpress_ID(self, id):
        if re.search("E-[A-Za0-9\-]{4}", id):
            return(True)
        else:
            return(False) 

    def build_artifact_response(self, artifact_id):
        metrics_response = self.get_metric_values(artifact_id)
        show_details_url = "http://www.plosone.org/search/advancedSearch.action?pageSize=10&journalOpt=all&unformattedQuery=everything%3A" + artifact_id
        metrics_response.update({"show_details_url":show_details_url})
        if (self.is_PDB_ID(artifact_id) or self.is_Genbank_ID(artifact_id) or self.is_GEO_ID(artifact_id) or self.is_ArrayExpress_ID(artifact_id)):
            metrics_response.update({"type":"dataset"})
        else:
            metrics_response.update({"type":"unknown"})
        return(metrics_response)
                
    def get_artifacts_metrics(self, query):
        response_dict = dict()
        error = None
        time_started = time.time()
        for artifact_id in query:
            ## What other fields would we want to search for up, I wonder?
            (artifact_id, lookup_id) = self.get_relevant_id(artifact_id, query[artifact_id], ["doi", "attacheddata"])
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
    
        

    