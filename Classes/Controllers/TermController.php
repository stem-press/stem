<?php
namespace ILab\Stem\Controllers;

use ILab\Stem\Core\Context;
use ILab\Stem\Core\Response;
use ILab\Stem\Core\Controller;
use ILab\Stem\Models\Post;
use ILab\Stem\Models\Term;
use Symfony\Component\HttpFoundation\Request;

class TermController extends PostsController
{
    public $term=null;
    public $result_count=0;

    public function __construct(Context $context, $template=null) {
        parent::__construct($context,$template);

        global $wp_query;
        $term = get_queried_object();
        $this->term=new Term($context,$term->term_id,$term->taxonomy);
        $this->result_count=$wp_query->found_posts;
    }

    public function getIndex(Request $request) {
        if ($this->template)
            return new Response($this->template,[
                'posts'=>$this->posts,
                'term'=>$this->term,
                'result_count'=>$this->result_count
            ]);
    }
}