#!/usr/bin/env python
import simplejson
import json
import urllib
import time
import re
import nose
from nose.tools import assert_equals
from BasePlugin import BasePluginClass
from BasePlugin import TestBasePluginClass
import os

# Permissions: RWX for owner, WX for others.  Set this here so that .pyc are created with these permissions
os.umask(022) 
    
# Conforms to API specified here:  https://github.com/mhahnel/Total-Impact/wiki/Plugin-requirements
# To do automated tests with nosy                
# nosy MendeleyPlugin.py -A \'not skip\'
    
def skip(f):
    f.skip = True
    return f

class PluginClass(BasePluginClass):
                
    # each plugin needs to customize this stuff                
    SOURCE_NAME = "Mendeley"
    SOURCE_DESCRIPTION = "A research management tool for desktop and web."
    SOURCE_URL = "http://www.mendeley.com/"
    SOURCE_ICON = "http://www.mendeley.com/favicon.ico"
    SOURCE_METRICS = dict(  readers="the number of readers of the article",
                            groups="the number of groups of the article")

    DEBUG = False

    TOTALIMPACT_MENDELEY_KEY = "3a81767f6212797750ef228c8cb466bc04dca4ba1"
    MENDELEY_LOOKUP_FROM_DOI_URL = "http://www.mendeley.com/oapi/documents/details/%s?type=doi&consumer_key=" + TOTALIMPACT_MENDELEY_KEY

    def __init__(self):
        pass

    # each plugin needs to write one of these    
    def get_page(self, doi):
        if not doi:
            return(None)
        
        # Mendeley API required double encoded doi!!!
        double_encoded_doi = urllib.quote(urllib.quote(doi, safe=""), safe="")
    
        query_url = self.MENDELEY_LOOKUP_FROM_DOI_URL % double_encoded_doi
        #print query_url
        try:
            response = self.get_cache_timeout_response(query_url)
        except:
            response = None
        return(response)  

    # each plugin needs to write one of these    
    def extract_stats(self, page, doi=None):
        #print page
        (header, content) = page
        json_page = json.loads(content)  # migrate this to simplejson too
        if not page:
            return(None)
        try:
            number_readers = json_page["stats"]["readers"]
            group_list = json_page["groups"]
            number_groups = len(group_list)
        except:
            return(None)
        response = {"readers":number_readers, "groups":number_groups}
        return(response)  
        

    # each plugin needs to write relevant versions of this
    def is_mendeley_doi(self, id):
        # Mendeley takes any crossref doi
        response = (self.CROSSREF_DOI_PATTERN.search(id) != None)
        return(response)
    
    # each plugin needs to write relevant versions of this            
    def artifact_type_recognized(self, doi):
        is_recognized = self.is_mendeley_doi(doi)
        return(is_recognized)   
 
    ## this changes for every plugin        
    def build_artifact_response(self, doi):
        if not doi:
            return(None)
        metrics_response = self.get_metric_values(doi)
        if not metrics_response:
            return(None)        
        response = dict(type="article")    
        response.update(metrics_response)
        return(response)
    
    ## this may be need to customized by plugins to support varied id types etc    
    ## every plugin should check API limitations and make sure they are respected here
    ## check Mendeley requirements!
    def get_artifacts_metrics(self, query):
        response_dict = dict()
        error_msg = None
        time_started = time.time()
        for artifact_id in query:
            doi = query[artifact_id]["doi"]
            if self.artifact_type_recognized(doi):
                artifact_response = self.build_artifact_response(doi)
                if artifact_response:
                    response_dict[artifact_id] = artifact_response
            if (time.time() - time_started > self.MAX_ELAPSED_TIME):
                error_msg = "TIMEOUT"
                break
        return(response_dict, error_msg)

class TestPluginClass(TestBasePluginClass):

    def setup(self):
        self.plugin = MendeleyPluginClass()
        self.test_parse_input = self.testinput.TEST_INPUT_DOI
    
    ## this changes for every plugin        
    def test_build_artifact_response(self):
        response = self.plugin.build_artifact_response('10.1371/journal.pmed.0040215')
        assert_equals(response, {'type': 'article', 'groups': 1, 'readers': 42})

    ## this changes for every plugin        
    def test_get_artifacts_metrics(self):
        response = self.plugin.get_artifacts_metrics(self.test_parse_input)
        assert_equals(response, ({u'10.1371/journal.pcbi.1000361': {'type': 'article', 'groups': 1, 'readers': 19}}, None))

    #each plugin should make sure its range of inputs are covered
    def test_run_plugin_doi(self):
        response = self.plugin.run_plugin(simplejson.dumps(self.testinput.TEST_INPUT_DOI))
        print response
        assert_equals(len(response), 458)

    def test_run_plugin_pmid(self):
        response = self.plugin.run_plugin(simplejson.dumps(self.testinput.TEST_INPUT_PMID))
        print response
        assert_equals(len(response), 379)

    def test_run_plugin_url(self):
        response = self.plugin.run_plugin(simplejson.dumps(self.testinput.TEST_INPUT_URL))
        print response
        assert_equals(len(response), 379)

    def test_run_plugin_invalid_id(self):
        response = self.plugin.run_plugin(simplejson.dumps(self.testinput.TEST_INPUT_DUD))
        print response
        assert_equals(len(response), 379)
    
    def test_run_plugin_multiple(self):
        response = self.plugin.run_plugin(simplejson.dumps(self.testinput.TEST_INPUT_ALL))
        print response
        assert_equals(len(response), 458)
        

    