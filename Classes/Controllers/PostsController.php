<?php

namespace ILab\Stem\Controllers;

use ILab\Stem\Core\Context;
use ILab\Stem\Core\Controller;
use ILab\Stem\Models\Post;
use Symfony\Component\HttpFoundation\Request;

class PostsController extends Controller
{
    public $posts=[];

    public function __construct(Context $context, $template=null) {
        parent::__construct($context,$template);

        global $wp_query;

        foreach($wp_query->posts as $post) {
            $this->posts[]=$this->context->modelForPost($post);
        }


    }

    public function getIndex(Request $request) {
        if ($this->template)
            return $this->context->render($this->template,['posts'=>$this->posts]);
    }
}