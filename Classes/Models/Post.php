<?php


namespace ILab\Stem\Models;

use Carbon\Carbon;
use ILab\Stem\Core\Context;
use ILab\Stem\Utilities\Text;

/**
 * Class Post.
 *
 * Represents a WordPress post
 */
class Post implements \JsonSerializable {
    /** @var string Type of post */
    protected static $postType = 'post';

    /** @var int|null ID of the post */
    protected $id;

    /** @var Context  */
    public $context;

    /** @var bool Determines if the model is deleted or not. */
    private $deleted = false;

    /** @var \WP_Post The underlying Wordpress post */
    protected $post;

    /** @var string|null The post status. Default 'draft'  */
    protected $status = null;

    /** @var Post|null The parent post, if any */
    protected $parent = null;

    /** @var int The order the post should be displayed in. */
    protected $menuOrder = 0;

    /** @var null|string Slug for the post */
    protected $slug = null;

    /** @var null|string Title for the post */
    protected $title = null;

    /** @var null|User The author of the post  */
    protected $author = null;

    /** @var null|Term The primary category for the post  */
    protected $topCategory = null;

    /** @var null|Term[] The top level categories for this post */
    protected $topCategories = null;

    /** @var null|Term[] All categories assigned to this post */
    protected $categories = null;

    /** @var null|Term[] All terms assigned to this post */
    protected $tags = null;

    /** @var null|string The permalink for this post  */
    protected $permalink = null;

    /** @var null|Attachment The featured image for the post */
    protected $thumbnail = null;

    /** @var null|Carbon The date the post was published */
    protected $date = null;

    /** @var null|Carbon The date the post was updated */
    protected $updated = null;

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

    /**
     * Post constructor.
     *
     * @param Context $context
     * @param \WP_Post $post
     */
    public function __construct(Context $context, \WP_Post $post = null) {
        $this->changes = new ChangeManager();

        $this->context = $context;

        if (!empty($post)) {
            $this->id = $post->ID;
            $this->post = $post;
            $this->menuOrder = $post->menu_order;
        }
    }

    //region Properties

    /**
     * The post's ID
     * @return int|null
     */
    public function id() {
        return $this->id;
    }

    /**
     * Returns the underlying Wordpress post
     *
     * @return \WP_Post
     */
    public function wpPost() {
        return $this->post;
    }

    /**
     * The post's type
     * @return string
     */
    public static function postType() {
        return self::$postType;
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
     * Title of the post
     * @return null|string
     */
    public function title() {
        if ($this->title != null) {
            return $this->title;
        }

        if (empty($this->id)) {
            return null;
        }

        $this->title = $this->post->post_title;
        return $this->title;
    }

    /**
     * Sets the title of the post
     * @param $title
     */
    public function setTitle($title) {
        $this->title = $title;
        $this->changes->addChange( 'post_title', $title);
    }


    /**
     * Author of the post
     *
     * @return User|null
     */
    public function author() {
        if ($this->author != null) {
            return $this->author;
        }

        if (empty($this->id)) {
            return null;
        }

        if ($this->post->post_author) {
            $this->author = new User($this->context, new \WP_User($this->post->post_author));
        }

        return $this->author;
    }

    /**
     * Sets the author
     *
     * @param User $user
     */
    public function setAuthor(User $user) {
        $this->author = $user;

        $this->changes->addChange('post_author', $user->id());
    }

    /**
     * The post's slug
     *
     * @return null|string
     */
    public function slug() {
        if ($this->slug != null) {
            return $this->slug;
        }

        if (empty($this->id)) {
            return null;
        }

        $this->slug = $this->post->post_name;
        return $this->slug;
    }

    /**
     * Sets the post's slug
     * @param string $newSlug
     */
    public function setSlug($newSlug) {
        $this->slug = $newSlug;
        $this->changes->addChange('post_name', $newSlug);
    }

    /**
     * Returns the date the post was published
     * @return Carbon|null
     */
    public function date() {
        if ($this->date != null) {
            return $this->date;
        }

        if (empty($this->id)) {
            return null;
        }

        $this->date = new Carbon($this->post->post_date_gmt);
        return $this->date;
    }

    /**
     * Returns the date the post was updated
     * @return Carbon|null
     */
    public function updated() {
        if ($this->updated != null) {
            return $this->updated;
        }

        if (empty($this->id)) {
            return null;
        }

        $this->updated = new Carbon($this->post->post_modified_gmt);
        return $this->updated;
    }

    /**
     * Returns the post's featured image
     * @return Attachment|null
     */
    public function thumbnail() {
        if ($this->thumbnail != null) {
            return $this->thumbnail;
        }

        if (empty($this->id)) {
            return null;
        }

        $thumb_id = get_post_thumbnail_id($this->post->ID);
        if ($thumb_id) {
            $this->thumbnail = $this->context->modelForPost(\WP_Post::get_instance($thumb_id));
        }
        return $this->thumbnail;
    }

    /**
     * Sets the thumbnail for the post
     * @param Attachment|int $attachmentOrId
     * @throws \Exception
     */
    public function setThumbnail($attachmentOrId = null) {
        if (empty($attachmentOrId)) {
            $this->thumbnail = null;
            $this->changes->clearThumbnail();
        } else {
            if (is_numeric($attachmentOrId)) {
                $attachmentOrId = $this->context->modelForPostID($attachmentOrId);
                if (!($attachmentOrId instanceof Attachment) || empty($attachmentOrId)) {
                    throw new \Exception('Invalid attachment ID');
                }
            }

            $this->thumbnail = $attachmentOrId;
            $this->changes->setThumbnail($attachmentOrId->id());
        }
    }

    /**
     * The post status. Default 'draft'
     * @return null|string
     */
    public function status() {
        if ($this->status != null) {
            return $this->status;
        }

        if (empty($this->id)) {
            return 'draft';
        }

        $this->status = $this->post->post_status;
        return $this->status;
    }

    /**
     * Sets the post's status
     * @param $status
     */
    public function setStatus($status) {
        $this->status = $status;
        $this->changes->addChange('post_status', $status);
    }

    /**
     * The parent post, if any
     * @return Attachment|Page|Post|null
     */
    public function parent() {
        if ($this->parent != null) {
            return $this->parent;
        }

        if (empty($this->id)) {
            return null;
        }

        if (empty($this->post->post_parent)) {
            return null;
        }

        $this->parent = $this->context->modelForPostID($this->post->post_parent);

        if (empty($this->parent)) {
            $this->parent = null;
        }

        return $this->parent;
    }

    /**
     * Sets the parent
     * @param Attachment|Page|Post|null|int $parent
     */
    public function setParent($parent) {
        if (empty($parent)) {
            $this->parent = null;
            $this->changes->addChange('post_parent', null);
        } else if (is_numeric($parent)) {
            $this->parent = $this->context->modelForPostID($parent);

            if (empty($this->parent)) {
                $this->parent = null;
            } else {
                $this->changes->addChange('post_parent', $parent);
            }
        } else {
            $this->parent = $parent;
            $this->changes->addChange('post_parent', $parent->id());
        }
    }

    /**
     * The order the post should be displayed in.
     * @return int
     */
    public function menuOrder() {
        return $this->menuOrder;
    }

    /**
     * Sets the menu order.
     * @param $order
     */
    public function setMenuOrder($order) {
        $this->menuOrder = $order;
        $this->changes->addChange('menu_order', $order);
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
    public function editLink() {
        return (empty($this->id)) ? null : get_edit_post_link($this->id);
    }

    /**
     * Returns the post's permalink
     * @return null|string
     */
    public function permalink() {
        if ($this->permalink != null) {
            return $this->permalink;
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

        $this->permalink = $permalink;
        return $this->permalink;
    }

    //endregion

    //region Categories/Terms

    /**
     * Returns the list of categories this post belongs to
     * @return Term[]|null
     */
    public function categories() {
        if ($this->categories != null) {
            return $this->categories;
        }

        if (empty($this->id)) {
            $this->categories = [];
        } else {
            $categories = wp_get_post_categories($this->post->ID);
            if ($categories && (count($categories) > 0)) {
                $this->categories = [];
                foreach ($categories as $categoryID) {
                    $this->categories[] = Term::term($this->context, $categoryID, 'category');
                }
            }
        }

        return $this->categories;
    }

    /**
     * Adds a category to the post
     * @param Term $category
     */
    public function addCategory($category) {
        $this->categories();

        foreach($this->categories as $cat) {
            if ($cat->id() == $category->id()) {
                return;
            }
        }

        $this->categories[] = $category;
        $this->changes->addCategory($category->id());

        $this->topCategories = null;
        $this->topCategory = null;
        $this->topCategory();
    }

    /**
     * Removes a category from the post
     *
     * @param Term $category
     */
    public function removeCategory($category) {
        $this->categories();

        $cleanedCats = [];
        foreach($this->categories as $cat) {
            if ($category->id() == $cat->id()) {
                continue;
            }

            $cleanedCats[] = $cat;
        }

        $this->categories = $cleanedCats;
        $this->changes->removeCategory($category);

        $this->topCategories = null;
        $this->topCategory = null;
        $this->topCategory();
    }

    /**
     * Returns the top category
     *
     * @return Term|null
     */
    public function topCategory() {
        if ($this->topCategory != null) {
            return $this->topCategory;
        }

        $cats = $this->categories();
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
            $this->topCategory = $this->topCategories[0];
        }

        return $this->topCategory;
    }

    /**
     * Returns the associated tags with this post
     * @return Term[]
     */
    public function tags() {
        if ($this->tags != null) {
            return $this->tags;
        }

        $this->tags = [];
        if (!empty($this->id)) {
            $tags = wp_get_post_tags($this->id);
            if ($tags && (count($tags) > 0)) {
                foreach ($tags as $tag) {
                    $this->tags[] = Term::termFromTermData($this->context, $tag);
                }
            }
        }

        return $this->tags;
    }

    /**
     * Adds a tag to a post
     *
     * @param Term $tag
     */
    public function addTag($tag) {
        $this->tags();

        foreach($this->tags as $existingTag) {
            if ($existingTag->id() == $tag->id()) {
                return;
            }
        }

        $this->tags[] = $tag;
        $this->changes->addTag($tag->id());
    }

    /**
     * Removes a tag from the post
     * @param Term $tag
     */
    public function removeTag($tag) {
        $this->tags();

        $cleanedTags = [];
        foreach($this->tags as $existingTag) {
            if ($existingTag->id() == $tag->id()) {
                continue;
            }

            $cleanedTags[] = $tag;
        }

        $this->tags = $cleanedTags;
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
            $this->changes->create(self::$postType);
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

        $this->id = null;
        $this->post->ID = null;
    }

    //endregion

    //region Content

    /**
     * Returns the post's content
     *
     * @param bool $stripEmptyParagraphs
     * @return null|string
     */
    public function content($stripEmptyParagraphs = false) {
        if ($this->content != null) {
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
            $this->content = str_replace('<p>&nbsp;</p>', '', $this->content);
        }

        return $this->content;
    }

    /**
     * Updates the post's content
     * @param $content
     */
    public function setContent($content) {
        $this->unfilteredContent = $content;
        $this->content = apply_filters('the_content', $content);
        $this->changes->addChange('post_content', $content);
    }

    /**
     * Returns any video embeds that might be in the post's content
     * @return array
     */
    public function videoEmbeds() {
        if ($this->content == null) {
            if (empty($this->content())) {
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
            if (empty($this->content())) {
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
                $text .= ' <a href="'.$this->permalink().'" class="read-more">'.$readmore.'</a>';
            }

            if (! $strip) {
                $text .= '</p>';
            }
        }

        $this->excerpt = trim($text);

        return $this->excerpt;
    }

    //endregion

    //region ACF Fields

    /**
     * Fetches the value for an ACF field
     * @param $field
     * @param mixed|null $defaultValue
     * @return mixed|null
     */
    public function field($field, $defaultValue = null) {
        if (isset($this->fieldsCache[$field])) {
            return $this->fieldsCache[$field];
        }

        if (empty($this->id)) {
            return $defaultValue;
        }

        $value = get_field($field, $this->id);
        if ($value === null) {
            return $defaultValue;
        }

        $this->fieldsCache[$field] = $value;
        return $value;
    }

    /**
     * Updates an ACF field
     * @param $field
     * @param $value
     */
    public function updateField($field, $value) {
        $this->fieldsCache[$field] = $value;
        $this->changes->updateField($field, $value);
    }

    /**
     * Deletes the value for an ACF field associated with this post
     * @param $field
     */
    public function deleteField($field) {
        if (isset($this->fieldsCache[$field])) {
            unset($this->fieldsCache[$field]);
        }

        $this->changes->deleteField($field);
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
            'type'=>self::$postType,
            'title'=>$this->title(),
            'slug'=>$this->slug(),
            'author'=>$this->author()->displayName(),
            'date'=>$this->date()->toIso8601String(),
            'updated'=>$this->updated()->toIso8601String(),
            'content'=>$this->content(),
            'excerpt'=>$this->excerpt(),
            'url'=>$this->permalink(),
            'mime_type'=>$this->post->post_mime_type,
            'thumbnail'=>$this->thumbnail(),
            'categories'=>$this->categories(),
            'tags'=>$this->tags(),
        ];
    }

    //endregion
}
