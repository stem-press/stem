<?php

namespace ILab\Stem\Models;


use Carbon\Carbon;
use ILab\Stem\Core\Context;
use ILab\Stem\Utilities\Text;

/**
 * Class Post
 *
 * Represents a WordPress post
 *
 * @package ILab\Stem\Models
 */
class Post extends WordPressModel
{
    public $id;
    public $context;

    protected $post;

    private $author = null;
    private $topCategory = null;
    private $topCategories = null;
    private $categories = null;
    private $tags = null;
    private $permalink = null;
    private $thumbnail = null;
    private $date = null;
    private $updated = null;
    private $content = null;


    public function __construct(Context $context, \WP_Post $post)
    {
        $this->id = $post->ID;
        $this->context = $context;
        $this->post = $post;
    }

    public function wpPost() {
        return $this->post;
    }

    public function cssClass($class = '') {
        $result = get_post_class($class, $this->id);
        return implode(' ', $result);
    }

    public function type() {
        return $this->post->post_type;
    }

    public function author()
    {
        if ($this->author)
            return $this->author;

        if ($this->post->post_author)
            $this->author = new User($this->context, new \WP_User($this->post->post_author));

        return $this->author;
    }

    public function categories()
    {
        if ($this->categories)
            return $this->categories;

        $categories = wp_get_post_categories($this->post->ID);
        if ($categories && (count($categories) > 0))
        {
            $this->categories = [];
            foreach ($categories as $categoryID)
            {
                $this->categories[] = Term::term($this->context, $categoryID, 'category');
            }
        }

        return $this->categories;
    }

    public function slug() {
        return $this->post->post_name;
    }

    public function topCategory()
    {
        if ($this->topCategory)
            return $this->topCategory;

        $cats = $this->categories();
        if ($cats)
        {
            $this->topCategories = [];
            foreach ($cats as $category)
            {
                $parent = $category->parent();
                if ($parent == null)
                    $this->topCategories[] = $category;
                else
                {
                    while (true)
                    {
                        if ($parent->parent == null)
                        {
                            $this->topCategories[] = $parent;
                            break;
                        }
                        else
                            $parent = $parent->parent;
                    }
                }
            }

            if (count($this->topCategories) > 0)
                $this->topCategory = $this->topCategories[0];
        }

        return $this->topCategory;
    }

    public function tags()
    {
        if ($this->tags)
            return $this->tags;

        $this->tags = [];
        $tags = wp_get_post_tags($this->post->ID);
        if ($tags && (count($tags) > 0))
        {
            foreach ($tags as $tag)
            {
                $this->tags[] = Term::termFromTermData($this->context, $tag);
            }
        }

        return $this->tags;
    }

    public function title()
    {
        return $this->post->post_title;
    }

    public function content($stripEmptyParagraphs = false)
    {
        if ($this->content)
            return $this->content;

        global $post;

        $previousPost = $post;
        $post = $this->post;
        $this->content = apply_filters('the_content', $this->post->post_content);
        $post = $previousPost;

        if ($stripEmptyParagraphs)
            $this->content = str_replace("<p>&nbsp;</p>","",$this->content);

        return $this->content;
    }
    
    public function videoEmbeds() {
        $embedRegexes=[
            "#(http|https):\\/\\/(www.)*youtube.com\\/embed\\/([^'\"]+)#i",
            '#(http|https)://(www\.)?youtube\.com/watch.*#i',
            '#(http|https)://(www\.)?youtube\.com/playlist.*#i',
            '#(http|https)://youtu\.be/.*#i',
            '#(http|https)?://(.+\.)?vimeo\.com/.*#i'
        ];

        $embeds=[];

        foreach ($embedRegexes as $regex) {
            $matches=[];
            if (preg_match_all($regex, $this->post->post_content, $matches)) {
                $match=$matches[0][0];

                if (strpos($match,'embed')>0) {
                    $parts=explode("/",$match);
                    $embeds[] = "https://www.youtube.com/watch?v=".array_pop($parts);
                } else {
                    $embeds[] = $match;
                }
            }
        }

        return $embeds;
    }

    public function date()
    {
        if ($this->date)
            return $this->date;

        $this->date = new Carbon($this->post->post_date_gmt);

        return $this->date;

        //return mysql2date($format, $this->post->post_date);
    }

    public function updated() {
        if ($this->updated)
            return $this->updated;

        $this->updated = new Carbon($this->post->post_modified_gmt);

        return $this->updated;
    }

    public function thumbnail()
    {
        if ($this->thumbnail)
            return $this->thumbnail;

        $thumb_id = get_post_thumbnail_id($this->post->ID);
        if ($thumb_id)
            $this->thumbnail = $this->context->modelForPost(\WP_Post::get_instance($thumb_id));

        return $this->thumbnail;
    }

    public function excerpt($len = 50, $force = false, $readmore = 'Read More', $strip = true, $allowed_tags = 'p a span b i br h1 h2 h3 h4 h5 ul li img blockquote')
    {
        $text = '';
        $trimmed = false;
        if (isset($this->post->post_excerpt) && strlen($this->post->post_excerpt))
        {
            if ($force)
            {
                $text = Text::trim($this->post->post_excerpt, $len, true, $allowed_tags);
                $trimmed = true;
            }
            else
            {
                $text = $this->post->post_excerpt;
            }
        }
        if (!strlen($text) && strpos($this->post->post_content, '<!--more-->') !== false)
        {
            $pieces = explode('<!--more-->', $this->post->post_content);
            $text = $pieces[0];
            if ($force)
            {
                $text = Text::trim($text, $len, true, $allowed_tags);
                $trimmed = true;
            }
        }
        if (!strlen($text))
        {
            $text = Text::trim($this->content(), $len, true, $allowed_tags);
            $trimmed = true;
        }
        if (!strlen(trim($text)))
        {
            return trim($text);
        }
        if ($strip)
        {
            $text = trim(strip_tags($text));
        }
        if (strlen($text))
        {
            $text = trim($text);
            $last = $text[strlen($text) - 1];
            if ($last != '.' && $trimmed)
            {
                $text .= ' &hellip; ';
            }
            if (!$strip)
            {
                $last_p_tag = strrpos($text, '</p>');
                if ($last_p_tag !== false)
                {
                    $text = substr($text, 0, $last_p_tag);
                }
                if ($last != '.' && $trimmed)
                {
                    $text .= ' &hellip; ';
                }
            }

            if ($readmore)
            {
                $text .= ' <a href="' . $this->permalink() . '" class="read-more">' . $readmore . '</a>';
            }

            if (!$strip)
            {
                $text .= '</p>';
            }
        }
        return trim($text);
    }

    public function permalink()
    {
        if ($this->permalink)
            return $this->permalink;

        $permalink = get_permalink($this->id);

        if ($this->context->useRelative) {
            if ($permalink && !empty($permalink)) {
                $parsed = parse_url($permalink);
                $plink  = $parsed['path'];
                if (isset($parsed['query']) && !empty($parsed['query']))
                    $plink .= '?' . $parsed['query'];

                $permalink = $plink;
            }
        }

        $this->permalink=$permalink;

        return $this->permalink;
    }

    public function field($field)
    {
        return get_field($field, $this->post->ID);
    }

    public function related($postTypes, $limit)
    {
        global $wpdb;
        array_walk($postTypes, function (&$value, $index)
        {
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

        $results=$wpdb->get_results($query);
        $related=[];
        if ($results) {
            foreach($results as $result)
            {
                $related[]=$this->context->modelForPost(\WP_Post::get_instance($result->ID));
            }
        }

        return $related;
    }

    public function editLink() {
        return get_edit_post_link($this->id);
    }

//
//    public function __debugInfo() {
//        return [
//            'id'=>$this->id,
//            'post_name'=>$this->post_name,
//            'title'=>$this->post->post_title,
//            'categories'=>$this->categories(),
//            'tags'=>$this->tags()
//        ];
//    }

    public function jsonSerialize() {
        return [
            'type'=>$this->post->post_type,
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
            'tags'=>$this->tags()
        ];
    }

}
