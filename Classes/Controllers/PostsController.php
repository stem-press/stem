<?php

namespace ILab\Stem\Controllers;

use ILab\Stem\Models\Post;
use ILab\Stem\Core\Context;
use ILab\Stem\Core\Response;
use ILab\Stem\Core\Controller;
use Symfony\Component\HttpFoundation\Request;

class PostsController extends Controller
{
    public $page = null;
    public $posts = [];
    public $totalPosts = 0;
    public $currentPage = 1;
    public $totalPages = 0;

    public function __construct(Context $context, $template = null)
    {
        parent::__construct($context, $template);

        global $wp_query;

        if ($wp_query->post && ($wp_query->post->post_type == 'page')) {
            $this->page = $context->modelForPost($wp_query->post);
            $context->cacheControl->setCacheControlHeadersForPage($this->page->id());
        }

        $this->totalPages = $wp_query->max_num_pages;
        $this->totalPosts = $wp_query->found_posts;
        $this->currentPage = $wp_query->query_vars['paged'] ?: 1;

        foreach ($wp_query->posts as $post) {
            $this->posts[] = $this->context->modelForPost($post);
        }
    }

    public function getIndex(Request $request)
    {
        if ($this->template) {
            return new Response($this->template, [
                'page' => $this->page,
                'totalPosts' => $this->totalPosts,
                'currentPage' => $this->currentPage,
                'totalPages' => $this->totalPages,
                'posts'=>$this->posts,
            ]);
        }
    }
}
