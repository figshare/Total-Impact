#!/usr/bin/env python

import urllib2
import re

SOURCE_URL = "http://www.icpsr.umich.edu/icpsrweb/ICPSR/studies/%s"
STATS_PATTERN = re.compile(r"View related literature</a> \((?P<stats>\d+)\)", re.DOTALL)
DEBUG = False

def get_page(doi):
    if not doi:
        return(None)
    accession = doi[-5:]
    url = SOURCE_URL % accession
    if (DEBUG):
        print url
    page = urllib2.urlopen(url).read()
    if (DEBUG):
        print page
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
    return(stats)  
        
test_input = "10.3886/ICPSR01225"
page = get_page(test_input)
stats = get_stats(page)
print stats
