#!/usr/bin/env python
import re
import simplejson
import time
import httplib2
import urllib2
import nose
from nose.tools import assert_equals
import os
import collections
from collections import defaultdict

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
    TEST_INPUT_SLIDESHARE_URL = {"ttp://www.slideshare.net/phylogenomics/eisenall-hands":{"url":"ttp://www.slideshare.net/phylogenomics/eisenall-hands"}}
    TEST_INPUT_ALL.update(TEST_INPUT_SLIDESHARE_URL)
    TEST_INPUT_GEO = {"GSE2109":{}}
    TEST_INPUT_ALL.update(TEST_INPUT_GEO)
    TEST_INPUT_TWEETED_URL = {"http://ow.ly/73HQN":{}}
    TEST_INPUT_ALL.update(TEST_INPUT_TWEETED_URL)
    TEST_INPUT_GITHUB_URL = {"https://github.com/mhahnel/total-impact":{}}
    TEST_INPUT_ALL.update(TEST_INPUT_GITHUB_URL) 
    TEST_INPUT_SOURCEFORGE_URL = {"http://sourceforge.net/projects/aresgalaxy":{}}
    TEST_INPUT_ALL.update(TEST_INPUT_SOURCEFORGE_URL) 
    TEST_INPUT_ICPSR_DOI = {"10.3886/ICPSR03131":{}}
    TEST_INPUT_ALL.update(TEST_INPUT_ICPSR_DOI) 
    TEST_INPUT_ARXIV_HANDLE = {"10.3886/ICPSR03131":{}}
    TEST_INPUT_ALL.update(TEST_INPUT_ARXIV_HANDLE) 
    TEST_INPUT_NATURE_PRECEDINGS_ID = {"10.3886/ICPSR03131":{}}
    TEST_INPUT_ALL.update(TEST_INPUT_NATURE_PRECEDINGS_ID) 
    TEST_INPUT_REPEC_ID = {"10.3886/ICPSR03131":{}}
    TEST_INPUT_ALL.update(TEST_INPUT_REPEC_ID) 
    TEST_INPUT_UMN_ID = {"http://conservancy.umn.edu/handle/107490":{}}
    TEST_INPUT_ALL.update(TEST_INPUT_UMN_ID) 
    TEST_INPUT_ORNLDAAC_DOI = {"10.3334/ORNLDAAC/912":{}}
    TEST_INPUT_ALL.update(TEST_INPUT_ORNLDAAC_DOI) 
    TEST_INPUT_DATACITE_DOI = {"10.3886/ICPSR03131":{}}
    TEST_INPUT_ALL.update(TEST_INPUT_DATACITE_DOI) 
    TEST_INPUT_MENDELEY_UUID = {"11f3b2c0-44e8-11e0-babf-0024e8453de6":{}}
    TEST_INPUT_ALL.update(TEST_INPUT_MENDELEY_UUID) 
        
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

    MAX_ELAPSED_TIME = 120 # seconds, part of plugin API specification
    
    CACHE_DIR = "../../.cache"  #so that all plugins, alias and metrics, running on this server can share cache

    DEBUG = False
    
    status = defaultdict(int)
    
    def is_crossref_doi(self, id):
        response = (self.DOI_PATTERN.search(id) != None)  ## Would exclude DataCite ids from here?
        return(response)

    def is_doi(self, id):
        response = self.is_crossref_doi(id)  ## Would also add DataCite ids here, but frankly they are already included
        return(response)
            
    def is_pmid(self, id):
        response = (self.PMID_PATTERN.search(id) != None)
        return(response)

    def is_mendeley_uuid(self, id):
        MENDELEY_UUID_PATTERN = re.compile(r"^\S{8}-\S{4}-\S{4}-\S{4}-\S{12}$", re.DOTALL)
        response = (MENDELEY_UUID_PATTERN.search(id) != None)
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
        start_time = time.time()
        (artifacts, error_msg) = self.get_artifacts_metrics(query)

        self.status["input_query_length"] = len(query.keys())
        self.status["elapsed_time"] = "%.4f" %(time.time() - start_time)
        self.status["response_length"] = len(artifacts.keys())

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
        self.status["has_error"] = has_error
        self.status["error_msg"] = error_msg
        last_update = time.time()
        self.status["last_update"] = int(last_update)
        self.status["last_update_str"] = time.ctime(last_update)
        response = dict(source_name=self.SOURCE_NAME, 
            last_update=last_update,
            has_error=has_error,
            error_msg=error_msg, 
            about=self.build_about(),
            status=self.status,
            artifacts=artifacts)
        json_response = simplejson.dumps(response)
        return(json_response)

    # I've temp disabled caching because it was causing permissions errors.
    # I'd like to implement this with memcached, instead, which will have much
    # better performance and be simpler.
    # I've temp disabled caching because it was causing permissions errors.
    # I'd like to implement this with memcached, instead, which will have much
    # better performance and be simpler.
    def get_cache_timeout_response(self,
                                    url,
                                    http_timeout_in_seconds = 20,
                                    max_cache_age_seconds = (1) * (24 * 60 * 60), # (number of days) * (number of seconds in a day),
                                    header_addons = {}):
        http = httplib2.Http(timeout=http_timeout_in_seconds)
        (response, content) = http.request(url)

        '''
        cache_read = http_cached.cache.get(url)
        if (cache_read):
            (response, content) = cache_read.split("\r\n\r\n", 1)
        else:
            ## response['cache-control'] = "max-age=" + str(max_cache_age_seconds)
            ## httplib2._updateCache(header_dict, response, content, http_cached.cache, url)
            if response.fromcache:
                self.status["count_got_response_from_cache"] += 1
            else:
                self.status["count_missed_cache"] += 1
                self.status["count_cache_miss_details"] = str(self.status["count_cache_miss_details"]) + "; " + url
                self.status["count_cache_miss_response"] = str(response)
                self.status["count_api_requests"] += 1

            if False:
                self.status["count_request_exception"] = "EXCEPTION!"
                self.status["count_uncached_call"] += 1
                self.status["count_api_requests"] += 1
                #(response, content) = http_cached.request(url, headers=header_dict.update({'cache-control':'no-cache'}))
                req = urllib2.Request(url, headers=header_dict)
                uh = urllib2.urlopen(req)
                content = uh.read()
                response = uh.info()
        '''

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
        #flip the order of the fields so that the first one in the fields list ends up at the front of the response
        fields.reverse()
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
