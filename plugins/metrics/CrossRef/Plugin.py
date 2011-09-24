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
    SOURCE_NAME = "CrossRef"
    SOURCE_DESCRIPTION = "An official Digital Object Identifier (DOI) Registration Agency of the International DOI Foundation."
    SOURCE_URL = "http://www.crossref.org/"
    SOURCE_ICON = "http://www.crossref.org/favicon.ico"
    SOURCE_METRICS = dict(  journal="the journal where the paper was published",
                            year="the year of the publication",
                            title="the title of the publication", 
                            authors="the authors of the publication")

    DEBUG = False

    CROSSREF_API_PATTERN = "http://doi.crossref.org/servlet/query?pid=mytotalimpact@gmail.com&qdata=%s&format=unixref"
    
    def get_page(self, doi_list):
        ## see http://www.crossref.org/help/Content/05_Interfacing_with_the_CrossRef_system/Using_HTTP.htm
        if not doi_list:
            return(None)
        doi_string = "%0A".join(doi_list)
        url = self.CROSSREF_API_PATTERN % doi_string
        if (self.DEBUG):
            print url
        page = self.get_cache_timeout_response(url)
        if (self.DEBUG):
            print page
        return(page)

    def extract_stats(self, page, doi_list):
        if not page:
            return(None)        
        (response_header, content) = page

        response = []
        soup = BeautifulStoneSoup(content)
        for doi_record in soup.findAll("doi_record"):
            try:
                doi = doi_record.doi.text
            except AttributeError:
                doi = None
                                
            if doi:
                try:
                    title = str(doi_record.title.text)
                    if (title == "DOI Not Found"):
                        return(None)
                except:
                    title = ""

                try:
                    year = str(doi_record.year.text)
                except:
                    year = ""        
        
                try:
                    journal = str(doi_record.abbrev_title.text)
                except:
                    journal = ""
        
                try:
                    authors = ", ".join([str(a.surname.text) for a in doi_record.findAll(contributor_role="author")])
                except:
                    authors = ""

                response += [(doi, dict(doi=doi, type="article", title=title, journal=journal, year=year, authors=authors))]
        return(response)  
    
    def get_metric_values(self, list_of_dois):
        page = self.get_page(list_of_dois)
        if page:
            response = self.extract_stats(page, list_of_dois)    
        else:
            response = None
        return(response)    
               
    def is_dryad_doi(self, id):        
        DRYAD_DOI_PATTERN = re.compile(r"10.5061/dryad", re.DOTALL)        
        response = (DRYAD_DOI_PATTERN.search(id) != None)
        return(response)
            
    def is_non_crossref_artifact(self, id):
        response = self.is_dryad_doi(id)
        return(response)
                
    def artifact_type_recognized(self, id):
        is_recognized = self.is_crossref_doi(id)
        if self.is_non_crossref_artifact(id):
            is_recognized = False
        return(is_recognized)   
        
    def build_artifact_response(self, list_of_doi_lookups):
        metrics_response = self.get_metric_values(list_of_doi_lookups)
        return(metrics_response)
        
    ## Crossref API doesn't seem to have limits, though we should check every few months to make sure still true            
    def get_artifacts_metrics(self, query):
        error = None
        doi_lookups = {}
        for artifact_id in query:
            (artifact_id, lookup_id) = self.get_relevant_id(artifact_id, query[artifact_id], ["doi"])
            if (artifact_id):
                doi_lookups[lookup_id] = artifact_id
        artifact_response = self.build_artifact_response(doi_lookups.keys())

        response_dict = dict()
        if artifact_response:
            for (lookup_id, response) in artifact_response:
                try:
                    corresponding_article_id = doi_lookups[lookup_id]
                    response_dict[doi_lookups[lookup_id]] = response
                except KeyError:
                    self.status["key error"] = str(self.status["key error"]) + "; Trouble looking up " + lookup_id
                    error = "KeyError: " + self.status["key error"]
            
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
    
        

    