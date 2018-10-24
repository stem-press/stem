.. rst-class:: phpdoctorst

.. role:: php(code)
	:language: php


Dispatcher
==========


.. php:namespace:: ILab\Stem\Core

.. php:class:: Dispatcher


	.. rst-class:: phpdoc-description
	
		| Dispatcher Context\.
		
		| This class dispatches requests to the appropriate controller or template
		
	

Properties
----------

Methods
-------

.. rst-class:: public

	.. php:method:: public __construct(\\ILab\\Stem\\Core\\Context $context)
	
		.. rst-class:: phpdoc-description
		
			| Constructor\.
			
		
		
		:Parameters:
			* **$context** (:any:`ILab\\Stem\\Core\\Context <ILab\\Stem\\Core\\Context>`)  

		
	
	

.. rst-class:: public

	.. php:method:: public dispatch()
	
		.. rst-class:: phpdoc-description
		
			| Dispatches the current exception\.
			
		
		
		:Throws: :any:`\\Exception <Exception>` 
	
	

.. rst-class:: public

	.. php:method:: public dispatchError( $statusCode, $exception)
	
		
	
	

