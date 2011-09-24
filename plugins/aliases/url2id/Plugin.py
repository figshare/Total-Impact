#!/usr/bin/env python
import simplejson
import json
import urllib
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
# nosy MendeleyPlugin.py -A \'not skip\'
    
def skip(f):
    f.skip = True
    return f

class PluginClass(BasePluginClass):
                
    # each plugin needs to customize this stuff                
    SOURCE_NAME = "url2id"
    SOURCE_DESCRIPTION = ""
    SOURCE_URL = ""
    SOURCE_ICON = ""
    SOURCE_METRICS = {}

    DEBUG = False

    def __init__(self):
        pass

    # each plugin needs to write one of these    
    def derive_synonymns(self, url):        
        match = re.search("http://www.ncbi.nlm.nih.gov/pubmed/(?P<id>\d+)", url, re.IGNORECASE)
        if (match):
            return({"pmid":match.group("id")})

        match = re.search("http://www.pubmedcentral.nih.gov/articlerender.fcgi?artid=(?P<id>\d+)", url, re.IGNORECASE)
        if (match):
            return({"pmcid":match.group("id")})

        match = re.search(r"(?P<id>http://hdl.handle.net/.*)", url, re.IGNORECASE)
        if (match):
            return({"hdl":match.group("id")})

        match = re.search("(?P<id>10\.\d+/\S+)", url, re.IGNORECASE)
        if (match):
            doi = match.group("id")
            doi = doi.replace("/abstract", "")
            return({"doi":doi})

        return(None)  


    # each plugin needs to write relevant versions of this            
    def artifact_type_recognized(self, id):
        if id:
            is_recognized = self.is_url(id)
        else:
            is_recognized = False
        return(is_recognized)   
 
     # list of possible ids should be in order of preference, most prefered first
    # returns the first valid one, or None if none are valid
    def get_valid_id(self, list_of_possible_ids):
        for id in list_of_possible_ids:
            if (self.artifact_type_recognized(id)):
                return(id)
        return(None)
        
    ## this changes for every plugin        
    def build_artifact_response(self, id):
        if not id:
            return(None)
        metrics_response = self.derive_synonymns(id)
        if not metrics_response:
            return(None)        
        response = dict(type="article", url=id)    
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
            (artifact_id, lookup_id) = self.get_relevant_id(artifact_id, query[artifact_id], ["url"])
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
        

    