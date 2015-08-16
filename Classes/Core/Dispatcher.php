<?php

namespace ILab\Stem\Core;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dispatcher Context
 *
 * This class dispatches requests to the appropriate controller or template
 *
 * @package ILab\Stem\Core
 */
class Dispatcher {
    private $context;

    public function __construct(Context $context) {
        $this->context=$context;
    }

    private function dispatchTemplate($type) {
        if (is_array($type))
        {
            foreach ($type as $name)
            {
                if ($this->dispatchTemplate($name))
                    return true;
            }
        }
        else
        {
            // normalize the name, eg front_page becomes front-page
            $name=preg_replace('|[^a-z0-9_]+|', '-',$type);

            // camel case the the controller class name, eg front_page becomes FrontPage
            $nameparts=explode('-',$name);
            array_walk($nameparts,function(&$value,$index){
               $value=ucfirst($value);
            });
            $classname=implode('',$nameparts);

            // Try running a controller first
            $class = $this->context->namespace.'\\Controllers\\'.$classname.'Controller';

            if (class_exists($class)) {
                $controller=new $class($this->context);

                $request=Request::createFromGlobals();

                $action=($request->query->has('_action')) ? ucfirst($request->query->get('_action')) : 'Index';
                $method=strtolower($request->getMethod()).$action;

                if (method_exists($controller,$method))
                    $response=call_user_func([$controller,$method],$request);
                else
                    throw new \Exception("Missing method '$method' on class '$class'.");

                if (is_object($response) && ($response instanceof Response))
                {
                    $response->send();
                }
                else if (is_string($response))
                {
                    echo $response;
                }

                return true;
            }

            //if we got here, then no class was loaded
            if (file_exists($this->context->viewPath.'templates/'.$name.'.php'))
            {
                echo $this->context->render('templates/'.$name,[$this->context]);
                return true;
            }
        }

        return false;
    }

    private function dispatchPostTypeArchiveTemplate() {
        $post_type = get_query_var('post_type');
        if (is_array($post_type))
            $post_type = reset($post_type);

        $obj = get_post_type_object($post_type);
        if (!$obj->has_archive)
            return false;

        $post_types = array_filter((array)get_query_var('post_type'));

        $templates = [];
        if (count($post_types)==1) {
            $post_type = reset($post_types);
            $templates[] = "archive-{$post_type}";
        }

        $templates[] = 'archive';

        return $this->dispatchTemplate($templates);
    }

    private function dispatchAttachmentTemplate() {
        global $posts;

        $templates=[];
        if (!empty($posts) && isset($posts[0]->post_mime_type)) {
            $type = explode( '/', $posts[0]->post_mime_type );

            if (!empty($type)) {
                $templates[]="attachment-$type[0]";
                if (!empty($type[1])) {
                    $templates[]="attachment-$type[1]";
                    $templates[]="attachment-$type[0]-$type[1]";
                }
            }
        }

        $templates[]='attachment';
        return $this->dispatchTemplate($templates);
    }

    private function dispatchSingleTemplate(){
        $object = get_queried_object();

        $templates = [];

        if (!empty($object->post_type))
            $templates[] = "single-{$object->post_type}";

        $templates[] = "single";

        return $this->dispatchTemplate($templates);
    }

    private function dispatchPageTemplate() {
        $id = get_queried_object_id();
        $template = get_page_template_slug();
        $pagename = get_query_var('pagename');

        if ( ! $pagename && $id ) {
            // If a static page is set as the front page, $pagename will not be set. Retrieve it from the queried object
            $post = get_queried_object();
            if ( $post )
                $pagename = $post->post_name;
        }

        $templates = [];
        if ($template)
            $templates[] = str_replace('.php','',$template);
        if ($pagename)
            $templates[] = "page-$pagename";
        if ($id)
            $templates[] = "page-$id";
        $templates[] = 'page';

        return $this->dispatchTemplate($templates);
    }

    private function dispatchTaxonomyTemplate() {
        $term = get_queried_object();

        $templates = [];

        if (!empty($term->slug)) {
            $taxonomy = $term->taxonomy;
            $templates[] = "taxonomy-$taxonomy-{$term->slug}";
            $templates[] = "taxonomy-$taxonomy";
        }

        $templates[] = 'taxonomy';

        return $this->dispatchTemplate($templates);
    }

    private function dispatchTermTemplate($termType) {
        $object = get_queried_object();

        $templates = [];

        if ( ! empty( $object->slug ) ) {
            $templates[] = "$termType-{$object->slug}";
            $templates[] = "$termType-{$object->term_id}";
        }
        $templates[] = $termType;

        return $this->dispatchTemplate($templates);
    }

    private function dispatchAuthorTemplate() {
        $author = get_queried_object();

        $templates = array();

        if ( $author instanceof WP_User ) {
            $templates[] = "author-{$author->user_nicename}";
            $templates[] = "author-{$author->ID}";
        }
        $templates[] = 'author';

        return $this->dispatchTemplate($templates);
    }

    private function dispatchArchiveTemplate() {
        $post_types = array_filter((array)get_query_var( 'post_type' ) );

        $templates = [];

        if (count($post_types) == 1) {
            $post_type = reset($post_types);
            $templates[] = "archive-{$post_type}";
        }

        $templates[] = 'archive';

        return $this->dispatchTemplate($templates);
    }

    public function dispatch() {
        global $wp_query;

        if (!isset($wp_query)) {
            _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1' );
            return;
        }

        if     ($wp_query->is_404() && $this->dispatchTemplate('404')):
        elseif ($wp_query->is_search() && $this->dispatchTemplate('search')):
        elseif ($wp_query->is_front_page() && $this->dispatchTemplate('front-page')):
        elseif ($wp_query->is_home() && $this->dispatchTemplate(['home','index'])):
        elseif ($wp_query->is_post_type_archive() && $this->dispatchPostTypeArchiveTemplate()):
        elseif ($wp_query->is_tax() && $this->dispatchTaxonomyTemplate()):
        elseif ($wp_query->is_attachment() && $this->dispatchAttachmentTemplate()):
        elseif ($wp_query->is_single() && $this->dispatchSingleTemplate()):
        elseif ($wp_query->is_page() && $this->dispatchPageTemplate()):
        elseif ($wp_query->is_category() && $this->dispatchTermTemplate('category')):
        elseif ($wp_query->is_tag()  && $this->dispatchTermTemplate('tag')):
        elseif ($wp_query->is_author() && $this->dispatchAuthorTemplate()):
        elseif ($wp_query->is_date() && $this->dispatchTemplate('date')):
        elseif ($wp_query->is_archive() && $this->dispatchArchiveTemplate()):
        elseif ($wp_query->is_comments_popup() && $this->dispatchTemplate('comments_popup')):
        elseif ($wp_query->is_paged() && $this->dispatchTemplate('paged')):
        else :
            $this->dispatchTemplate('index');

        endif;
    }
}