#!/usr/bin/env python

import urllib2
import re

DRYAD_DOI_URL = "http://dx.doi.org/"
DRYAD_VIEWS_PATTERN = re.compile("(?P<views>\d+) views", re.DOTALL)

def get_dryad_page(doi):
    if not doi:
        return(None)
    query_url = DRYAD_DOI_URL + doi
    page = urllib2.urlopen(query_url).read()
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
    return(views)  
        
test_doi = "10.5061/dryad.j1fd7"
page = get_dryad_page(test_doi)
views = get_number_views(page)
print views

class doiToPmidChanger:
    def __init__(self):
        self.cache={}

    def convert(self, doi):
        if doi in self.cache:
            return self.cache[doi]

        url = "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?term=%s&email=maximilianh@gmail.com" %doi
        xml = urllib2.urlopen(url)
        for l in xml:
            if l.find("<Id>")!=-1:
                # <Id>16027735</Id>
                pmid = l.strip().replace("<Id>","").replace("</Id>", "")
                # strip of part after first _!
                self.cache[doi]=pmid
 
 
                return pmid