.. rst-class:: phpdoctorst

.. role:: php(code)
	:language: php


Post
====


.. php:namespace:: ILab\Stem\Models

.. php:class:: Post


	.. rst-class:: phpdoc-description
	
		| Class Post\.
		
		| Represents a WordPress post
		
	
	:Implements:
		:php:interface:`JsonSerializable` 
	

Properties
----------

.. php:attr:: protected postType

	:Type: string Type of post


.. php:attr:: protected static id

	:Type: int | null ID of the post


.. php:attr:: public static context

	:Type: :any:`\\ILab\\Stem\\Core\\Context <ILab\\Stem\\Core\\Context>` 


.. php:attr:: protected static post

	:Type: :any:`\\WP\_Post <WP\_Post>` The underlying Wordpress post


.. php:attr:: protected static status

	:Type: string | null The post status\. Default \'draft\'


.. php:attr:: protected static parent

	:Type: :any:`\\ILab\\Stem\\Models\\Post <ILab\\Stem\\Models\\Post>` | null The parent post, if any


.. php:attr:: protected static menuOrder

	:Type: int The order the post should be displayed in\.


.. php:attr:: protected static slug

	:Type: null | string Slug for the post


.. php:attr:: protected static title

	:Type: null | string Title for the post


.. php:attr:: protected static author

	:Type: null | :any:`\\ILab\\Stem\\Models\\User <ILab\\Stem\\Models\\User>` The author of the post


.. php:attr:: protected static topCategory

	:Type: null | :any:`\\ILab\\Stem\\Models\\Term <ILab\\Stem\\Models\\Term>` The primary category for the post


.. php:attr:: protected static topCategories

	:Type: null | :any:`\\ILab\\Stem\\Models\\Term\[\] <ILab\\Stem\\Models\\Term>` The top level categories for this post


.. php:attr:: protected static categories

	:Type: null | :any:`\\ILab\\Stem\\Models\\Term\[\] <ILab\\Stem\\Models\\Term>` All categories assigned to this post


.. php:attr:: protected static tags

	:Type: null | :any:`\\ILab\\Stem\\Models\\Term\[\] <ILab\\Stem\\Models\\Term>` All terms assigned to this post


.. php:attr:: protected static permalink

	:Type: null | string The permalink for this post


.. php:attr:: protected static thumbnail

	:Type: null | :any:`\\ILab\\Stem\\Models\\Attachment <ILab\\Stem\\Models\\Attachment>` The featured image for the post


.. php:attr:: protected static date

	:Type: null | :any:`\\Carbon\\Carbon <Carbon\\Carbon>` The date the post was published


.. php:attr:: protected static updated

	:Type: null | :any:`\\Carbon\\Carbon <Carbon\\Carbon>` The date the post was updated


.. php:attr:: protected static content

	:Type: null | string The post\'s content


.. php:attr:: protected static unfilteredContent

	:Type: null | string The post\'s unfiltered content


.. php:attr:: protected static excerpt

	:Type: null | string The post\'s excerpt


.. php:attr:: protected static changes

	:Type: :any:`\\ILab\\Stem\\Models\\Utilities\\ChangeManager <ILab\\Stem\\Models\\Utilities\\ChangeManager>` Manager for changes


.. php:attr:: protected static fieldsCache

	:Type: array ACF fields cache


.. php:attr:: protected static meta

	:Type: null | array Cached metadata


Methods
-------

.. rst-class:: public

	.. php:method:: public __construct(\\ILab\\Stem\\Core\\Context $context, \\WP\_Post $post=null)
	
		.. rst-class:: phpdoc-description
		
			| Post constructor\.
			
		
		
		:Parameters:
			* **$context** (:any:`ILab\\Stem\\Core\\Context <ILab\\Stem\\Core\\Context>`)  
			* **$post** (:any:`WP\_Post <WP\_Post>`)  

		
	
	

.. rst-class:: public static

	.. php:method:: public static postType()
	
		.. rst-class:: phpdoc-description
		
			| The post\'s type
			
		
		
		:Returns: string 
	
	

.. rst-class:: public static

	.. php:method:: public static postTypeProperties()
	
		.. rst-class:: phpdoc-description
		
			| Subclasses should override to provide custom post type properties\.  It\'s recommended to use \`CustomPostTypeBuilder\`
			| to define your custom post type, but you can also return an array of arguments that work with \`register\_post\_type\(\)\`\.
			
		
		
		:Returns: :any:`\\ILab\\Stem\\Models\\Utilities\\CustomPostTypeBuilder <ILab\\Stem\\Models\\Utilities\\CustomPostTypeBuilder>` | array | null 
	
	

.. rst-class:: public static

	.. php:method:: public static registerFields()
	
		.. rst-class:: phpdoc-description
		
			| Allows subclasses to configure their ACF fields in code\.  Don\'t worry about specifying the location
			| element, it will be added automatically if it is missing\.
			
			| Recommend to use \`\\StoutLogic\\AcfBuilder\\FieldsBuilder\` and return the result from \`build\(\)\`
			
		
		
		:Returns: array | null 
	
	

.. rst-class:: public

	.. php:method:: public id()
	
		.. rst-class:: phpdoc-description
		
			| The post\'s ID
			
		
		
		:Returns: int | null 
	
	

.. rst-class:: public

	.. php:method:: public wpPost()
	
		.. rst-class:: phpdoc-description
		
			| Returns the underlying Wordpress post
			
		
		
		:Returns: :any:`\\WP\_Post <WP\_Post>` 
	
	

.. rst-class:: public

	.. php:method:: public cssClass( $class="")
	
		.. rst-class:: phpdoc-description
		
			| Returns the CSS classes for this post as a single string
			
		
		
		:Parameters:
			* **$class** (string | array)  One or more classes to add to the class list.

		
		:Returns: string 
	
	

.. rst-class:: public

	.. php:method:: public title()
	
		.. rst-class:: phpdoc-description
		
			| Title of the post
			
		
		
		:Returns: null | string 
	
	

.. rst-class:: public

	.. php:method:: public setTitle( $title)
	
		.. rst-class:: phpdoc-description
		
			| Sets the title of the post
			
		
		
		:Parameters:
			* **$title**  

		
	
	

.. rst-class:: public

	.. php:method:: public author()
	
		.. rst-class:: phpdoc-description
		
			| Author of the post
			
		
		
		:Returns: :any:`\\ILab\\Stem\\Models\\User <ILab\\Stem\\Models\\User>` | null 
	
	

.. rst-class:: public

	.. php:method:: public setAuthor(\\ILab\\Stem\\Models\\User $user)
	
		.. rst-class:: phpdoc-description
		
			| Sets the author
			
		
		
		:Parameters:
			* **$user** (:any:`ILab\\Stem\\Models\\User <ILab\\Stem\\Models\\User>`)  

		
	
	

.. rst-class:: public

	.. php:method:: public slug()
	
		.. rst-class:: phpdoc-description
		
			| The post\'s slug
			
		
		
		:Returns: null | string 
	
	

.. rst-class:: public

	.. php:method:: public setSlug( $newSlug)
	
		.. rst-class:: phpdoc-description
		
			| Sets the post\'s slug
			
		
		
		:Parameters:
			* **$newSlug** (string)  

		
	
	

.. rst-class:: public

	.. php:method:: public date()
	
		.. rst-class:: phpdoc-description
		
			| Returns the date the post was published
			
		
		
		:Returns: :any:`\\Carbon\\Carbon <Carbon\\Carbon>` | null 
	
	

.. rst-class:: public

	.. php:method:: public updated()
	
		.. rst-class:: phpdoc-description
		
			| Returns the date the post was updated
			
		
		
		:Returns: :any:`\\Carbon\\Carbon <Carbon\\Carbon>` | null 
	
	

.. rst-class:: public

	.. php:method:: public thumbnail()
	
		.. rst-class:: phpdoc-description
		
			| Returns the post\'s featured image
			
		
		
		:Returns: :any:`\\ILab\\Stem\\Models\\Attachment <ILab\\Stem\\Models\\Attachment>` | null 
	
	

.. rst-class:: public

	.. php:method:: public setThumbnail( $attachmentOrId=null)
	
		.. rst-class:: phpdoc-description
		
			| Sets the thumbnail for the post
			
		
		
		:Parameters:
			* **$attachmentOrId** (:any:`ILab\\Stem\\Models\\Attachment <ILab\\Stem\\Models\\Attachment>` | int)  

		
		:Throws: :any:`\\Exception <Exception>` 
	
	

.. rst-class:: public

	.. php:method:: public status()
	
		.. rst-class:: phpdoc-description
		
			| The post status\. Default \'draft\'
			
		
		
		:Returns: null | string 
	
	

.. rst-class:: public

	.. php:method:: public setStatus( $status)
	
		.. rst-class:: phpdoc-description
		
			| Sets the post\'s status
			
		
		
		:Parameters:
			* **$status**  

		
	
	

.. rst-class:: public

	.. php:method:: public parent()
	
		.. rst-class:: phpdoc-description
		
			| The parent post, if any
			
		
		
		:Returns: :any:`\\ILab\\Stem\\Models\\Attachment <ILab\\Stem\\Models\\Attachment>` | :any:`\\ILab\\Stem\\Models\\Page <ILab\\Stem\\Models\\Page>` | :any:`\\ILab\\Stem\\Models\\Post <ILab\\Stem\\Models\\Post>` | null 
	
	

.. rst-class:: public

	.. php:method:: public setParent( $parent)
	
		.. rst-class:: phpdoc-description
		
			| Sets the parent
			
		
		
		:Parameters:
			* **$parent** (:any:`ILab\\Stem\\Models\\Attachment <ILab\\Stem\\Models\\Attachment>` | :any:`\\ILab\\Stem\\Models\\Page <ILab\\Stem\\Models\\Page>` | :any:`\\ILab\\Stem\\Models\\Post <ILab\\Stem\\Models\\Post>` | null | int)  

		
	
	

.. rst-class:: public

	.. php:method:: public menuOrder()
	
		.. rst-class:: phpdoc-description
		
			| The order the post should be displayed in\.
			
		
		
		:Returns: int 
	
	

.. rst-class:: public

	.. php:method:: public setMenuOrder( $order)
	
		.. rst-class:: phpdoc-description
		
			| Sets the menu order\.
			
		
		
		:Parameters:
			* **$order**  

		
	
	

.. rst-class:: public

	.. php:method:: public meta( $key=null, $defaultValue=null)
	
		.. rst-class:: phpdoc-description
		
			| Returns the post\'s metadata
			
		
		
		:Parameters:
			* **$key** (null | string)  The metadata key to return a value for, passing null returns all of the metadata
			* **$defaultValue** (null | mixed)  The default value to return if the key doesn't exist

		
		:Returns: array | mixed | null 
	
	

.. rst-class:: public

	.. php:method:: public updateMeta( $key, $value)
	
		.. rst-class:: phpdoc-description
		
			| Updates metadata value
			
		
		
		:Parameters:
			* **$key**  
			* **$value**  

		
	
	

.. rst-class:: public

	.. php:method:: public deleteMeta( $key)
	
		.. rst-class:: phpdoc-description
		
			| Deletes a metadata item
			
		
		
		:Parameters:
			* **$key**  

		
	
	

.. rst-class:: public

	.. php:method:: public editLink()
	
		.. rst-class:: phpdoc-description
		
			| Returns the edit link for this post\.
			
		
		
		:Returns: null | string 
	
	

.. rst-class:: public

	.. php:method:: public permalink()
	
		.. rst-class:: phpdoc-description
		
			| Returns the post\'s permalink
			
		
		
		:Returns: null | string 
	
	

.. rst-class:: public

	.. php:method:: public categories()
	
		.. rst-class:: phpdoc-description
		
			| Returns the list of categories this post belongs to
			
		
		
		:Returns: :any:`\\ILab\\Stem\\Models\\Term\[\] <ILab\\Stem\\Models\\Term>` | null 
	
	

.. rst-class:: public

	.. php:method:: public addCategory( $category)
	
		.. rst-class:: phpdoc-description
		
			| Adds a category to the post
			
		
		
		:Parameters:
			* **$category** (:any:`ILab\\Stem\\Models\\Term <ILab\\Stem\\Models\\Term>`)  

		
	
	

.. rst-class:: public

	.. php:method:: public removeCategory( $category)
	
		.. rst-class:: phpdoc-description
		
			| Removes a category from the post
			
		
		
		:Parameters:
			* **$category** (:any:`ILab\\Stem\\Models\\Term <ILab\\Stem\\Models\\Term>`)  

		
	
	

.. rst-class:: public

	.. php:method:: public topCategory()
	
		.. rst-class:: phpdoc-description
		
			| Returns the top category
			
		
		
		:Returns: :any:`\\ILab\\Stem\\Models\\Term <ILab\\Stem\\Models\\Term>` | null 
	
	

.. rst-class:: public

	.. php:method:: public tags()
	
		.. rst-class:: phpdoc-description
		
			| Returns the associated tags with this post
			
		
		
		:Returns: :any:`\\ILab\\Stem\\Models\\Term\[\] <ILab\\Stem\\Models\\Term>` 
	
	

.. rst-class:: public

	.. php:method:: public addTag( $tag)
	
		.. rst-class:: phpdoc-description
		
			| Adds a tag to a post
			
		
		
		:Parameters:
			* **$tag** (:any:`ILab\\Stem\\Models\\Term <ILab\\Stem\\Models\\Term>`)  

		
	
	

.. rst-class:: public

	.. php:method:: public removeTag( $tag)
	
		.. rst-class:: phpdoc-description
		
			| Removes a tag from the post
			
		
		
		:Parameters:
			* **$tag** (:any:`ILab\\Stem\\Models\\Term <ILab\\Stem\\Models\\Term>`)  

		
	
	

.. rst-class:: public

	.. php:method:: public hasChanges()
	
		.. rst-class:: phpdoc-description
		
			| Determines if the model has changes that need to be saved or updated\.
			
		
		
		:Returns: bool 
	
	

.. rst-class:: public

	.. php:method:: public save()
	
		.. rst-class:: phpdoc-description
		
			| Saves or Updates the post
			
		
		
		:Throws: :any:`\\Exception <Exception>` 
	
	

.. rst-class:: public

	.. php:method:: public delete( $force_delete=false)
	
		.. rst-class:: phpdoc-description
		
			| Deletes the post
			
		
		
		:Parameters:
			* **$force_delete** (bool)  

		
	
	

.. rst-class:: public

	.. php:method:: public content( $stripEmptyParagraphs=false)
	
		.. rst-class:: phpdoc-description
		
			| Returns the post\'s content
			
		
		
		:Parameters:
			* **$stripEmptyParagraphs** (bool)  

		
		:Returns: null | string 
	
	

.. rst-class:: public

	.. php:method:: public setContent( $content)
	
		.. rst-class:: phpdoc-description
		
			| Updates the post\'s content
			
		
		
		:Parameters:
			* **$content**  

		
	
	

.. rst-class:: public

	.. php:method:: public videoEmbeds()
	
		.. rst-class:: phpdoc-description
		
			| Returns any video embeds that might be in the post\'s content
			
		
		
		:Returns: array 
	
	

.. rst-class:: public

	.. php:method:: public excerpt( $len=50, $force=false, $readmore=Read More, $strip=true, $allowed_tags=p a span b i br h1 h2 h3 h4 h5 ul li img blockquote)
	
		.. rst-class:: phpdoc-description
		
			| Generates the post\'s excerpt
			
		
		
		:Parameters:
			* **$len** (int)  
			* **$force** (bool)  
			* **$readmore** (string)  
			* **$strip** (bool)  
			* **$allowed_tags** (string)  

		
		:Returns: null | string 
	
	

.. rst-class:: protected

	.. php:method:: protected getACFProperty( $property, $fieldName=null, $transformer=null)
	
		.. rst-class:: phpdoc-description
		
			| Fetches an ACF field and assigns it to a class property
			
		
		
		:Parameters:
			* **$property** (string)  
			* **$fieldName** (string | null)  
			* **$transformer** (null | callable)  

		
		:Returns: mixed | null 
		:Throws: :any:`\\Samrap\\Acf\\Exceptions\\BuilderException <Samrap\\Acf\\Exceptions\\BuilderException>` 
	
	

.. rst-class:: protected

	.. php:method:: protected setACFProperty( $property, $fieldName, $value, $transformer=null)
	
		.. rst-class:: phpdoc-description
		
			| Sets a property backed by ACF and signals a change
			
		
		
		:Parameters:
			* **$property** (string)  
			* **$fieldName** (string)  
			* **$value** (mixed | null)  
			* **$transformer** (null | callable)  

		
	
	

.. rst-class:: public

	.. php:method:: public field( $field, $defaultValue=null)
	
		.. rst-class:: phpdoc-description
		
			| Fetches the value for an ACF field
			
		
		
		:Parameters:
			* **$field**  
			* **$defaultValue** (mixed | null)  

		
		:Returns: mixed | null 
	
	

.. rst-class:: public

	.. php:method:: public updateField( $field, $value)
	
		.. rst-class:: phpdoc-description
		
			| Updates an ACF field
			
		
		
		:Parameters:
			* **$field**  
			* **$value**  

		
	
	

.. rst-class:: public

	.. php:method:: public deleteField( $field)
	
		.. rst-class:: phpdoc-description
		
			| Deletes the value for an ACF field associated with this post
			
		
		
		:Parameters:
			* **$field**  

		
	
	

.. rst-class:: public

	.. php:method:: public related( $postTypes, $limit)
	
		.. rst-class:: phpdoc-description
		
			| Returns related posts
			
		
		
		:Parameters:
			* **$postTypes**  
			* **$limit**  

		
		:Returns: array 
	
	

.. rst-class:: public

	.. php:method:: public jsonSerialize()
	
		
	
	

.. rst-class:: public static

	.. php:method:: public static query()
	
		.. rst-class:: phpdoc-description
		
			| Creates a Query object for this post model
			
		
		
		:Returns: :any:`\\ILab\\Stem\\Models\\Query\\Query <ILab\\Stem\\Models\\Query\\Query>` 
	
	

.. rst-class:: public static

	.. php:method:: public static find( $id)
	
		.. rst-class:: phpdoc-description
		
			| Returns the post with the given id, or null if not found
			
		
		
		:Parameters:
			* **$id**  

		
		:Returns: :any:`\\ILab\\Stem\\Models\\Post <ILab\\Stem\\Models\\Post>` | null 
		:Throws: :any:`\\Exception <Exception>` 
	
	

.. rst-class:: public static

	.. php:method:: public static first()
	
		.. rst-class:: phpdoc-description
		
			| Returns the first post of this type
			
		
		
		:Returns: :any:`\\ILab\\Stem\\Models\\Post <ILab\\Stem\\Models\\Post>` | null 
	
	

.. rst-class:: public static

	.. php:method:: public static count()
	
		.. rst-class:: phpdoc-description
		
			| Returns the number of posts in the database\.  Note this incurs a DB call every time
			| this is called\.
			
		
		
		:Returns: int 
	
	

.. rst-class:: public static

	.. php:method:: public static where( ...$args)
	
		.. rst-class:: phpdoc-description
		
			| Creates a query with the initial with clause
			
		
		
		:Parameters:
			* **$args** (mixed)  

		
		:Returns: :any:`\\ILab\\Stem\\Models\\Query\\Query <ILab\\Stem\\Models\\Query\\Query>` 
		:Throws: :any:`\\Exception <Exception>` 
	
	

