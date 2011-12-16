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
# nosy SlidesharePlugin.py -A \'not skip\'
    
def skip(f):
    f.skip = True
    return f

class PluginClass(BasePluginClass):
                
    # each plugin needs to customize this stuff                
    SOURCE_NAME = "Slideshare"
    SOURCE_DESCRIPTION = "The best way to share presentations, documents and professional videos."
    SOURCE_URL = "http://www.slideshare.net/"
    SOURCE_ICON = "http://www.slideshare.net/favicon.ico" #rgb(244, 138, 52)
    SOURCE_METRICS = dict(  downloads="the number of downloads of the presentation",
                            favorites="the number of times a presentation has been favorited",
                            comments="the number of comments on the presentation",
                            views="the number of views of the presentation"
                            )

    DEBUG = False

    SLIDESHARE_KEY = ""
    SLIDESHARE_SECRET = ""
    SLIDESHARE_API_URL = ""
    SLIDESHARE_URL_PATTERN = re.compile("http://www.slideshare.net/.+")

    def __init__(self):
        config = ConfigParser.ConfigParser()
        config.readfp(open('../../../config/creds.ini'))
        self.SLIDESHARE_SECRET =  config.get('apis', 'Slideshare_secret')
        url_str = "http://www.slideshare.net/api/2/get_slideshow?api_key=%s&detailed=1&ts=%s&hash=%s&slideshow_url=%s"
        self.SLIDESHARE_API_URL = url_str % (config.get('apis', 'Slideshare_key'), '%s', '%s', '%s')
        
    # each plugin needs to write one of these    
    def get_page(self, id):
        if not id:
            return(None)
        ts = time.time()
        hash_combo = hashlib.sha1(self.SLIDESHARE_SECRET + str(ts)).hexdigest()
        query_url = self.SLIDESHARE_API_URL %(ts, hash_combo, id)
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
        #print(soup)

        downloads = self.get_as_int(soup.numdownloads)
        views = self.get_as_int(soup.numviews)
        comments = self.get_as_int(soup.numcomments)
        favorites = self.get_as_int(soup.numfavorites)
        try:
            title = soup.title.text
            title = title.encode("latin1")
        except:
            title = ""
        try:
            upload_year = soup.created.text[-4:]
        except:
            upload_year = ""
        
        response = {"show_details_url":id, "upload_year":upload_year, "downloads":downloads, "views":views, "comments":comments, "favorites":favorites, "title":title}
        return(response)         

    # each plugin needs to write relevant versions of this
    def is_slideshare_url(self, id):
        response = (self.SLIDESHARE_URL_PATTERN.search(id) != None)
        return(response)
    
    # each plugin needs to write relevant versions of this            
    def artifact_type_recognized(self, id):
        if id:
            is_recognized = self.is_slideshare_url(id)
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
        metrics_response = self.get_metric_values(id)
        if not metrics_response:
            return(None)        
        response = dict(type="slides")    
        response.update(metrics_response)
        return(response)

    ## every plugin should check API limitations and make sure they are respected here
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
        

    