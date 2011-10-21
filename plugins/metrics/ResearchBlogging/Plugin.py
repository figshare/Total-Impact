#!/usr/bin/env python
import simplejson
import json
import urllib
import time
import re
import BeautifulSoup
from BeautifulSoup import BeautifulStoneSoup 
import hashlib
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
# nosy SlidesharePlugin.py -A \'not skip\'
    
def skip(f):
    f.skip = True
    return f

class PluginClass(BasePluginClass):
                
    # each plugin needs to customize this stuff                
    SOURCE_NAME = "Research Blogging"
    SOURCE_DESCRIPTION = "allows readers to easily find blog posts about serious peer-reviewed research"
    SOURCE_URL = "http://researchblogging.org/"
    SOURCE_ICON = "http://researchblogging.org/favicon.ico" 
    SOURCE_METRICS = dict(  blogs="the number of blogs about this research article indexed by Research Blogging")

    DEBUG = False

    RESEARCHBLOGGING_URL = "http://researchblogging.org/post-search/list?search_text=%s"

    # each plugin needs to write one of these    
    def get_page(self, doi):
        if not id:
            return(None)
        query_url = self.RESEARCHBLOGGING_URL %(doi)
        #print url
        try:
            response = self.get_cache_timeout_response(query_url)
        except:
            response = None
        return(response)  

    def get_as_int(self, mystr):
        try:
            response = int(mystr.text)
        except:
            response = None
        return(response)
    
    def extract_stats(self, page, id=None):
        if not page:
            return(None)

        (header, xml) = page
        soup = BeautifulStoneSoup(xml)
        response = {}
        #print(soup.prettify())

        try:
            blogDivs = soup.findAll("div", "articleData")
            if (blogDivs):
                response.update({"blogs":len(blogDivs)})
        except:
            pass
                
        return(response)         
    
    # each plugin needs to write relevant versions of this            
    def artifact_type_recognized(self, id):
        if id:
            is_recognized = self.is_doi(id)
        else:
            is_recognized = False
        return(is_recognized)   
            
    ## this changes for every plugin        
    def build_artifact_response(self, id):
        if not id:
            return(None)
        metrics_response = self.get_metric_values(id)
        if not metrics_response:
            return(None)        
        response = dict(type="article", show_details_url=self.RESEARCHBLOGGING_URL %id)    
        response.update(metrics_response)
        return(response)

    ## every plugin should check API limitations and make sure they are respected here
    def get_artifacts_metrics(self, query):
        response_dict = dict()
        error_msg = None
        time_started = time.time()
        for artifact_id in query:
            (artifact_id, lookup_id) = self.get_relevant_id(artifact_id, query[artifact_id], ["doi"])
            if (artifact_id):
                artifact_response = self.build_artifact_response(lookup_id)
                if artifact_response:
                    response_dict[artifact_id] = artifact_response
            if (time.time() - time_started > self.MAX_ELAPSED_TIME):
                error_msg = "TIMEOUT"
                break
        return(response_dict, error_msg)
    
    
class TestPluginClass(TestBasePluginClass):
    
    def setup(self):
        self.plugin = SlidesharePluginClass()
        self.test_parse_input = self.testinput.TEST_INPUT_SLIDESHARE_URL
    
    ## this changes for every plugin        
    def test_build_artifact_response(self):
        response = self.plugin.build_artifact_response("http://www.slideshare.net/phylogenomics/eisen")
        assert_equals(response, {'favorites': 2, 'upload_year': u'2010', 'title': 'Jonathan Eisen talk at #ievobio 2010', 'downloads': 10, 'views': 71984, 'type': 'slides', 'comments': 0})

    ## this changes for every plugin        
    def test_get_artifacts_metrics(self):
        response = self.plugin.get_artifacts_metrics(self.test_parse_input)
        print self.test_parse_input
        assert_equals(response, ({'http://www.slideshare.net/phylogenomics/eisen': {'favorites': 2, 'upload_year': u'2010', 'title': 'Jonathan Eisen talk at #ievobio 2010', 'downloads': 10, 'views': 71984, 'type': 'slides', 'comments': 0}}, None))

    #each plugin should make sure its range of inputs are covered
    def test_run_plugin_slideshare(self):
        response = self.plugin.run_plugin(simplejson.dumps(self.testinput.TEST_INPUT_SLIDESHARE_URL))
        print response
        assert_equals(len(response), 852)

    def test_run_plugin_doi(self):
        response = self.plugin.run_plugin(simplejson.dumps(self.testinput.TEST_INPUT_DOI))
        print response
        assert_equals(len(response), 649)

    def test_run_plugin_pmid(self):
        response = self.plugin.run_plugin(simplejson.dumps(self.testinput.TEST_INPUT_PMID))
        print response
        assert_equals(len(response), 649)

    def test_run_plugin_url(self):
        response = self.plugin.run_plugin(simplejson.dumps(self.testinput.TEST_INPUT_URL))
        print response
        assert_equals(len(response), 649)

    def test_run_plugin_invalid_id(self):
        response = self.plugin.run_plugin(simplejson.dumps(self.testinput.TEST_INPUT_DUD))
        print response
        assert_equals(len(response), 649)
    
    def test_run_plugin_multiple(self):
        response = self.plugin.run_plugin(simplejson.dumps(self.testinput.TEST_INPUT_ALL))
        print response
        assert_equals(len(response), 852) 
        

    