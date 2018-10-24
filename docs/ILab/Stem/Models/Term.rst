.. rst-class:: phpdoctorst

.. role:: php(code)
	:language: php


Term
====


.. php:namespace:: ILab\Stem\Models

.. php:class:: Term


	:Implements:
		:php:interface:`JsonSerializable` 
	

Properties
----------

.. php:attr:: public static context



.. php:attr:: protected static id



.. php:attr:: protected static name



.. php:attr:: protected static slug



.. php:attr:: protected static group



.. php:attr:: protected static taxonomy



.. php:attr:: protected static description



.. php:attr:: protected static parent



.. php:attr:: protected static count



.. php:attr:: protected static permalink



Methods
-------

.. rst-class:: public

	.. php:method:: public __construct(\\ILab\\Stem\\Core\\Context $context, $termId, $taxonomy, $termData=null)
	
		
	
	

.. rst-class:: public static

	.. php:method:: public static termFromTermData( $context, $termData)
	
		
	
	

.. rst-class:: public static

	.. php:method:: public static findTerm( $termToFind)
	
		
	
	

.. rst-class:: public static

	.. php:method:: public static term( $context, $termId, $taxonomy)
	
		
	
	

.. rst-class:: public

	.. php:method:: public permalink()
	
		
	
	

.. rst-class:: public

	.. php:method:: public id()
	
		
	
	

.. rst-class:: public

	.. php:method:: public name()
	
		
	
	

.. rst-class:: public

	.. php:method:: public slug()
	
		
	
	

.. rst-class:: public

	.. php:method:: public group()
	
		
	
	

.. rst-class:: public

	.. php:method:: public taxonomy()
	
		
	
	

.. rst-class:: public

	.. php:method:: public description()
	
		
	
	

.. rst-class:: public

	.. php:method:: public parent()
	
		
	
	

.. rst-class:: public

	.. php:method:: public count()
	
		
	
	

.. rst-class:: public

	.. php:method:: public __debugInfo()
	
		
	
	

.. rst-class:: public

	.. php:method:: public jsonSerialize()
	
		
	
	

