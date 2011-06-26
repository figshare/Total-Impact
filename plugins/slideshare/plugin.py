#!/usr/bin/env python

# Conforms to API specified here:  https://github.com/mhahnel/Total-Impact/wiki/Plugin-requirements
from SlidesharePlugin import SlidesharePluginClass as MyPlugin

from optparse import OptionParser
import simplejson
    
# can call "python plugin.py" from command line, no args, to get sample output
def main():
    parser = OptionParser(usage="usage: %prog [options] filename",
                          version="%prog 1.0")
    (options, args) = parser.parse_args()
    if len(args) == 1:
        json_in = args[0]
    else:    
        json_in = simplejson.dumps(MyPlugin.TEST_INPUT_ALL)
        print("Didn't get any input args, so going to use sample input: ")
        print(json_in)
        print("")
    
    json_out = MyPlugin().run_plugin(json_in)
    print json_out
    return(json_out)

if __name__ == '__main__':
    main() 
            
    