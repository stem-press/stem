.. rst-class:: phpdoctorst

.. role:: php(code)
	:language: php


Query
=====


.. php:namespace:: ILab\Stem\Models\Query

.. rst-class::  final

.. php:class:: Query


	.. rst-class:: phpdoc-description
	
		| Fluent interface for the horrible WP\_Query
		
	

Properties
----------

Methods
-------

.. rst-class:: public

	.. php:method:: public __construct(\\ILab\\Stem\\Core\\Context $context, $postType, $subquery=false, $metaqueryRelation=null)
	
		.. rst-class:: phpdoc-description
		
			| Query constructor\.
			
		
		
		:Parameters:
			* **$context** (:any:`ILab\\Stem\\Core\\Context <ILab\\Stem\\Core\\Context>`)  
			* **$postType** (null | string | string[])  
			* **$subquery** (bool)  
			* **$metaqueryRelation** (null | string)  

		
	
	

.. rst-class:: public

	.. php:method:: public __get( $name)
	
		
		:Parameters:
			* **$name**  

		
		:Returns: :any:`\\ILab\\Stem\\Models\\Query\\Field <ILab\\Stem\\Models\\Query\\Field>` | :any:`\\ILab\\Stem\\Models\\Query\\Query <ILab\\Stem\\Models\\Query\\Query>` 
		:Throws: :any:`\\Exception <Exception>` 
	
	

.. rst-class:: public

	.. php:method:: public or( $callable)
	
		
	
	

.. rst-class:: public

	.. php:method:: public and( $callable)
	
		
	
	

.. rst-class:: public

	.. php:method:: public limit( $limit)
	
		
	
	

.. rst-class:: public

	.. php:method:: public offset( $offset)
	
		
	
	

.. rst-class:: public

	.. php:method:: public page( $page)
	
		
	
	

.. rst-class:: public

	.. php:method:: public taxonomy( $taxonomy, $valueType=term\_id)
	
		.. rst-class:: phpdoc-description
		
			| Perform taxonomy query
			
		
		
		:Parameters:
			* **$taxonomy** (string)  
			* **$valueType** (string)  

		
		:Returns: :any:`\\ILab\\Stem\\Models\\Query\\Field <ILab\\Stem\\Models\\Query\\Field>` 
		:Throws: :any:`\\Exception <Exception>` 
	
	

.. rst-class:: public

	.. php:method:: public search( $searchTerms)
	
		.. rst-class:: phpdoc-description
		
			| Set the text search terms
			
		
		
		:Parameters:
			* **$searchTerms** (string)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public field( $field, $operator, $value, $type=CHAR, $queryName=null)
	
		.. rst-class:: phpdoc-description
		
			| Meta key query
			
		
		
		:Parameters:
			* **$field** (string)  
			* **$operator** (string)  
			* **$value** (mixed | null)  
			* **$type** (string)  
			* **$queryName** (string)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public where( ...$args)
	
		.. rst-class:: phpdoc-description
		
			| Add a where clause to the query\.
			
		
		
		:Parameters:
			* **$args** (mixed)  

		
		:Throws: :any:`\\Exception <Exception>` 
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public whereWithArgs(array $args)
	
		.. rst-class:: phpdoc-description
		
			| Add a where clause to the query passing in a 2 element array containing the field
			| and value, or a 3 element array containing the field, operator and value\.
			
		
		
		:Parameters:
			* **$args** (array)  

		
		:Throws: :any:`\\Exception <Exception>` 
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public order( $field, $direction=ASC, $append=true)
	
		
	
	

.. rst-class:: public

	.. php:method:: public first()
	
		.. rst-class:: phpdoc-description
		
			| Returns the first post
			
		
		
		:Returns: :any:`\\ILab\\Stem\\Models\\Post <ILab\\Stem\\Models\\Post>` | null 
	
	

.. rst-class:: public

	.. php:method:: public last()
	
		.. rst-class:: phpdoc-description
		
			| Returns the last post
			
		
		
		:Returns: :any:`\\ILab\\Stem\\Models\\Post <ILab\\Stem\\Models\\Post>` | null 
	
	

.. rst-class:: public

	.. php:method:: public get()
	
		.. rst-class:: phpdoc-description
		
			| Executes the query and returns the result
			
		
		
		:Returns: :any:`\\ILab\\Stem\\Models\\Query\\PostCollection <ILab\\Stem\\Models\\Query\\PostCollection>` 
	
	

.. rst-class:: public

	.. php:method:: public build()
	
		.. rst-class:: phpdoc-description
		
			| Builds the arguments that will be used with WP\_Query
			
		
		
		:Returns: array 
	
	

.. rst-class:: protected

	.. php:method:: protected buildMetaQuery()
	
		.. rst-class:: phpdoc-description
		
			| Builds the meta queries
			
		
		
		:Returns: array 
	
	

