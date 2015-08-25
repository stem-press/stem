<?php

namespace ILab\Stem\Models;


use ILab\Stem\Core\Context;

class Term {
    private static $termCache=[];

    public $id;
    public $name;
    public $slug;
    public $group;
    public $taxonomy;
    public $description;
    public $parent;
    public $count;

    public function __construct(Context $context, $termId, $taxonomy, $termData=null) {
        if (!$termData)
            $termData=get_term($termId,$taxonomy);

        if (!$termData)
            throw new \Exception('Invalid term and taxonomy');

        $this->context=$context;
        $this->id=$termData->term_id;
        $this->name=$termData->name;
        $this->slug=$termData->slug;
        $this->group=$termData->term_group;
        $this->taxonomy=$taxonomy;
        $this->description=$termData->description;
        $this->count=$termData->count;

        if ($termData->parent)
        {
            $this->parent=self::term($context, $termData->parent, $taxonomy);
        }
    }

    public static function termFromTermData($context, $termData) {
        return new Term($context, null, null, $termData);
    }

    public static function term($context, $termId, $taxonomy) {
        $key="$taxonomy-$termId";
        if (isset(self::$termCache[$key]))
            return self::$termCache[$key];

        $term=new Term($context, $termId, $taxonomy);
        self::$termCache[$key]=$term;

        return $term;
    }
}