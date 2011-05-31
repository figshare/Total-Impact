#!/usr/bin/env python

### @export "imports"
import urllib2
import re
import urllib
from optparse import OptionParser
# import any libraries you need for scraping/obtaining data here

### @export "data-source"
# Replace these with the URL for your data source and an ID pattern
# in the form of a regular expression that can be used to match the
# ID's to one the plugin can parse
# TODO - need a test check URL for the template plugin
BASE_DATA_URL = "http://urlforyourdatasource.com/%s"
ID_PATTERN = re.compile("REGEX-FOR-CHECK", re.DOTALL | re.IGNORECASE)

### @export "run-plugin"
def run_plugin(id):
    """Central coordination routine for the plugin"""

    ### @export "check-id-pattern"
    if not ID_PATTERN.search(id):
        return(None)

    ### @export "get-page-parse-response"
    page = get_data_page(id)
    if page:
        response = get_stats(page)
    else:
        response = None
    return(response)

### @export "get-data-page"
def get_data_page(id):
    """Obtain page from the data source for the item identified by id"""

    if not id:
        return(None)

    ### @export "retrieve-page-check-and-return"
    # You may need to modify this line if your target service requires
    # double encoding a more complex http request.
    query_url = BASE_DATA_URL % id

    try:
        page = urllib2.urlopen(query_url)
    except urllib2.HTTPError, err:
        if err.code == 404:
            page = None
        else:
            raise    
    return(page)  

### @export "get-stats"
def get_stats(page):
    """Routine for getting the desired data out of the returned page"""

    if not page:
        return(None)

    ### @export "try-to-parse"
    try:
        # Process the page to obtain the desired data. This might involve 
        # any form of parsing, scraping, or whatever is required. It's polite 
        # to wrap this in a try clause in case something goes wrong
        parameter = 1
        parameter2 = "great"

    except:
        return(None)


    ### @export "wrap-results"
    response = {"parameter"  : parameter,
                "parameter2" : parameter2}

    return(response)

### @export "main-call"
def main():
    parser = OptionParser(usage="usage: %prog [options] filename",
                          version="%prog 1.0")
    (options, args) = parser.parse_args()

    ### @export "only-one-arg"
    if len(args) != 1:
        parser.error("wrong number of arguments")

    ### @export "set-id-and-run"
    id = args[0]

    response = run_plugin(id)
    return(response)

### @export "if-name-main"
if __name__ == '__main__':
    main()

