.. rst-class:: phpdoctorst

.. role:: php(code)
	:language: php


Log
===


.. php:namespace:: ILab\Stem\Core

.. rst-class::  final

.. php:class:: Log


	.. rst-class:: phpdoc-description
	
		| Class View\.
		
		| Base class for rendering views
		
	

Properties
----------

Methods
-------

.. rst-class:: public static

	.. php:method:: public static initialize( $config=null)
	
		
	
	

.. rst-class:: public static

	.. php:method:: public static instance()
	
		
	
	

.. rst-class:: public static

	.. php:method:: public static emergency( $message, array $context=\[\])
	
		.. rst-class:: phpdoc-description
		
			| System is unusable\.
			
		
		
		:Parameters:
			* **$message** (string)  
			* **$context** (array)  

		
		:Returns: null 
	
	

.. rst-class:: public static

	.. php:method:: public static alert( $message, array $context=\[\])
	
		.. rst-class:: phpdoc-description
		
			| Action must be taken immediately\.
			
			| Example: Entire website down, database unavailable, etc\. This should
			| trigger the SMS alerts and wake you up\.
			
		
		
		:Parameters:
			* **$message** (string)  
			* **$context** (array)  

		
		:Returns: null 
	
	

.. rst-class:: public static

	.. php:method:: public static critical( $message, array $context=\[\])
	
		.. rst-class:: phpdoc-description
		
			| Critical conditions\.
			
			| Example: Application component unavailable, unexpected exception\.
			
		
		
		:Parameters:
			* **$message** (string)  
			* **$context** (array)  

		
		:Returns: null 
	
	

.. rst-class:: public static

	.. php:method:: public static error( $message, array $context=\[\])
	
		.. rst-class:: phpdoc-description
		
			| Runtime errors that do not require immediate action but should typically
			| be logged and monitored\.
			
		
		
		:Parameters:
			* **$message** (string)  
			* **$context** (array)  

		
		:Returns: null 
	
	

.. rst-class:: public static

	.. php:method:: public static warning( $message, array $context=\[\])
	
		.. rst-class:: phpdoc-description
		
			| Exceptional occurrences that are not errors\.
			
			| Example: Use of deprecated APIs, poor use of an API, undesirable things
			| that are not necessarily wrong\.
			
		
		
		:Parameters:
			* **$message** (string)  
			* **$context** (array)  

		
		:Returns: null 
	
	

.. rst-class:: public static

	.. php:method:: public static notice( $message, array $context=\[\])
	
		.. rst-class:: phpdoc-description
		
			| Normal but significant events\.
			
		
		
		:Parameters:
			* **$message** (string)  
			* **$context** (array)  

		
		:Returns: null 
	
	

.. rst-class:: public static

	.. php:method:: public static info( $message, array $context=\[\])
	
		.. rst-class:: phpdoc-description
		
			| Interesting events\.
			
			| Example: User logs in, SQL logs\.
			
		
		
		:Parameters:
			* **$message** (string)  
			* **$context** (array)  

		
		:Returns: null 
	
	

.. rst-class:: public static

	.. php:method:: public static debug( $message, array $context=\[\])
	
		.. rst-class:: phpdoc-description
		
			| Detailed debug information\.
			
		
		
		:Parameters:
			* **$message** (string)  
			* **$context** (array)  

		
		:Returns: null 
	
	

.. rst-class:: public static

	.. php:method:: public static log( $level, $message, array $context=\[\])
	
		.. rst-class:: phpdoc-description
		
			| Logs with an arbitrary level\.
			
		
		
		:Parameters:
			* **$level** (mixed)  
			* **$message** (string)  
			* **$context** (array)  

		
		:Returns: null 
	
	

.. rst-class:: public static

	.. php:method:: public static flush()
	
		
	
	

