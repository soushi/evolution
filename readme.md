##Modification to Evolution 1.0.6
===
##Changelog

###08/04/2012

* Began update of sample site
* Replaced WebLogin snippets with WebLoginPE version 1.3.2b. This version is an improved version that offers the option of included external config files, and has the language array keys as informative text rather than numbers. For example, $wlpe_lang['username_used'] instead of $wlpe_lang[14].

###02/04/2012

* Fixed bug in ManagerManager when using manual setting for jQuery location [View](https://github.com/sottwell/evolution/commit/a13ffa16a296e16ae320571c38c313f27f6e3871) 
* Moved Template Variables to its own tab [View](https://github.com/sottwell/evolution/commit/1a2c0546c93207b33dcc1138f820952825c22e76)


###31/03/2012 

* Updated master to 1.0.6
* Modified Template Editing page to not show assigned TVs if there are none [View](https://github.com/sottwell/evolution/commit/da99fa130ef80eeac838e9cf435c78993eba337e)
* Began creating a new default sample template and sample site based on HTML 5 [View](https://github.com/sottwell/evolution/commit/af8abf80d60813d8ff7896bb9b32257bd9b04de0)

###Previous Modifications:

* Modify manager/index.php line 170 to check for SESSION variable and HTTP_REFERER to permit QM+ access when user is not allowed Manager access
* Modify qm.inc.php line 55 to set a SESSION variable to enable QM+ access
* Improved manager lockout display
* Moved Manager templates from assets/templates/manager to manager/media/style/themeName/templates/
* Updated ManagerManager plugin to 0.3.11
* Updated TinyMCE to 3.5b2
