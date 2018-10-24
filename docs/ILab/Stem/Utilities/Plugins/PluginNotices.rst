.. rst-class:: phpdoctorst

.. role:: php(code)
	:language: php


PluginNotices
=============


.. php:namespace:: ILab\Stem\Utilities\Plugins

.. php:class:: PluginNotices


	.. rst-class:: phpdoc-description
	
		| Handles important user notices across the
		| entire admin about suggested plugins\.
		
	

Properties
----------

Methods
-------

.. rst-class:: public

	.. php:method:: public __construct( $args, $manager, $plugins)
	
		.. rst-class:: phpdoc-description
		
			| Class constructor\.
			
		
		
		:Parameters:
			* **$args** (array)  {
			    Arguments required for object.
			
			    @type string $admin_url            URL to plugin manager.
			    @type string $package_url          URL to drop-in package.
			    @type string $nag_action           Text to lead user to manage suggested plugins.
			    @type string $nag_dismiss          Text to dismiss admin notice.
			    @type string $nag_update           Text to tell user there are incompatible plugins that need updating.
			    @type string $nag_install_single   Text to tell user there is a suggested plugins to install.
			    @type string $nag_install_multiple Text to tell user there are suggested plugins to install.
			}
			* **$manager** (:any:`ILab\\Stem\\Utilities\\Plugins\\PluginManager <ILab\\Stem\\Utilities\\Plugins\\PluginManager>`)  Plugin manager object.
			* **$plugins** (:any:`ILab\\Stem\\Utilities\\Plugins\\Plugins <ILab\\Stem\\Utilities\\Plugins\\Plugins>`)  Plugins object.

		
	
	

.. rst-class:: public

	.. php:method:: public set_notices()
	
		.. rst-class:: phpdoc-description
		
			| Sets admin plugin notices, which are seen
			| throughout the entire admin\.
			
		
		
		:Since: 1.0.0 
	
	

.. rst-class:: public

	.. php:method:: public add_assets()
	
		.. rst-class:: phpdoc-description
		
			| Add any assets needed for plugin notices\.
			
		
		
		:Since: 1.0.0 
	
	

.. rst-class:: public

	.. php:method:: public add_notices()
	
		.. rst-class:: phpdoc-description
		
			| Handles admin plugin notices, which are seen
			| throughout the entire admin\.
			
		
		
		:Since: 1.0.0 
	
	

.. rst-class:: public

	.. php:method:: public display( $key, $value, $message)
	
		.. rst-class:: phpdoc-description
		
			| Display custom admin notice\.
			
		
		
		:Since: 1.0.0 
	
	

.. rst-class:: public

	.. php:method:: public dismiss()
	
		.. rst-class:: phpdoc-description
		
			| Dimiss admin notices via Ajax\.
			
		
		
		:Since: 1.0.0 
	
	

