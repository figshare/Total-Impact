#Plugins are independent and encapsulated in their subdirectories 
#  (though they may use shared code in the plugins dir, to reduce duplication)
#Plugins need to meet the API described at https://github.com/mhahnel/Total-Impact/wiki/Plugin-requirements

#Many of these plugins will return sample input and output test by calling the plugins with no arguments.  
#Those implemented in python can be run en masse with this command:

for p in ./*/index.cgi; do python "$p"; done

