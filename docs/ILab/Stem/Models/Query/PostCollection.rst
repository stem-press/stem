.. rst-class:: phpdoctorst

.. role:: php(code)
	:language: php


PostCollection
==============


.. php:namespace:: ILab\Stem\Models\Query

.. php:class:: PostCollection


	.. rst-class:: phpdoc-description
	
		| Represents the results from a query or \\WP\_Query
		
	
	:Implements:
		:php:interface:`ArrayAccess` :php:interface:`Iterator` :php:interface:`Countable` 
	

Properties
----------

.. php:attr:: protected static context

	:Type: :any:`\\ILab\\Stem\\Core\\Context <ILab\\Stem\\Core\\Context>` | null The context


.. php:attr:: protected static posts

	:Type: :any:`\\ILab\\Stem\\Models\\Query\\Post\[\] <ILab\\Stem\\Models\\Query\\Post>` Posts


.. php:attr:: protected static query

	:Type: :any:`\\ILab\\Stem\\Models\\Query\\Query <ILab\\Stem\\Models\\Query\\Query>` | null The query that generated the collection


.. php:attr:: protected static wpQuery

	:Type: null | :any:`\\WP\_Query <WP\_Query>` The WP\_Query


.. php:attr:: protected static args

	:Type: array The arguments used for the WP\_Query


Methods
-------

.. rst-class:: public

	.. php:method:: public __construct(\\ILab\\Stem\\Core\\Context $context, \\ILab\\Stem\\Models\\Query\\Query $query=null, \\WP\_Query $wpQuery=null)
	
		.. rst-class:: phpdoc-description
		
			| PostCollection constructor\.
			
		
		
		:Parameters:
			* **$context** (:any:`ILab\\Stem\\Core\\Context <ILab\\Stem\\Core\\Context>`)  
			* **$query** (:any:`ILab\\Stem\\Models\\Query\\Query <ILab\\Stem\\Models\\Query\\Query>` | null)  
			* **$wpQuery** (:any:`WP\_Query <WP\_Query>` | null)  

		
	
	

.. rst-class:: public

	.. php:method:: public count()
	
		.. rst-class:: phpdoc-description
		
			| Count elements of an object
			
		
		
		:Returns: int The custom count as an integer\.
			</p\>
			<p\>
			The return value is cast to an integer\.
		
		:Since: 5.1.0 
	
	

.. rst-class:: public

	.. php:method:: public total()
	
		.. rst-class:: phpdoc-description
		
			| Total posts that could be returned by the underlying query
			
		
		
		:Returns: int 
	
	

.. rst-class:: public

	.. php:method:: public pages()
	
		.. rst-class:: phpdoc-description
		
			| Total number of pages of posts
			
		
		
		:Returns: int 
	
	

.. rst-class:: public

	.. php:method:: public currentPage()
	
		.. rst-class:: phpdoc-description
		
			| The current page
			
		
		
		:Returns: int 
	
	

.. rst-class:: public

	.. php:method:: public offset()
	
		.. rst-class:: phpdoc-description
		
			| The current offset
			
		
		
		:Returns: int 
	
	

.. rst-class:: public

	.. php:method:: public arguments()
	
		.. rst-class:: phpdoc-description
		
			| The arguments used to build the query, for debugging
			
		
		
		:Returns: array 
	
	

.. rst-class:: public

	.. php:method:: public sql()
	
		.. rst-class:: phpdoc-description
		
			| The SQL used to generate the results, for debugging and chuckles\.
			
		
		
		:Returns: string 
	
	

.. rst-class:: public

	.. php:method:: public current()
	
		.. rst-class:: phpdoc-description
		
			| Return the current element
			
		
		
		:Returns: mixed Can return any type\.
		:Since: 5.0.0 
	
	

.. rst-class:: public

	.. php:method:: public next()
	
		.. rst-class:: phpdoc-description
		
			| Move forward to next element
			
		
		
		:Returns: void Any returned value is ignored\.
		:Since: 5.0.0 
	
	

.. rst-class:: public

	.. php:method:: public key()
	
		.. rst-class:: phpdoc-description
		
			| Return the key of the current element
			
		
		
		:Returns: mixed scalar on success, or null on failure\.
		:Since: 5.0.0 
	
	

.. rst-class:: public

	.. php:method:: public valid()
	
		.. rst-class:: phpdoc-description
		
			| Checks if current position is valid
			
		
		
		:Returns: bool The return value will be casted to boolean and then evaluated\.
			Returns true on success or false on failure\.
		
		:Since: 5.0.0 
	
	

.. rst-class:: public

	.. php:method:: public rewind()
	
		.. rst-class:: phpdoc-description
		
			| Rewind the Iterator to the first element
			
		
		
		:Returns: void Any returned value is ignored\.
		:Since: 5.0.0 
	
	

.. rst-class:: public

	.. php:method:: public offsetExists( $offset)
	
		.. rst-class:: phpdoc-description
		
			| Whether a offset exists
			
		
		
		:Parameters:
			* **$offset** (mixed)  <p>
			An offset to check for.
			</p>

		
		:Returns: bool true on success or false on failure\.
			</p\>
			<p\>
			The return value will be casted to boolean if non\-boolean was returned\.
		
		:Since: 5.0.0 
	
	

.. rst-class:: public

	.. php:method:: public offsetGet( $offset)
	
		.. rst-class:: phpdoc-description
		
			| Offset to retrieve
			
		
		
		:Parameters:
			* **$offset** (mixed)  <p>
			The offset to retrieve.
			</p>

		
		:Returns: mixed Can return all value types\.
		:Since: 5.0.0 
	
	

.. rst-class:: public

	.. php:method:: public offsetSet( $offset, $value)
	
		.. rst-class:: phpdoc-description
		
			| Offset to set
			
		
		
		:Parameters:
			* **$offset** (mixed)  <p>
			The offset to assign the value to.
			</p>
			* **$value** (mixed)  <p>
			The value to set.
			</p>

		
		:Returns: void 
		:Since: 5.0.0 
		:Throws: :any:`\\Exception <Exception>` 
	
	

.. rst-class:: public

	.. php:method:: public offsetUnset( $offset)
	
		.. rst-class:: phpdoc-description
		
			| Offset to unset
			
		
		
		:Parameters:
			* **$offset** (mixed)  <p>
			The offset to unset.
			</p>

		
		:Returns: void 
		:Since: 5.0.0 
		:Throws: :any:`\\Exception <Exception>` 
	
	

.. rst-class:: public

	.. php:method:: public __debugInfo()
	
		
	
	

