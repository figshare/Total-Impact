#!/usr/bin/python -u
print "Content-Type:text/html\r\n"

# Conforms to API specified here:  https://github.com/mhahnel/Total-Impact/wiki/Plugin-requirements
import os
from optparse import OptionParser
import simplejson
import cgi, cgitb 
import Plugin

# to see in browser, call index.cgi?query={"10.1371\/journal.pbio.0060034":{"doi":false,"url":false,"pmid":false},"10.1371\/journal.pbio.0050072":{"doi":false,"url":false,"pmid":false}}     
def get_param_from_http_request():
    # Create instance of FieldStorage 
    form = cgi.FieldStorage() 

    # Get data from fields
    json_in = form.getvalue('query')
    return(json_in)

# can call "python index.cgi" from command line, no args, to get sample output
# or with custom input, call the argument from browser with single quote around it, like this:
# python index.cgi '{"10.1371\/journal.pbio.0060034":{"doi":false,"url":false,"pmid":false},"10.1371\/journal.pbio.0050072":{"doi":false,"url":false,"pmid":false}}'
def get_param_from_command_line():
    parser = OptionParser(usage="usage: %prog [options] filename",
                          version="%prog 1.0")
    (options, args) = parser.parse_args()
    if len(args) == 1:
        json_in = args[0]
    else:    
        json_in = None
    return(json_in)

#### Determine if being called from a browser or command line
if 'REQUEST_METHOD' in os.environ:
    json_in = get_param_from_http_request()
    verbose = False
else:
    json_in = get_param_from_command_line()
    verbose = True
    
# if no arg, then use sample input    
if (not json_in):
    json_in = simplejson.dumps(Plugin.PluginClass.TEST_INPUT_ALL)
    if (verbose):
        print("Didn't get any input args, so going to use sample input: <p><p>\r\n")
        print(json_in)
        print("<p><p>\r\n")
        print("Output:<p>\r\n")    
    
# get the output!
json_out = Plugin.PluginClass().run_plugin(json_in)
print json_out
   
            
    