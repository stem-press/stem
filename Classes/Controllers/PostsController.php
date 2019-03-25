<?php

namespace Stem\Controllers;

use Stem\Models\Post;
use Stem\Core\Context;
use Stem\Core\Response;
use Stem\Core\Controller;
use Stem\Models\Query\PostCollection;
use Symfony\Component\HttpFoundation\Request;

class PostsController extends Controller {
    public $page = null;
    public $posts = null;

    public function __construct(Context $context, $template = null) {
        parent::__construct($context, $template);

        global $wp_query;

        if ($wp_query->post && ($wp_query->post->post_type == 'page')) {
            $this->page = $context->modelForPost($wp_query->post);
            $context->cacheControl->setCacheControlHeadersForPage($this->page->id);
        }

        $this->posts = new PostCollection($context, null, $wp_query);
    }

    public function getIndex(Request $request) {
        if ($this->template) {
            return new Response($this->template, [
                'page' => $this->page,
                'posts'=>$this->posts,
            ]);
        }

        return null;
    }
}
