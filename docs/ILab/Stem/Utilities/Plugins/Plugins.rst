.. rst-class:: phpdoctorst

.. role:: php(code)
	:language: php


Plugins
=======


.. php:namespace:: ILab\Stem\Utilities\Plugins

.. php:class:: Plugins


	.. rst-class:: phpdoc-description
	
		| Handles formatting and storing suggested
		| plugins\.
		
	

Properties
----------

Methods
-------

.. rst-class:: public

	.. php:method:: public __construct( $pre_plugins, $manager)
	
		.. rst-class:: phpdoc-description
		
			| Class constructor\.
			
		
		
		:Parameters:
			* **$pre_plugins** (array)  Suggested plugins to format.
			* **$manager** (:any:`ILab\\Stem\\Utilities\\Plugins\\PluginManager <ILab\\Stem\\Utilities\\Plugins\\PluginManager>`)  Plugin manager object.

		
	
	

.. rst-class:: public

	.. php:method:: public set()
	
		.. rst-class:: phpdoc-description
		
			| Consistently format plugins and add some
			| helpful info\.
			
		
		
		:Since: 1.0.0 
	
	

.. rst-class:: public

	.. php:method:: public is_set()
	
		.. rst-class:: phpdoc-description
		
			| Check if plugins have been setup and formatted\.
			
		
		
		:Since: 1.0.0 
		:Returns: bool If $plugins has been properly setup\.
	
	

.. rst-class:: public

	.. php:method:: public add( $plugin, $info=null)
	
		.. rst-class:: phpdoc-description
		
			| Format and store plugin to $this\-\>plugins\.
			
			| The "status" of a plugin can be one of the following:
			| 
			| 1\. \`not\-installed\` It\'s simply not installed\.
			| 2\. \`inactive\`      Installed, but not activated yet\.
			| 3\. \`incompatible\`  Activated, but installed version is less than suggested version\.
			| 4\. \`active\`        Installed, activated and compatible\.
			| 
			| Note: The \`incompatible\` status will only be applied
			| if the current version is less than the suggsted version;
			| it will NOT be applied simply because WordPress says
			| the plugin has an update available\.
			| 
			| The final stored plugin data will be an array formatted,
			| as follows\.
			| 
			| $plugin \{
			
		
		
		:Parameters:
			* **$plugin** (array)  {
			    Plugin info from initial object creation.
			
			    @type string $name    Name of plugin, like `My Plugin`.
			    @type string $slug    Slug of plugin, like `my-plugin`.
			    @type string $url     URL to plugin website, ONLY if not on wordpress.org.
			    @type string $version Suggested plugin version, like `2.0+`.
			}
			* **$info** (array)  {
			    Optional. Plugin info from WP, if installed.
			
			    @type string $file            Location of plugin file.
			    @type string $current_version Current installed version.
			    @type string $new_version     Newest version available.
			    @type bool   $is_active       Whether plugin is active.
			}

		
		:Since: 1.0.0 
	
	

.. rst-class:: public

	.. php:method:: public get( $slug="")
	
		.. rst-class:: phpdoc-description
		
			| Get data for a plugin, or data for all plugins\.
			
		
		
		:Parameters:
			* **$slug** (string)  Optional. Slug of plugin to retrieve data for. Leave empty for all plugins.

		
		:Since: 1.0.0 
		:Returns: array | bool Data for all plugins, data for single plugin, or \`false\` if single plugin doesn\'t exist\.
	
	

.. rst-class:: public

	.. php:method:: public get_installed()
	
		.. rst-class:: phpdoc-description
		
			| Match installed plugins from WordPress against
			| our suggested plugins, to return information
			| for our suggested plugins, wnich are currently
			| installed\.
			
		
		
		:Since: 1.0.0 
		:Returns: array $installed Installed plugin that are suggested\.
	
	

