.. rst-class:: phpdoctorst

.. role:: php(code)
	:language: php


Context
=======


.. php:namespace:: ILab\Stem\Core

.. php:class:: Context


	.. rst-class:: phpdoc-description
	
		| Class Context\.
		
		| This class represents the current request context and acts like the orchestrator for everything\.
		
	

Properties
----------

.. php:attr:: public static rootPath

	:Type: string Root path to the theme\.


.. php:attr:: public static classPath

	:Type: string Path to classes\.


.. php:attr:: public static namespace

	:Type: string Classes namespace\.


.. php:attr:: public static config

	:Type: array App configuration\.


.. php:attr:: public static textdomain

	:Type: string | null The text domain for the app


.. php:attr:: protected static setupCallback

	:Type: callable Callback for theme setup\.


.. php:attr:: protected static deployCallback

	:Type: callable Callback for post deployment\.  You need to set \'stem\-new\-deploy\' option to true via WP\-CLI to trigger this\.


.. php:attr:: protected static preGetPostsCallback

	:Type: callable Callback for pre\_get\_posts hook\.


.. php:attr:: protected static dispatcher

	:Type: :any:`\\ILab\\Stem\\Core\\Dispatcher <ILab\\Stem\\Core\\Dispatcher>` Dispatcher for requests\.


.. php:attr:: public static cacheControl

	:Type: :any:`\\ILab\\Stem\\Core\\CacheControl <ILab\\Stem\\Core\\CacheControl>` | null CacheControl manager


.. php:attr:: public static debug

	:Type: bool Determines if the context is running in debug mode\.


.. php:attr:: public static siteHost

	:Type: string Site host\.


.. php:attr:: public static httpHost

	:Type: string Http host\.


.. php:attr:: public static request

	:Type: null | :any:`\\Symfony\\Component\\HttpFoundation\\Request <Symfony\\Component\\HttpFoundation\\Request>` Current request\.


.. php:attr:: public static environment

	:Type: string The current environment\.


.. php:attr:: public static ui

	:Type: :any:`\\ILab\\Stem\\Core\\UI <ILab\\Stem\\Core\\UI>` The UI context\.


.. php:attr:: public static admin

	:Type: :any:`\\ILab\\Stem\\Core\\Admin <ILab\\Stem\\Core\\Admin>` The Admin context\.


.. php:attr:: public static currentBuild

	:Type: int The current build as defined the app\.php config\.


.. php:attr:: protected static modelMap

	:Type: array Map of post\_types to model classes


Methods
-------

.. rst-class:: public static

	.. php:method:: public static initialize( $rootPath)
	
		.. rst-class:: phpdoc-description
		
			| Creates the context for this theme\.  Should be called in functions\.php of the theme\.
			
		
		
		:Parameters:
			* **$rootPath**  string The root path to the theme

		
		:Returns: :any:`\\ILab\\Stem\\Core\\Context <ILab\\Stem\\Core\\Context>` The new context
		:Throws: :any:`\\Exception <Exception>` 
	
	

.. rst-class:: public static

	.. php:method:: public static current()
	
		.. rst-class:: phpdoc-description
		
			| Returns the current context\.
			
		
		
		:Returns: :any:`\\ILab\\Stem\\Core\\Context <ILab\\Stem\\Core\\Context>` The current context
	
	

.. rst-class:: protected

	.. php:method:: protected addSetupHook()
	
		
	
	

.. rst-class:: protected

	.. php:method:: protected setup()
	
		.. rst-class:: phpdoc-description
		
			| Final setup step
			
		
		
	
	

.. rst-class:: public

	.. php:method:: public onPreGetPosts( $callable)
	
		.. rst-class:: phpdoc-description
		
			| Sets a callable for pre\_get\_posts filter\.
			
		
		
		:Parameters:
			* **$callable**  callable

		
	
	

.. rst-class:: public

	.. php:method:: public onSetup( $callback)
	
		.. rst-class:: phpdoc-description
		
			| Sets a user supplied callback to call when doing the theme setup\.
			
		
		
		:Parameters:
			* **$callback**  callable

		
	
	

.. rst-class:: public

	.. php:method:: public onDeploy( $callback)
	
		.. rst-class:: phpdoc-description
		
			| Sets a user supplied callback to call after a site has been deployed\.  You need to set \'stem\-new\-deploy\' option
			| to true via WP\-CLI to trigger this\.
			
		
		
		:Parameters:
			* **$callback**  callable

		
	
	

.. rst-class:: public

	.. php:method:: public setting( $settingPath, $default=false)
	
		.. rst-class:: phpdoc-description
		
			| Returns a setting using a path string, eg \'options/views/engine\'\.  Consider this
			| a poor man\'s xpath\.
			
		
		
		:Parameters:
			* **$settingPath** (string)  The "path" in the config settings to look up.
			* **$default** (bool | mixed)  The default value to return if the settings doesn't exist.

		
		:Returns: bool | mixed The result
	
	

.. rst-class:: protected

	.. php:method:: protected dispatch()
	
		.. rst-class:: phpdoc-description
		
			| Dispatches the current request\.
			
		
		
	
	

.. rst-class:: public

	.. php:method:: public modelForPost(\\WP\_Post $post)
	
		.. rst-class:: phpdoc-description
		
			| Creates a model instance for the supplied WP\_Post object\.
			
		
		
		:Parameters:
			* **$post** (:any:`WP\_Post <WP\_Post>`)  

		
		:Returns: :any:`\\ILab\\Stem\\Models\\Attachment <ILab\\Stem\\Models\\Attachment>` | :any:`\\ILab\\Stem\\Models\\Page <ILab\\Stem\\Models\\Page>` | :any:`\\ILab\\Stem\\Models\\Post <ILab\\Stem\\Models\\Post>` | null 
	
	

.. rst-class:: public

	.. php:method:: public modelForPostID( $postId)
	
		.. rst-class:: phpdoc-description
		
			| Creates a model instance for the supplied post ID\.
			
		
		
		:Parameters:
			* **$postId** (int)  

		
		:Returns: :any:`\\ILab\\Stem\\Models\\Attachment <ILab\\Stem\\Models\\Attachment>` | :any:`\\ILab\\Stem\\Models\\Page <ILab\\Stem\\Models\\Page>` | :any:`\\ILab\\Stem\\Models\\Post <ILab\\Stem\\Models\\Post>` | null 
	
	

.. rst-class:: public

	.. php:method:: public findPosts( $args)
	
		.. rst-class:: phpdoc-description
		
			| Performs a query for posts\.
			
		
		
		:Parameters:
			* **$args**  

		
		:Returns: array 
	
	

.. rst-class:: public

	.. php:method:: public createController( $pageType, $template)
	
		.. rst-class:: phpdoc-description
		
			| Creates a controller for the given page type\.
			
		
		
		:Parameters:
			* **$pageType**  string
			* **$template**  string

		
		:Returns: :any:`\\ILab\\Stem\\Controllers\\PageController <ILab\\Stem\\Controllers\\PageController>` | :any:`\\ILab\\Stem\\Controllers\\PostController <ILab\\Stem\\Controllers\\PostController>` | :any:`\\ILab\\Stem\\Controllers\\PostsController <ILab\\Stem\\Controllers\\PostsController>` | null 
	
	

.. rst-class:: public

	.. php:method:: public mapController( $wpTemplateName)
	
		.. rst-class:: phpdoc-description
		
			| Maps a wordpress template to a controller\.
			
		
		
		:Parameters:
			* **$wpTemplateName**  

		
		:Returns: null 
	
	

