#!/usr/bin/env python

# Conforms to API specified here:  https://github.com/mhahnel/Total-Impact/wiki/Plugin-requirements

import re
from rdflib import Graph
import StringIO
from optparse import OptionParser
import string
import simplejson
import time
import nose
from nose.tools import assert_equals
import httplib2
import logging
logging.basicConfig() # to suppress warning from rdflib
    
def skip(f):
    f.skip = True
    return f
                
# nosy plugin.py -A \'not skip\'
                
SOURCE_NAME = "CrossRef"
SOURCE_DESCRIPTION = "An official Digital Object Identifier (DOI) Registration Agency of the International DOI Foundation."
SOURCE_URL = "http://www.crossref.org/"
SOURCE_ICON = "http://www.crossref.org/favicon.ico"
SOURCE_METRICS = dict(  journal="the journal where the paper was published",
                        date="the date of the publication",
                        title="the title of the publication", 
                        doi="the DOI of the publication, if applicable",
                        url="the url of the full text of the publication",
                        pmid="the PubMed identifier of the publication, if applicable")


TEST_GOLD_ABOUT = {'metrics': {'date': 'the date of the publication', 'doi': 'the DOI of the publication, if applicable', 'title': 'the title of the publication', 'url': 'the url of the full text of the publication', 'journal': 'the journal where the paper was published', 'pmid': 'the PubMed identifier of the publication, if applicable'}, 'url': 'http://www.crossref.org/', 'icon': 'http://www.crossref.org/favicon.ico', 'desc': 'An official Digital Object Identifier (DOI) Registration Agency of the International DOI Foundation.'}
TEST_GOLD_JSON_RESPONSE_STARTS_WITH = '{"artifacts": {}, "about": {"metrics": {"date": "the date of the publication", "doi": "the DOI of the publication, if applicable", "title": "the title of the publication", "url": "the url of the full text of the publication", "journal": "the journal where the paper was published", "pmid": "the PubMed identifier of the publication, if applicable"}, "url": "http://www.crossref.org/", "icon": "http://www.crossref.org/favicon.ico", "desc": "An official Digital Object Identifier (DOI) Registration Agency of the International DOI Foundation."}, "error": "false", "source_name": "CrossRef", "last_update": 130'
TEST_INPUT = '{"10.1371/journal.pcbi.1000361":{"doi":"10.1371/journal.pcbi.1000361","url":"FALSE","pmid":"19381256"}}'
TEST_GOLD_PARSED_INPUT = {u'10.1371/journal.pcbi.1000361': {u'url': u'FALSE', u'pmid': u'19381256', u'doi': u'10.1371/journal.pcbi.1000361'}}
TEST_INPUT_DOI = {"10.1371/journal.pcbi.1000361":{"doi":"10.1371/journal.pcbi.1000361","url":"FALSE","pmid":"FALSE"}}
TEST_INPUT_PMID = {"17808382":{"doi":"FALSE","url":"FALSE","pmid":"17808382"}}
TEST_INPUT_URL = {"http://onlinelibrary.wiley.com/doi/10.1002/asi.21512/abstract":{"doi":"FALSE","url":"http://onlinelibrary.wiley.com/doi/10.1002/asi.21512/abstract","pmid":"FALSE"}}
TEST_INPUT_DUD = {"NotAValidID":{"doi":"FALSE","url":"FALSE","pmid":"FALSE"}}
TEST_INPUT_ALL = TEST_INPUT_DUD.copy()
TEST_INPUT_ALL.update(TEST_INPUT_URL)
TEST_INPUT_ALL.update(TEST_INPUT_PMID)
TEST_INPUT_ALL.update(TEST_INPUT_DOI)

DOI_LOOKUP_URL = "http://dx.doi.org/%s"
DEBUG = False
# All CrossRef DOI prefixes begin with "10" followed by a number of four or more digits
#f rom http://www.crossref.org/02publishers/doi-guidelines.pdf
DOI_PATTERN = re.compile("(10.(\d)+/(\S)+)", re.DOTALL)

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
    #assert_equals(response, "hi")
    assert(response.startswith(TEST_GOLD_JSON_RESPONSE_STARTS_WITH))
    
def build_json_response(artifacts={}, error="false"):
    response = dict(source_name=SOURCE_NAME, 
        last_update=int(time.time()),
        error=error, 
        about=build_about(),
        artifacts=artifacts)
    json_response = simplejson.dumps(response)
    return(json_response)

def get_cache_timeout_response(url, 
                                http_timeout_in_seconds = 20, 
                                max_cache_age_seconds = (1) * (24 * 60 * 60), # (number of days) * (number of seconds in a day), 
                                header_addons = {}):
    """docstring for fname"""
    http_cached = httplib2.Http(".cache", timeout=http_timeout_in_seconds)
    header_dict = {'cache-control':'max-age='+str(max_cache_age_seconds)}
    header_dict.update(header_addons)
    (response, content) = http_cached.request(url, headers=header_dict)
    return(response, content)

def get_page(doi):
    if not doi:
        return(None)
    url = DOI_LOOKUP_URL % doi
    if (DEBUG):
        print url
    try:
        page = get_cache_timeout_response(url, header_addons={'Accept':'application/rdf+xml'})
        if (DEBUG):
            print page
    except:
        page = None
    return(page)

def extract_stats(page, doi):
    # crossref extraction code based on example at https://gist.github.com/931878
    if not page:
        return(None)        
    (response_header, content) = page
    
    try:
        url = response_header["content-location"]
        g = Graph()
        g.parse(StringIO.StringIO(content), format="xml")
    except:
        return(None)        
    title = None
    journal = None
    pubdate = None
    for s, p, o in g:
        if (doi in s) and (str(p)=="http://purl.org/dc/terms/title"):
            title = o.title()
        if (doi in s) and (str(p)=="http://purl.org/dc/terms/publisher"):
            journal = o.title()
        if (doi not in s) and (str(p)=="http://purl.org/dc/terms/date"):
            pubdate = o.title()
    response = dict(title=title, journal=journal, pubdate=pubdate, url=url)
    return(response)  
    
def get_metric_values(doi):
    page = get_page(doi)
    if page:
        response = extract_stats(page, doi)    
    else:
        response = None
    return(response)    

    
def get_doi_from_pmid(pmid):
    TOOL_EMAIL = "total-impact@googlegroups.com"
    url = "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id=%s&retmode=xml&email=%s" % (pmid, TOOL_EMAIL)
    (response, xml) = get_cache_timeout_response(url)
    try:
        doi = re.search('<ArticleId IdType="doi">(?P<doi>.*?)</ArticleId>', xml).group("doi")
    except:
        doi = ""
    return(doi)

def get_doi_from_url(url):
    return("")

def get_pmid_from_doi(doi):
    TOOL_EMAIL = "total-impact@googlegroups.com"
    url = "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?term=%s&email=%s" % (doi, TOOL_EMAIL)
    (response, xml) = get_cache_timeout_response(url)
    try:
        pmid = re.search("<Id>(?P<pmid>\d*)</Id>", xml).group("pmid")
    except:
        pmid = ""
    return(pmid)

def test_build_artifact_response():
    response = build_artifact_response(TEST_GOLD_PARSED_INPUT['10.1371/journal.pcbi.1000361'])
    assert_equals(response, {'doi': u'10.1371/journal.pcbi.1000361', 'pubdate': None, 'title': u'Adventures In Semantic Publishing: Exemplar Semantic Enhancements Of A Research Article', 'url': 'http://data.crossref.org/10.1371%2Fjournal.pcbi.1000361', 'journal': u'Public Library Of Science (Plos)', 'pmid': u'19381256', 'type': 'article'})
    
def build_artifact_response(artifact_query):
    doi = artifact_query["doi"]
    pmid = artifact_query["pmid"]
    url = artifact_query["url"]
    if doi=="FALSE" and pmid=="FALSE" and url=="FALSE":
        return(None)
    if doi=="FALSE":
        if pmid!="FALSE":
            doi = get_doi_from_pmid(pmid)
        else:
            doi = get_doi_from_url(url)
    if not DOI_PATTERN.search(doi):
        return(None)
    metrics_response = get_metric_values(doi)
    if not pmid:
        pmid = get_pmid_from_doi(doi)
    if pmid or metrics_response:
        response = dict(type="article", pmid=pmid, doi=doi, url=url)    
    else:
        return(None)
    if metrics_response:
        response.update(metrics_response)
    return(response)
    
def test_parse_input():
    response = parse_input(TEST_INPUT)
    assert_equals(response, TEST_GOLD_PARSED_INPUT)
        
def parse_input(json_in):
    query = simplejson.loads(json_in)
    return(query)

def test_get_artifacts_metrics():
    response = get_artifacts_metrics(TEST_GOLD_PARSED_INPUT)
    assert_equals(response, ({u'10.1371/journal.pcbi.1000361': {'doi': u'10.1371/journal.pcbi.1000361', 'pubdate': None, 'title': u'Adventures In Semantic Publishing: Exemplar Semantic Enhancements Of A Research Article', 'url': 'http://data.crossref.org/10.1371%2Fjournal.pcbi.1000361', 'journal': u'Public Library Of Science (Plos)', 'pmid': u'19381256', 'type': 'article'}}, 'NA'))
            
def get_artifacts_metrics(query):
    response_dict = dict()
    for artifact_id in query:
        artifact_response = build_artifact_response(query[artifact_id])
        if artifact_response:
            response_dict[artifact_id] = artifact_response
    error = "NA"
    return(response_dict, error)
   
def test_run_plugin_doi():
    response = run_plugin(simplejson.dumps(TEST_INPUT_DOI))
    #assert_equals(response, "hi")
    assert(response.startswith('{"artifacts": {"10.1371/journal.pcbi.1000361": {"doi": "10.1371/journal.pcbi.1000361", "pubdate": null, "title": "Adventures In Semantic Publishing: Exemplar Semantic Enhancements Of A Research Article", "url": "http://data.crossref.org/10.1371%2Fjournal.pcbi.1000361", "journal": "Public Library Of Science (Plos)", "pmid": "FALSE", "type": "article"}}, "about": {"metrics": {"date": "the date of the publication", "doi": "the DOI of the publication, if applicable", "title": "the title of the publication", "url": "the url of the full text of the publication", "journal": "the journal where the paper was published", "pmid": "the PubMed identifier of the publication, if applicable"}, "url": "http://www.crossref.org/", "icon": "http://www.crossref.org/favicon.ico", "desc": "An official Digital Object Identifier (DOI) Registration Agency of the International DOI Foundation."}, "error": "NA", "source_name": "CrossRef", "last_update": 130'))

def test_run_plugin_pmid():
    response = run_plugin(simplejson.dumps(TEST_INPUT_PMID))
    assert(response.startswith('{"artifacts": {"17808382": {"doi": "10.1126/science.141.3579.392", "pubdate": null, "title": "Citations In Popular And Interpretive Science Writing", "url": "http://data.crossref.org/10.1126%2Fscience.141.3579.392", "journal": "American Association For The Advancement Of Science Aaas (Science)", "pmid": "17808382", "type": "article"}}, "about": {"metrics": {"date": "the date of the publication", "doi": "the DOI of the publication, if applicable", "title": "the title of the publication", "url": "the url of the full text of the publication", "journal": "the journal where the paper was published", "pmid": "the PubMed identifier of the publication, if applicable"}, "url": "http://www.crossref.org/", "icon": "http://www.crossref.org/favicon.ico", "desc": "An official Digital Object Identifier (DOI) Registration Agency of the International DOI Foundation."}, "error": "NA", "source_name": "CrossRef", "last_update": 130'))

def test_run_plugin_url():
    response = run_plugin(simplejson.dumps(TEST_INPUT_URL))
    #assert_equals(response, "hi")
    assert(response.startswith('{"artifacts": {}, "about": {"metrics": {"date": "the date of the publication", "doi": "the DOI of the publication, if applicable", "title": "the title of the publication", "url": "the url of the full text of the publication", "journal": "the journal where the paper was published", "pmid": "the PubMed identifier of the publication, if applicable"}, "url": "http://www.crossref.org/", "icon": "http://www.crossref.org/favicon.ico", "desc": "An official Digital Object Identifier (DOI) Registration Agency of the International DOI Foundation."}, "error": "NA", "source_name": "CrossRef", "last_update": 130'))

def test_run_plugin_invalid_id():
    response = run_plugin(simplejson.dumps(TEST_INPUT_DUD))
    assert(response.startswith('{"artifacts": {}, "about": {"metrics": {"date": "the date of the publication", "doi": "the DOI of the publication, if applicable", "title": "the title of the publication", "url": "the url of the full text of the publication", "journal": "the journal where the paper was published", "pmid": "the PubMed identifier of the publication, if applicable"}, "url": "http://www.crossref.org/", "icon": "http://www.crossref.org/favicon.ico", "desc": "An official Digital Object Identifier (DOI) Registration Agency of the International DOI Foundation."}, "error": "NA", "source_name": "CrossRef", "last_update": 130'))
    
def test_run_plugin_multiple():
    response = run_plugin(simplejson.dumps(TEST_INPUT_ALL))
    assert_equals(len(response), 1272)
    
def run_plugin(json_in):
    query = parse_input(json_in)
    (artifacts, error) = get_artifacts_metrics(query)
    json_out = build_json_response(artifacts, error)
    return(json_out)

def main():
    parser = OptionParser(usage="usage: %prog [options] filename",
                          version="%prog 1.0")
    (options, args) = parser.parse_args()
    if len(args) != 1:
        parser.error("wrong number of arguments")
    json_in = args[0]
    
    # uncomment to test
    #json_in = simplejson.dumps(TEST_INPUT_ALL)
    #print(json_in)
    
    json_out = run_plugin(json_in)
    print json_out
    return(json_out)

if __name__ == '__main__':
    main() 
            
#test_input = "10.1371/journal.pcbi.1000361"


    
        