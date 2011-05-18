#!/usr/bin/env python

import urllib2
import re
import urllib
import time
import string
import hashlib
import BeautifulSoup
from BeautifulSoup import BeautifulStoneSoup 

from optparse import OptionParser

TOTALIMPACT_SLIDESHARE_KEY = "nyHCUoNM"
TOTALIMPACT_SLIDESHARE_SECRET = "z7sRiGCG"
SLIDESHARE_DOI_URL = "http://www.slideshare.net/api/2/get_slideshow?api_key=nyHCUoNM&detailed=1&ts=%s&hash=%s&slideshow_url=%s"

URL_PATTERN = re.compile("http://www.slideshare.net/.+")

def run_plugin(id):
    if not URL_PATTERN.search(id):
        return(None)
        
    page = get_page(id)
    if page:
        response = get_stats(page)
    else:
        response = None
    return(response)

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
    print(soup)
    try:
        downloads = int(soup.numdownloads.text)
    except:
        downloads = None
        
    try:
        views = int(soup.numviews.text)
    except:
        views = None
        
    try:    
        comments = int(soup.numcomments.text)
    except:
        comments = None
        
    try:
        favorites = int(soup.numfavorites.text)
    except:
        favorites = None

    try:
        title = soup.title.text
        if title:
            title = title.encode("latin1")
            for punc in [":", ","]:
                title = title.replace(punc, "-")        
    except:
        title = None
        
    response = {"downloads":downloads, "views":views, "comments":comments, "favorites":favorites, "TITLE":title}
    return(response)  
        

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

#example = "http://www.slideshare.net/hpiwowar/7-data-citation-challenges-illustrated-with-data-includes-elephants"

    
