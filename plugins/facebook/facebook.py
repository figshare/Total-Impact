#!/usr/bin/env python

import urllib2
import re
import BeautifulSoup
from BeautifulSoup import BeautifulStoneSoup 

DOI_LOOKUP_URL = "http://dx.doi.org/%s"

SOURCE_URL = "http://api.facebook.com/restserver.php?method=links.getStats&urls=%s"
DEBUG = True

def get_redirect_url(doi):
    if not doi:
        return(None)
    doi_url = DOI_LOOKUP_URL % doi
    doi_redirect_url = urllib2.urlopen(doi_url).url
    
    if (DEBUG):
        print doi_url
        print doi_redirect_url
    
    return(doi_redirect_url)    

def get_page(lookup):
    if not lookup:
        return(None)
    url = SOURCE_URL % lookup
    if (DEBUG):
        print url
    page = urllib2.urlopen(url).read()
    if (DEBUG):
        print page
    return(page) 
    
def get_stats(page):
    if not page:
        return(None)
    soup = BeautifulStoneSoup(page)
    like_count = soup.like_count.text
    share_count = soup.share_count.text
    click_count = soup.click_count.text
    comment_count = soup.comment_count.text
    stats = {"like count":like_count, "share count":share_count, "click_count":click_count, "comment_count":comment_count}
    return(stats)  
        
#test_input = "10.1371/journal.pcbi.1000361"
#test_input = "10.1371/journal.pmed.0040215"
test_input = "10.1371/journal.pone.0000308"

redirect_url = get_redirect_url(test_input)
page = get_page(redirect_url)
stats = get_stats(page)
print stats
