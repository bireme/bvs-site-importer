BVS Site Importer - 2011
========================
 
This script imports the XML data of a BVS-Site 5.x to a XML_RPC structure that 
can be later imported into a Wordpress site customized with both BVS-Site and
Multi Language Framework plugins.

Instructions:
* Set the path to the XML BVS-Site directory in the __$XML_DIRECTORY__
* Set the language you want to import in the __$LANGUAGE__ variable (in BVS-Site
  instances with more than one language active the process should be done for each 
  language)
* For each language choose either the default site for the default language and
  the appropriate subdir site for the other languages

What sort of information is converted:
* All items of __collection__, __community__ and __about__ types are converted as follows:
  * if the item is __available__ (enabled) the content is converted as __draft__;
  * if the item is __unavailable__ (disabled) the content is converted as __trash__.
* The __highlights__ type is converted to an item without title as stated above.
* The __RSS__ and __HTML__ types are not converted.

More information available at:
* http://github.com/bireme/bvs-site-wp-plugin
* http://github.com/bireme/wp-multi-language-framework
