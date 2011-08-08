#!/usr/bin/env python
import simplejson
import json
import urllib
import urllib2
import string
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
# nosy CrossrefPlugin.py -A \'not skip\'
    
def skip(f):
    f.skip = True
    return f

class PluginClass(BasePluginClass):
                
    # each plugin needs to customize this stuff                
    SOURCE_NAME = "Dryad"
    SOURCE_DESCRIPTION = "An international repository of data underlying peer-reviewed articles in the basic and applied biology."
    SOURCE_URL = "http://www.datadryad.org/"
    SOURCE_ICON = "http://dryad.googlecode.com/svn-history/r4402/trunk/dryad/dspace/modules/xmlui/src/main/webapp/themes/Dryad/images/favicon.ico"
    SOURCE_METRICS = dict(  page_views="the journal where the paper was published",
                            year="the year of the publication",
                            title="the title of the publication", 
                            authors="the authors of the publication")

    DEBUG = False

    DRYAD_DOI_URL = "http://dx.doi.org/"
    DRYAD_VIEWS_PATTERN = re.compile("(?P<views>\d+) views", re.DOTALL)
    DRYAD_DOWNLOADS_PATTERN = re.compile("(?P<downloads>\d+) downloads", re.DOTALL)
    DRYAD_CITATION_PATTERN = re.compile('please cite the Dryad data package:.*<blockquote>(?P<authors>.+?)\((?P<year>\d{4})\).*(?P<title>Data from.+?)<span>Dryad', re.DOTALL)

    DRYAD_DOI_PATTERN = re.compile("(10.(\d)+/dryad(\S)+)", re.DOTALL | re.IGNORECASE)

    def get_page(self, doi):
        ## curl -D - -L -H "Accept: application/unixref+xml" "http://dx.doi.org/10.1126/science.1157784" 
        if not doi:
            return(None)
        url = self.DRYAD_DOI_URL + doi
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
        # crossref extraction code based on example at https://gist.github.com/931878
        if not page:
            return(None)        
        (response_header, content) = page
    
        view_matches = self.DRYAD_VIEWS_PATTERN.finditer(content)
        try:
            views = sum([int(view_match.group("views")) for view_match in view_matches])
        except ValueError:
            views = None

        download_matches = self.DRYAD_DOWNLOADS_PATTERN.finditer(content)
        try:
            downloads = sum([int(download_match.group("downloads")) for download_match in download_matches])
        except ValueError:
            downloads = None

        citation_matches = self.DRYAD_CITATION_PATTERN.search(content)
        try:
            authors = citation_matches.group("authors")
            year = citation_matches.group("year")
            title = citation_matches.group("title")
        except ValueError:
            authors = None
            year = None
            title = None
                
        return({"page_views":views, "total_downloads":downloads, "title":title, "year":year, "authors":authors})
    
    
    def get_metric_values(self, doi):
        page = self.get_page(doi)
        if page:
            response = self.extract_stats(page, doi)    
        else:
            response = None
        return(response)    
    
    def is_dryad_doi(self, id):        
        response = (self.DRYAD_DOI_PATTERN.search(id) != None)
        return(response)
                            
    def artifact_type_recognized(self, id):
        response = self.is_dryad_doi(id)
        return(response)   
        
    def build_artifact_response(self, artifact_id):
        metrics_response = self.get_metric_values(artifact_id)
        metrics_response.update({"type":"dataset"})
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
    
        

    