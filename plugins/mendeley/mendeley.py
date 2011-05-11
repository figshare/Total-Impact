#!/usr/bin/env python

import urllib2
import re
import urllib
import json

from optparse import OptionParser

TOTALIMPACT_MENDELEY_KEY = "3a81767f6212797750ef228c8cb466bc04dca4ba1"
MENDELEY_DOI_URL = "http://www.mendeley.com/oapi/documents/details/%s?type=doi&consumer_key=" + TOTALIMPACT_MENDELEY_KEY

def get_mendeley_page(doi):
    if not doi:
        return(None)
    # Mendeley API required double encoded doi
    double_encoded_doi = urllib.quote(urllib.quote(doi, safe=""), safe="")
    query_url = MENDELEY_DOI_URL % double_encoded_doi
    print query_url
    try:
        page = urllib2.urlopen(query_url).read()
    except urllib2.HTTPError, err:
        if err.code == 404:
            page = None
        else:
            raise    
    return(page)  

def get_stats(page):
    json_page = json.loads(page)
    if not page:
        return(None)
    try:
        number_readers = json_page["stats"]["readers"]
        title = json_page["title"]
        publication_outlet = json_page["publication_outlet"]
        year = json_page["year"]
        authors = ", ".join([author["surname"] for author in json_page["authors"]])
    except ValueError:
        return(None)
    response = {"number_readers":number_readers, "title":title, "publication_outlet":publication_outlet, "year":year, "authors":authors}
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

    print options
    print args
    
    doi = args[0]
    page = get_mendeley_page(doi)
    response = get_stats(page)
    print response


if __name__ == '__main__':
    main()

#mendeley_test_doi = "10.1038/ng0411-281"
#mendeley_test_doi = "10.1371/journal.pcbi.1000361"
#mendeley_test_doi = "10.1371/journal.pmed.0040215"
#mendeley_test_doi = "10.1371/journal.pone.0000308"

#page = get_mendeley_page(mendeley_test_doi)
#response = get_stats(page)
#print response
    