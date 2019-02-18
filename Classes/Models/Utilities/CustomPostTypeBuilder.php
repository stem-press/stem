<?php

namespace Stem\Models\Utilities;

class CustomPostTypeBuilder {
    protected $adminColumns = [];
    protected $adminFilters = [];
    protected $siteSortables = [];
    protected $siteFilters = [];
    protected $postProperties = [];
    protected $names = [];
    protected $postType = null;

    public function __construct($postType, $singularName, $pluralName = null, $slug = null) {
        $this->postType = $postType;

        $this->postProperties['reset_base'] = $slug ?: $postType;

        $this->names = [
            'singular' => $singularName,
            'plural' => $pluralName ?: $singularName,
            'slug' => $slug ?: $postType
        ];
    }

    //region Post Properties

    /**
     * The base slug that this post type will use when accessed using the REST API.
     * @param bool $value
     * @return $this
     */
    public function restBase($value) {
        $this->postProperties['rest_base'] = $value;
        return $this;
    }

    /**
     * Whether to expose this post type in the REST API.
     * @param bool $value
     * @return $this
     */
    public function showInRest($value) {
        $this->postProperties['show_in_rest'] = $value;
        return $this;
    }

    /**
     * Whether this post type shows up in the RSS feed
     * @param bool $value
     * @return $this
     */
    public function showInFeed($value = false) {
        $this->postProperties['show_in_feed'] = $value;
        return $this;
    }

    /**
     * A short descriptive summary of what the post type is.
     * @param string $value
     * @return $this
     */
    public function description($value) {
        $this->postProperties['description'] = $value;
        return $this;
    }

    /**
     * Controls how the type is visible to authors (showInNavMenus, showUI) and readers (excludeFromSearch, publiclyQueryable).
     *
     * 'true' - Implies excludeFromSearch: false, publiclyQueryable: true, showInNavMenus: true, and showUI:true. The built-in
     * types attachment, page, and post are similar to this.
     *
     * 'false' - Implies excludeFromSearch: true, publiclyQueryable: false, showInNavMenus: false, and showUI: false. The built-in
     * types nav_menu_item and revision are similar to this. Best used if you'll provide your own editing and viewing interfaces (or none at all).
     *
     * @param bool $value
     * @return $this
     */
    public function isPublic($value = true) {
        $this->postProperties['public'] = $value;
        return $this;
    }

    /**
     * Whether queries can be performed on the front end as part of parse_request().
     * @param bool $value
     * @return $this
     */
    public function publicQueryable($value) {
        $this->postProperties['publicly_queryable'] = $value;
        return $this;
    }

    /**
     * Whether to exclude posts with this post type from front end search results.
     * @param bool $value
     * @return $this
     */
    public function excludeFromSearch($value) {
        $this->postProperties['exclude_from_search'] = $value;
        return $this;
    }

    /**
     * Whether the post type is hierarchical (e.g. page). Allows Parent to be specified. The 'supports' parameter should contain
     * 'page-attributes' to show the parent select box on the editor page.
     *
     * @param bool $value
     * @return $this
     */
    public function hierarchical($value) {
        $this->postProperties['hierarchical'] = $value;
        return $this;
    }

    /**
     * Whether post_type is available for selection in navigation menus.
     * @param bool $value
     * @return $this
     */
    public function showInNavMenus($value) {
        $this->postProperties['show_in_nav_menus'] = $value;
        return $this;
    }

    /**
     * Whether to generate a default UI for managing this post type in the admin.
     * @param bool $value
     * @return $this
     */
    public function showUI($value) {
        $this->postProperties['show_ui'] = $value;
        return $this;
    }

    /**
     * Where to show the post type in the admin menu. showUI must be true.
     *
     * 'false' - do not display in the admin menu
     * 'true' - display as a top level menu
     * 'some string' - If an existing top level page such as 'tools.php' or 'edit.php?post_type=page', the post type will be placed as a sub menu of that.
     *
     * @param bool|string $value
     * @return $this
     */
    public function showInMenu($value) {
        $this->postProperties['show_in_menu'] = $value;
        return $this;
    }

    /**
     * Whether to make this post type available in the WordPress admin bar.
     * @param bool $value
     * @return $this
     */
    public function showInAdminBar($value) {
        $this->postProperties['show_in_admin_bar'] = $value;
        return $this;
    }

    /**
     * The position in the menu order the post type should appear. showInMenu must be true.
     * @param int $value
     * @return $this
     */
    public function menuPosition($value = 6) {
        $this->postProperties['menu_position'] = $value;
        return $this;
    }

    /**
     * The url to the icon to be used for this menu or the name of the dashicon to use
     * @param string $value
     * @return $this
     */
    public function menuIcon($value) {
        $this->postProperties['menu_icon'] = $value;
        return $this;
    }

    /**
     * Can this post_type be exported.
     * @param bool $value
     * @return $this
     */
    public function canExport($value) {
        $this->postProperties['can_export'] = $value;
        return $this;
    }

    /**
     * Whether to delete posts of this type when deleting a user. If true, posts of this type belonging to the user will
     * be moved to trash when then user is deleted.
     * @param bool $value
     * @return $this
     */
    public function deleteWithUser($value) {
        $this->postProperties['delete_with_user'] = $value;
        return $this;
    }

    /**
     * Enables post type archives. Will use $post_type as archive slug by default.
     * @param bool $value
     * @return $this
     */
    public function hasArchive($value) {
        $this->postProperties['has_archive'] = $value;
        return $this;
    }

    /**
     * Sets the query_var key for this post type.
     * @param bool|string $value
     * @return $this
     */
    public function queryVar($value) {
        $this->postProperties['query_var'] = $value;
        return $this;
    }

    /**
     * The string to use to build the read, edit, and delete capabilities
     * @param string $value
     * @return $this
     */
    public function capabilityType($value = 'post') {
        $this->postProperties['capability_type'] = $value;
        return $this;
    }

    /**
     * Whether to use the internal default meta capability handling.
     * @param bool $value
     * @return $this
     */
    public function mapMetaCap($value) {
        $this->postProperties['map_meta_cap'] = $value;
        return $this;
    }

    /**
     * The title for "Featured Image" if supports thumbnail
     * @param $value
     * @return $this
     */
    public function featuredImageName($value) {
        $this->postProperties['featured_image'] = $value;
        return $this;
    }

    /**
     * Defines the capabilities required for various editing functions
     *
     * @param string $editPost
     * @param string $editPosts
     * @param string $editOthersPosts
     * @param string $editPublishedPosts
     * @param string $editPrivatePosts
     * @return $this
     */
    public function editCapabilities($editPost = 'edit_post', $editPosts = 'edit_posts', $editOthersPosts = 'edit_others_posts', $editPublishedPosts = 'edit_published_posts', $editPrivatePosts = 'edit_private_posts') {
        if (!isset($this->postProperties['capabilities'])) {
            $this->postProperties['capabilities'] = [];
        }

        $this->postProperties['capabilities']["edit_post"] = $editPost;
        $this->postProperties['capabilities']["edit_posts"] = $editPosts;
        $this->postProperties['capabilities']["edit_others_posts"] = $editOthersPosts;
        $this->postProperties['capabilities']["edit_private_posts"] = $editPrivatePosts;
        $this->postProperties['capabilities']["edit_published_posts"] = $editPublishedPosts;

        return $this;
    }

    /**
     * Defines the capabilities required for various publishing functions
     *
     * @param string $createPosts
     * @param string $publishPosts
     * @return $this
     */
    public function publishCapabilities($createPosts = 'edit_posts', $publishPosts = 'publish_posts') {
        if (!isset($this->postProperties['capabilities'])) {
            $this->postProperties['capabilities'] = [];
        }

        $this->postProperties['capabilities']["create_posts"] = $createPosts;
        $this->postProperties['capabilities']["publish_posts"] = $publishPosts;

        return $this;
    }

    /**
     * Defines the capabilities required for various reading functions
     *
     * @param string $read
     * @param string $readPost
     * @param string $readPrivatePosts
     * @return $this
     */
    public function readCapabilities($read = 'read', $readPost = 'read_post', $readPrivatePosts = 'read_private_posts') {
        if (!isset($this->postProperties['capabilities'])) {
            $this->postProperties['capabilities'] = [];
        }

        $this->postProperties['capabilities']["read_post"] = $readPost;
        $this->postProperties['capabilities']["read_private_posts"] = $readPrivatePosts;
        $this->postProperties['capabilities']["read"] = $read;

        return $this;

    }

    /**
     * Defines the capabilities required for various delete functions
     *
     * @param string $deletePost
     * @param string $deletePosts
     * @param string $deletePublishedPosts
     * @param string $deleteOthersPosts
     * @param string $deletePrivatePosts
     * @return $this
     */
    public function deleteCapabilities($deletePost = 'delete_post', $deletePosts = 'delete_posts', $deletePublishedPosts = 'delete_published_posts', $deleteOthersPosts = 'delete_others_posts', $deletePrivatePosts = 'delete_private_posts') {
        if (!isset($this->postProperties['capabilities'])) {
            $this->postProperties['capabilities'] = [];
        }

        $this->postProperties['capabilities']["delete_post"] = $deletePost;
        $this->postProperties['capabilities']["delete_posts"] = $deletePosts;
        $this->postProperties['capabilities']["delete_private_posts"] = $deletePrivatePosts;
        $this->postProperties['capabilities']["delete_published_posts"] = $deletePublishedPosts;
        $this->postProperties['capabilities']["delete_others_posts"] = $deleteOthersPosts;

        return $this;
    }

    /**
     * Enable/disable rewrites for this CPT.
     *
     * If you pass in a string, this will be used as the permalink structure.  See the following for more information:
     * https://github.com/johnbillion/extended-cpts/wiki/Custom-permalink-structures
     *
     * @param bool|string $value
     * @return $this
     */
    public function rewrite($value) {
        if (is_string($value)) {
            $this->postProperties['rewrite'] = [
                'permastruct' => $value
            ];
        } else if (is_bool($value)) {
            if ($value) {
                $this->postProperties['rewrite'] = [
                    'slug' => $this->names['slug'],
                    'with_front' => true,
                    'feeds' => (isset($this->postProperties['has_archive'])) ? $this->postProperties['has_archive'] : false,
                    'pages' => true,
                    'ep_mask' =>EP_PERMALINK
                ];

            } else {
                $this->postProperties['rewrite'] = false;
            }
        }

        return $this;
    }

    /**
     * The permalink structure slug. Defaults to the $post_type value.
     * @param string $value
     * @return $this
     */
    public function rewriteSlug($value) {
        if (!isset($this->postProperties['rewrite']) || ($this->postProperties['rewrite'] === false)) {
            $this->rewrite(true);
        }

        $this->postProperties['rewrite']['slug'] = $value;

        return $this;
    }

    /**
     * Should the permalink structure be prepended with the front base. (example: if your permalink structure is /blog/,
     * then your links will be: false->/news/, true->/blog/news/)
     * @param bool $value
     * @return $this
     */
    public function rewriteWithFront($value) {
        if (!isset($this->postProperties['rewrite']) || ($this->postProperties['rewrite'] === false)) {
            $this->rewrite(true);
        }

        $this->postProperties['rewrite']['with_front'] = $value;

        return $this;
    }

    /**
     * Should a feed permalink structure be built for this post type
     * @param bool $value
     * @return $this
     */
    public function rewriteFeeds($value) {
        if (!isset($this->postProperties['rewrite']) || ($this->postProperties['rewrite'] === false)) {
            $this->rewrite(true);
        }

        $this->postProperties['rewrite']['feeds'] = $value;

        return $this;
    }

    /**
     * Should the permalink structure provide for pagination
     * @param bool $value
     * @return $this
     */
    public function rewritePages($value) {
        if (!isset($this->postProperties['rewrite']) || ($this->postProperties['rewrite'] === false)) {
            $this->rewrite(true);
        }

        $this->postProperties['rewrite']['pages'] = $value;

        return $this;
    }

    /**
     * Assign an endpoint mask for this post type
     * @param $value
     * @return $this
     */
    public function rewriteEPMask($value) {
        if (!isset($this->postProperties['rewrite']) || ($this->postProperties['rewrite'] === false)) {
            $this->rewrite(true);
        }

        $this->postProperties['rewrite']['ep_mask'] = $value;

        return $this;
    }

    /**
     * Enables/disables an item for support
     * @param bool $enabled
     * @param $item
     */
    private function enableDisableSupports($enabled, $item) {
        if (!isset($this->postProperties['supports'])) {
            $this->postProperties['supports'] = [];
        }


        if ($enabled) {
            if (!in_array($item, $this->postProperties['supports'])) {
                $this->postProperties['supports'][] = $item;
            }
        } else {
            $this->postProperties['supports'] = array_diff($this->postProperties['supports'], [$item]);
            if (empty($this->postProperties['supports'])) {
	            $this->postProperties['supports'] = [];
            }
        }
    }

    /**
     * CPT supports titles
     * @param bool $value
     * @return $this
     */
    public function supportsTitle($value) {
        $this->enableDisableSupports($value, 'title');

        return $this;
    }

    /**
     * CPT supports the content editor
     * @param bool $value
     * @return $this
     */
    public function supportsEditor($value) {
        $this->enableDisableSupports($value, 'editor');

        return $this;
    }

    /**
     * CPT supports assigning authors
     * @param bool $value
     * @return $this
     */
    public function supportsAuthor($value) {
        $this->enableDisableSupports($value, 'author');

        return $this;
    }

    /**
     * CPT supports thumbnails (featured image)
     * @param bool $value
     * @return $this
     */
    public function supportsThumbnail($value) {
        $this->enableDisableSupports($value, 'thumbnail');

        return $this;
    }

    /**
     * CPT supports excerpts
     * @param bool $value
     * @return $this
     */
    public function supportsExcerpt($value) {
        $this->enableDisableSupports($value, 'excerpt');

        return $this;
    }

    /**
     * CPT supports trackbacks
     * @param bool $value
     * @return $this
     */
    public function supportsTrackbacks($value) {
        $this->enableDisableSupports($value, 'trackbacks');

        return $this;
    }

    /**
     * CPT supports custom fields
     * @param bool $value
     * @return $this
     */
    public function supportsCustomFields($value) {
        $this->enableDisableSupports($value, 'custom-fields');

        return $this;
    }

    /**
     * CPT supports revisions
     * @param bool $value
     * @return $this
     */
    public function supportsRevisions($value) {
        $this->enableDisableSupports($value, 'revisions');

        return $this;
    }

    /**
     * CPT supports page attributes
     * @param bool $value
     * @return $this
     */
    public function supportsPageAttributes($value) {
        $this->enableDisableSupports($value, 'page-attributes');

        return $this;
    }

    /**
     * CPT supports post formats
     * @param bool $value
     * @return $this
     */
    public function supportsPostFormats($value) {
        $this->enableDisableSupports($value, 'post-formats');

        return $this;
    }

    /**
     * Specify all the things this CPT supports
     * @param array $items
     * @return $this
     */
    public function supports($items) {
        $this->postProperties['supports'] = $items;

        return $this;
    }


    //endregion

    //region Site Sortables/Filters

    /**
     * Adds a custom sorting value for front end development. See the following for more information:
     * https://github.com/johnbillion/extended-cpts/wiki/Query-vars-for-sorting
     *
     * @param $key
     * @param $attributes
     * @return $this
     */
    public function addSiteSortable($key, $attributes) {
        $this->siteSortables[$key] = $attributes;
        return $this;
    }

    /**
     * Adds query filters for front end queries.  See the following for more information:
     * https://github.com/johnbillion/extended-cpts/wiki/Query-vars-for-filtering
     *
     * @param $key
     * @param $attributes
     * @return $this
     */
    public function addSiteFilter($key, $attributes) {
        $this->siteFilters[$key] = $attributes;
        return $this;
    }

    //endregion

    //region Admin Columns

    /**
     * Add a column to the admin for meta values
     *
     * @param string $name
     * @param string $metaKey
     * @param null|string $title
     * @param null|string $dateFormat
     * @param null|string $cap
     * @return $this
     */
    public function addAdminMetaColumn($name, $metaKey, $title = null, $dateFormat = null, $cap = null) {
        $def = ['meta_key' => $metaKey];

        if (!empty($title)) {
            $def['title'] = $title;
        }

        if (!empty($dateFormat)) {
            $def['date_format'] = $dateFormat;
        }

        if (!empty($cap)) {
            $def['cap'] = $cap;
        }

        $this->adminColumns[$name] = $def;
        return $this;
    }

    public function addAdminACFColumn($name, $field, $title, $cap = null) {
        $def = [
            'title' => $title,
            'function' => function() use ($field) {
                global $post;
                $val = get_field($field, $post->ID);
                return $val;
            }
        ];

        if (!empty($cap)) {
            $def['cap'] = $cap;
        }

        $this->adminColumns[$name] = $def;
        return $this;
    }

    /**
     * Add a column to the admin for a taxonomy type
     *
     * @param string $name
     * @param string $taxonomy
     * @param null|string $title
     * @param null|string $link
     * @param null|string $cap
     * @return $this
     */
    public function addAdminTaxonomyColumn($name, $taxonomy, $title = null, $link = null, $cap = null) {
        $def = ['taxonomy' => $taxonomy];

        if (!empty($title)) {
            $def['title'] = $title;
        }

        if (!empty($link)) {
            $def['link'] = $link;
        }

        if (!empty($cap)) {
            $def['cap'] = $cap;
        }

        $this->adminColumns[$name] = $def;
        return $this;
    }

    /**
     * Add a column to the admin for the featured image
     * @param string $name
     * @param string $title
     * @param string $imageSize
     * @param null|int $width
     * @param null|int $height
     * @param null|string $cap
     * @return $this
     */
    public function addAdminFeaturedImage($name, $title, $imageSize = 'thumbnail', $width = null, $height = null, $cap = null) {
        $def = [
            'title' => $title,
            'featured_image' => $imageSize
        ];

        if (!empty($width) && !empty($height)) {
            $def['width'] = $width;
            $def['height'] = $height;
        }

        if (!empty($cap)) {
            $def['cap'] = $cap;
        }

        $this->adminColumns[$name] = $def;
        return $this;
    }

    /**
     * Add a column to the admin for a field in the post
     * @param string $name
     * @param string $postField
     * @param null|string $title
     * @param null|string $dateFormat
     * @param null|string $cap
     * @return $this
     */
    public function addAdminPostFieldColumn($name, $postField, $title = null, $dateFormat = null, $cap = null) {
        $def = ['post_filed' => $postField];

        if (!empty($title)) {
            $def['title'] = $title;
        }

        if (!empty($date_format)) {
            $def['date_format'] = $dateFormat;
        }

        if (!empty($cap)) {
            $def['cap'] = $cap;
        }

        $this->adminColumns[$name] = $def;
        return $this;
    }

    //endregion

    //region Admin Filters

    /**
     * Adds a dropdown filter for a meta key.  If no options are specified, all of the unique existing values for that
     * meta key are used.
     *
     * @param string $name
     * @param string $metaKey
     * @param null|string $title
     * @param null|array|callable $options
     * @param null|string $cap
     * @return $this
     */
    public function addAdminMetaDropdownFilter($name, $metaKey, $title = null, $options = null, $cap = null) {
        $def = ['meta_key' => $metaKey];

        if (!empty($title)) {
            $def['title'] = $title;
        }

        if (!empty($options)) {
            $def['options'] = $options;
        }

        if (!empty($cap)) {
            $def['cap'] = $cap;
        }

        $this->adminFilters[$name] = $def;
        return $this;
    }

    /**
     * Adds a text search filter to the admin for a meta value
     *
     * @param string $name
     * @param string $metaKey
     * @param null|string $title
     * @param null|string $cap
     * @return $this
     */
    public function addAdminMetaSearchFilter($name, $metaKey, $title = null, $cap = null) {
        $def = ['meta_search_key' => $metaKey];

        if (!empty($title)) {
            $def['title'] = $title;
        }

        if (!empty($cap)) {
            $def['cap'] = $cap;
        }

        $this->adminFilters[$name] = $def;
        return $this;
    }

    /**
     * Adds a drop down that filters items that have the meta value with the given key.
     *
     * @param string $name
     * @param array|callable $options
     * @param null|string $title
     * @param null|string $cap
     * @return $this
     */
    public function addAdminMetaExistsDropdown($name, $options, $title = null, $cap = null) {
        $def = ['meta_exists' => $options];

        if (!empty($title)) {
            $def['title'] = $title;
        }

        if (!empty($cap)) {
            $def['cap'] = $cap;
        }

        $this->adminFilters[$name] = $def;
        return $this;
    }

    /**
     * Displays a select dropdown populated with all the available terms for the given taxonomy
     *
     * @param string $name
     * @param string $taxonomy
     * @param null|string $title
     * @param null|string $cap
     * @return $this
     */
    public function addAdminTaxonomyDropdown($name, $taxonomy, $title = null, $cap = null) {
        $def = ['taxonomy' => $taxonomy];

        if (!empty($title)) {
            $def['title'] = $title;
        }

        if (!empty($cap)) {
            $def['cap'] = $cap;
        }

        $this->adminFilters[$name] = $def;
        return $this;
    }

    //endregion

    //region Register

    /**
     * Registers the custom post type
     */
    public function register() {
        $args = $this->postProperties;

        if (!empty($this->adminColumns)) {
            $args['admin_cols'] = $this->adminColumns;
        }

        if (!empty($this->adminFilters)) {
            $args['admin_filters'] = $this->adminFilters;
        }

        if (!empty($this->siteSortables)) {
            $args['site_sortables'] = $this->siteSortables;
        }

        if (!empty($this->siteFilters)) {
            $args['site_filters'] = $this->siteFilters;
        }

        if (empty($args['supports'])) {
        	$args['supports'] = false;
        }

        register_extended_post_type($this->postType, $args, $this->names);
    }

    //endregion

}