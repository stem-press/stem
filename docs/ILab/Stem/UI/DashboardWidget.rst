.. rst-class:: phpdoctorst

.. role:: php(code)
	:language: php


DashboardWidget
===============


.. php:namespace:: ILab\Stem\UI

.. rst-class::  abstract

.. php:class:: DashboardWidget


	.. rst-class:: phpdoc-description
	
		| Class DashboardWidget\.
		
		| Represents a dashboard widget on the WordPress dashboard\.
		
	

Properties
----------

.. php:attr:: public static context



.. php:attr:: protected static request



.. php:attr:: protected static config



Methods
-------

.. rst-class:: public

	.. php:method:: public __construct(\\ILab\\Stem\\Core\\Context $context, \\Symfony\\Component\\HttpFoundation\\Request $request, $config=\[\])
	
		
		:Parameters:
			* **$context**  

		
	
	

.. rst-class:: public abstract

	.. php:method:: public abstract render()
	
		.. rst-class:: phpdoc-description
		
			| Renders the dashboard widget\.
			
		
		
		:Returns: string 
	
	

