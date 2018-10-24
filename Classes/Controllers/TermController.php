<?php

namespace ILab\Stem\Controllers;

use ILab\Stem\Models\Term;
use ILab\Stem\Core\Context;
use ILab\Stem\Core\Response;
use Symfony\Component\HttpFoundation\Request;

class TermController extends PostsController {
    public $term = null;
    public $result_count = 0;

    /**
     * TermController constructor.
     * @param Context $context
     * @param null $template
     * @throws \Exception
     */
    public function __construct(Context $context, $template = null) {
        parent::__construct($context, $template);

        $term = get_queried_object();
        $this->term = new Term($context, $term->term_id, $term->taxonomy);
    }

    public function getIndex(Request $request) {
        if ($this->template) {
            return new Response($this->template, [
                'posts'=>$this->posts,
                'term'=>$this->term,
                'result_count'=>$this->result_count,
            ]);
        }
    }
}
