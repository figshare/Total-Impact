# The Template Plugin

## Introduction

*Replace the text in this section with an introduction to your plugin, the data sources and the information provided.*
 
The template plugin is a template to use for the preparation of your own Total-Impact plugins. You should replace the text as noted in this documentation, the plugin itself, and the various test scripts. This documentation is written using Dexy which means that the documents themselves will act as a test. If you run Dexy over your local repository and everything is correctly in place then Dexy should complete running and the documentation should contain all the appropriate information.

The python plugins for Total-Impact have a simple structure with three routines. The *run_plugin* method coordinates the action of the *get_data* and *parse_data* methods. The *main* method simply handles parameter passing from the rest of the Total-Impact system.

## Imports

The python libraries required for this plugin to work are:

{{ d['template-plugin.py|idio']['imports'] }}

## The data source

*Insert into the text in this section references to your data source and the type of identifier that is used. You will need to modify the main plugin file to add the main URL and regular expression to match identifiers to. You will need to modify the file #### to set the identifier you wish to use for testing*

Our plugin obtains data from the *data-source* using a *identifier type* as the identifier. We are using *identifier* as the test identifier for this documentation.

{{ d['template-plugin.py|idio']['data-source'] }}

<!-- TODO show where the test code sets the test identifier -->

The data source returns a *page* in the following form when queried with our test identifer.

<!-- TODO show the result of running get_data with the default identifier -->

## Plugin specific methods

### run_plugin

In most cases this method will not need to be changed. This section should nonetheless be left in place in the documentation as it provides a test that the coordination is functioning.

The run_plugin method coordinates the action of the other two core methods.  The first step is to check that the identifer is of the right type for this data source. The ID_PATTERN variable is set

<!-- TODO show where the regex for the ID_PATTERN variable is set -->

The method first checks that the *identifier* it has been passed matches the pattern specified.

{{ d['template-plugin.py|idio']['check-id-pattern'] }}

<!-- TODO show actual check against the default test ID pattern? -->

The method then obtains the page as specified by calling the *get_data* method. If a page is correctly received it then parses it using the *get_stats* method and returns the response to Total-Impact core.

{{ d['template-plugin.py|idio']['get-page-parse-response'] }}

The form of the returned dictionary for this plugin is as follows.

<!-- TODO show the result of running run_plugin with default id -->
<!-- TODO need to write script? -->

### get_data_page

*In most cases this method and its documentation will not need to be changed. The only place likely to require change is the fetching of the page itself which might require encoding or some preprocessing or a more complex call. You should nonetheless leave the remainder of this section in the documentation as it provides a test of the functionality of your plugin.*

The *get_data* method is called with an identifier. The method  first checks that an id has been passed to it.

{{ d['template-plugin.py|idio']['get-data-page'] }}

The query URL is set by combining the BASE_DATA_URL with the id and an attempt is made to obtain the page. If successful the page is returned. If a 404 is received then the routine returns *None*. If there is some other error this is raised back up the request chain.

{{ d['template-plugin.py|idio']['retrieve-page-check-and-return'] }}

If functioning correctly the *get_data* function should return a *page* of the form shown here.

<!-- TODO show the page returned - need to write script separately to do this? -->

### get_stats

*This is the core specific method of your plugin and should be clearly documented as others will likely wish to build on it. The description for the template is obviously vague. Your main parsing routine should be inserted in the try: clause. Parsing errors are currently handled silently. If you wish to divide up your documentation of the parsing component of your plugin you can easily create more sections by adding additonal *### @export* markers to your code*

The *get_stats* routine receives the raw page returned by *get_data*. The parsing will be specific to the specific plugin and data source.

The method first checks that the page has been provided and if not it returns *None*.

{{ d['template-plugin.py|idio']['get-stats'] }}

The actual parsing routine is wrapped in a try clause in case of parsing problems.

{{ d['template-plugin.py|idio']['try-to-parse'] }}

The response to be sent to the total impact core is then wrapped as a Python Dictionary/JSON object and returned.

{{ d['template-plugin.py|idio']['wrap-results'] }}

The results are returned as a dictionary in the following form:

<!-- TODO show what the method returns given the default id - need to write script? -->

### main

There should not be any need to change *main*. This is included only for completeness' sake and can be removed if you haven't modified main within your plugin.

The main method accepts a command line argument from the Total-Impact core and parses the arguments. 

{{ d['template-plugin.py|idio']['main-call'] }}

As all plugins work to obtain data based on one identifier from one data source this should only ever be called with one argument which is checked. 

{{ d['template-plugin.py|idio']['only-one-arg'] }}

The run_plugin method is then called and the final response returned back to the command line where it is collected.

{{ d['template-plugin.py|idio']['set-id-and-run'] }}

Finally if the plugin is called from the command line (which is how it usually functions) then main is called.

{{ d['template-plugin.py|idio']['if-name-main'] }}
