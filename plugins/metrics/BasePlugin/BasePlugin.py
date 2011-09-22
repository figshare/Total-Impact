#!/usr/bin/env python
import re
import simplejson
import time
import httplib2
import urllib2
import nose
from nose.tools import assert_equals
import os

# Permissions: RWX for owner, WX for others.  Set this here so that .pyc are created with these permissions
os.umask(022) 
    
def skip(f):
    f.skip = True
    return f
                
# Conforms to API specified here:  https://github.com/mhahnel/Total-Impact/wiki/Plugin-requirements
# To do automated tests with nosy                
# nosy plugin.py -A \'not skip\'

class TestInput(object):
    # each plugin needs to make sure these are all set up appropriately
    TEST_GOLD_JSON_RESPONSE_STARTS_WITH = '{"artifacts": {}, "about": {"metrics": {"date": "the date of the publication", "doi": "the DOI of the publication, if applicable", "title": "the title of the publication", "url": "the url of the full text of the publication", "journal": "the journal where the paper was published", "pmid": "the PubMed identifier of the publication, if applicable"}, "url": "http://www.crossref.org/", "icon": "http://www.crossref.org/.ico", "desc": "An official Digital Object Identifier (DOI) Registration Agency of the International DOI Foundation."}, "error": "false", "source_name": "CrossRef", "last_update": 130'
    TEST_INPUT = '{"10.1371/journal.pcbi.1000361":{"doi":"10.1371/journal.pcbi.1000361",}}'
    TEST_GOLD_PARSED_INPUT = eval(TEST_INPUT)
    
    TEST_INPUT_DOI = {"10.1371/journal.pcbi.1000361":{"doi":"10.1371/journal.pcbi.1000361"}}
    TEST_INPUT_ALL = TEST_INPUT_DOI.copy()
    TEST_INPUT_DRYAD_DOI = {"10.5061/dryad.1295":{"doi":"10.5061/dryad.1295"}}
    TEST_INPUT_ALL.update(TEST_INPUT_DRYAD_DOI)
    TEST_INPUT_ATTACHED_DRYAD_DOI = {"10.1371/journal.pone.0000308":{"attacheddatadoi":"10.5061/dryad.j2c4g"}}
    TEST_INPUT_ALL.update(TEST_INPUT_ATTACHED_DRYAD_DOI)
    TEST_INPUT_BAD_DOI = {"10.1371/abc.abc.123":{"doi":"10.1371/abc.abc.123"}}
    TEST_INPUT_ALL.update(TEST_INPUT_BAD_DOI)
    TEST_INPUT_PMID = {"17808382":{"pmid":"17808382","doi":"10.1126/science.141.3579.392"}}
    TEST_INPUT_ALL.update(TEST_INPUT_PMID)
    TEST_INPUT_FIGSHARE_HANDLE = {"http://hdl.handle.net/10779/51bbbd9afc8d13d7385f26b0817f304d":{"url":"http://hdl.handle.net/10779/51bbbd9afc8d13d7385f26b0817f304d"}}
    TEST_INPUT_ALL.update(TEST_INPUT_FIGSHARE_HANDLE)
    TEST_INPUT_URL = {"http://onlinelibrary.wiley.com/doi/10.1002/asi.21512/abstract":{"url":"http://onlinelibrary.wiley.com/doi/10.1002/asi.21512/abstract"}}
    TEST_INPUT_ALL.update(TEST_INPUT_URL)
    TEST_INPUT_DUD = {"NotAValidDOI":{"doi":"NotAValidDOI"}}
    TEST_INPUT_ALL.update(TEST_INPUT_DUD)
    TEST_INPUT_NOTHING = {"":{}}
    TEST_INPUT_ALL.update(TEST_INPUT_NOTHING)
    TEST_INPUT_PLOS_DOI = {"10.1371/journal.pcbi.1000361":{"doi":"10.1371/journal.pcbi.1000361"}}
    TEST_INPUT_ALL.update(TEST_INPUT_PLOS_DOI)
    TEST_INPUT_SLIDESHARE_URL = {"http://www.slideshare.net/phylogenomics/eisen":{"url":"http://www.slideshare.net/phylogenomics/eisen"}}
    TEST_INPUT_ALL.update(TEST_INPUT_SLIDESHARE_URL)
    TEST_INPUT_GEO = {"GSE2109":{}}
    TEST_INPUT_ALL.update(TEST_INPUT_GEO)
        
class BasePluginClass(object):

    SOURCE_NAME = "Base Plugin"
    SOURCE_DESCRIPTION = "Base class."
    SOURCE_URL = ""
    SOURCE_ICON = ""
    SOURCE_METRICS = dict()

    TEST_INPUT_ALL = TestInput().TEST_INPUT_ALL
    
    # All CrossRef DOI prefixes begin with "10" followed by a number of four or more digits
    #f rom http://www.crossref.org/02publishers/doi-guidelines.pdf
    DOI_PATTERN = re.compile(r"^10\.(\d)+/(\S)+$", re.DOTALL)
    CROSSREF_DOI_PATTERN = re.compile(r"^10\.(\d)+/(\S)+$", re.DOTALL)

    # PMIDs are 1 to 8 digit numbers, as per http://www.nlm.nih.gov/bsd/mms/medlineelements.html#pmid    
    PMID_PATTERN = re.compile(r"^\d{1,8}$", re.DOTALL)

    TOOL_NAME = "total-impact.org"
    TOOL_EMAIL = "total-impact@googlegroups.com"
    MAX_ELAPSED_TIME = 120 # seconds, part of plugin API specification

    DEBUG = False

    def is_crossref_doi(self, id):
        response = (self.DOI_PATTERN.search(id) != None)  ## Would exclude DataCite ids from here?
        return(response)

    def is_doi(self, id):
        response = self.is_crossref_doi(id)  ## Would also add DataCite ids here, but frankly they are already included
        return(response)
            
    def is_pmid(self, id):
        response = (self.PMID_PATTERN.search(id) != None)
        return(response)

    def is_url(self, id):
        response = False
        try:
            if ("http" in id[0:4]):
                response = True
        except:
            pass
        return(response)
        
    def run_plugin(self, json_in):
        query = self.parse_input(json_in)
        (artifacts, error_msg) = self.get_artifacts_metrics(query)
        json_out = self.build_json_response(artifacts, error_msg)
        return(json_out)
        
    def parse_input(self, json_in):
        query = simplejson.loads(json_in)
        return(query)

    def build_about(self):
        response = dict(desc=self.SOURCE_DESCRIPTION,
                                url=self.SOURCE_URL, 
                                icon=self.SOURCE_ICON, 
                                metrics=self.SOURCE_METRICS)
        return(response)
    
    def build_json_response(self, artifacts={}, error_msg=None):
        if (error_msg):
            has_error = "TRUE"
        else:
            has_error = "FALSE"
            error_msg = "NA"
        response = dict(source_name=self.SOURCE_NAME, 
            last_update=str(int(time.time())),
            has_error=has_error,
            error_msg=error_msg, 
            about=self.build_about(),
            artifacts=artifacts)
        json_response = simplejson.dumps(response)
        return(json_response)

    def get_cache_timeout_response(self, 
                                    url, 
                                    http_timeout_in_seconds = 20, 
                                    max_cache_age_seconds = (1) * (24 * 60 * 60), # (number of days) * (number of seconds in a day), 
                                    header_addons = {}):
        http_cached = httplib2.Http(".cache", timeout=http_timeout_in_seconds)
        header_dict = {'cache-control':'max-age='+str(max_cache_age_seconds)}
        header_dict.update(header_addons)
        try:
            (response, content) = http_cached.request(url, headers=header_dict)
        except:
            #(response, content) = http_cached.request(url, headers=header_dict.update({'cache-control':'no-cache'}))
            req = urllib2.Request(url, headers=header_dict)
            uh = urllib2.urlopen(req)
            content = uh.read()
            response = uh.info()
        return(response, content)

    # each plugin needs to write a get_page and extract_stats    
    def get_metric_values(self, doi):
        page = self.get_page(doi)
        if page:
            response = self.extract_stats(page, doi)    
        else:
            response = None
        return(response)        

    def get_candidate_ids(self, artifact_id, aliases, fields):
        response = [artifact_id]
        for field in fields:
            if aliases.has_key(field):
                # The fields have priority over the artifact name itself
                response = [aliases[field]] + response
        return(response)
        
    def get_relevant_id(self, artifact_id, aliases, fields):
        candidate_ids = self.get_candidate_ids(artifact_id, aliases, fields)
        for alias_id in candidate_ids:
            if self.artifact_type_recognized(alias_id):
                return(artifact_id, alias_id)
        return(None, None)
        
    ## this may be need to customized by plugins to support varied id types etc    
    ## every plugin should check API limitations and make sure they are respected here
    def get_artifacts_metrics(self, query):
        pass
        
    # each plugin needs to write one of these    
    def get_page(self, doi):
        pass

    # each plugin needs to write one of these    
    def extract_stats(self, page, doi=None):
        pass
    
    # each plugin needs to write relevant versions of this            
    def artifact_type_recognized(self, doi):
        pass
        
    ## this changes for every plugin        
    def build_artifact_response(self, doi):
        pass


class TestBasePluginClass(object):
    testinput = TestInput()

    def test_parse_input(self):
        response = BasePluginClass().parse_input(self.testinput.TEST_INPUT)
        assert_equals(response, self.testinput.TEST_GOLD_PARSED_INPUT)

    def test_build_empty_json_response(self):
        response = BasePluginClass().build_json_response()
        assert_equals(eval(response)["has_error"], "FALSE")
        assert_equals(eval(response)["error_msg"], "NA")

    def test_build_json_response_error_handling(self):
        response = BasePluginClass().build_json_response({}, "TIMEOUT")
        assert_equals(eval(response)["has_error"], "TRUE")
        assert_equals(eval(response)["error_msg"], "TIMEOUT")
