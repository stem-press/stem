.. rst-class:: phpdoctorst

.. role:: php(code)
	:language: php


Router
======


.. php:namespace:: ILab\Stem\Core

.. php:class:: Router


	.. rst-class:: phpdoc-description
	
		| Class Router\.
		
		| Handles routing of non\-wordpress requests to controllers or callable functions
		
	

Properties
----------

Methods
-------

.. rst-class:: public

	.. php:method:: public __construct(\\ILab\\Stem\\Core\\Context $context)
	
		
	
	

.. rst-class:: public

	.. php:method:: public addRoute( $early, $name, $routeStr, $destination, $defaults=\[\], $requirements=\[\], $methods=\[\])
	
		
	
	

.. rst-class:: public

	.. php:method:: public dispatch( $early, \\Symfony\\Component\\HttpFoundation\\Request $req)
	
		.. rst-class:: phpdoc-description
		
			| Dispatches the request\.  Returns true if dispatched, false if no routes match
			
		
		
		:Parameters:
			* **$early** (bool)  For matching routes that should happen before WordPress loads completely
			* **$req** (:any:`Symfony\\Component\\HttpFoundation\\Request <Symfony\\Component\\HttpFoundation\\Request>`)  

		
		:Returns: bool 
		:Throws: :any:`\\Invoker\\Exception\\InvocationException <Invoker\\Exception\\InvocationException>` 
		:Throws: :any:`\\Invoker\\Exception\\NotCallableException <Invoker\\Exception\\NotCallableException>` 
		:Throws: :any:`\\Invoker\\Exception\\NotEnoughParametersException <Invoker\\Exception\\NotEnoughParametersException>` 
		:Throws: :any:`\\Invoker\\Exception\\InvocationException <Invoker\\Exception\\InvocationException>` 
		:Throws: :any:`\\Invoker\\Exception\\NotCallableException <Invoker\\Exception\\NotCallableException>` 
		:Throws: :any:`\\Invoker\\Exception\\NotEnoughParametersException <Invoker\\Exception\\NotEnoughParametersException>` 
		:Throws: :any:`\\Invoker\\Exception\\InvocationException <Invoker\\Exception\\InvocationException>` 
		:Throws: :any:`\\Invoker\\Exception\\NotCallableException <Invoker\\Exception\\NotCallableException>` 
		:Throws: :any:`\\Invoker\\Exception\\NotEnoughParametersException <Invoker\\Exception\\NotEnoughParametersException>` 
	
	

