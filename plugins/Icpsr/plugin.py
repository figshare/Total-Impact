#!/usr/bin/env python

# Conforms to API specified here:  https://github.com/mhahnel/Total-Impact/wiki/Plugin-requirements

import re
from BeautifulSoup import BeautifulStoneSoup
from optparse import OptionParser
import string
import simplejson
import BeautifulSoup
from BeautifulSoup import BeautifulStoneSoup 
import hashlib
import time
import nose
from nose.tools import assert_equals
import httplib2
    
def skip(f):
    f.skip = True
    return f
                
# To do automated tests with nosy                
# nosy plugin.py -A \'not skip\'
                
# each plugin needs to customize this stuff                
SOURCE_NAME = "SlideShare"
SOURCE_DESCRIPTION = "The best way to share presentations, documents and professional videos."
SOURCE_URL = "http://www.slideshare.net/"
SOURCE_ICON = "http://www.slideshare.net/favicon.ico"
SOURCE_METRICS = dict(  title="the title of the publication",
                        year_uploaded="the year the presentation was uploaded",
                        downloads="the number of downloads of the presentation",
                        favorites="the number of times a presentation has been favorited",
                        comments="the number of comments on the presentation",
                        views="the number of views of the presentation"
                        )

# plugins should make sure this list includes relevant input coverage
# each plugin needs to make sure these are all set up appropriately
TEST_GOLD_ABOUT = {'metrics': {'favorites': 'the number of times a presentation has been favorited', 'title': 'the title of the publication', 'downloads': 'the number of downloads of the presentation', 'views': 'the number of views of the presentation', 'comments': 'the number of comments on the presentation', 'year_uploaded': 'the year the presentation was uploaded'}, 'url': 'http://www.slideshare.net/', 'icon': 'http://www.slideshare.net/favicon.ico', 'desc': 'The best way to share presentations, documents and professional videos.'}
TEST_GOLD_JSON_RESPONSE_STARTS_WITH = '{"artifacts": {}, "about": {"metrics": {"date": "the date of the publication", "doi": "the DOI of the publication, if applicable", "title": "the title of the publication", "url": "the url of the full text of the publication", "journal": "the journal where the paper was published", "pmid": "the PubMed identifier of the publication, if applicable"}, "url": "http://www.crossref.org/", "icon": "http://www.crossref.org/favicon.ico", "desc": "An official Digital Object Identifier (DOI) Registration Agency of the International DOI Foundation."}, "error": "false", "source_name": "CrossRef", "last_update": 130'
TEST_INPUT = '{"10.3886/ICPSR04549":{"doi":"10.3886/ICPSR04549","url":"FALSE","pmid":"FALSE"}}'
TEST_GOLD_PARSED_INPUT = eval(TEST_INPUT)

TEST_INPUT_PLOS_DOI = {"10.1371/journal.pcbi.1000361":{"doi":"10.1371/journal.pcbi.1000361","url":"FALSE","pmid":"FALSE"}}
TEST_INPUT_DRYAD_DOI = {"10.5061/dryad.1295":{"doi":"10.5061/dryad.1295","url":"FALSE","pmid":"FALSE"}}
TEST_INPUT_ICPSR_DOI = {"10.3886/ICPSR04549":{"doi":"10.3886/ICPSR04549","url":"FALSE","pmid":"FALSE"}}
TEST_INPUT_BAD_DOI = {"10.1371/abc.abc.123":{"doi":"10.1371/abc.abc.123","url":"FALSE","pmid":"FALSE"}}
TEST_INPUT_PMID = {"17808382":{"doi":"FALSE","url":"FALSE","pmid":"17808382"}}
TEST_INPUT_URL = {"http://onlinelibrary.wiley.com/doi/10.1002/asi.21512/abstract":{"doi":"FALSE","url":"http://onlinelibrary.wiley.com/doi/10.1002/asi.21512/abstract","pmid":"FALSE"}}
TEST_INPUT_DUD = {"NotAValidDOI":{"doi":"NotAValidDOI","url":"FALSE","pmid":"FALSE"}}
TEST_INPUT_SLIDESHARE_URL = {"http://www.slideshare.net/phylogenomics/eisen":{"doi":"FALSE","url":"http://www.slideshare.net/phylogenomics/eisen","pmid":"FALSE"}}
TEST_INPUT_ALL = TEST_INPUT_DUD.copy()
TEST_INPUT_ALL.update(TEST_INPUT_URL)
TEST_INPUT_ALL.update(TEST_INPUT_PMID)
TEST_INPUT_ALL.update(TEST_INPUT_PLOS_DOI)
TEST_INPUT_ALL.update(TEST_INPUT_DRYAD_DOI)
TEST_INPUT_ALL.update(TEST_INPUT_ICPSR_DOI)
TEST_INPUT_ALL.update(TEST_INPUT_DUD)
TEST_INPUT_ALL.update(TEST_INPUT_SLIDESHARE_URL)

DEBUG = False

MAX_ELAPSED_TIME = 30 # seconds, part of plugin API specification

DEBUG = False

ICPSR_DOI_PATTERN = re.compile(r"^10\.(\d)+/icpsr(\S)+$", re.DOTALL | re.IGNORECASE)
ICPSR_SOURCE_URL = "http://www.icpsr.umich.edu/icpsrweb/ICPSR/studies/%s"
STATS_PATTERN = re.compile(r"View related literature</a> \((?P<stats>\d+)\)", re.DOTALL)

 
# each plugin needs to write one of these   
def get_page(doi):
    if not doi:
        return(None)
    accession = doi[-5:]
    query_url = ICPSR_SOURCE_URL % accession
    response = get_cache_timeout_response(query_url)
    #if (DEBUG):
    #    print page
    return(response) 
     

def extract_stats(page):
    if not page:
        return(None)
    (header, content) = page
    stats_matches = STATS_PATTERN.search(content)
    if not stats_matches:
        return(None)
    try:
        stats = float(stats_matches.group("stats"))
    except ValueError:
        return(None)
    return({"related_refs": stats})  
    
# each plugin needs to write relevant versions of this
def is_icpsr_doi(id):
    # Mendeley takes any crossref doi
    response = (ICPSR_DOI_PATTERN.search(id) != None)
    return(response)
    
# each plugin needs to write relevant versions of this            
def artifact_type_recognized(id):
    is_recognized = is_icpsr_doi(id)
    return(is_recognized)   

## this changes for every plugin        
def test_build_artifact_response():
    response = build_artifact_response("http://www.slideshare.net/phylogenomics/eisen")
    assert_equals(response, {'ARTIFACT-TITLE': 'Jonathan Eisen talk at #ievobio 2010', 'upload_year': u'2010', 'views': 71742, 'favorites': 2, 'downloads': 9, 'type': 'slides', 'comments': 0})
        
## this changes for every plugin        
def build_artifact_response(id):
    if not id:
        return(None)
    metrics_response = get_metric_values(id)
    if not metrics_response:
        return(None)        
    response = dict(type="slides")    
    response.update(metrics_response)
    return(response)

## this changes for every plugin        
def test_get_artifacts_metrics():
    response = get_artifacts_metrics(TEST_GOLD_PARSED_INPUT)
    assert_equals(response, ({'http://www.slideshare.net/phylogenomics/eisen': {'ARTIFACT-TITLE': 'Jonathan Eisen talk at #ievobio 2010', 'upload_year': u'2010', 'views': 71742, 'favorites': 2, 'downloads': 9, 'type': 'slides', 'comments': 0}}, None))
    
## every plugin should check API limitations and make sure they are respected here
## check Mendeley requirements!
def get_artifacts_metrics(query):
    response_dict = dict()
    error_msg = None
    time_started = time.time()
    for artifact_id in query:
        url = query[artifact_id]["url"]
        if artifact_type_recognized(url):
            artifact_response = build_artifact_response(url)
            if artifact_response:
                response_dict[artifact_id] = artifact_response
        if (time.time() - time_started > MAX_ELAPSED_TIME):
            error_msg = "TIMEOUT"
            break
    return(response_dict, error_msg)

def test_parse_input():
    response = parse_input(TEST_INPUT)
    assert_equals(response, TEST_GOLD_PARSED_INPUT)
        
def parse_input(json_in):
    query = simplejson.loads(json_in)
    return(query)

def test_build_about():
    response = build_about()
    assert_equals(response, TEST_GOLD_ABOUT)

def build_about():
    response = dict(desc=SOURCE_DESCRIPTION,
                            url=SOURCE_URL, 
                            icon=SOURCE_ICON, 
                            metrics=SOURCE_METRICS)
    return(response)
        
def test_build_json_response():
    response = build_json_response()
    response_no_timestamp = re.sub('130\d+', '130', response)
    assert_equals(response_no_timestamp, '{"about": {"metrics": {"favorites": "the number of times a presentation has been favorited", "title": "the title of the publication", "downloads": "the number of downloads of the presentation", "views": "the number of views of the presentation", "comments": "the number of comments on the presentation", "year_uploaded": "the year the presentation was uploaded"}, "url": "http://www.slideshare.net/", "icon": "http://www.slideshare.net/favicon.ico", "desc": "The best way to share presentations, documents and professional videos."}, "source_name": "SlideShare", "artifacts": {}, "error_msg": "NA", "has_error": "FALSE", "last_update": "130"}')

def test_build_json_response_error_handling():
    response = build_json_response({}, "TIMEOUT")
    response_no_timestamp = re.sub('130\d+', '130', response)
    assert_equals(response_no_timestamp, '{"about": {"metrics": {"favorites": "the number of times a presentation has been favorited", "title": "the title of the publication", "downloads": "the number of downloads of the presentation", "views": "the number of views of the presentation", "comments": "the number of comments on the presentation", "year_uploaded": "the year the presentation was uploaded"}, "url": "http://www.slideshare.net/", "icon": "http://www.slideshare.net/favicon.ico", "desc": "The best way to share presentations, documents and professional videos."}, "source_name": "SlideShare", "artifacts": {}, "error_msg": "TIMEOUT", "has_error": "TRUE", "last_update": "130"}')
    
def build_json_response(artifacts={}, error_msg=None):
    if (error_msg):
        has_error = "TRUE"
    else:
        has_error = "FALSE"
        error_msg = "NA"
    response = dict(source_name=SOURCE_NAME, 
        last_update=str(int(time.time())),
        has_error=has_error,
        error_msg=error_msg, 
        about=build_about(),
        artifacts=artifacts)
    json_response = simplejson.dumps(response)
    return(json_response)

def get_cache_timeout_response(url, 
                                http_timeout_in_seconds = 20, 
                                max_cache_age_seconds = (1) * (24 * 60 * 60), # (number of days) * (number of seconds in a day), 
                                header_addons = {}):
    http_cached = httplib2.Http(".cache", timeout=http_timeout_in_seconds)
    header_dict = {'cache-control':'max-age='+str(max_cache_age_seconds)}
    header_dict.update(header_addons)
    (response, content) = http_cached.request(url, headers=header_dict)
    return(response, content)

# each plugin needs to write a get_page and extract_stats    
def get_metric_values(doi):
    page = get_page(doi)
    if page:
        response = extract_stats(page)    
    else:
        response = None
    return(response)        
           
#each plugin should make sure its range of inputs are covered
def test_run_plugin_doi():
    response = run_plugin(simplejson.dumps(TEST_INPUT_PLOS_DOI))
    print response
    assert_equals(len(response), 649)

def test_run_plugin_pmid():
    response = run_plugin(simplejson.dumps(TEST_INPUT_PMID))
    print response
    assert_equals(len(response), 649)

def test_run_plugin_url():
    response = run_plugin(simplejson.dumps(TEST_INPUT_URL))
    print response
    assert_equals(len(response), 649)

def test_run_plugin_invalid_id():
    response = run_plugin(simplejson.dumps(TEST_INPUT_DUD))
    print response
    assert_equals(len(response), 649)
    
def test_run_plugin_multiple():
    response = run_plugin(simplejson.dumps(TEST_INPUT_ALL))
    print response
    assert_equals(len(response), 860)

def test_run_plugin_slideshare_url():
    response = run_plugin(simplejson.dumps(TEST_INPUT_SLIDESHARE_URL))
    print response
    assert_equals(len(response), 860)

def test_run_plugin_icpsr_doi():
    response = run_plugin(simplejson.dumps(TEST_INPUT_ICPSR_DOI))
    print response
    assert_equals(len(response), 860)
    
def run_plugin(json_in):
    query = parse_input(json_in)
    (artifacts, error_msg) = get_artifacts_metrics(query)
    json_out = build_json_response(artifacts, error_msg)
    return(json_out)

# can call "python plugin.py" from command line, no args, to get sample output
def main():
    parser = OptionParser(usage="usage: %prog [options] filename",
                          version="%prog 1.0")
    (options, args) = parser.parse_args()
    if len(args) == 1:
        json_in = args[0]
    else:    
        json_in = simplejson.dumps(TEST_INPUT_ALL)
        print("Didn't get any input args, so going to use sample input: ")
        print(json_in)
        print("")
    
    json_out = run_plugin(json_in)
    print json_out
    return(json_out)

if __name__ == '__main__':
    main() 
            
        
       
