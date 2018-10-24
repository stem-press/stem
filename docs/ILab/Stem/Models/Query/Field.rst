.. rst-class:: phpdoctorst

.. role:: php(code)
	:language: php


Field
=====


.. php:namespace:: ILab\Stem\Models\Query

.. php:class:: Field


	.. rst-class:: phpdoc-description
	
		| Represents a "fluent" field in a query\.
		
	

Properties
----------

.. php:attr:: protected static query

	:Type: :any:`\\ILab\\Stem\\Models\\Query\\Query <ILab\\Stem\\Models\\Query\\Query>` The query that owns this field


.. php:attr:: protected static allowedOperators

	:Type: string[] The operators that this field can use


.. php:attr:: protected static fieldName

	:Type: null | string Name of the field


.. php:attr:: protected static callback

	:Type: null | callable Callback to call when the value has been set


.. php:attr:: protected static value

	:Type: null | mixed 


.. php:attr:: protected static operator

	:Type: null | string 


.. php:attr:: protected allOperators

	:Type: array All of the operators that can be used on any field


Methods
-------

.. rst-class:: public

	.. php:method:: public __construct(\\ILab\\Stem\\Models\\Query\\Query $query, string $fieldName, array $allowedOperators, callable $callback)
	
		.. rst-class:: phpdoc-description
		
			| Field constructor\.
			
		
		
		:Parameters:
			* **$query** (:any:`ILab\\Stem\\Models\\Query\\Query <ILab\\Stem\\Models\\Query\\Query>`)  
			* **$fieldName** (string)  
			* **$allowedOperators** (array)  
			* **$callback** (callable)  

		
		:Throws: :any:`\\Exception <Exception>` 
	
	

.. rst-class:: public

	.. php:method:: public fieldName()
	
		.. rst-class:: phpdoc-description
		
			| Name of the field
			
		
		
		:Returns: null | string 
	
	

.. rst-class:: public

	.. php:method:: public value()
	
		.. rst-class:: phpdoc-description
		
			| Value of the field
			
		
		
		:Returns: mixed | null 
	
	

.. rst-class:: public

	.. php:method:: public operator()
	
		.. rst-class:: phpdoc-description
		
			| Operator being used
			
		
		
		:Returns: null | string 
	
	

.. rst-class:: public

	.. php:method:: public __call( $name, $arguments)
	
		.. rst-class:: phpdoc-description
		
			| Magic method for calling the field operator
			
		
		
		:Parameters:
			* **$name**  
			* **$arguments**  

		
		:Returns: :any:`\\ILab\\Stem\\Models\\Query\\Query <ILab\\Stem\\Models\\Query\\Query>` 
		:Throws: :any:`\\Exception <Exception>` 
	
	

