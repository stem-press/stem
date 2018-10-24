.. rst-class:: phpdoctorst

.. role:: php(code)
	:language: php


View
====


.. php:namespace:: ILab\Stem\Core

.. rst-class::  abstract

.. php:class:: View


	.. rst-class:: phpdoc-description
	
		| Class View\.
		
		| Base class for rendering views
		
	

Properties
----------

.. php:attr:: protected static debug



.. php:attr:: protected static context



.. php:attr:: protected static ui



.. php:attr:: protected static viewName



Methods
-------

.. rst-class:: public

	.. php:method:: public __construct(\\ILab\\Stem\\Core\\Context $context=null, \\ILab\\Stem\\Core\\UI $ui=null, $viewName=null)
	
		
	
	

.. rst-class:: public abstract static

	.. php:method:: public abstract static renderView(\\ILab\\Stem\\Core\\Context $context, \\ILab\\Stem\\Core\\UI $ui, $view, $data)
	
		
	
	

.. rst-class:: public abstract static

	.. php:method:: public abstract static viewExists(\\ILab\\Stem\\Core\\UI $ui, $view)
	
		
	
	

