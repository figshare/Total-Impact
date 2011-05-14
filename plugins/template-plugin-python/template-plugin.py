#!/usr/bin/env python

import urllib2
import re
import urllib
from optparse import OptionParser
# import parser libraries you need for scraping/obtaining data 

BASE_DATA_URL = "http://urlforyourdatasource.com/%s"
ID_PATTERN = re.compile("REGEX-FOR-CHECK", re.DOTALL | re.IGNORECASE)

def run_plugin(id):
    """Central coordination routine for the plugin"""

    # Check whether ID matches the regex for IDs from this data source
    if not ID_PATTERN.search(id):
        return(None)
    page = get_data_from_source(id)
    if page:
        response = get_stats(page)
    else:
        response = None
    return(response)


def get_data_from_source(id):
    """Obtain page from the data source for the item identified by id"""

    if not id:
        return(None)

    query_url = BASE_DATA_URL % id

    try:
        page = urllib2.urlopen(query_url)
    except urllib2.HTTPError, err:
        if err.code == 404:
            page = None
        else:
            raise    
    return(page)  

def get_stats(page):
    """Routine for getting the desired data out of the returned page"""

    if not page:
        return(None)

    try:
        # Process the page to obtain the desired data. This might involve 
        # any formof parsing, scraping, or whatever is required. It's polite 
        # to wrap this in a try clause in case something goes wrong

    except:
        parameter = None


    # The response is wrapped as a dictionary or JSON object
    response = {"parameter"  : parameter,
                "parameter2" : paramter2}

    return(response)  
        



def main():
    parser = OptionParser(usage="usage: %prog [options] filename",
                          version="%prog 1.0")
    (options, args) = parser.parse_args()

    if len(args) != 1:
        parser.error("wrong number of arguments")

    id = args[0]
    
    response = run_plugin(id)
    return(response)

if __name__ == '__main__':
    main()

