.. rst-class:: phpdoctorst

.. role:: php(code)
	:language: php


ShortCode
=========


.. php:namespace:: ILab\Stem\UI

.. rst-class::  abstract

.. php:class:: ShortCode


	.. rst-class:: phpdoc-description
	
		| Class ShortCode\.
		
		| Represents a dashboard widget on the WordPress dashboard\.
		
	

Properties
----------

.. php:attr:: protected static context



.. php:attr:: protected static config



Methods
-------

.. rst-class:: public

	.. php:method:: public __construct(\\ILab\\Stem\\Core\\Context $context, $config=\[\])
	
		.. rst-class:: phpdoc-description
		
			| ShortCode constructor\.
			
		
		
		:Parameters:
			* **$context** (:any:`ILab\\Stem\\Core\\Context <ILab\\Stem\\Core\\Context>`)  
			* **$config** (array)  

		
	
	

.. rst-class:: public

	.. php:method:: public registerUI( $shortCode)
	
		.. rst-class:: phpdoc-description
		
			| Registers the UI for the shortcode via Shortcake plugin\.  If Shortcake isn\'t installed, this will not be called\.
			
			| Additionally, if you have the UI defined in your config for the shortcode, this won\'t be called either\.
			
		
		
		:Parameters:
			* **$shortCode** (string)  The shortcode's name as defined in ui.php configuration.

		
	
	

.. rst-class:: public abstract

	.. php:method:: public abstract render( $attrs=\[\], $content=null)
	
		.. rst-class:: phpdoc-description
		
			| Renders the shortcode\.
			
		
		
		:Returns: string 
	
	

