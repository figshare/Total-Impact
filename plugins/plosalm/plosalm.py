#!/usr/bin/env python

import urllib2
import re
import urllib
import BeautifulSoup
from BeautifulSoup import BeautifulStoneSoup 
from optparse import OptionParser

PLOS_ALM_COUNTER_URL = "http://www.plosreports.org/services/rest?method=usage.stats&doi=%s"

PLOS_DOI_PATTERN = re.compile("(10.(\d)+/journal.p(\S)+)", re.DOTALL | re.IGNORECASE)

def run_plugin(doi):
    # Right now this is only designed to look up dois
    if not PLOS_DOI_PATTERN.search(doi):
        return(None)
    page = get_plos_alms(doi)
    if page:
        response = get_stats(page)
    else:
        response = None
    return(response)


def get_plos_alms(doi):
    if not doi:
        return(None)

    query_url = PLOS_ALM_COUNTER_URL % doi
    # print query_url
    try:
        alm_xml = urllib2.urlopen(query_url).read()
    except urllib2.HTTPError, err:
        if err.code == 404:
            page = None
        else:
            raise    
    return(alm_xml)  

def get_stats(alm_xml):
    
    soup = BeautifulStoneSoup(alm_xml)
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
    print response
    return(response)  
        

from optparse import OptionParser

def main():
    parser = OptionParser(usage="usage: %prog [options] filename",
                          version="%prog 1.0")
    (options, args) = parser.parse_args()

    if len(args) != 1:
        parser.error("wrong number of arguments")

    doi = args[0]
    
    response = run_plugin(doi)
    #print response
    return(response)


if __name__ == '__main__':
    main()

#test_doi = "10.1038/ng0411-281"
#est_doi = "10.1371/journal.pcbi.1000361"
#test_doi = "10.1371/journal.pmed.0040215"
#test_doi = "10.1371/journal.pone.0000308"
   
