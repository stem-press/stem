<?php

namespace Stem\Models;

use Stem\Core\Context;

class Term implements \JsonSerializable {
    private static $termCache = [];

    public $context = null;

    protected $id = null;

    protected $name = null;
    protected $slug = null;
    protected $group = null;
    protected $taxonomy = null;
    protected $description = null;
    protected $parent = null;
    protected $count = 0;
    protected $permalink = null;

    public function __construct(Context $context, $termId, $taxonomy, $termData = null)
    {
        if (! $termData) {
            $termData = get_term($termId, $taxonomy);
        }

        if (! $termData) {
            throw new \Exception('Invalid term and taxonomy');
        }
        $this->context = $context;
        $this->id = $termData->term_id;
        $this->name = $termData->name;
        $this->slug = $termData->slug;
        $this->group = $termData->term_group;
        $this->taxonomy = $taxonomy ?: $termData->taxonomy;
        $this->description = $termData->description;
        $this->count = $termData->count;

        if ($termData->parent) {
            $this->parent = self::term($context, $termData->parent, $taxonomy);
        }
    }

    public static function termFromTermData($context, $termData)
    {
        return new self($context, null, null, $termData);
    }

    public static function findTerm($termToFind)
    {
        $terms = get_terms(['post_tag'], ['slug'=>sanitize_title($termToFind)]);

        return $terms;
    }

    public static function findCustomTerm($taxes, $termToFind)
    {
        $terms = get_terms($taxes, ['slug'=>sanitize_title($termToFind)]);

        return $terms;
    }

    public static function term($context, $termId, $taxonomy)
    {
        $key = "$taxonomy-$termId";
        if (isset(self::$termCache[$key])) {
            return self::$termCache[$key];
        }

        $term = new self($context, $termId, $taxonomy);
        self::$termCache[$key] = $term;

        return $term;
    }

    public function permalink()
    {
        if ($this->permalink) {
            return $this->permalink;
        }

        $this->permalink = get_term_link($this->id);

        return $this->permalink;
    }

    public function id() {
        return $this->id;
    }

    public function name()
    {
        return $this->name;
    }

    public function slug()
    {
        return $this->slug;
    }

    public function group()
    {
        return $this->group;
    }

    public function taxonomy()
    {
        return $this->taxonomy;
    }

    public function description()
    {
        return $this->description;
    }

    public function parent()
    {
        return $this->parent;
    }

    public function count()
    {
        return $this->count;
    }

    public function __debugInfo()
    {
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'slug'=>$this->slug,
            'group'=>$this->group,
            'taxonomy'=>$this->taxonomy,
        ];
    }

    public function jsonSerialize()
    {
        return [
            'name'=>$this->name,
            'slug'=>$this->slug,
            'taxonomy'=>($this->taxonomy == 'post_tag') ? 'tag' : $this->taxonomy,
        ];
    }
}
