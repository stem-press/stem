.. rst-class:: phpdoctorst

.. role:: php(code)
	:language: php


ChangeManager
=============


.. php:namespace:: ILab\Stem\Models\Utilities

.. php:class:: ChangeManager


	.. rst-class:: phpdoc-description
	
		| Manages changes to a post
		
	

Properties
----------

.. php:attr:: protected static changes



Methods
-------

.. rst-class:: public

	.. php:method:: public __construct()
	
		
	
	

.. rst-class:: public

	.. php:method:: public hasChanges()
	
		.. rst-class:: phpdoc-description
		
			| Returns if changes are pending
			
		
		
		:Returns: bool 
	
	

.. rst-class:: public

	.. php:method:: public addChange( $field, $value)
	
		.. rst-class:: phpdoc-description
		
			| Adds a change for the post
			
		
		
		:Parameters:
			* **$field**  
			* **$value**  

		
	
	

.. rst-class:: public

	.. php:method:: public setThumbnail( $attachmentId)
	
		.. rst-class:: phpdoc-description
		
			| Sets the post\'s thumbnail
			
		
		
		:Parameters:
			* **$attachmentId**  

		
	
	

.. rst-class:: public

	.. php:method:: public clearThumbnail()
	
		.. rst-class:: phpdoc-description
		
			| Clears the post\'s thumbnail
			
		
		
	
	

.. rst-class:: public

	.. php:method:: public addCategory( $category)
	
		.. rst-class:: phpdoc-description
		
			| Adds a category addition
			
		
		
		:Parameters:
			* **$category**  

		
	
	

.. rst-class:: public

	.. php:method:: public removeCategory( $category)
	
		.. rst-class:: phpdoc-description
		
			| Adds a category removal
			
		
		
		:Parameters:
			* **$category**  

		
	
	

.. rst-class:: public

	.. php:method:: public addTag( $tag)
	
		.. rst-class:: phpdoc-description
		
			| Adds a tag addition
			
		
		
		:Parameters:
			* **$tag**  

		
	
	

.. rst-class:: public

	.. php:method:: public removeTag( $tag)
	
		.. rst-class:: phpdoc-description
		
			| Adds a tag removal
			
		
		
		:Parameters:
			* **$tag**  

		
	
	

.. rst-class:: public

	.. php:method:: public updateField( $field, $value)
	
		.. rst-class:: phpdoc-description
		
			| Updates ACF fields
			
		
		
		:Parameters:
			* **$field**  
			* **$value**  

		
	
	

.. rst-class:: public

	.. php:method:: public deleteField( $field)
	
		.. rst-class:: phpdoc-description
		
			| Deletes an ACF field value
			
		
		
		:Parameters:
			* **$field**  

		
	
	

.. rst-class:: public

	.. php:method:: public updateMeta( $key, $value)
	
		.. rst-class:: phpdoc-description
		
			| Updates metadata for a post
			
		
		
		:Parameters:
			* **$key**  
			* **$value**  

		
	
	

.. rst-class:: public

	.. php:method:: public deleteMeta( $key)
	
		.. rst-class:: phpdoc-description
		
			| Deletes metadata for a post
			
		
		
		:Parameters:
			* **$key**  

		
	
	

.. rst-class:: public

	.. php:method:: public create( $postType)
	
		.. rst-class:: phpdoc-description
		
			| Creates a new post with the changes in the list
			
		
		
		:Parameters:
			* **$postType**  

		
		:Returns: bool | int | :any:`\\WP\_Error <WP\_Error>` Returns false if no changes present, \\WP\_Error if there is an error, otherwise the post\'s ID
		:Throws: :any:`\\Exception <Exception>` 
	
	

.. rst-class:: public

	.. php:method:: public update( $post_id)
	
		.. rst-class:: phpdoc-description
		
			| Updates an existing post with the changes in the list
			
		
		
		:Parameters:
			* **$post_id**  

		
		:Returns: bool | int | :any:`\\WP\_Error <WP\_Error>` Returns false if no changes present, \\WP\_Error if there is an error, otherwise the post\'s ID
		:Throws: :any:`\\Exception <Exception>` 
	
	

