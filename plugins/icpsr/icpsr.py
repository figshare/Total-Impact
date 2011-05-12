#!/usr/bin/env python

import urllib2
import re
from optparse import OptionParser

SOURCE_URL = "http://www.icpsr.umich.edu/icpsrweb/ICPSR/studies/%s"
STATS_PATTERN = re.compile(r"View related literature</a> \((?P<stats>\d+)\)", re.DOTALL)
DEBUG = False

ICPSR_DOI_PATTERN = re.compile("(10.(\d)+/icpsr(\S)+)", re.DOTALL | re.IGNORECASE)

def run_plugin(doi):
    # Right now this is only designed to look up dois
    if not ICPSR_DOI_PATTERN.search(doi):
        return(None)

    page = get_page(id)
    if page:
        response = get_stats(page)
    else:
        response = None
    return(response)
    
def get_page(doi):
    if not doi:
        return(None)
    accession = doi[-5:]
    url = SOURCE_URL % accession
    #if (DEBUG):
    #    print url
    page = urllib2.urlopen(url).read()
    #if (DEBUG):
    #    print page
    return(page)  

def get_stats(page):
    if not page:
        return(None)
    stats_matches = STATS_PATTERN.search(page)
    if not stats_matches:
        return(None)
    try:
        stats = float(stats_matches.group("stats"))
    except ValueError:
        return(None)
    return({"related_refs": stats})  

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
            
#test_input = "10.3886/ICPSR01225"
#page = get_page(test_input)
#stats = get_stats(page)
#print stats
