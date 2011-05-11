#!/usr/bin/env python

# code based on example at https://gist.github.com/931878

import urllib2
import urllib
import re
from rdflib import Graph
from rdflib import Namespace
import StringIO
from optparse import OptionParser

DOI_LOOKUP_URL = "http://dx.doi.org/%s"

DEBUG = False


def get_parsed_page(doi):
    if not doi:
        return(None)
    url = DOI_LOOKUP_URL % doi
    if (DEBUG):
        print url
    op = urllib.FancyURLopener()
    op.addheader('Accept', 'application/rdf+xml')
    f = op.open(url)
    parsed_page = f.read()
    if (DEBUG):
        print parsed_page
    return(parsed_page)  

def get_stats(parsed_page, doi):
    if not parsed_page:
        return(None)
        
    g = Graph()
    g.parse(StringIO.StringIO(parsed_page), format="xml")

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
            
    response = {"title":title, "journal":journal, "pubdate":pubdate}
    return(response)  

def main():
    parser = OptionParser(usage="usage: %prog [options] filename",
                          version="%prog 1.0")
    (options, args) = parser.parse_args()

    if len(args) != 1:
        parser.error("wrong number of arguments")

    id = args[0]
    page = get_parsed_page(id)
    response = get_stats(page, id)
   
    print response
    return(response)

if __name__ == '__main__':
    main() 
            
#test_input = "10.1371/journal.pcbi.1000361"
#parsed_page = get_parsed_page(test_input)
#stats = get_stats(parsed_page)
#print stats




    
        