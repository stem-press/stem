.. rst-class:: phpdoctorst

.. role:: php(code)
	:language: php


User
====


.. php:namespace:: ILab\Stem\Models

.. php:class:: User


	.. rst-class:: phpdoc-description
	
		| Class User\.
		
		| Represents a WordPress User
		
	
	:Implements:
		:php:interface:`JsonSerializable` 
	

Properties
----------

.. php:attr:: public static context

	.. rst-class:: phpdoc-description
	
		| The current context\.
		
	
	:Type: :any:`\\ILab\\Stem\\Core\\Context <ILab\\Stem\\Core\\Context>` 


.. php:attr:: protected static user

	.. rst-class:: phpdoc-description
	
		| Underlying user\.
		
	
	:Type: :any:`\\WP\_User <WP\_User>` 


.. php:attr:: protected static id

	:Type: :any:`\\ILab\\Stem\\Models\\number <ILab\\Stem\\Models\\number>` | null User\'s id


Methods
-------

.. rst-class:: public

	.. php:method:: public __construct(\\ILab\\Stem\\Core\\Context $context, \\WP\_User $user)
	
		
	
	

.. rst-class:: public

	.. php:method:: public id()
	
		
	
	

.. rst-class:: public

	.. php:method:: public user()
	
		.. rst-class:: phpdoc-description
		
			| The underlying \\WP\_User object\.
			
		
		
		:Returns: :any:`\\WP\_User <WP\_User>` 
	
	

.. rst-class:: public

	.. php:method:: public permalink()
	
		.. rst-class:: phpdoc-description
		
			| User\'s permalink\.
			
		
		
		:Returns: null | string 
	
	

.. rst-class:: public

	.. php:method:: public avatar( $size=96, $default="", $alt="", $args=null)
	
		.. rst-class:: phpdoc-description
		
			| The user\'s gravatar image tag\.
			
		
		
		:Parameters:
			* **$size** (int)  
			* **$default** (string)  
			* **$alt** (string)  
			* **$args** (null)  

		
		:Returns: bool | string 
	
	

.. rst-class:: public

	.. php:method:: public displayName()
	
		.. rst-class:: phpdoc-description
		
			| User\'s display name\.
			
		
		
		:Returns: null | string 
	
	

.. rst-class:: public

	.. php:method:: public firstName()
	
		.. rst-class:: phpdoc-description
		
			| First name\.
			
		
		
		:Returns: null | string 
	
	

.. rst-class:: public

	.. php:method:: public lastName()
	
		.. rst-class:: phpdoc-description
		
			| Last name\.
			
		
		
		:Returns: null | string 
	
	

.. rst-class:: public

	.. php:method:: public email()
	
		.. rst-class:: phpdoc-description
		
			| User\'s email address\.
			
		
		
		:Returns: null | string 
	
	

.. rst-class:: public

	.. php:method:: public website()
	
		.. rst-class:: phpdoc-description
		
			| Website URL\.
			
		
		
		:Returns: null | string 
	
	

.. rst-class:: public

	.. php:method:: public bio()
	
		.. rst-class:: phpdoc-description
		
			| Bio or description\.
			
		
		
		:Returns: null | string 
	
	

.. rst-class:: public

	.. php:method:: public jsonSerialize()
	
		
	
	

