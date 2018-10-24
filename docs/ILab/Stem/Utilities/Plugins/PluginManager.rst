.. rst-class:: phpdoctorst

.. role:: php(code)
	:language: php


PluginManager
=============


.. php:namespace:: ILab\Stem\Utilities\Plugins

.. php:class:: PluginManager


	.. rst-class:: phpdoc-description
	
		| Plugin manager class\.
		
		| This class is responsible for the following:
		| 
		| 1\. Sets up the admin page up to manage the
		| plugins recommended by the package\.
		| 
		| 2\. Handles installation, updating, activating,
		| and deactivating of suggested plugins, using
		| primary built\-in WordPress functionality\.
		| 
		| 3\. Includes the Stem\_Plugins class and instantiates
		| the object for holding suggested plugins\.
		| 
		| 4\. Includes the Stem\_Plugin\_Notices class and
		| instantiates the object to display notices to the
		| user across all admin pages, EXCEPT the one we
		| added by Stem\_Plugin\_Manager\.
		| 
		| 5\. Adds link to our admin page from the standard
		| \`Plugins\` screen\.
		| 
		| 6\. Adds link to our admin page from the
		| \`Plugins \> Add New\` screen\.
		
	

Properties
----------

Methods
-------

.. rst-class:: public

	.. php:method:: public __construct( $plugins, $args=array\(\))
	
		.. rst-class:: phpdoc-description
		
			| Class constructor\.
			
		
		
		:Parameters:
			* **$plugins** (array)  Initial, unformatted suggested plugins.
			* **$args** (array)  {
			    Optional. Overriding class options.
			
			    @type string $page_title     Title for admin page.
			    @type string $views_title    Title used for subtle link on Plugins page.
			    @type string $extended_title A more descriptive title.
			    @type string $tab_title      Title for plugin installer tab.
			    @type string $menu_title     Title for admin sidebar menu.
			    @type string $menu_slug      Slug used for admin page URL.
			    @type string $capability     User capability for accessing admin page.
			}

		
		:Since: 1.0.0 
	
	

.. rst-class:: public

	.. php:method:: public add_page()
	
		.. rst-class:: phpdoc-description
		
			| Add the suggsted plugin admin page to manage
			| plugins\.
			
		
		
		:Since: 1.0.0 
	
	

.. rst-class:: public

	.. php:method:: public add_assets()
	
		.. rst-class:: phpdoc-description
		
			| Add any CSS or JavaScript to plugin manager
			| admin page\.
			
		
		
		:Since: 1.0.0 
	
	

.. rst-class:: public

	.. php:method:: public display_page()
	
		.. rst-class:: phpdoc-description
		
			| Display the suggsted plugin admin page to
			| manage plugins\.
			
		
		
		:Since: 1.0.0 
	
	

.. rst-class:: public

	.. php:method:: public add_plugins_view( $views)
	
		.. rst-class:: phpdoc-description
		
			| Adds a link to Plugins screen that links
			| to our plugin manager admin page\.
			
		
		
		:Since: 1.0.0 
	
	

.. rst-class:: public

	.. php:method:: public add_install_view( $tabs)
	
		.. rst-class:: phpdoc-description
		
			| Add tab to Plugin Installer screen that links
			| to our plugin manager admin page\.
			
		
		
		:Since: 1.0.0 
	
	

.. rst-class:: public

	.. php:method:: public get_admin_url()
	
		.. rst-class:: phpdoc-description
		
			| Get the URL to the admin page to manage plugins\.
			
		
		
		:Since: 1.0.0 
		:Returns: string URL to plugin\-manager admin page\.
	
	

.. rst-class:: public

	.. php:method:: public get_plugin_source( $plugin)
	
		.. rst-class:: phpdoc-description
		
			| Get source to display for a plugin\.
			
		
		
		:Parameters:
			* **$plugin** (array)  Plugin data.

		
		:Since: 1.0.0 
		:Returns: string Plugin source, \`wordpress\.org\` or \`third\-party\`\.
	
	

.. rst-class:: public

	.. php:method:: public is_admin_screen()
	
		.. rst-class:: phpdoc-description
		
			| Check whether we\'re currently on the plugin
			| manager admin page or not\.
			
		
		
		:Since: 1.0.0 
		:Returns: bool If plugin\-manager admin page\.
	
	

.. rst-class:: public

	.. php:method:: public row_refresh()
	
		.. rst-class:: phpdoc-description
		
			| Handle Ajax request when a plugin\'s status has
			| changed and its table row needs to be refreshed\.
			
		
		
		:Since: 1.0.0 
	
	

.. rst-class:: public

	.. php:method:: public request()
	
		.. rst-class:: phpdoc-description
		
			| Handles any non\-Ajax request\.
			
			| We use this for activating and deactivating plugins
			| because these actions require that the WordPress
			| admin is refreshed\. So handling them through Ajax
			| is a bit unnecessary\.
			| 
			| This method handles both the activation and
			| deactivation processes, along with error message
			| handling and success message on redirect\.
			| 
			| Also, single plugin and bulk processing is supported\.
			
		
		
		:Since: 1.0.0 
	
	

