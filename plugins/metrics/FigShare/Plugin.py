#!/usr/bin/env python
import simplejson
import json
import urllib
import urllib2
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
    SOURCE_NAME = "FigShare"
    SOURCE_DESCRIPTION = "Publish ALL your data."
    SOURCE_URL = "http://www.figshare.com/"
    SOURCE_ICON = "http://www.figshare.com/figures/favicon.ico"
    SOURCE_METRICS = dict(title="The title of the artifact", 
                            page_views="The number of page views on FigShare")

    DEBUG = False

    FIGSHARE_API_URL = "http://figshare.com/labbook/api.php?handle=%s"
    FIGSHARE_HANDLE_PATTERN = re.compile(r"hdl.handle.net/(?P<id>10779/\w+)")

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

    def extract_stats(self, page, handle_id):
        if not page:
            return(None)        
        (response_header, content) = page
                
        api_result = simplejson.loads(content)        
        title = api_result["pagina"]
        page_views = api_result["total"]
            
        metrics_dict = dict(title=title, page_views=page_views)
        return(metrics_dict)
    
    
    def get_metric_values(self, id):
        try:
            handle_id = self.FIGSHARE_HANDLE_PATTERN.search(id).group("id")
        except:
            return ({})
        query_url = self.FIGSHARE_API_URL % handle_id
        page = self.get_page(query_url)
        if page:
            response = self.extract_stats(page, handle_id)    
        else:
            response = {}
        return(response)    

    def is_figshare_handle(self, id):
        response = (self.FIGSHARE_HANDLE_PATTERN.search(id) != None)
        return(response)
                                        
    def artifact_type_recognized(self, id):
        response = self.is_figshare_handle(id)
        return(response)   
        
    def build_artifact_response(self, artifact_id):
        metrics_response = self.get_metric_values(artifact_id)
        metrics_response.update({"type":"dataset"})
        return(metrics_response)
                
    def get_artifacts_metrics(self, query):
        response_dict = dict()
        error = None
        time_started = time.time()
        for artifact_id in query:
            (artifact_id, lookup_id) = self.get_relevant_id(artifact_id, query[artifact_id], ["url"])
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
    
        

    