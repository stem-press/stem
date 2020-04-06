<?php


namespace Stem\Models;

use Carbon\Carbon;
use Samrap\Acf\Acf;
use Stem\Core\Context;
use Stem\Models\Query\PostCollection;
use Stem\Models\Query\Query;
use Stem\Models\Utilities\ChangeManager;
use Stem\Models\Utilities\CustomPostTypeBuilder;
use Stem\Models\Utilities\PropertiesProxy;
use Stem\Utilities\Text;

/**
 * Class Post.
 *
 * Represents a WordPress post
 *
 * @property-read int|null $id
 * @property string|null $title
 * @property string|null $slug
 * @property string|null $status
 * @property int|null $menuOrder
 * @property Post|null $parent
 * @property Attachment|null $thumbnail
 * @property User|null $author
 * @property-read string|null $editLink
 * @property-read string|null $permalink
 * @property-read null|Term[] $categories
 * @property-read null|Term $topCategory
 * @property-read null|Term[] $tags
 * @property-read string|null $strippedContent
 * @property string|null $content
 * @property-read string[]|null $videoEmbeds
 * @property Carbon|null $date
 * @property Carbon|null $update
 */
class Post implements \JsonSerializable {
	/** @var bool Determines if the model is read-only. */
	protected static $isReadOnly = false;

    /** @var string Type of post */
    protected static $postType = 'post';

	/** @var string[] ACF Fields  */
	protected static $metaProperties = [];

	/** @var string[] ACF Fields  */
	protected static $readOnlyMetaProperties = [];

	/** @var array Properties */
	protected $postProperties = [
		'title' => 'post_title',
		'slug' => 'post_name',
		'status' => 'post_status',
		'menuOrder' => 'menu_order'
	];

	/** @var array Properties */
	protected $dateProperties = [
		'date' => 'post_date_gmt',
		'updated' => 'post_modified_gmt'
	];

	/** @var array Properties */
	protected $modelProperties = [
		'parent' => 'post_parent'
	];

	/** @var null|PropertiesProxy  */
	protected $propertiesProxy = null;

    /** @var Context  */
    public $context;

    /** @var bool Determines if the model is deleted or not. */
    private $deleted = false;

    /** @var \WP_Post The underlying Wordpress post */
    protected $post;

    /** @var Post|null The parent post  */
	protected $_parent = null;

    /** @var null|User The author of the post  */
	protected $_author = null;

	/** @var null|Attachment The featured image for the post */
	protected $_thumbnail = null;

    /** @var null|Term The primary category for the post  */
	protected $_topCategory = null;

	/** @var null|Term[] All categories assigned to this post */
	protected $_categories = null;

	/** @var null|Term[] All terms assigned to this post */
	protected $_tags = null;

    /** @var null|Term[] The top level categories for this post */
    protected $topCategories = null;

    /** @var null|array All terms assigned to this post for a given taxonomy */
    protected $taxes = null;

	/** @var null|string The permalink for this post  */
	protected $_permalink = null;

	/** @var null|string The edit post link for this post  */
	protected $_editPostLink = null;

    /** @var null|Carbon The date the post was published */
	protected $_date = null;

    /** @var null|Carbon The date the post was updated */
	protected $_updated = null;

    /** @var null|string The post's content */
    protected $content = null;

    /** @var null|string The post's unfiltered content */
    protected $unfilteredContent = null;

    /** @var null|string The post's excerpt */
    protected $excerpt = null;

    /** @var ChangeManager Manager for changes */
    protected $changes;

    /** @var array ACF fields cache */
    protected $fieldsCache = [];

    /** @var null|array Cached metadata */
    protected $meta = null;

    /** @var array Field validators for properties */
    protected $propertyValidators = [];

    /**
     * Post constructor.
     *
     * @param Context $context
     * @param \WP_Post $post
     */
    public function __construct(Context $context = null, \WP_Post $post = null) {
        $this->changes = new ChangeManager();

        if (empty($context)) {
        	$this->context = Context::current();
        } else {
            $this->context = $context;
        }

        if (!empty($post)) {
            $this->post = $post;
        }
    }

    //region Custom Post Type

    /**
     * The post's type
     * @return string
     */
    public static function postType() {
        return static::$postType;
    }

    /**
     * Subclasses should override to provide custom post type properties.  It's recommended to use `CustomPostTypeBuilder`
     * to define your custom post type, but you can also return an array of arguments that work with `register_post_type()`.
     *
     * @return CustomPostTypeBuilder|array|null
     */
    public static function postTypeProperties() {
        return null;
    }

	/**
	 * Indicates that this post type has multiple custom field groups.  If true,
	 * then `registerFields()` is not called, but `registerMultipleFields()` is.
	 * @return bool
	 */
    public static function multipleFieldGroups() {
    	return false;
    }

	/**
	 * Allows subclasses to configure their ACF fields in code.  Don't worry about specifying the location
	 * element, it will be added automatically if it is missing.
	 *
	 * Recommend to use `\StoutLogic\AcfBuilder\FieldsBuilder` and return the result from `build()`
	 *
	 * @return array|null
	 */
	public static function registerFields() {
		return null;
	}

	/**
	 * Allows subclasses to configure their ACF fields in code.  Don't worry about specifying the location
	 * element, it will be added automatically if it is missing.
	 *
	 * @return array|null
	 */
	public static function registerMultipleFields() {
		return null;
	}

	/**
	 * Registers any views for this model's table view
	 * @param array $views
	 * @return array
	 */
	public static function registerViews($views) {
		return $views;
	}

	/**
	 * Determines if Gutenberg should be disabled for this custom post type
	 * @return bool
	 */
	public static function disableGutenberg() {
		return false;
	}

	/**
	 * Parses ACF fields
	 * @param $prefix
	 * @param $fields
	 *
	 * @return array
	 */
	private static function parseFields($prefix, $fields) {
		$result = [];

		foreach($fields as $field) {
			$fieldName = camelCaseString($field['name']);
			if (!in_array($field['type'], ['group', 'repeater'])) {
				$result[$fieldName] = [
					'field' => $prefix.$field['name'],
					'type' => $field['type']
				];
			} else {
				$newPrefix = ($field['type'] == 'repeater') ? $prefix.$field['name'].'_'.'{INDEX}_' : $prefix.$field['name'].'_';
				$result[$fieldName] = [
					'field' => $prefix.$field['name'],
					'type' => $field['type'],
					'fields' => static::parseFields($newPrefix,  $field['sub_fields'])
				];
			}
		}

		return $result;
	}

	/**
	 * Updates this model's meta property map based on ACF field defs
	 * @param $fields
	 */
    public static function updatePropertyMap($fields) {
    	if (empty($fields) || !is_array($fields)) {
    		return;
	    }

	    static::$metaProperties[static::class] = static::parseFields('', $fields);
    }

	/**
	 * Called when a custom post type model is added to the context.
	 */
    public static function initialize() {

    }

    //endregion

	//region Post Events

	public function willDelete()  {

	}

	public function didDelete() {

	}

	public function trashed() {

	}

	public function restored() {

	}

	public function didSave() {

	}

	public function didUpdate() {

	}

	//endregion

	//region Dynamic Properties

	/**
	 * Gets a property value
	 * @param $name
	 * @param $postProperty
	 *
	 * @return mixed|null
	 */
	protected function getProperty($name, $postProperty) {
    	$privateName = '_'.$name;
    	if (property_exists($this, $privateName)) {
		    if (!empty($this->{$privateName})) {
			    return $this->{$privateName};
		    }

		    if (empty($this->post)) {
			    return null;
		    }

		    $this->{$privateName} = $this->post->{$postProperty};
		    return $this->{$privateName};
	    }

    	if ($this->changes->hasChange($postProperty)) {
    		return $this->changes->value($postProperty);
	    }

    	if (empty($this->post)) {
    		return null;
	    }

    	return $this->post->{$postProperty};
	}

	/**
	 * Gets a model property value
	 * @param $name
	 * @param $postProperty
	 *
	 * @return Attachment|Page|Post|null
	 */
	protected function getModelProperty($name, $postProperty) {
		$privateName = '_'.$name;

		$hasProperty = property_exists($this, $privateName);
		if ($hasProperty && !empty($this->{$privateName})) {
			return $this->{$privateName};
		}

		if (empty($this->post)) {
			if (!$this->changes->hasChange($postProperty)) {
				return null;
			}

			$id = $this->changes->value($postProperty);
		} else {
			$id = $this->post->{$postProperty};
		}

		$model = $this->context->modelForPostID($id);

		if ($hasProperty) {
			$this->{$privateName} = $model;
		}

		return $model;
	}

	/**
	 * Gets a a date property value
	 * @param $name
	 * @param $postProperty
	 *
	 * @return Carbon|null
	 */
	protected function getDateProperty($name, $postProperty) {
		$privateName = '_'.$name;

		$hasProperty = property_exists($this, $privateName);
		if ($hasProperty && !empty($this->{$privateName})) {
			return $this->{$privateName};
		}

		if (empty($this->post)) {
			if (!$this->changes->hasChange($postProperty)) {
				return null;
			}

			$dateVal = $this->changes->value($postProperty);
		} else {
			$dateVal = $this->post->{$postProperty};
		}

		try {
			$date = Carbon::parse($dateVal);
		} catch (\Exception $ex) {
			$date = Carbon::createFromFormat('d/m/Y', $dateVal);
		}

		$date->setTimezone(Context::timezone());

		if ($hasProperty) {
			$this->{$privateName} = $date;
		}

		return $date;
	}

	/**
	 * Sets a property value
	 * @param $name
	 * @param $postProperty
	 * @param $value
	 */
	protected function setProperty($name, $postProperty, $value) {
		$privateName = '_'.$name;
		if (property_exists($this, $privateName)) {
			$this->{$privateName} = $value;
		}

		$this->changes->addChange($postProperty, $value);
	}

	/**
	 * Sets a model property value
	 * @param $name
	 * @param $postProperty
	 * @param $value
	 *
	 * @throws \Exception
	 */
	protected function setModelProperty($name, $postProperty, $value) {
		$privateName = '_'.$name;

		if (is_numeric($value)) {
			$value = $this->context->modelForPostID($value);

			if (empty($value)) {
				throw new \Exception("Invalid post id '{$value}'.");
			}
		} else if ($value instanceof \WP_Post) {
			$value = $this->context->modelForPost($value);
		}

		if (property_exists($this, $privateName)) {
			$this->{$privateName} = $value;
		}

		$this->changes->addChange($postProperty, $value->id);
	}

	/**
	 * Sets a date property value
	 * @param $name
	 * @param $postProperty
	 * @param $value
	 */
	protected function setDateProperty($name, $postProperty, $value) {
		$privateName = '_'.$name;

		if ($value instanceof Carbon) {
			$dateVal = $value;
			$value = $value->toDateTimeString();
		} else {
			if (is_numeric($value)) {
				$dateVal = Carbon::createFromTimestamp($value);
				$value = $dateVal->toDateTimeString();
			} else {
				$dateVal = Carbon::parse($value);
			}
		}

		if (property_exists($this, $privateName)) {
			$this->{$privateName} = $dateVal;
		}

		$this->changes->addChange($postProperty, $value);
	}

	/**
	 * Magic property accessor method
	 *
	 * @param $name
	 *
	 * @return Carbon|int|mixed|Attachment|Page|Post|PropertiesProxy|null
	 */
	public function __get($name) {
    	if ($name == 'id') {
    		return empty($this->post) ? null : $this->post->ID;
	    }

    	if (isset($this->postProperties[$name])) {
            return $this->getProperty($name, $this->postProperties[$name]);
	    }

		if (isset($this->modelProperties[$name])) {
			return $this->getModelProperty($name, $this->modelProperties[$name]);
		}

		if (isset($this->dateProperties[$name])) {
			return $this->getDateProperty($name, $this->dateProperties[$name]);
		}

		$getFunction = 'get'.ucfirst($name);
		if (method_exists($this, $getFunction)) {
			return call_user_func([$this, $getFunction]);
		}

		if (isset(static::$metaProperties[static::class])) {
			if (empty($this->propertiesProxy)) {
				$this->propertiesProxy = new PropertiesProxy($this, static::$metaProperties[static::class], static::$isReadOnly, static::$readOnlyMetaProperties);
			}

			return $this->propertiesProxy->__get($name);
		}

		return null;
	}

	/**
	 * Magic property setter method
	 * @param $name
	 * @param $value
	 *
	 * @throws InvalidPropertiesException
	 */
	public function __set($name, $value) {
    	if (isset($this->postProperties[$name])) {
			$this->setProperty($name, $this->postProperties[$name], $value);
			return;
	    }

		if (isset($this->modelProperties[$name])) {
			$this->setModelProperty($name, $this->modelProperties[$name], $value);
			return;
		}

		if (isset($this->dateProperties[$name])) {
			$this->setDateProperty($name, $this->dateProperties[$name], $value);
			return;
		}

		if ($name == 'thumbnail') {
			$this->_thumbnail = $value;
			$this->changes->setThumbnail($value);
			return;
		}

		$getFunction = 'get'.ucfirst($name);
		$setFunction = 'set'.ucfirst($name);
		if (method_exists($this, $getFunction)) {
			if (method_exists($this, $setFunction)) {
				call_user_func([$this, $setFunction], $value);
				return;
			} else {
				throw new InvalidPropertiesException("Property '$name' is read-only.");
			}
		}

		if (isset(static::$metaProperties[static::class])) {
			if(empty($this->propertiesProxy)) {
				$this->propertiesProxy = new PropertiesProxy($this, static::$metaProperties[static::class], static::$isReadOnly, static::$readOnlyMetaProperties);
			}

			$this->propertiesProxy->__set($name, $value);

			return;
		}

		throw new InvalidPropertiesException("Unknown property '$name'.");
	}

	public function __isset($name) {
		if ($name == 'id') {
			return true;
		}

		if (isset($this->postProperties[$name])) {
			return true;
		}

		if (isset($this->modelProperties[$name])) {
			return true;
		}

		if (isset($this->dateProperties[$name])) {
			return true;
		}

		$getFunction = 'get'.ucfirst($name);
		if (method_exists($this, $getFunction)) {
			return true;
		}

		if (isset(static::$metaProperties[static::class])) {
			if (empty($this->propertiesProxy)) {
				$this->propertiesProxy = new PropertiesProxy($this, static::$metaProperties[static::class], static::$isReadOnly, static::$readOnlyMetaProperties);
			}

			return $this->propertiesProxy->__isset($name);
		}

		return false;
	}
	//endregion

    //region Properties

    /**
     * Returns the underlying Wordpress post
     *
     * @return \WP_Post
     */
    public function wpPost() {
        return $this->post;
    }

    /**
     * Returns the CSS classes for this post as a single string
     * @param string|array $class One or more classes to add to the class list.
     * @return string
     */
    public function cssClass($class = '') {
        if (empty($this->id)) {
            if (is_array($class)) {
                return implode(' ', $class);
            } else {
                return $class;
            }
        }

        $result = get_post_class($class, $this->id);
        return implode(' ', $result);
    }

    /**
     * Author of the post
     *
     * @return User|null
     */
    protected function getAuthor() {
        if ($this->_author != null) {
            return $this->_author;
        }

        if (empty($this->id)) {
            return null;
        }

        if ($this->post->post_author) {
            $this->_author = new User($this->context, new \WP_User($this->post->post_author));
        }

        return $this->_author;
    }

    /**
     * Sets the author
     *
     * @param User $user
     */
	protected function setAuthor(User $user) {
        $this->_author = $user;

        $this->changes->addChange('post_author', $user->id());
    }

    /**
     * Returns the post's featured image
     * @return Attachment|null
     */
	protected function getThumbnail() {
        if ($this->_thumbnail != null) {
            return $this->_thumbnail;
        }

        if (empty($this->id)) {
            return null;
        }

        $thumb_id = get_post_thumbnail_id($this->post->ID);
        if ($thumb_id) {
            $this->_thumbnail = $this->context->modelForPost(\WP_Post::get_instance($thumb_id));
        }
        return $this->_thumbnail;
    }

    /**
     * Sets the thumbnail for the post
     * @param Attachment|int $attachmentOrId
     * @throws \Exception
     */
	protected function setThumbnail($attachmentOrId = null) {
        if (empty($attachmentOrId)) {
            $this->_thumbnail = null;
            $this->changes->clearThumbnail();
        } else {
            if (is_numeric($attachmentOrId)) {
                $attachmentOrId = $this->context->modelForPostID($attachmentOrId);
                if (!($attachmentOrId instanceof Attachment) || empty($attachmentOrId)) {
                    throw new \Exception('Invalid attachment ID');
                }
            }

            $this->_thumbnail = $attachmentOrId;
            $this->changes->setThumbnail($attachmentOrId->id);
        }
    }

    //endregion

    //region Metadata

    /**
     * Returns the post's metadata
     *
     * @param null|string $key The metadata key to return a value for, passing null returns all of the metadata
     * @param null|mixed $defaultValue The default value to return if the key doesn't exist
     * @return array|mixed|null
     */
    public function meta($key=null, $defaultValue=null) {
        $this->insureMeta();

        if (empty($key)) {
            return $this->meta;
        } else {
            if (isset($this->meta[$key])) {
                return $this->meta[$key];
            }

            return $defaultValue;
        }
    }

    /**
     * Updates metadata value
     * @param $key
     * @param $value
     */
    public function updateMeta($key, $value) {
        $this->insureMeta();

        $this->meta[$key] = $value;
        $this->changes->updateMeta($key, $value);
    }

    /**
     * Deletes a metadata item
     * @param $key
     */
    public function deleteMeta($key) {
        $this->insureMeta();

        if (isset($this->meta[$key])) {
            unset($this->meta[$key]);
            $this->changes->deleteMeta($key);
        }
    }

    /**
     * Insures metadata is loaded for this post
     */
    private function insureMeta() {
        if ($this->meta == null) {
            if (empty($this->id)) {
                $this->meta = [];
            } else {
                $this->meta = get_post_meta($this->id);
            }
        }
    }

    //endregion

    //region Links

    /**
     * Returns the edit link for this post.
     * @return null|string
     */
	protected function getEditLink() {
	    if ($this->_editPostLink != null) {
		    return $this->_editPostLink;
	    }

        $this->_editPostLink = (empty($this->id)) ? null : str_replace('&amp;', '&', get_edit_post_link($this->id));
	    return $this->_editPostLink;
    }

    /**
     * Returns the post's permalink
     * @return null|string
     */
	protected function getPermalink() {
        if ($this->_permalink != null) {
            return $this->_permalink;
        }

        if (empty($this->id)) {
            return null;
        }

        $permalink = get_permalink($this->id);

        if ($this->context->ui->useRelative) {
            if ($permalink && ! empty($permalink)) {
                $parsed = parse_url($permalink);
                $plink = $parsed['path'];
                if (isset($parsed['query']) && ! empty($parsed['query'])) {
                    $plink .= '?'.$parsed['query'];
                }

                $permalink = $plink;
            }
        }

        $this->_permalink = $permalink;
        return $this->_permalink;
    }

    //endregion

    //region Categories/Terms

    /**
     * Returns the list of categories this post belongs to
     * @return Term[]|null
     */
	protected function getCategories() {
        if ($this->_categories != null) {
            return $this->_categories;
        }

        if (empty($this->id)) {
            $this->_categories = [];
        } else {
            $categories = wp_get_post_categories($this->post->ID);
            if ($categories && (count($categories) > 0)) {
                $this->_categories = [];
                foreach ($categories as $categoryID) {
                    $this->_categories[] = Term::term($this->context, $categoryID, 'category');
                }
            }
        }

        return $this->_categories;
    }

    /**
     * Returns the list of categories this post belongs to
     * @return Term[]|null
     */
    public function tax($taxonomy) {
        if ($this->taxes[$taxonomy] != null) {
            return $this->taxes[$taxonomy];
        }

        if (empty($this->id)) {
            $this->taxes[$taxonomy] = [];
        } else {
            $taxes = wp_get_object_terms($this->id, $taxonomy);
            if (is_array($taxes) && (count($taxes) > 0)) {
                $this->taxes[$taxonomy] = [];
                foreach ($taxes as $termID) {
                    if ($termID instanceof \WP_Term) {
                        $this->taxes[$taxonomy][] = Term::termFromTermData($this->context, $termID);
                    } else if (is_numeric($termID)) {
                        $this->taxes[$taxonomy][] = Term::term($this->context, $termID, $taxonomy);
                    }
                }
            }
        }

        return $this->taxes[$taxonomy];
    }

    /**
     * Adds a category to the post
     * @param Term $category
     */
    public function addCategory($category) {
        $this->getCategories();

        foreach($this->_categories as $cat) {
            if ($cat->id() == $category->id()) {
                return;
            }
        }

        $this->_categories[] = $category;
        $this->changes->addCategory($category->id());

        $this->topCategories = null;
        $this->_topCategory = null;
        $this->getTopCategory();
    }

    /**
     * Removes a category from the post
     *
     * @param Term $category
     */
    public function removeCategory($category) {
        $this->getCategories();

        $cleanedCats = [];
        foreach($this->_categories as $cat) {
            if ($category->id() == $cat->id()) {
                continue;
            }

            $cleanedCats[] = $cat;
        }

        $this->_categories = $cleanedCats;
        $this->changes->removeCategory($category);

        $this->topCategories = null;
        $this->_topCategory = null;
	    $this->getTopCategory();
    }

    /**
     * Returns the top category
     *
     * @return Term|null
     */
    public function getTopCategory() {
        if ($this->_topCategory != null) {
            return $this->_topCategory;
        }

        $cats = $this->getCategories();
        $this->topCategories = [];
        foreach ($cats as $category) {
            $parent = $category->parent();
            if ($parent == null) {
                $this->topCategories[] = $category;
            } else {
                while (true) {
                    if ($parent->parent() == null) {
                        $this->topCategories[] = $parent;
                        break;
                    } else {
                        $parent = $parent->parent();
                    }
                }
            }
        }

        if (count($this->topCategories) > 0) {
            $this->_topCategory = $this->topCategories[0];
        }

        return $this->_topCategory;
    }

    /**
     * Returns the associated tags with this post
     * @return Term[]
     */
    protected function getTags() {
        if ($this->_tags != null) {
            return $this->_tags;
        }

        $this->_tags = [];
        if (!empty($this->id)) {
            $tags = wp_get_post_tags($this->id);
            if ($tags && (count($tags) > 0)) {
                foreach ($tags as $tag) {
                    $this->_tags[] = Term::termFromTermData($this->context, $tag);
                }
            }
        }

        return $this->_tags;
    }

    /**
     * Adds a tag to a post
     *
     * @param Term $tag
     */
    public function addTag($tag) {
        $this->getTags();

        foreach($this->_tags as $existingTag) {
            if ($existingTag->id() == $tag->id()) {
                return;
            }
        }

        $this->_tags[] = $tag;
        $this->changes->addTag($tag->id());
    }

    /**
     * Removes a tag from the post
     * @param Term $tag
     */
    public function removeTag($tag) {
        $this->getTags();

        $cleanedTags = [];
        foreach($this->_tags as $existingTag) {
            if ($existingTag->id() == $tag->id()) {
                continue;
            }

            $cleanedTags[] = $tag;
        }

        $this->_tags = $cleanedTags;
        $this->changes->removeTag($tag);
    }

    //endregion

    //region Update/Save/Delete

    /**
     * Determines if the model has changes that need to be saved or updated.
     * @return bool
     */
    public function hasChanges() {
        return $this->changes->hasChanges();
    }

    /**
     * Saves or Updates the post
     * @throws \Exception
     */
    public function save() {
        if ($this->deleted) {
            throw new \Exception("Attempting to save a deleted model.");
        }

        if ($this->id == null) {
        	$id = $this->changes->create(static::$postType);
        	if (empty($this->post)) {
        		$this->post = \WP_Post::get_instance($id);
	        } else {
                $this->post->ID = $id;
	        }
        } else {
            $this->changes->update($this->id);
        }
    }

    /**
     * Deletes the post
     * @param bool $force_delete
     */
    public function delete($force_delete = false) {
        if ($this->deleted || ($this->id == null)) {
            return;
        }

        $this->deleted = true;

        wp_delete_post($this->id, $force_delete);

        $this->post->ID = null;
    }

	public function trash() {
		if ($this->deleted || ($this->id == null)) {
			return;
		}

		wp_trash_post($this->id);
	}

	public function restore() {
		if ($this->deleted || ($this->id == null)) {
			return;
		}

		wp_untrash_post($this->id);
	}

	public function duplicate($metaFilter, $status = 'unchanged') {
		if ($this->deleted || ($this->id == null)) {
			return null;
		}

		add_filter('stem/post/skip-update-notification', [$this, 'skipUpdateNotification']);

		$wpPost = get_post($this->id, ARRAY_A);
		unset($wpPost['ID']);
		unset($wpPost['guid']);

		if ($status !== 'unchanged') {
			$wpPost['post_status'] = $status;
		}

		$newPostId = wp_insert_post($wpPost);

		remove_filter('stem/post/skip-update-notification', [$this, 'skipUpdateNotification']);

		if (is_wp_error($newPostId)) {
			return null;
		}

		global $wpdb;
		$allMeta = $wpdb->get_results("select * from {$wpdb->postmeta} where post_id = {$this->id}");
		foreach($allMeta as $meta) {
			if (($metaFilter !== null) && call_user_func($metaFilter, $meta->meta_key)) {
				continue;
			}

			update_post_meta($newPostId, $meta->meta_key, maybe_unserialize($meta->meta_value));
		}

		return $this->context->modelForPostID($newPostId);
	}

	public function skipUpdateNotification($notify) {
		return true;
	}

    //endregion

    //region Content

	/**
	 * Returns content stripped of empty <p> tags.
	 * @return string|null
	 */
	protected function getStrippedContent() {
    	return $this->getContent(true);
	}

    /**
     * Returns the post's content
     *
     * @param bool $stripEmptyParagraphs
     * @return null|string
     */
	protected function getContent($stripEmptyParagraphs = false) {
        if (($this->content != null) && !$stripEmptyParagraphs) {
            return $this->content;
        }

        if (empty($this->id)) {
            return null;
        }

        $this->unfilteredContent = $this->post->post_content;

        global $post;

        $previousPost = $post;
        $post = $this->post;
        $this->content = apply_filters('the_content', $this->unfilteredContent);
        $post = $previousPost;

        if ($stripEmptyParagraphs) {
            return str_replace('<p>&nbsp;</p>', '', $this->content);
        }

        return $this->content;
    }

    /**
     * Updates the post's content
     * @param $content
     */
	protected function setContent($content) {
        $this->unfilteredContent = $content;
        $this->content = apply_filters('the_content', $content);
        $this->changes->addChange('post_content', $content);
    }

    /**
     * Returns any video embeds that might be in the post's content
     * @return array
     */
    protected function getVideoEmbeds() {
        if ($this->content == null) {
            if (empty($this->getContent())) {
                return [];
            }
        }

        $embedRegexes = [
            "#(http|https):\\/\\/(www.)*youtube.com\\/embed\\/([^'\"]+)#i",
            '#(http|https)://(www\.)?youtube\.com/watch.*#i',
            '#(http|https)://(www\.)?youtube\.com/playlist.*#i',
            '#(http|https)://youtu\.be/.*#i',
            '#(http|https)?://(.+\.)?vimeo\.com/.*#i',
        ];

        $embeds = [];

        foreach ($embedRegexes as $regex) {
            $matches = [];
            if (preg_match_all($regex, $this->content, $matches)) {
                $match = $matches[0][0];

                if (strpos($match, 'embed') > 0) {
                    $parts = explode('/', $match);
                    $embeds[] = 'https://www.youtube.com/watch?v='.array_pop($parts);
                } else {
                    $embeds[] = $match;
                }
            }
        }

        return $embeds;
    }

    /**
     * Generates the post's excerpt
     *
     * @param int $len
     * @param bool $force
     * @param string $readmore
     * @param bool $strip
     * @param string $allowed_tags
     * @return null|string
     */
    public function excerpt($len = 50, $force = false, $readmore = 'Read More', $strip = true, $allowed_tags = 'p a span b i br h1 h2 h3 h4 h5 ul li img blockquote') {
        if (($this->excerpt == null) && (!empty($this->id))) {
            $this->excerpt = $this->post->post_excerpt;
        }

        if ($this->content == null) {
            if (empty($this->getContent())) {
                return '';
            }
        }

        $text = '';
        $trimmed = false;
        if (!empty($this->excerpt) && strlen($this->excerpt)) {
            if ($force) {
                $text = Text::trim($this->excerpt, $len, true, $allowed_tags);
                $trimmed = true;
            } else {
                $text = $this->excerpt;
            }
        }

        if (!strlen($text) && strpos($this->content, '<!--more-->') !== false) {
            $pieces = explode('<!--more-->', $this->content);
            $text = $pieces[0];
            if ($force) {
                $text = Text::trim($text, $len, true, $allowed_tags);
                $trimmed = true;
            }
        }

        if (! strlen($text)) {
            $text = Text::trim($this->content, $len, null, $allowed_tags);
            $trimmed = true;
        }

        if (! strlen(trim($text))) {
            return trim($text);
        }

        if ($strip) {
            $text = trim(strip_tags($text));
        }

        if (strlen($text)) {
            $text = trim($text);
            $last = $text[strlen($text) - 1];
            if ($last != '.' && $trimmed) {
                if (strpos($text, '&hellip;') > 0) {
                    $text = str_replace('&hellip;', ' &hellip;', $text);
                } else {
                    $text .= ' &hellip; ';
                }
            }

            if (! $strip) {
                $last_p_tag = strrpos($text, '</p>');
                if ($last_p_tag !== false) {
                    $text = substr($text, 0, $last_p_tag);
                }
                if ($last != '.' && $trimmed) {
                    $text .= ' &hellip; ';
                }
            }

            if ($readmore) {
                $text .= ' <a href="'.$this->getPermalink().'" class="read-more">'.$readmore.'</a>';
            }

            if (! $strip) {
                $text .= '</p>';
            }
        }

        $this->excerpt = trim($text);

        return $this->excerpt;
    }

    //endregion

    //region ACF Fields / Properties

    /**
     * Fetches an ACF field and assigns it to a class property
     *
     * @param string $property
     * @param string|null $fieldName
     * @param null|callable $transformer
     * @param null|mixed $defaultValue
     * @return mixed|null
     * @throws \Samrap\Acf\Exceptions\BuilderException
     */
	public function getField($fieldName, $transformer = null, $defaultValue = null) {
    	$property = camelCaseString($fieldName);

    	if (property_exists($this, $property)) {
		    if ($this->{$property} != null) {
			    return $this->{$property};
		    }
	    } else if (isset($this->fieldsCache[$fieldName])) {
    		return $this->fieldsCache[$fieldName];
	    }

        if (empty($this->id)) {
            return $defaultValue;
        }

        $fieldName = $fieldName ?: $property;

        $val = Acf::field($fieldName, $this->id)->get();
        if ($val != null) {
            if ($transformer != null) {
                $val = $transformer($val);
            }

	        if (property_exists($this, $property)) {
		        $this->{$property} = $val;
	        } else {
	        	$this->fieldsCache[$fieldName] = $val;
	        }
        } else {
            $val = $defaultValue;
        }

        return $val;
    }

    /**
     * Sets a property backed by ACF and signals a change
     *
     * @param string $property
     * @param string $fieldName
     * @param mixed|null $value
     * @param null|callable $transformer
     */
    public function updateField($fieldName, $value, $transformer = null) {
	    $property = camelCaseString($fieldName);

        if ($transformer != null) {
            $value = $transformer($value);
        }

	    if (property_exists($this, $property)) {
		    $this->{$property} = $value;
	    } else {
	    	$this->fieldsCache[$fieldName] = $value;
	    }

        $this->changes->updateField($fieldName, $value);
    }

    //endregion

    //region Related Posts

    /**
     * Returns related posts
     *
     * @param $postTypes
     * @param $limit
     * @return array
     */
    public function related($postTypes, $limit) {
        global $wpdb;
        array_walk($postTypes, function (&$value, $index) {
            $value = "'$value'";
        });
        $postTypes = implode(',', $postTypes);

        $query = <<<"QUERY"
SELECT WP.ID, COUNT(*) AS tag_count, rand() as random FROM  wp_term_relationships T1 JOIN  wp_term_relationships T2
ON
	T1.term_taxonomy_id = T2.term_taxonomy_id
AND
	T1.object_id != T2.object_id
JOIN
	wp_posts WP
ON
	T2.object_id = WP.ID
WHERE
	T1.object_id = {$this->id}
    AND WP.post_status='publish'
    and WP.post_type in ($postTypes)
GROUP BY
	T2.object_id
ORDER BY COUNT(*) DESC, random desc
limit $limit
QUERY;

        $results = $wpdb->get_results($query);
        $related = [];
        if ($results) {
            foreach ($results as $result) {
                $related[] = $this->context->modelForPost(\WP_Post::get_instance($result->ID));
            }
        }

        return $related;
    }

    //endregion

    //region JSON

    public function jsonSerialize() {
        return [
            'type'=>static::$postType,
            'title'=>$this->title,
            'slug'=>$this->slug,
            'author'=>$this->author->displayName(),
            'date'=>$this->date->toIso8601String(),
            'updated'=>$this->updated->toIso8601String(),
            'content'=>$this->getContent(),
            'excerpt'=>$this->excerpt(),
            'url'=>$this->permalink,
            'mime_type'=>$this->post->post_mime_type,
            'thumbnail'=>$this->thumbnail,
            'categories'=>$this->categories,
            'tags'=>$this->tags,
        ];
    }

    //endregion

    //region Queries

    /**
     * Creates a Query object for this post model
     * @return Query
     */
    public static function query() {
        return new Query(Context::current(), static::$postType);
    }

	/**
	 * Returns the post with the given id, or null if not found
	 * @param $id
	 * @return Post|null
	 * @throws \Exception
	 */
	public static function find($id) {
		return Context::current()->modelForPostID($id);
	}

	/**
	 * Returns all of the posts of this type
	 * @param $status
	 * @return PostCollection|null
	 * @throws \Exception
	 */
	public static function all($status = ['publish', 'draft', 'trash']) {
		$query = static::query();

		if (!empty($status)) {
			$query->status->in($status);
		}

		$query->limit(-1);
		return $query->get();
	}

    /**
     * Returns the first post of this type
     * @return Post|null
     */
    public static function first() {
        return static::query()->first();
    }

    /**
     * Returns the number of posts in the database.  Note this incurs a DB call every time
     * this is called.
     *
     * @return int
     */
    public static function count() {
        return static::query()->limit(1)->get()->total();
    }

    /**
     * Creates a query with the initial with clause
     *
     * @param mixed ...$args
     * @return Query
     * @throws \Exception
     */
    public static function where(...$args) {
        return static::query()->whereWithArgs($args);
    }

    //endregion

	//region Mass assignment

	/**
	 * Assigns form values to ACF properties.  Properties must be defined in `propertyValidators`.
	 *
	 * @param $formValues
	 *
	 * @throws InvalidPropertiesException
	 */
	public function assign($formValues) {
    	$invalids = validateArray($formValues, $this->propertyValidators);
    	if (!empty($invalids)) {
			throw new InvalidPropertiesException("Invalid form values: ".implode(', ', $invalids), $invalids);
		}

		foreach($this->propertyValidators as $key => $validator) {
			if (!isset($formValues[$key])) {
				continue;
			}

			$this->updateField($key, arrayPath($formValues, $key, null));
		}

		$this->propertiesProxy = null;
	}
	//endregion
}
