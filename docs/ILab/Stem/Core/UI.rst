.. rst-class:: phpdoctorst

.. role:: php(code)
	:language: php


UI
==


.. php:namespace:: ILab\Stem\Core

.. php:class:: UI


	.. rst-class:: phpdoc-description
	
		| Class Theme\.
		
		| This class represents the manager for the front end
		
	

Properties
----------

.. php:attr:: protected static context

	.. rst-class:: phpdoc-description
	
		| Current context\.
		
	
	:Type: :any:`\\ILab\\Stem\\Core\\Context <ILab\\Stem\\Core\\Context>` 


.. php:attr:: public static config

	.. rst-class:: phpdoc-description
	
		| Theme configuration\.
		
	
	:Type: array 


.. php:attr:: public static viewPath

	.. rst-class:: phpdoc-description
	
		| Path to views\.
		
	
	:Type: string 


.. php:attr:: public static textdomain

	.. rst-class:: phpdoc-description
	
		| The text domain for internationalization\.
		
	
	:Type: string 


.. php:attr:: public static themePath

	.. rst-class:: phpdoc-description
	
		| Root url to the theme\.
		
	
	:Type: string 


.. php:attr:: public static jsPath

	.. rst-class:: phpdoc-description
	
		| Path to javascript\.
		
	
	:Type: string 


.. php:attr:: public static cssPath

	.. rst-class:: phpdoc-description
	
		| Path to CSS\.
		
	
	:Type: string 


.. php:attr:: public static imgPath

	.. rst-class:: phpdoc-description
	
		| Path to images\.
		
	
	:Type: string 


.. php:attr:: public static useRelative

	.. rst-class:: phpdoc-description
	
		| Determine if relative links should be used anywhere applicable\.
		
	
	:Type: bool 


.. php:attr:: protected static imgixEnabled

	.. rst-class:: phpdoc-description
	
		| Determines if ILAB Media Tool\'s imgix tool is enabled\.
		
	
	:Type: bool 


.. php:attr:: protected static currentImageSize

	.. rst-class:: phpdoc-description
	
		| The current image size being manipulated by wordpress\.
		
	
	:Type: null | string 


.. php:attr:: protected static srcsetConfig

	.. rst-class:: phpdoc-description
	
		| Config for srcset attributes on images\.
		
	
	:Type: array 


.. php:attr:: protected static removeText

	.. rst-class:: phpdoc-description
	
		| Array of text to remove from final output\.
		
	
	:Type: array 


.. php:attr:: protected static removeRegexes

	.. rst-class:: phpdoc-description
	
		| Array of regexes to remove text from final output\.
		
	
	:Type: array 


.. php:attr:: protected static replaceText

	.. rst-class:: phpdoc-description
	
		| Array of text replacements for final output\.
		
	
	:Type: array 


.. php:attr:: protected static replaceRegexes

	.. rst-class:: phpdoc-description
	
		| Array of regexes to replace text in final output\.
		
	
	:Type: array 


.. php:attr:: protected static shortCodes

	.. rst-class:: phpdoc-description
	
		| Array of ShortCode classes\.
		
	
	:Type: array 


.. php:attr:: public static forcedDomain

	.. rst-class:: phpdoc-description
	
		| The forced domain\.
		
	
	:Type: string 


.. php:attr:: protected static viewClass

	.. rst-class:: phpdoc-description
	
		| View class\.
		
	
	:Type: string 


.. php:attr:: public static amp

	.. rst-class:: phpdoc-description
	
		| Amp manager\.
		
	
	:Type: :any:`\\ILab\\Stem\\Core\\AMP <ILab\\Stem\\Core\\AMP>` 


.. php:attr:: public static theme

	.. rst-class:: phpdoc-description
	
		| Customizer theme manager\.
		
	
	:Type: :any:`\\ILab\\Stem\\UI\\Theme <ILab\\Stem\\UI\\Theme>` 


.. php:attr:: public static enqueueConfig



.. php:attr:: public static blocks

	.. rst-class:: phpdoc-description
	
		| Blocks manager
		
	
	:Type: :any:`\\ILab\\Stem\\Core\\Blocks <ILab\\Stem\\Core\\Blocks>` 


Methods
-------

.. rst-class:: public

	.. php:method:: public __construct(\\ILab\\Stem\\Core\\Context $context)
	
		.. rst-class:: phpdoc-description
		
			| Constructor\.
			
		
		
		:Parameters:
			* **$context**  Context The current context

		
	
	

.. rst-class:: protected

	.. php:method:: protected parseEnqueue()
	
		
	
	

.. rst-class:: public

	.. php:method:: public setup()
	
		
	
	

.. rst-class:: public

	.. php:method:: public setting( $settingPath, $default=false)
	
		.. rst-class:: phpdoc-description
		
			| Returns a setting using a path string, eg \'options/views/engine\'\.  Consider this
			| a poor man\'s xpath\.
			
		
		
		:Parameters:
			* **$settingPath**  The "path" in the config settings to look up.
			* **$default** (bool | mixed)  The default value to return if the settings doesn't exist.

		
		:Returns: bool | mixed The result
	
	

.. rst-class:: public

	.. php:method:: public enqueueSetting( $settingPath, $default=false)
	
		.. rst-class:: phpdoc-description
		
			| Returns an enqueue setting using a path string, eg \'options/views/engine\'\.  Consider this
			| a poor man\'s xpath\.
			
		
		
		:Parameters:
			* **$settingPath**  The "path" in the config settings to look up.
			* **$default** (bool | mixed)  The default value to return if the settings doesn't exist.

		
		:Returns: bool | mixed The result
	
	

.. rst-class:: public

	.. php:method:: public setupKirkiCustomizer()
	
		.. rst-class:: phpdoc-description
		
			| Set up the Kirki Customizer\.
			
		
		
	
	

.. rst-class:: public

	.. php:method:: public registerShortcode( $shortcode, $callable)
	
		.. rst-class:: phpdoc-description
		
			| Registers a shortcode\.
			
		
		
		:Parameters:
			* **$shortcode**  string
			* **$callable**  callable

		
	
	

.. rst-class:: public

	.. php:method:: public viewExists( $view)
	
		.. rst-class:: phpdoc-description
		
			| Determines if a view exists in the file system\.
			
		
		
		:Parameters:
			* **$view**  

		
		:Returns: bool 
	
	

.. rst-class:: public

	.. php:method:: public render( $view, $data)
	
		.. rst-class:: phpdoc-description
		
			| Renders a view\.
			
		
		
		:Parameters:
			* **$view**  string The name of the view
			* **$data**  array The data to display in the view

		
		:Returns: string The rendered view
	
	

.. rst-class:: public

	.. php:method:: public header()
	
		.. rst-class:: phpdoc-description
		
			| Outputs the Wordpress generated header html
			
		
		
		:Returns: mixed | string 
	
	

.. rst-class:: public

	.. php:method:: public footer()
	
		.. rst-class:: phpdoc-description
		
			| Outputs the Wordpress generated footer html
			
		
		
		:Returns: string 
	
	

.. rst-class:: public

	.. php:method:: public image( $src)
	
		.. rst-class:: phpdoc-description
		
			| Returns the image src to an image included in the theme\.
			
		
		
		:Parameters:
			* **$src**  

		
		:Returns: string 
	
	

.. rst-class:: public

	.. php:method:: public file( $src)
	
		.. rst-class:: phpdoc-description
		
			| Returns the url for a file in the theme\.
			
		
		
		:Parameters:
			* **$src**  

		
		:Returns: string 
	
	

.. rst-class:: public

	.. php:method:: public script( $src)
	
		.. rst-class:: phpdoc-description
		
			| Returns the script src to an image included in the theme\.
			
		
		
		:Parameters:
			* **$src**  

		
		:Returns: string 
	
	

.. rst-class:: public

	.. php:method:: public css( $src)
	
		.. rst-class:: phpdoc-description
		
			| Returns the css src to an image included in the theme\.
			
		
		
		:Parameters:
			* **$src**  

		
		:Returns: string 
	
	

.. rst-class:: public

	.. php:method:: public menu( $name, $stripUL=false, $removeText=false, $insertGap="", $array=false)
	
		.. rst-class:: phpdoc-description
		
			| Renders a Wordpress generated menu\.
			
		
		
		:Parameters:
			* **$name**  string
			* **$stripUL** (bool | bool)  
			* **$removeText** (bool | bool)  
			* **$insertGap** (string)  
			* **$array** (bool | bool)  

		
		:Returns: bool | mixed | object | string | void 
	
	

.. rst-class:: public

	.. php:method:: public imageDownsize( $fail, $id, $size)
	
		
	
	

.. rst-class:: public

	.. php:method:: public calculateSrcSet( $sources, $size_array, $image_src, $image_meta, $attachment_id)
	
		
	
	

.. rst-class:: public

	.. php:method:: public calculateImageSizes( $sizes, $size, $image_src, $image_meta, $attachment_id)
	
		
	
	

