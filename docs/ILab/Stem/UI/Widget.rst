.. rst-class:: phpdoctorst

.. role:: php(code)
	:language: php


Widget
======


.. php:namespace:: ILab\Stem\UI

.. rst-class::  abstract

.. php:class:: Widget



Properties
----------

.. php:attr:: protected static wpWidget

	:Type: :any:`\\ILab\\Stem\\UI\\Utilities\\WPWidgetWrapper <ILab\\Stem\\UI\\Utilities\\WPWidgetWrapper>` The underlying WordPress widget


.. php:attr:: protected static context



.. php:attr:: protected static ui



.. php:attr:: protected static template



.. php:attr:: protected static formTemplate



Methods
-------

.. rst-class:: public

	.. php:method:: public __construct(\\ILab\\Stem\\Core\\Context $context, \\ILab\\Stem\\Core\\UI $ui)
	
		
	
	

.. rst-class:: public abstract

	.. php:method:: public abstract id()
	
		
	
	

.. rst-class:: public abstract

	.. php:method:: public abstract name()
	
		
	
	

.. rst-class:: public

	.. php:method:: public render( $data)
	
		
	
	

.. rst-class:: public

	.. php:method:: public renderForm( $data)
	
		
	
	

.. rst-class:: public

	.. php:method:: public processData( $data)
	
		
	
	

.. rst-class:: public

	.. php:method:: public fieldID( $field)
	
		
	
	

.. rst-class:: public

	.. php:method:: public fieldName( $field)
	
		
	
	

