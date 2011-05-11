#!/usr/bin/env python

import urllib2
import re
import urllib
import time
import hashlib
import BeautifulSoup
from BeautifulSoup import BeautifulStoneSoup 

from optparse import OptionParser

TOTALIMPACT_SLIDESHARE_KEY = "nyHCUoNM"
TOTALIMPACT_SLIDESHARE_SECRET = "z7sRiGCG"
SLIDESHARE_DOI_URL = "http://www.slideshare.net/api/2/get_slideshow?api_key=nyHCUoNM&detailed=1&ts=%s&hash=%s&slideshow_url=%s"

def get_page(id):
    if not id:
        return(None)
    ts = time.time()
    hash_combo = hashlib.sha1(TOTALIMPACT_SLIDESHARE_SECRET + str(ts)).hexdigest()
    url = SLIDESHARE_DOI_URL %(ts, hash_combo, id)
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
    (options, args) = parser.parse_args()

    if len(args) != 1:
        parser.error("wrong number of arguments")

    id = args[0]
    page = get_page(id)
    response = get_stats(page)
    print response
    return(response)


if __name__ == '__main__':
    main()

#example = "http://www.slideshare.net/hpiwowar/7-data-citation-challenges-illustrated-with-data-includes-elephants"

    
