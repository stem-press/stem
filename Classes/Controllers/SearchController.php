<?php

namespace ILab\Stem\Controllers;

use ILab\Stem\Core\Context;
use ILab\Stem\Core\Response;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends PostsController {
    public $search_terms = '';

    public function __construct(Context $context, $template = null) {
        parent::__construct($context, $template);

        $this->search_terms = get_search_query();
    }

    public function getIndex(Request $request) {
        if ($this->template) {
            return new Response($this->template, [
                'posts'=>$this->posts,
                'terms'=>$this->search_terms,
            ]);
        }
    }
}
