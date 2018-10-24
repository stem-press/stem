.. rst-class:: phpdoctorst

.. role:: php(code)
	:language: php


ErrorController
===============


.. php:namespace:: ILab\Stem\Controllers

.. php:class:: ErrorController


	:Parent:
		:php:class:`ILab\\Stem\\Core\\Controller`
	

Properties
----------

.. php:attr:: protected exception

	.. rst-class:: phpdoc-description
	
		| The current exception\.
		
	
	:Type: :any:`\\Exception <Exception>` 


.. php:attr:: protected statusCode



Methods
-------

.. rst-class:: public

	.. php:method:: public getIndex(\\Symfony\\Component\\HttpFoundation\\Request $request)
	
		
	
	

.. rst-class:: public static

	.. php:method:: public static setCurrentError( $statusCode, $exception=null)
	
		
	
	

.. rst-class:: public static

	.. php:method:: public static currentStatusCode()
	
		
	
	

.. rst-class:: public static

	.. php:method:: public static currentException()
	
		
	
	

