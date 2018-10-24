.. rst-class:: phpdoctorst

.. role:: php(code)
	:language: php


ViewDirective
=============


.. php:namespace:: ILab\Stem\Core

.. rst-class::  abstract

.. php:class:: ViewDirective


	.. rst-class:: phpdoc-description
	
		| Class ViewDirective\.
		
		| Base class for extending the view templates with a custom directive\.  This only works for Blade or similar views\.
		| For Twig, you\'ll have to extend using Twig\_TokenParser and Twig\_Node\.
		
	

Properties
----------

.. php:attr:: protected static context



Methods
-------

.. rst-class:: public

	.. php:method:: public __construct(\\ILab\\Stem\\Core\\Context $context=null)
	
		
	
	

.. rst-class:: public abstract

	.. php:method:: public abstract execute( $args)
	
		.. rst-class:: phpdoc-description
		
			| Executes the directive\.
			
		
		
		:Parameters:
			* **$args** (array)  Arguments for the directive

		
		:Returns: mixed 
	
	

