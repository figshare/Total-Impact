#!/usr/bin/env python

import urllib2
import urllib
import re
from rdflib import Graph
from rdflib import Namespace
import StringIO
from optparse import OptionParser
import string
import simplejson
import time
import nose
from nose.tools import assert_equals

def skip(f):
    f.skip = True
    return f
                
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

DOI_LOOKUP_URL = "http://dx.doi.org/%s"
DEBUG = False
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


def get_parsed_page(doi):
    if not doi:
        return(None)
    url = DOI_LOOKUP_URL % doi
    if (DEBUG):
        print url
    try:
        op = urllib.FancyURLopener()
        op.addheader('Accept', 'application/rdf+xml')
        f = op.open(url)
        parsed_page = f.read()
        if (DEBUG):
            print parsed_page
    except:
        parsed_page = None
    return(parsed_page)  

def extract_stats(parsed_page, doi):
# crossref extraction code based on example at https://gist.github.com/931878
    if not parsed_page:
        return(None)        
    try:
        g = Graph()
        g.parse(StringIO.StringIO(parsed_page), format="xml")
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
            print pubdate
        
    response = dict(title=title, journal=journal, pubdate=pubdate)
    return(response)  

def get_doi_from_pmid(pmid):
    return(10)

def get_pmid_from_doi(pmid):
    return(7)
    
def get_metric_results(doi):
    page = get_parsed_page(doi)
    if page:
        response = extract_stats(page, doi)    
    #return(dict(title="my paper"))    
    return(response)    

def test_build_artifact_response():
    response = build_artifact_response(TEST_GOLD_PARSED_INPUT['10.1371/journal.pcbi.1000361'])
    assert_equals(response, {'doi': u'10.1371/journal.pcbi.1000361', 'pubdate': None, 'title': u'Adventures In Semantic Publishing: Exemplar Semantic Enhancements Of A Research Article', 'url': u'FALSE', 'pmid': u'19381256', 'type': 'article', 'journal': u'Public Library Of Science (Plos)'})
    
def build_artifact_response(artifact_query):
    doi = artifact_query["doi"]
    pmid = artifact_query["pmid"]
    url = artifact_query["url"]
    if not doi and not pmid:
        return(None)
    if not doi:
        doi = get_doi_from_pmid(pmid)
    if not pmid:
        pmid = get_pmid_from_doi(doi)
    response = dict(type="article", pmid=pmid, doi=doi, url=url)    
    if DOI_PATTERN.search(doi):
        metrics_response = get_metric_results(doi)        
        response.update(metrics_response)
    return(response)
    
def test_parse_input():
    response = parse_input(TEST_INPUT)
    assert_equals(response, TEST_GOLD_PARSED_INPUT)
        
def parse_input(json_in):
    query = simplejson.loads(json_in)
    return(query)

def test_get_artifacts_metrics():
    response = get_metrics(TEST_GOLD_PARSED_INPUT)
    assert_equals(response, {u'10.1371/journal.pcbi.1000361': {'doi': u'10.1371/journal.pcbi.1000361', 'pubdate': None, 'title': u'Adventures In Semantic Publishing: Exemplar Semantic Enhancements Of A Research Article', 'url': u'FALSE', 'pmid': u'19381256', 'type': 'article', 'journal': u'Public Library Of Science (Plos)'}})
            
def get_artifacts_metrics(query):
    response_dict = dict()
    for artifact_id in query:
        artifact_response = build_artifact_response(query[artifact_id])
        if artifact_response:
            response_dict[artifact_id] = artifact_response
    return(response_dict)
    
    
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
    json_out = run_plugin(json_in)
    print json_out
    return(json_out)

if __name__ == '__main__':
    main() 
            
#test_input = "10.1371/journal.pcbi.1000361"


    
        