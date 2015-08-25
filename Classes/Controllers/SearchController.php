<?php
namespace ILab\Stem\Controllers;

use ILab\Stem\Core\Context;
use ILab\Stem\Core\Controller;
use ILab\Stem\Models\Post;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends PostsController
{
    public $search_terms='';
    public $result_count=0;

    public function __construct(Context $context, $template=null) {
        parent::__construct($context,$template);

        global $wp_query;

        $this->search_terms=get_search_query();
        $this->result_count=$wp_query->found_posts;
    }

    public function getIndex(Request $request) {
        if ($this->template)
            return $this->context->render($this->template,[
                'posts'=>$this->posts,
                'terms'=>$this->search_terms,
                'result_count'=>$this->result_count
            ]);
    }
}