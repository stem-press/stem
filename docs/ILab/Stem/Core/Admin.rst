.. rst-class:: phpdoctorst

.. role:: php(code)
	:language: php


Admin
=====


.. php:namespace:: ILab\Stem\Core

.. php:class:: Admin


	.. rst-class:: phpdoc-description
	
		| Class Admin\.
		
		| This class process the admin configuration, adjusting the WordPress admin\.
		
	

Properties
----------

.. php:attr:: protected static context

	.. rst-class:: phpdoc-description
	
		| Current context\.
		
	
	:Type: :any:`\\ILab\\Stem\\Core\\Context <ILab\\Stem\\Core\\Context>` 


.. php:attr:: public static config

	.. rst-class:: phpdoc-description
	
		| Admin configuration\.
		
	
	:Type: array 


Methods
-------

.. rst-class:: public

	.. php:method:: public __construct(\\ILab\\Stem\\Core\\Context $context)
	
		.. rst-class:: phpdoc-description
		
			| Constructor\.
			
		
		
		:Parameters:
			* **$context**  Context The current context

		
	
	

.. rst-class:: protected

	.. php:method:: protected setup()
	
		.. rst-class:: phpdoc-description
		
			| Performs basic setup\.
			
		
		
	
	

.. rst-class:: protected

	.. php:method:: protected configureAdminBar()
	
		
	
	

.. rst-class:: protected

	.. php:method:: protected configureFooter()
	
		
	
	

.. rst-class:: protected

	.. php:method:: protected configureCustomization()
	
		
	
	

.. rst-class:: protected

	.. php:method:: protected configureWidgets()
	
		.. rst-class:: phpdoc-description
		
			| Configures dashboard widget\.
			
		
		
	
	

.. rst-class:: public

	.. php:method:: public setting( $settingPath, $default=false)
	
		.. rst-class:: phpdoc-description
		
			| Returns a setting using a path string, eg \'options/views/engine\'\.  Consider this
			| a poor man\'s xpath\.
			
		
		
		:Parameters:
			* **$settingPath**  The "path" in the config settings to look up.
			* **$default** (bool | mixed)  The default value to return if the settings doesn't exist.

		
		:Returns: bool | mixed The result
	
	

