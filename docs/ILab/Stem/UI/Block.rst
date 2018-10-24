.. rst-class:: phpdoctorst

.. role:: php(code)
	:language: php


Block
=====


.. php:namespace:: ILab\Stem\UI

.. php:class:: Block


	.. rst-class:: phpdoc-description
	
		| Block class for user defined blocks
		
	

Properties
----------

.. php:attr:: protected static context

	:Type: :any:`\\ILab\\Stem\\Core\\Context <ILab\\Stem\\Core\\Context>` | null 


.. php:attr:: protected static ui

	:Type: :any:`\\ILab\\Stem\\Core\\UI <ILab\\Stem\\Core\\UI>` | null 


.. php:attr:: protected static template

	:Type: null | string 


.. php:attr:: protected static name

	:Type: string 


.. php:attr:: protected static title

	:Type: string 


.. php:attr:: protected static description

	:Type: string 


.. php:attr:: protected static category

	:Type: string 


.. php:attr:: protected static icon

	:Type: string 


.. php:attr:: protected static keywords

	:Type: array 


.. php:attr:: protected static acfFields

	:Type: array | null 


Methods
-------

.. rst-class:: public

	.. php:method:: public __construct(\\ILab\\Stem\\Core\\Context $context, \\ILab\\Stem\\Core\\UI $ui, $data=null)
	
		
	
	

.. rst-class:: protected

	.. php:method:: protected configureBlock()
	
		.. rst-class:: phpdoc-description
		
			| Allow subclasses to configure the block before any user supplied data is applied\.
			
		
		
	
	

.. rst-class:: protected

	.. php:method:: protected configureFields()
	
		.. rst-class:: phpdoc-description
		
			| Allows subclasses to configure their ACF fields in code\.  Don\'t worry about specifying the location
			| element, it will be added automatically if it is missing\.
			
			| Recommend to use \`\\StoutLogic\\AcfBuilder\\FieldsBuilder\` and return the result from \`build\(\)\`
			
		
		
		:Returns: array | null 
	
	

.. rst-class:: public

	.. php:method:: public registerFields()
	
		.. rst-class:: phpdoc-description
		
			| Register the block\'s fields with ACF\.
			
		
		
	
	

.. rst-class:: public

	.. php:method:: public description()
	
		.. rst-class:: phpdoc-description
		
			| Description of the block
			
		
		
		:Returns: string 
		:Throws: :any:`\\Exception <Exception>` 
	
	

.. rst-class:: public

	.. php:method:: public icon()
	
		.. rst-class:: phpdoc-description
		
			| The icon for the block
			
		
		
		:Returns: string 
		:Throws: :any:`\\Exception <Exception>` 
	
	

.. rst-class:: public

	.. php:method:: public keywords()
	
		.. rst-class:: phpdoc-description
		
			| Keywords for the block
			
		
		
		:Returns: array 
	
	

.. rst-class:: public

	.. php:method:: public title()
	
		.. rst-class:: phpdoc-description
		
			| Title for the block
			
		
		
		:Returns: string 
		:Throws: :any:`\\Exception <Exception>` 
	
	

.. rst-class:: public

	.. php:method:: public name()
	
		.. rst-class:: phpdoc-description
		
			| Name/slug for the block
			
		
		
		:Returns: string 
		:Throws: :any:`\\Exception <Exception>` 
	
	

.. rst-class:: public

	.. php:method:: public category()
	
		.. rst-class:: phpdoc-description
		
			| Name of the category that the block belongs to
			
		
		
		:Returns: string 
		:Throws: :any:`\\Exception <Exception>` 
	
	

.. rst-class:: public

	.. php:method:: public categorySlug()
	
		.. rst-class:: phpdoc-description
		
			| Slug for the category
			
		
		
		:Returns: string 
		:Throws: :any:`\\Exception <Exception>` 
	
	

.. rst-class:: public

	.. php:method:: public render( $data)
	
		.. rst-class:: phpdoc-description
		
			| Renders the block
			
		
		
		:Parameters:
			* **$data** (array)  

		
		:Returns: string 
	
	

