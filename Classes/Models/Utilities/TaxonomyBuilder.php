<?php

namespace Stem\Models\Utilities;

use Stem\Models\InvalidPropertiesException;

/**
 * Easily build custom taxonomies
 *
 * @package Stem\Models\Utilities
 *
 * @property bool $public
 * @property bool $canQuery
 * @property bool $ui
 * @property bool $showInMenu
 * @property bool $showInNavMenus
 * @property bool $showInRest
 * @property string $restBase
 * @property string $restControllerClass
 * @property bool $showTagCloud
 * @property bool $showInQuickEdit
 * @property callable $metaboxCallback
 * @property bool $showAdminColumn
 * @property string $description
 * @property bool $heirarchical
 * @property callable $updateCountCallback
 * @property bool|string $queryVar
 * @property bool $sort
 * @property bool|array $rewrite
 * @property string $rewriteSlug
 * @property bool $rewriteWithFront
 * @property bool $rewriteHierarchical
 * @property string $rewriteEndpointMask
 * @property string $capabilityManageTerms
 * @property string $capabilityEditTerms
 * @property string $capabilityDeleteTerms
 * @property string $capabilityAssignTerms
 *
 * @method $this setPublic(bool $public)
 * @method $this setCanQuery(bool $canQuery)
 * @method $this setUi(bool $ui)
 * @method $this setShowInMenu(bool $showInMenu)
 * @method $this setShowInNavMenus(bool $showInNavMenus)
 * @method $this setShowInRest(bool $showInRest)
 * @method $this setRestBase(string $restBase)
 * @method $this setRestControllerClass(string $restControllerClass)
 * @method $this setShowTagCloud(bool $showTagCloud)
 * @method $this setShowInQuickEdit(bool $showInQuickEdit)
 * @method $this setMetaboxCallback(callable $metaboxCallback)
 * @method $this setShowAdminColumn(bool $showAdminColumn)
 * @method $this setShowDescription(string $description)
 * @method $this setHeirarchical(bool $heirarchical)
 * @method $this setUpdateCountCallback(callable $updateCountCallback)
 * @method $this setQueryVar(bool|string $queryVar)
 * @method $this setSort(bool $sort)
 * @method $this setRewrite(bool|array $rewrite)
 * @method $this setRewriteSlug(string $rewriteSlug)
 * @method $this setRewriteWithFront(bool $rewriteWithFront)
 * @method $this setRewriteHierarchical(bool $rewriteHierarchical)
 * @method $this setRewriteEndpointMask(string $rewriteEndpointMask)
 * @method $this setCapabilityManageTerms(string $capabilityManageTerms)
 * @method $this setCapabilityEditTerms(string $capabilityEditTerms)
 * @method $this setCapabilityDeleteTerms(string $capabilityDeleteTerms)
 * @method $this setCapabilityAssignTerms(string $capabilityAssignTerms)
 */
class TaxonomyBuilder {
	private $identifier = null;
	private $args = [];

	private static $argumentsMap = [
		'public' => 'public',
		'canQuery' => 'publicly_queryable',
		'ui' => 'show_ui',
		'showInMenu' => 'show_in_menu',
		'showInNavMenus' => 'show_in_nav_menus',
		'showInRest' => 'show_in_rest',
		'restBase' => 'rest_base',
		'restControllerClass' => 'rest_controller_class',
		'showTagcloud' => 'show_tagcloud',
		'showInQuickEdit' => 'show_in_quick_edit',
		'metaboxCallback' => 'meta_box_cb',
		'showAdminColumn' => 'show_admin_column',
		'description' => 'description',
		'hierarchical' => 'hierarchical',
		'updateCountCallback' => 'update_count_callback',
		'queryVar' => 'query_var',
		'capabilities' => 'capabilities',
		'sort' => 'sort',
        'rewriteSlug' => 'slug',
	    'rewriteWithFront' => 'with_front',
		'rewriteHierarchical' => 'hierarchical',
		'rewriteEndpointMask' => 'ep_mask',
		'capabilityManageTerms' => 'manage_terms',
        'capabilityEditTerms' => 'manage_categories',
		'capabilityDeleteTerms' => 'delete_terms',
        'capabilityAssignTerms' => 'assign_terms'
	];

	public function __construct($identifier, $singularName, $pluralName) {
		$this->args['labels'] = [
			'name' => $pluralName,
			'singular_name' => $singularName,
			'menu_name' => $pluralName,
			'all_items' => "All $pluralName",
			'edit_items' => "Edit $singularName",
			'view_item' => "View $singularName",
			'update_item' => "Update $singularName",
			'add_new_item' => "Add New $singularName",
			'new_item_name' => "New $singularName Name",
			'parent_item' => "Parent $singularName",
			'parent_item_color' => "Parent $singularName:",
			'search_items' => "Search $pluralName",
			'popular_items' => "Popular $pluralName",
			'separate_items_with_commas' => "Separate ".strtolower($pluralName)." with commas",
			'add_or_remove_items' => "Add or remove ".strtolower($pluralName),
			'choose_from_most_used' => "Choose from the most used ".strtolower($pluralName),
			'not_found' => "No ".strtolower($pluralName)." found.",
			'back_to_items' => "Back to ".strtolower($pluralName)
		];

		$this->identifier = $identifier;
	}

	public function __set($name, $value) {
		if (in_array($name, ['rewriteSlug', 'rewriteWithFront', 'rewriteHierarchical', 'rewriteEndpointMask'])) {
			if (empty($this->args['rewrite'])) {
				$this->args['rewrite'] = [];
			}

			$key = static::$argumentsMap[$name];
			$this->args['rewrite'][$key] = $value;
		} else if (in_array($name, ['capabilityManageTerms', 'capabilityEditTerms', 'capabilityDeleteTerms', 'capabilityAssignTerms'])) {
			if (empty($this->args['capabilities'])) {
				$this->args['capabilities'] = [];
			}

			$key = static::$argumentsMap[$name];
			$this->args['capabilities'][$key] = $value;
		} else {
			if (isset(static::$argumentsMap[$name])) {
				$key = static::$argumentsMap[$name];

				$this->args[$key] = $value;
			}
		}
	}

	public function __get($name) {
		if (in_array($name, ['rewriteSlug', 'rewriteWithFront', 'rewriteHierarchical', 'rewriteEndpointMask'])) {
			$key = static::$argumentsMap[$name];
			return arrayPath($this->args, "rewrite/$key", null);
		} else if (in_array($name, ['capabilityManageTerms', 'capabilityEditTerms', 'capabilityDeleteTerms', 'capabilityAssignTerms'])) {
			$key = static::$argumentsMap[$name];
			return arrayPath($this->args, "capabilities/$key", null);
		} else {
			if (isset(static::$argumentsMap[$name])) {
				$key = static::$argumentsMap[$name];
				return arrayPath($this->args, $key, null);
			}
		}

		trigger_error("Unknown property '".__CLASS__."::$name", E_USER_ERROR);
	}

	public function __isset($name) {
		if (in_array($name, ['rewriteSlug', 'rewriteWithFront', 'rewriteHierarchical', 'rewriteEndpointMask'])) {
			if (isset($this->args['rewrite'])) {
				$key = static::$argumentsMap[$name];
				return isset($this->args['rewrite'][$key]);
			}
		} else if (in_array($name, ['capabilityManageTerms', 'capabilityEditTerms', 'capabilityDeleteTerms', 'capabilityAssignTerms'])) {
			if (isset($this->args['capabilities'])) {
				$key = static::$argumentsMap[$name];
				return isset($this->args['capabilities'][$key]);
			}
		} else {
			if (isset(static::$argumentsMap[$name])) {
				$key = static::$argumentsMap[$name];
				return isset($this->args[$key]);
			}
		}

		return false;
	}

	public function __call($name, $arguments) {
		if (strpos($name, 'set') === 0) {
			$name = lcfirst(substr($name, 3));
			$this->__set($name, $arguments[0]);
			return $this;
		}

		trigger_error('Call to undefined method '.__CLASS__.'::'.$name.'()', E_USER_ERROR);
	}

	public function register() {
		register_taxonomy($this->identifier, null, $this->args);
	}
}