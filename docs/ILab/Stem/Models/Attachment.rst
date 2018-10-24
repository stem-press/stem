.. rst-class:: phpdoctorst

.. role:: php(code)
	:language: php


Attachment
==========


.. php:namespace:: ILab\Stem\Models

.. php:class:: Attachment


	.. rst-class:: phpdoc-description
	
		| Class Attachment\.
		
		| Represents a media attachment
		
	
	:Parent:
		:php:class:`ILab\\Stem\\Models\\Post`
	

Properties
----------

.. php:attr:: protected postType



.. php:attr:: protected static filename

	:Type: null | string The attachment\'s filename


.. php:attr:: protected static url

	:Type: null | string The attachment\'s url


.. php:attr:: protected static link

	:Type: null | string The link to the attachment\'s page


.. php:attr:: protected static alt

	:Type: null | string The attachment\'s alt text


.. php:attr:: protected static description

	:Type: null | string The attachment\'s description


.. php:attr:: protected static caption

	:Type: null | string The attachment\'s caption


.. php:attr:: protected static mime

	:Type: null | string The attachment\'s mime type


.. php:attr:: protected static type

	:Type: null | string The attachment\'s type


.. php:attr:: protected static subtype

	:Type: null | string The attachment\'s subtype


.. php:attr:: protected static icon

	:Type: null | string The URL for the icon representing the attachment\'s mime type


.. php:attr:: protected static attachmentInfo

	:Type: null | array Extra information about the attachment


Methods
-------

.. rst-class:: public

	.. php:method:: public __construct(\\ILab\\Stem\\Core\\Context $context, \\WP\_Post $post=null)
	
		
	
	

.. rst-class:: public

	.. php:method:: public filename()
	
		.. rst-class:: phpdoc-description
		
			| The filename of the attachment
			
		
		
		:Returns: null | string 
	
	

.. rst-class:: public

	.. php:method:: public url()
	
		.. rst-class:: phpdoc-description
		
			| The URL for the attachment\'s original image
			
		
		
		:Returns: null | string 
	
	

.. rst-class:: public

	.. php:method:: public link()
	
		.. rst-class:: phpdoc-description
		
			| Link to the attachment\'s page
			
		
		
		:Returns: null | string 
	
	

.. rst-class:: public

	.. php:method:: public alt()
	
		.. rst-class:: phpdoc-description
		
			| Attachment\'s alt text
			
		
		
		:Returns: null | string 
	
	

.. rst-class:: public

	.. php:method:: public setAlt( $alt)
	
		.. rst-class:: phpdoc-description
		
			| Sets the alt text for the attachment
			
		
		
		:Parameters:
			* **$alt** (string)  

		
	
	

.. rst-class:: public

	.. php:method:: public description()
	
		.. rst-class:: phpdoc-description
		
			| Description of the attachment
			
		
		
		:Returns: null | string 
	
	

.. rst-class:: public

	.. php:method:: public setDescription( $description)
	
		.. rst-class:: phpdoc-description
		
			| Sets the description for the attachment
			
		
		
		:Parameters:
			* **$description** (string)  

		
	
	

.. rst-class:: public

	.. php:method:: public caption()
	
		.. rst-class:: phpdoc-description
		
			| The caption for the attachment
			
		
		
		:Returns: null | string 
	
	

.. rst-class:: public

	.. php:method:: public setCaption( $caption)
	
		.. rst-class:: phpdoc-description
		
			| Sets the caption for the attachment
			
		
		
		:Parameters:
			* **$caption** (string)  

		
	
	

.. rst-class:: public

	.. php:method:: public type()
	
		.. rst-class:: phpdoc-description
		
			| The attachment\'s primary type
			
		
		
		:Returns: null | string 
	
	

.. rst-class:: public

	.. php:method:: public subType()
	
		.. rst-class:: phpdoc-description
		
			| The attachment\'s sub type
			
		
		
		:Returns: null | string 
	
	

.. rst-class:: public

	.. php:method:: public mime()
	
		.. rst-class:: phpdoc-description
		
			| The attachment\'s mime type
			
		
		
		:Returns: null | string 
	
	

.. rst-class:: public

	.. php:method:: public setMime( $mime)
	
		.. rst-class:: phpdoc-description
		
			| Sets the attachment\'s mime type
			
		
		
		:Parameters:
			* **$mime** (string)  

		
	
	

.. rst-class:: public

	.. php:method:: public icon()
	
		.. rst-class:: phpdoc-description
		
			| URL for the icon representing the attachment\'s mime type
			
		
		
		:Returns: null | string 
	
	

.. rst-class:: public

	.. php:method:: public img( $size=original, $attr=false, $stripDimensions=false)
	
		.. rst-class:: phpdoc-description
		
			| Returns an img tag using the requested size template\.
			
		
		
		:Parameters:
			* **$size** (string)  The size template to use, specify 'original' for original size.
			* **$attr** (bool)  Any additional attributes to add to the tag
			* **$stripDimensions** (bool)  Strip dimensions from the tag

		
		:Returns: string | null 
	
	

.. rst-class:: public

	.. php:method:: public ampImg( $size=thumbnail, $responsive=true, $attr=null)
	
		.. rst-class:: phpdoc-description
		
			| Generates an amp\-img tag\.
			
		
		
		:Parameters:
			* **$size** (string)  
			* **$responsive** (bool)  
			* **$attr** (array | null)  Any additional attributes to add to the tag

		
		:Returns: string 
	
	

.. rst-class:: public

	.. php:method:: public src( $size=original)
	
		.. rst-class:: phpdoc-description
		
			| Returns the url for an image using the requested size template\.
			
		
		
		:Parameters:
			* **$size** (string)  The size template to use.

		
		:Returns: string | null 
	
	

.. rst-class:: public

	.. php:method:: public attachmentInfo( $forceReload=false)
	
		.. rst-class:: phpdoc-description
		
			| Loads the attachment\'s extra info
			
		
		
		:Parameters:
			* **$forceReload** (bool)  

		
		:Returns: array | null 
	
	

.. rst-class:: public

	.. php:method:: public jsonSerialize()
	
		
	
	

