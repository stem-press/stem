.. rst-class:: phpdoctorst

.. role:: php(code)
	:language: php


EditorPlugin
============


.. php:namespace:: ILab\Stem\UI

.. rst-class::  abstract

.. php:class:: EditorPlugin


	.. rst-class:: phpdoc-description
	
		| Class EditorPlugin\.
		
		| Wraps a plugin for the TinyMCE editor\.
		
	

Properties
----------

.. php:attr:: protected static context



.. php:attr:: protected static config



Methods
-------

.. rst-class:: public

	.. php:method:: public __construct(\\ILab\\Stem\\Core\\Context $context, $config=\[\])
	
		.. rst-class:: phpdoc-description
		
			| EditorPlugin constructor\.
			
		
		
		:Parameters:
			* **$context** (:any:`ILab\\Stem\\Core\\Context <ILab\\Stem\\Core\\Context>`)  
			* **$config** (array)  

		
	
	

.. rst-class:: public abstract

	.. php:method:: public abstract identifier()
	
		.. rst-class:: phpdoc-description
		
			| Returns the identifier for the plugin\.
			
		
		
		:Returns: string 
	
	

.. rst-class:: public

	.. php:method:: public styles()
	
		.. rst-class:: phpdoc-description
		
			| Returns a string or array of CSS stylesheet URLs to enqueue\.
			
		
		
		:Returns: string | array | null 
	
	

.. rst-class:: public

	.. php:method:: public scripts()
	
		.. rst-class:: phpdoc-description
		
			| Returns a string or array of script URLs to enqueue\.
			
		
		
		:Returns: array | string | null 
	
	

.. rst-class:: public

	.. php:method:: public buttons()
	
		.. rst-class:: phpdoc-description
		
			| Array of buttons to add to the editor UI\.
			
		
		
		:Returns: array 
	
	

.. rst-class:: public

	.. php:method:: public onBeforeInit( $mceSettings)
	
		.. rst-class:: phpdoc-description
		
			| This is triggered before the TinyMCE editor settings are output to the client\.
			
		
		
		:Parameters:
			* **$mceSettings**  The TinyMCE settings

		
	
	

.. rst-class:: public

	.. php:method:: public onInit( $mceSettings)
	
		.. rst-class:: phpdoc-description
		
			| This is triggered after the TinyMCE js is loaded, but before any editors are created\.
			
		
		
		:Parameters:
			* **$mceSettings**  The TinyMCE settings

		
	
	

.. rst-class:: public

	.. php:method:: public onAfterInit( $mceSettings)
	
		.. rst-class:: phpdoc-description
		
			| This is triggered after the TinyMCE editor settings are output to the client\.
			
		
		
		:Parameters:
			* **$mceSettings**  The TinyMCE settings

		
	
	

