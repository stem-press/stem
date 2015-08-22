<?php

namespace ILab\Stem\Core;
use ILab\Stem\Models\Attachment;
use ILab\Stem\Models\Page;
use ILab\Stem\Models\Post;

/**
 * Class Context
 *
 * This class represents the current request Context and acts like the orchestrator for everything.
 *
 * @package ILab\Stem\Core
 */
class Context {
    private $modelCache=[];

    private static $contexts=[];

    /**
     * Root path to the theme
     * @var string
     */
    public $rootPath;

    /**
     * Path to views
     * @var string
     */
    public $viewPath;

    /**
     * Path to javascript
     * @var string
     */
    public $jsPath;

    /**
     * Path to CSS
     * @var string
     */
    public $cssPath;

    /**
     * Path to classes
     * @var string
     */
    public $classPath;

    /**
     * Classes namespace
     * @var string
     */
    public $namespace;

    /**
     * Theme configuration
     * @var array
     */
    public $config;

    /**
     * Callback for theme setup
     * @var callable
     */
    protected $setupCallback;

    /**
     * Dispatcher for requests
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * Factory functions for creating models for a given post type
     * @var array
     */
    protected $factories=[];

    /**
     * The text domain for internationalization
     * @var string
     */
    public $textdomain;


    /**
     * Constructor
     *
     * Throws an exception if config.json file is missing.
     *
     * @param $rootPath string The root path to the theme
     * @throws \Exception
     */
    public function __construct($rootPath) {
        if (!file_exists($rootPath.'/config.json'))
            throw new \Exception('Missing config.json for theme.');

        $this->config=json_decode(file_get_contents($rootPath.'/config.json'),true);

        $this->textdomain=$this->config['textdomain'];

        // Setup our paths
        $this->rootPath=$rootPath;
        $this->classPath=$rootPath.'/classes/';
        $this->viewPath=$rootPath.'/views/';
        $this->jsPath=get_template_directory_uri().'/js/';
        $this->cssPath=get_template_directory_uri().'/css/';
        $this->imgPath=get_template_directory_uri().'/img/';
        $this->namespace=$this->config['namespace'];

        // Create the controller/template dispatcher
        $this->dispatcher=new Dispatcher($this);

        // Autoload function for theme classes
        spl_autoload_register(function($class) {
            if ('\\' == $class[0]) {
                $class = substr($class, 1);
            }

            $class=strtr($class, '\\', DIRECTORY_SEPARATOR);
            if (file_exists($this->classPath.$class.'.php'))
            {
                require_once($this->classPath . $class . '.php');
                return true;
            }

            return false;
        });

        // Theme setup action hook
        add_action('after_setup_theme', function(){
            $this->setup();
        });

        add_filter('template_include',function($template){
            $this->dispatch();
        });
    }

    /**
     * Does theme setup
     */
    protected function setup()
    {
        // configures theme support
        if (isset($this->config['support']))
        {
            foreach ($this->config['support'] as $feature => $params)
            {
                if (is_array($params))
                    add_theme_support($feature, $params);
                else
                    add_theme_support($feature);
            }
        }

        // configure image sizes
        if (isset($this->config['sizes']))
        {
            foreach($this->config['sizes'] as $key => $info){
                if ($key=='post-thumbnail') {
                    set_post_thumbnail_size( $info['width'],$info['height'],$info['crop']);
                }
                else {
                    add_image_size($key,$info['width'],$info['height'],$info['crop']);
                }
            }
        }

        if (isset($this->config['menu']))
        {
            $menus=[];
            foreach($this->config['menu'] as $key => $title)
                $menus[$key]=__($title,$this->textdomain);

            register_nav_menus($menus);
        }

        add_action( 'wp_enqueue_scripts', function(){
            if (isset($this->config['manifest']))
            {
                if (file_exists($this->rootPath.'/'.$this->config['manifest']))
                {
                    $manifest=json_decode(file_get_contents($this->rootPath.'/'.$this->config['manifest']),true);
                    if (isset($manifest['dependencies']))
                    {
                        foreach($manifest['dependencies'] as $key=>$info) {
                            $ext=pathinfo($key,PATHINFO_EXTENSION);
                            if ($ext=='js')
                                wp_enqueue_script($key,$this->jsPath.$key,['jquery'],false,true);
                            else if ($ext=='css')
                                wp_enqueue_style($key,$this->cssPath.$key);
                        }
                    }
                }
            }
        });

        // call the user supplied callback
        if ($this->setupCallback)
            call_user_func($this->setupCallback);
    }

    /**
     * Dispatches the current request
     */
    protected function dispatch() {

        $this->dispatcher->dispatch();
    }

    /**
     * Sets a user supplied callback to call when doing the theme setup
     * @param $callback callable
     */
    public function onSetup($callback) {
        $this->setupCallback=$callback;
    }

    /**
     * Creates the context for this theme.  Should be called in functions.php of the theme
     *
     * @param $domain string Name of the theme's domain, eg the name of the theme
     * @param $rootPath string The root path to the theme
     * @return Context The new context
     */
    public static function create($domain,$rootPath) {
        $context=new Context($rootPath);
        self::$contexts[$domain]=$context;
        return $context;
    }

    /**
     * Returns the context for the theme's domain
     *
     * @param $domain string The name of the theme's domain, eg the name of the theme
     * @return Context The theme's context
     */
    public static function get($domain) {
        return self::$contexts[$domain];
    }

    /**
     * Renders a view
     *
     * @param $view string The name of the view
     * @param $data array The data to display in the view
     * @return string The rendered view
     */
    public function render($view,$data) {
        return View::render_view($this,$view,$data);
    }

    /**
     * Set the factory function for creating this model for this post type.
     *
     * @param $post_type string
     * @param $callable callable
     */
    public function setCustomPostTypeModelFactory($post_type, $callable) {
        $this->factories[$post_type]=$callable;
    }

    /**
     * Sets the function for creating models for Posts
     * @param $callable callable
     */
    public function setPostModelFactory($callable) {
        $this->setCustomPostTypeModelFactory('post',$callable);
    }

    /**
     * Sets the function for creating models for Pages
     * @param $callable callable
     */
    public function setPageModelFactory($callable) {
        $this->setCustomPostTypeModelFactory('page',$callable);
    }

    /**
     * Sets the function for creating models for Attachments
     * @param $callable callable
     */
    public function setAttachmentModelFactory($callable) {
        $this->setCustomPostTypeModelFactory('attachment',$callable);
    }

    /**
     * Creates a model instance for the supplied WP_Post object
     *
     * @param \WP_Post $post
     * @return Attachment|Page|Post
     */
    public function modelForPost(\WP_Post $post) {
        if (isset($this->modelCache["m-$post->ID"]))
            return $this->modelCache["m-$post->ID"];

        if (isset($this->factories[$post->post_type])) {
            $result=call_user_func_array($this->factories[$post->post_type],[$this,$post]);
        }
        else {
            if ($post->post_type=='attachment')
                $result=new Attachment($this,$post);
            else if ($post->post_type=='page')
                $result=new Page($this,$post);
            else
                $result=new Post($this,$post);
        }

        if ($result)
            $this->modelCache["m-$post->ID"]=$result;

        return $result;
    }

    public function header() {
        ob_start();
        wp_head();
        $header=ob_get_clean();
        $header=preg_replace("/<!--\\s*(?:.*)Yoast(?:.*)-->/", "", $header);
        // TODO: Relative URL filtering
        return $header;
    }


    public function footer() {
        ob_start();
        wp_footer();
        $footer=ob_get_clean();
        // TODO: Relative URL filtering
        return $footer;
    }

    public function image($src) {
        return $this->imgPath.$src;
    }

    public function menu($name, $stripUL=false, $removeText=false) {
        if (!$stripUL)
            $menu=wp_nav_menu(['theme_location'=>$name,'echo'=>false, 'container'=>false]);
        else
        {
            $menu=wp_nav_menu(['theme_location'=>$name,'echo'=>false, 'container'=>false, 'items_wrap'=>'%3$s']);
            $matches=[];
            preg_match_all('#(<li\s+id=\"[aA-zZ0-9-]+\"\s+class=\"([^"]+)\"\s*>(.*)<\/li>)#',$menu,$matches);
            if (isset($matches[2]) && isset($matches[3]))
            {
                $links=[];
                for($i=0; $i<count($matches[2]); $i++) {
                    $link=$matches[3][$i];
                    $classes=[];
                    $matchedClasses=explode(' ',$matches[2][$i]);
                    foreach($matchedClasses as $class)
                    {
                        if (strpos($class,'menu-')!==0)
                            $classes[]=$class;
                    }

                    $links[]=substr_replace($link,'class="'.implode(' ',$classes).'" ',3,0);
                }

                $menu=implode("\n",$links);
            }
        }

        if ($removeText) {
            $menu=preg_replace("/(<a\\s*[^>]*>){1}(?:.*)(<\\/a>)/m", "$1$2", $menu);
        }

        return $menu;
    }

    public function findPosts($args){
        $query=new \WP_Query($args);

        $posts=[];
        foreach($query->posts as $post) {
            $posts[]=$this->modelForPost($post);
        }

        return $posts;
    }

}