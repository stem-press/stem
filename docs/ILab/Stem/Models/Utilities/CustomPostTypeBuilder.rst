.. rst-class:: phpdoctorst

.. role:: php(code)
	:language: php


CustomPostTypeBuilder
=====================


.. php:namespace:: ILab\Stem\Models\Utilities

.. php:class:: CustomPostTypeBuilder



Properties
----------

.. php:attr:: protected static adminColumns



.. php:attr:: protected static adminFilters



.. php:attr:: protected static siteSortables



.. php:attr:: protected static siteFilters



.. php:attr:: protected static postProperties



.. php:attr:: protected static names



.. php:attr:: protected static postType



Methods
-------

.. rst-class:: public

	.. php:method:: public __construct( $postType, $singularName, $pluralName=null, $slug=null)
	
		
	
	

.. rst-class:: public

	.. php:method:: public restBase( $value)
	
		.. rst-class:: phpdoc-description
		
			| The base slug that this post type will use when accessed using the REST API\.
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public showInRest( $value)
	
		.. rst-class:: phpdoc-description
		
			| Whether to expose this post type in the REST API\.
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public showInFeed( $value=false)
	
		.. rst-class:: phpdoc-description
		
			| Whether this post type shows up in the RSS feed
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public description( $value)
	
		.. rst-class:: phpdoc-description
		
			| A short descriptive summary of what the post type is\.
			
		
		
		:Parameters:
			* **$value** (string)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public isPublic( $value=true)
	
		.. rst-class:: phpdoc-description
		
			| Controls how the type is visible to authors \(showInNavMenus, showUI\) and readers \(excludeFromSearch, publiclyQueryable\)\.
			
			| \'true\' \- Implies excludeFromSearch: false, publiclyQueryable: true, showInNavMenus: true, and showUI:true\. The built\-in
			| types attachment, page, and post are similar to this\.
			| 
			| \'false\' \- Implies excludeFromSearch: true, publiclyQueryable: false, showInNavMenus: false, and showUI: false\. The built\-in
			| types nav\_menu\_item and revision are similar to this\. Best used if you\'ll provide your own editing and viewing interfaces \(or none at all\)\.
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public publicQueryable( $value)
	
		.. rst-class:: phpdoc-description
		
			| Whether queries can be performed on the front end as part of parse\_request\(\)\.
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public excludeFromSearch( $value)
	
		.. rst-class:: phpdoc-description
		
			| Whether to exclude posts with this post type from front end search results\.
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public hierarchical( $value)
	
		.. rst-class:: phpdoc-description
		
			| Whether the post type is hierarchical \(e\.g\. page\)\. Allows Parent to be specified\. The \'supports\' parameter should contain
			| \'page\-attributes\' to show the parent select box on the editor page\.
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public showInNavMenus( $value)
	
		.. rst-class:: phpdoc-description
		
			| Whether post\_type is available for selection in navigation menus\.
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public showUI( $value)
	
		.. rst-class:: phpdoc-description
		
			| Whether to generate a default UI for managing this post type in the admin\.
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public showInMenu( $value)
	
		.. rst-class:: phpdoc-description
		
			| Where to show the post type in the admin menu\. showUI must be true\.
			
			| \'false\' \- do not display in the admin menu
			| \'true\' \- display as a top level menu
			| \'some string\' \- If an existing top level page such as \'tools\.php\' or \'edit\.php?post\_type=page\', the post type will be placed as a sub menu of that\.
			
		
		
		:Parameters:
			* **$value** (bool | string)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public showInAdminBar( $value)
	
		.. rst-class:: phpdoc-description
		
			| Whether to make this post type available in the WordPress admin bar\.
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public menuPosition( $value=6)
	
		.. rst-class:: phpdoc-description
		
			| The position in the menu order the post type should appear\. showInMenu must be true\.
			
		
		
		:Parameters:
			* **$value** (int)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public menuIcon( $value)
	
		.. rst-class:: phpdoc-description
		
			| The url to the icon to be used for this menu or the name of the dashicon to use
			
		
		
		:Parameters:
			* **$value** (string)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public canExport( $value)
	
		.. rst-class:: phpdoc-description
		
			| Can this post\_type be exported\.
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public deleteWithUser( $value)
	
		.. rst-class:: phpdoc-description
		
			| Whether to delete posts of this type when deleting a user\. If true, posts of this type belonging to the user will
			| be moved to trash when then user is deleted\.
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public hasArchive( $value)
	
		.. rst-class:: phpdoc-description
		
			| Enables post type archives\. Will use $post\_type as archive slug by default\.
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public queryVar( $value)
	
		.. rst-class:: phpdoc-description
		
			| Sets the query\_var key for this post type\.
			
		
		
		:Parameters:
			* **$value** (bool | string)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public capabilityType( $value=post)
	
		.. rst-class:: phpdoc-description
		
			| The string to use to build the read, edit, and delete capabilities
			
		
		
		:Parameters:
			* **$value** (string)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public mapMetaCap( $value)
	
		.. rst-class:: phpdoc-description
		
			| Whether to use the internal default meta capability handling\.
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public featuredImageName( $value)
	
		.. rst-class:: phpdoc-description
		
			| The title for "Featured Image" if supports thumbnail
			
		
		
		:Parameters:
			* **$value**  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public editCapabilities( $editPost=edit\_post, $editPosts=edit\_posts, $editOthersPosts=edit\_others\_posts, $editPublishedPosts=edit\_published\_posts, $editPrivatePosts=edit\_private\_posts)
	
		.. rst-class:: phpdoc-description
		
			| Defines the capabilities required for various editing functions
			
		
		
		:Parameters:
			* **$editPost** (string)  
			* **$editPosts** (string)  
			* **$editOthersPosts** (string)  
			* **$editPublishedPosts** (string)  
			* **$editPrivatePosts** (string)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public publishCapabilities( $createPosts=edit\_posts, $publishPosts=publish\_posts)
	
		.. rst-class:: phpdoc-description
		
			| Defines the capabilities required for various publishing functions
			
		
		
		:Parameters:
			* **$createPosts** (string)  
			* **$publishPosts** (string)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public readCapabilities( $read=read, $readPost=read\_post, $readPrivatePosts=read\_private\_posts)
	
		.. rst-class:: phpdoc-description
		
			| Defines the capabilities required for various reading functions
			
		
		
		:Parameters:
			* **$read** (string)  
			* **$readPost** (string)  
			* **$readPrivatePosts** (string)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public deleteCapabilities( $deletePost=delete\_post, $deletePosts=delete\_posts, $deletePublishedPosts=delete\_published\_posts, $deleteOthersPosts=delete\_others\_posts, $deletePrivatePosts=delete\_private\_posts)
	
		.. rst-class:: phpdoc-description
		
			| Defines the capabilities required for various delete functions
			
		
		
		:Parameters:
			* **$deletePost** (string)  
			* **$deletePosts** (string)  
			* **$deletePublishedPosts** (string)  
			* **$deleteOthersPosts** (string)  
			* **$deletePrivatePosts** (string)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public rewrite( $value)
	
		.. rst-class:: phpdoc-description
		
			| Enable/disable rewrites for this CPT\.
			
			| If you pass in a string, this will be used as the permalink structure\.  See the following for more information:
			| https://github\.com/johnbillion/extended\-cpts/wiki/Custom\-permalink\-structures
			
		
		
		:Parameters:
			* **$value** (bool | string)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public rewriteSlug( $value)
	
		.. rst-class:: phpdoc-description
		
			| The permalink structure slug\. Defaults to the $post\_type value\.
			
		
		
		:Parameters:
			* **$value** (string)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public rewriteWithFront( $value)
	
		.. rst-class:: phpdoc-description
		
			| Should the permalink structure be prepended with the front base\. \(example: if your permalink structure is /blog/,
			| then your links will be: false\-\>/news/, true\-\>/blog/news/\)
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public rewriteFeeds( $value)
	
		.. rst-class:: phpdoc-description
		
			| Should a feed permalink structure be built for this post type
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public rewritePages( $value)
	
		.. rst-class:: phpdoc-description
		
			| Should the permalink structure provide for pagination
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public rewriteEPMask( $value)
	
		.. rst-class:: phpdoc-description
		
			| Assign an endpoint mask for this post type
			
		
		
		:Parameters:
			* **$value**  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public supportsTitle( $value)
	
		.. rst-class:: phpdoc-description
		
			| CPT supports titles
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public supportsEditor( $value)
	
		.. rst-class:: phpdoc-description
		
			| CPT supports the content editor
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public supportsAuthor( $value)
	
		.. rst-class:: phpdoc-description
		
			| CPT supports assigning authors
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public supportsThumbnail( $value)
	
		.. rst-class:: phpdoc-description
		
			| CPT supports thumbnails \(featured image\)
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public supportsExcerpt( $value)
	
		.. rst-class:: phpdoc-description
		
			| CPT supports excerpts
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public supportsTrackbacks( $value)
	
		.. rst-class:: phpdoc-description
		
			| CPT supports trackbacks
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public supportsCustomFields( $value)
	
		.. rst-class:: phpdoc-description
		
			| CPT supports custom fields
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public supportsRevisions( $value)
	
		.. rst-class:: phpdoc-description
		
			| CPT supports revisions
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public supportsPageAttributes( $value)
	
		.. rst-class:: phpdoc-description
		
			| CPT supports page attributes
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public supportsPostFormats( $value)
	
		.. rst-class:: phpdoc-description
		
			| CPT supports post formats
			
		
		
		:Parameters:
			* **$value** (bool)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public supports( $items)
	
		.. rst-class:: phpdoc-description
		
			| Specify all the things this CPT supports
			
		
		
		:Parameters:
			* **$items** (array)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public addSiteSortable( $key, $attributes)
	
		.. rst-class:: phpdoc-description
		
			| Adds a custom sorting value for front end development\. See the following for more information:
			| https://github\.com/johnbillion/extended\-cpts/wiki/Query\-vars\-for\-sorting
			
		
		
		:Parameters:
			* **$key**  
			* **$attributes**  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public addSiteFilter( $key, $attributes)
	
		.. rst-class:: phpdoc-description
		
			| Adds query filters for front end queries\.  See the following for more information:
			| https://github\.com/johnbillion/extended\-cpts/wiki/Query\-vars\-for\-filtering
			
		
		
		:Parameters:
			* **$key**  
			* **$attributes**  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public addAdminMetaColumn( $name, $metaKey, $title=null, $dateFormat=null, $cap=null)
	
		.. rst-class:: phpdoc-description
		
			| Add a column to the admin for meta values
			
		
		
		:Parameters:
			* **$name** (string)  
			* **$metaKey** (string)  
			* **$title** (null | string)  
			* **$dateFormat** (null | string)  
			* **$cap** (null | string)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public addAdminACFColumn( $name, $field, $title, $cap=null)
	
		
	
	

.. rst-class:: public

	.. php:method:: public addAdminTaxonomyColumn( $name, $taxonomy, $title=null, $link=null, $cap=null)
	
		.. rst-class:: phpdoc-description
		
			| Add a column to the admin for a taxonomy type
			
		
		
		:Parameters:
			* **$name** (string)  
			* **$taxonomy** (string)  
			* **$title** (null | string)  
			* **$link** (null | string)  
			* **$cap** (null | string)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public addAdminFeaturedImage( $name, $title, $imageSize=thumbnail, $width=null, $height=null, $cap=null)
	
		.. rst-class:: phpdoc-description
		
			| Add a column to the admin for the featured image
			
		
		
		:Parameters:
			* **$name** (string)  
			* **$title** (string)  
			* **$imageSize** (string)  
			* **$width** (null | int)  
			* **$height** (null | int)  
			* **$cap** (null | string)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public addAdminPostFieldColumn( $name, $postField, $title=null, $dateFormat=null, $cap=null)
	
		.. rst-class:: phpdoc-description
		
			| Add a column to the admin for a field in the post
			
		
		
		:Parameters:
			* **$name** (string)  
			* **$postField** (string)  
			* **$title** (null | string)  
			* **$dateFormat** (null | string)  
			* **$cap** (null | string)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public addAdminMetaDropdownFilter( $name, $metaKey, $title=null, $options=null, $cap=null)
	
		.. rst-class:: phpdoc-description
		
			| Adds a dropdown filter for a meta key\.  If no options are specified, all of the unique existing values for that
			| meta key are used\.
			
		
		
		:Parameters:
			* **$name** (string)  
			* **$metaKey** (string)  
			* **$title** (null | string)  
			* **$options** (null | array | callable)  
			* **$cap** (null | string)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public addAdminMetaSearchFilter( $name, $metaKey, $title=null, $cap=null)
	
		.. rst-class:: phpdoc-description
		
			| Adds a text search filter to the admin for a meta value
			
		
		
		:Parameters:
			* **$name** (string)  
			* **$metaKey** (string)  
			* **$title** (null | string)  
			* **$cap** (null | string)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public addAdminMetaExistsDropdown( $name, $options, $title=null, $cap=null)
	
		.. rst-class:: phpdoc-description
		
			| Adds a drop down that filters items that have the meta value with the given key\.
			
		
		
		:Parameters:
			* **$name** (string)  
			* **$options** (array | callable)  
			* **$title** (null | string)  
			* **$cap** (null | string)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public addAdminTaxonomyDropdown( $name, $taxonomy, $title=null, $cap=null)
	
		.. rst-class:: phpdoc-description
		
			| Displays a select dropdown populated with all the available terms for the given taxonomy
			
		
		
		:Parameters:
			* **$name** (string)  
			* **$taxonomy** (string)  
			* **$title** (null | string)  
			* **$cap** (null | string)  

		
		:Returns: $this 
	
	

.. rst-class:: public

	.. php:method:: public register()
	
		.. rst-class:: phpdoc-description
		
			| Registers the custom post type
			
		
		
	
	

