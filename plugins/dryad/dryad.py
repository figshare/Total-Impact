#!/usr/bin/env python

import urllib2
import re

DRYAD_DOI_URL = "http://dx.doi.org/"
DRYAD_VIEWS_PATTERN = re.compile("(?P<views>\d+) views", re.DOTALL)

DRYAD_DOI_PATTERN = re.compile("(10.(\d)+/dryad(\S)+)", re.DOTALL | re.IGNORECASE)

def run_plugin(doi):
    # Right now this is only designed to look up dois
    if not DRYAD_DOI_PATTERN.search(doi):
        return(None)
    page = get_dryad_page(doi)
    if page:
        response = get_number_views(page)
    else:
        response = None
    return(response)
    
def get_dryad_page(doi):
    if not doi:
        return(None)
    query_url = DRYAD_DOI_URL + doi
    try:
        page = urllib2.urlopen(query_url).read()
    except urllib2.HTTPError:
        page = None
    return(page)  

def get_number_views(page):
    if not page:
        return(None)
    view_matches = DRYAD_VIEWS_PATTERN.search(page)
    if not view_matches:
        return(None)
    try:
        views = float(view_matches.group("views"))
    except ValueError:
        return(None)
    return({"page_views":views})  
        

from optparse import OptionParser

def main():
    parser = OptionParser(usage="usage: %prog [options] filename",
                          version="%prog 1.0")
    (options, args) = parser.parse_args()

    if len(args) != 1:
        parser.error("wrong number of arguments")

    id = args[0]
    response = run_plugin(id)
    print response
    return(response)


if __name__ == '__main__':
    main()
    
#test_doi = "10.5061/dryad.j1fd7"
#page = get_dryad_page(test_doi)
#views = get_number_views(page)
#print views    