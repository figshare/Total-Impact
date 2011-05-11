#!/usr/bin/env python

import urllib2
import re
import urllib
import time
import sha
import BeautifulSoup
from BeautifulSoup import BeautifulStoneSoup 

from optparse import OptionParser

TOTALIMPACT_SLIDESHARE_KEY = "nyHCUoNM"
TOTALIMPACT_SLIDESHARE_SECRET = "z7sRiGCG"
MENDELEY_DOI_URL = "http://www.slideshare.net/api/2/get_slideshow?api_key=nyHCUoNM&detailed=1&ts=%s&hash=%s&slideshow_url=%s"


def get_page(id):
    if not id:
        return(None)
    ts = time.time()
    hash_combo = sha.new(TOTALIMPACT_SLIDESHARE_SECRET + str(ts)).hexdigest()
    url = MENDELEY_DOI_URL %(ts, hash_combo, id)
    #print url
    try:
        page = urllib2.urlopen(url).read()
    except urllib2.HTTPError, err:
        if err.code == 404:
            page = None
        else:
            raise    
    return(page)  

def get_stats(page):
    if not page:
        return(None)
    soup = BeautifulStoneSoup(page)
    downloads = soup.numdownloads.text
    views = soup.numviews.text
    comments = soup.numcomments.text
    favorites = soup.numfavorites.text
    response = {"downloads":downloads, "views":views, "comments":comments, "favorites":favorites}
    return(response)  
        

from optparse import OptionParser

def main():
    parser = OptionParser(usage="usage: %prog [options] filename",
                          version="%prog 1.0")
    #parser.add_option("-x", "--xhtml",
    #                  action="store_true",
    #                  dest="xhtml_flag",
    #                  default=False,
    #                  help="create a XHTML template instead of HTML")
    (options, args) = parser.parse_args()

    if len(args) != 1:
        parser.error("wrong number of arguments")

    #print options
    #print args
    
    id = args[0]
    page = get_page(id)
    response = get_stats(page)
    print response


if __name__ == '__main__':
    main()

#example = "http://www.slideshare.net/hpiwowar/7-data-citation-challenges-illustrated-with-data-includes-elephants"

mendeley_test_id = "http://www.slideshare.net/hpiwowar/7-data-citation-challenges-illustrated-with-data-includes-elephants"
#mendeley_test_doi = "10.1371/journal.pcbi.1000361"
#mendeley_test_doi = "10.1371/journal.pmed.0040215"
#mendeley_test_doi = "10.1371/journal.pone.0000308"

#page = get_mendeley_page(mendeley_test_doi)
#response = get_stats(page)
#print response
    
