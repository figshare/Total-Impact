#!/usr/bin/env python

# Conforms to API specified here:  https://github.com/mhahnel/Total-Impact/wiki/Plugin-requirements

import re
from BeautifulSoup import BeautifulStoneSoup
from optparse import OptionParser
import string
import simplejson
import BeautifulSoup
from BeautifulSoup import BeautifulStoneSoup 
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
SOURCE_NAME = "PLoS"
SOURCE_DESCRIPTION = "Nonprofit publisher of open access articles in science and medicine."
SOURCE_URL = "http://www.plos.org/"
SOURCE_ICON = "http://www.plosbiology.org/images/favicon.ico"
SOURCE_METRICS = dict(  downloads="the number of downloads of the article from the PLoS website")

# plugins should make sure this list includes relevant input coverage
# each plugin needs to make sure these are all set up appropriately
TEST_GOLD_ABOUT = {'metrics': {'downloads': 'the number of downloads of the article from the PLoS website'}, 'url': 'http://www.plos.org/', 'icon': 'http://www.plosbiology.org/images/favicon.ico', 'desc': 'Nonprofit publisher of open access articles in science and medicine.'}
TEST_GOLD_JSON_RESPONSE_STARTS_WITH = '{"artifacts": {}, "about": {"metrics": {"date": "the date of the publication", "doi": "the DOI of the publication, if applicable", "title": "the title of the publication", "url": "the url of the full text of the publication", "journal": "the journal where the paper was published", "pmid": "the PubMed identifier of the publication, if applicable"}, "url": "http://www.crossref.org/", "icon": "http://www.crossref.org/favicon.ico", "desc": "An official Digital Object Identifier (DOI) Registration Agency of the International DOI Foundation."}, "error": "false", "source_name": "CrossRef", "last_update": 130'
TEST_INPUT = '{"10.1371/journal.pcbi.1000361":{"doi":"10.1371/journal.pcbi.1000361","url":"FALSE","pmid":"FALSE"}}'
TEST_GOLD_PARSED_INPUT = {u'10.1371/journal.pcbi.1000361': {u'url': u'FALSE', u'pmid': u'FALSE', u'doi': u'10.1371/journal.pcbi.1000361'}}

TEST_INPUT_PLOS_DOI = {"10.1371/journal.pcbi.1000361":{"doi":"10.1371/journal.pcbi.1000361","url":"FALSE","pmid":"FALSE"}}
TEST_INPUT_DRYAD_DOI = {"10.5061/dryad.1295":{"doi":"10.5061/dryad.1295","url":"FALSE","pmid":"FALSE"}}
TEST_INPUT_BAD_DOI = {"10.1371/abc.abc.123":{"doi":"10.1371/abc.abc.123","url":"FALSE","pmid":"FALSE"}}
TEST_INPUT_PMID = {"17808382":{"doi":"FALSE","url":"FALSE","pmid":"17808382"}}
TEST_INPUT_URL = {"http://onlinelibrary.wiley.com/doi/10.1002/asi.21512/abstract":{"doi":"FALSE","url":"http://onlinelibrary.wiley.com/doi/10.1002/asi.21512/abstract","pmid":"FALSE"}}
TEST_INPUT_DUD = {"NotAValidDOI":{"doi":"NotAValidDOI","url":"FALSE","pmid":"FALSE"}}
TEST_INPUT_NOTHING = {"NotAValidDOI":{"doi":"FALSE","url":"FALSE","pmid":"FALSE"}}
TEST_INPUT_ALL = TEST_INPUT_DUD.copy()
TEST_INPUT_ALL.update(TEST_INPUT_URL)
TEST_INPUT_ALL.update(TEST_INPUT_PMID)
TEST_INPUT_ALL.update(TEST_INPUT_PLOS_DOI)
TEST_INPUT_ALL.update(TEST_INPUT_DRYAD_DOI)
TEST_INPUT_ALL.update(TEST_INPUT_DUD)
TEST_INPUT_ALL.update(TEST_INPUT_NOTHING)

DEBUG = False

MAX_ELAPSED_TIME = 30 # seconds, part of plugin API specification

PLOS_ALM_COUNTER_URL = "http://www.plosreports.org/services/rest?method=usage.stats&doi=%s"
PLOS_DOI_PATTERN = re.compile(r"^10\.(\d)+/journal.p(\S)+$", re.DOTALL | re.IGNORECASE)

 
# each plugin needs to write one of these    

def get_page(doi):
    if not doi:
        return(None)

    query_url = PLOS_ALM_COUNTER_URL % doi
    # print query_url
    try:
        response = get_cache_timeout_response(query_url)
    except:
        response = None
    return(response) 
    
# each plugin needs to write one of these    
def extract_stats(page):
    (header, xml) = page
    soup = BeautifulStoneSoup(xml)
    #print(soup)
    
    # This use of nextSibling is a result of a documented bug in BeautifulStoneSoup which
    # fails to parse nested tags with the same name. It appears to consistently find the
    # correct data with this cludge 
    # http://www.mail-archive.com/debian-bugs-dist@lists.debian.org/msg869932.html
    try:
        total_downloads = int(soup.total.nextSibling.text)
    except:
        total_downloads = None
        
    if total_downloads == 0:
        return None
    #print total_downloads

    response = {"downloads":total_downloads}
    return(response)  
        

# each plugin needs to write relevant versions of this
def is_plos_doi(id):
    # Mendeley takes any crossref doi
    response = (PLOS_DOI_PATTERN.search(id) != None)
    return(response)
    
# each plugin needs to write relevant versions of this            
def artifact_type_recognized(id):
    is_recognized = is_plos_doi(id)
    return(is_recognized)   

## this changes for every plugin        
def test_build_artifact_response():
    response = build_artifact_response('10.1371/journal.pmed.0040215')
    assert_equals(response, {'downloads': 8611, 'type': 'article'})
        
## this changes for every plugin        
def build_artifact_response(doi):
    if not doi:
        return(None)
    metrics_response = get_metric_values(doi)
    if not metrics_response:
        return(None)        
    response = dict(type="article")    
    response.update(metrics_response)
    return(response)

## this changes for every plugin        
def test_get_artifacts_metrics():
    response = get_artifacts_metrics(TEST_GOLD_PARSED_INPUT)
    assert_equals(response, ({u'10.1371/journal.pcbi.1000361': {'downloads': 10084, 'type': 'article'}}, None))
    
## every plugin should check API limitations and make sure they are respected here
## check Mendeley requirements!
def get_artifacts_metrics(query):
    response_dict = dict()
    error_msg = None
    time_started = time.time()
    for artifact_id in query:
        doi = query[artifact_id]["doi"]
        if artifact_type_recognized(doi):
            artifact_response = build_artifact_response(doi)
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
    assert_equals(response_no_timestamp, '{"about": {"metrics": {"downloads": "the number of downloads of the article from the PLoS website"}, "url": "http://www.plos.org/", "icon": "http://www.plosbiology.org/images/favicon.ico", "desc": "Nonprofit publisher of open access articles in science and medicine."}, "source_name": "PLoS", "artifacts": {}, "error_msg": "NA", "has_error": "FALSE", "last_update": "130"}')

def test_build_json_response_error_handling():
    response = build_json_response({}, "TIMEOUT")
    response_no_timestamp = re.sub('130\d+', '130', response)
    assert_equals(response_no_timestamp, '{"about": {"metrics": {"downloads": "the number of downloads of the article from the PLoS website"}, "url": "http://www.plos.org/", "icon": "http://www.plosbiology.org/images/favicon.ico", "desc": "Nonprofit publisher of open access articles in science and medicine."}, "source_name": "PLoS", "artifacts": {}, "error_msg": "TIMEOUT", "has_error": "TRUE", "last_update": "130"}')
    
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
    assert_equals(len(response), 450)

def test_run_plugin_pmid():
    response = run_plugin(simplejson.dumps(TEST_INPUT_PMID))
    print response
    assert_equals(len(response), 379)

def test_run_plugin_url():
    response = run_plugin(simplejson.dumps(TEST_INPUT_URL))
    print response
    assert_equals(len(response), 379)

def test_run_plugin_invalid_id():
    response = run_plugin(simplejson.dumps(TEST_INPUT_DUD))
    print response
    assert_equals(len(response), 379)
    
def test_run_plugin_multiple():
    response = run_plugin(simplejson.dumps(TEST_INPUT_ALL))
    print response
    assert_equals(len(response), 450)
    
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
            
#test_input = "10.1038/ng0411-281"
#test_input = "10.1371/journal.pcbi.1000361"
#test_input = "10.1371/journal.pmed.0040215"
#test_input = "10.1371/journal.pone.0000308"

        
       
