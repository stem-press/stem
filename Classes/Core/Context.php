<?php

namespace ILab\Stem\Core;

use ILab\Stem\Controllers\SearchController;
use ILab\Stem\Controllers\PostController;
use ILab\Stem\Controllers\PostsController;
use ILab\Stem\Controllers\PageController;
use ILab\Stem\Controllers\TermController;
use ILab\Stem\Models\Attachment;
use ILab\Stem\Models\Page;
use ILab\Stem\Models\Post;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Context
 *
 * This class represents the current request Context and acts like the orchestrator for everything.
 *
 * @package ILab\Stem\Core
 */
class Context {
    /**
     * Controller Map
     * @var array
     */
    private $controllerMap=[];

    /**
     * Model cache
     * @var array
     */
    private $modelCache=[];

    /**
     * Current context
     * @var Context
     */
    private static $currentContext;

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
     * Callback for pre_get_posts hook
     * @var callable
     */
    protected $preGetPostsCallback;

    /**
     * Dispatcher for requests
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * Factory functions for creating models for a given post type
     * @var array
     */
    protected $modelFactories=[];

    /**
     * Factory functions for creating controllers
     * @var array
     */
    protected $controllerFactories=[];

    /**
     * The text domain for internationalization
     * @var string
     */
    public $textdomain;

    /**
     * Determines if the context is running in debug mode
     * @var bool
     */
    public $debug;

    /**
     * Collection of routes
     * @var Router
     */
    private $router;

    /**
     * Site host
     * @var string
     */
    public $siteHost='';

    /**
     * Current request
     * @var null|Request
     */
    public $request=null;


    /**
     * Constructor
     *
     * Throws an exception if config/app.json file is missing.
     *
     * @param $rootPath string The root path to the theme
     * @throws \Exception
     */
    public function __construct($rootPath) {
        $this->siteHost=parse_url(site_url(),PHP_URL_HOST);
        if (!file_exists($rootPath.'/config/app.json'))
            throw new \Exception('Missing app.json for theme.');

        // Create the request object
        $this->request=Request::createFromGlobals();

        $this->config = JSONParser::parse(file_get_contents($rootPath.'/config/app.json'));

        $this->textdomain=$this->config['textdomain'];

        // Setup our paths
        $this->rootPath=$rootPath;
        $this->classPath=$rootPath.'/classes/';
        $this->viewPath=$rootPath.'/views/';

        $this->router=new Router($this);

        $this->jsPath=get_template_directory_uri().'/js/';
        $this->cssPath=get_template_directory_uri().'/css/';
        $this->imgPath=get_template_directory_uri().'/img/';
        $this->namespace=$this->config['namespace'];

        $this->debug=(defined(WP_DEBUG) || (getenv('WP_ENV')=='development'));

        // Create the controller/template dispatcher
        $this->dispatcher=new Dispatcher($this);
//
//        if (isset($this->config['controller-map']))
//            $this->controllerMap=$this->config['controller-map'];

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

        if (isset($this->config['page-controllers'])) {
            $this->templates=$this->config['page-controllers'];
            foreach($this->config['page-controllers'] as $key => $controller)
                $this->controllerMap[strtolower(preg_replace("/\\s+/","-",$key))]=$controller;

            add_filter('theme_page_templates',function($page_templates, $theme, $post){
                foreach($this->config['page-controllers'] as $key => $controller)
                    $page_templates[preg_replace("/\\s+/","-",$key).'.php']=$key;

                return $page_templates;
            }, 10, 3);
        }

        // Load/save ACF Pro JSON fields to our config directory
        add_filter('acf/settings/save_json', function ( $path ) {
            $path = get_stylesheet_directory() . '/config/fields';
            return $path;
        });

        add_filter('acf/settings/load_json', function ( $paths ) {
            unset($paths[0]);
            $paths[] = get_stylesheet_directory() . '/config/fields';
            return $paths;
        });

        if (file_exists($rootPath.'/config/types.json')) {
            add_action( 'init', [$this, 'installCustomPostTypes'], 10000);
        }
    }

    /**
     * Installs multiple custom post types from individual json files.
     */
    private function installMultipleCustomPostTypes() {
        if (!file_exists($this->rootPath.'/config/types/'))
            return;

        $files = glob($this->rootPath.'/config/types/*.json');

        foreach($files as $file) {
            $type = JSONParser::parse(file_get_contents($file));
            $name = (isset($type['name'])) ? $type['name'] : null;
            register_post_type($name, $type);
        }
    }

    /**
     * Install custom post types
     */
    public function installCustomPostTypes() {
        if (!file_exists($this->rootPath.'/config/types.json')) {
            $this->installMultipleCustomPostTypes();
            return;
        }

        $types = JSONParser::parse(file_get_contents($this->rootPath . '/config/types.json'));

        foreach($types as $cpt => $details)
            register_post_type($cpt, $details);

        $this->installMultipleCustomPostTypes();
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

        // configure routes
        if (file_exists($this->rootPath.'/config/routes.json')) {
            $routesConfig = JSONParser::parse(file_get_contents($this->rootPath . '/config/routes.json'));
            foreach($routesConfig as $routeName => $routeInfo) {
                $defaults = (isset($routeInfo['defaults']) && is_array($routeInfo['defaults'])) ? $routeInfo['defaults'] : [];
                $requirements = (isset($routeInfo['requirements']) && is_array($routeInfo['requirements'])) ? $routeInfo['requirements'] : [];
                $methods = (isset($routeInfo['methods']) && is_array($routeInfo['methods'])) ? $routeInfo['methods'] : [];
                $this->router->addRoute($routeName, $routeInfo['endPoint'], $routeInfo['controller'], $defaults, $requirements, $methods);
            }
        }

        // configure image sizes
        if (file_exists($this->rootPath.'/config/sizes.json')) {
            $sizesConfig = JSONParser::parse(file_get_contents($this->rootPath.'/config/sizes.json'));
            $customSizes=[];

            foreach($sizesConfig as $key => $info){
                if ($key=='post-thumbnail') {
                    set_post_thumbnail_size( $info['width'],$info['height'],$info['crop']);
                }
                else {
                    add_image_size($key,$info['width'],$info['height'],$info['crop']);
                }

                if (isset($info['display']) && $info['display'])
                    $customSizes[]=$key;
            }

            if (count($customSizes)>0) {
                add_filter('image_size_names_choose',function($sizes) use ($customSizes) {
                    foreach($customSizes as $size) {
                        $sizes[$size]=ucwords(str_replace('_', ' ', str_replace('-', ' ', $size)));
                    }

                    return $sizes;
                });
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
            if (isset($this->config['enqueue'])) {
                $enqueueConfig=$this->config['enqueue'];
                if (isset($enqueueConfig['useManifest']) && $enqueueConfig['useManifest'])
                    $this->enqueueManifest();

                if (isset($enqueueConfig['js'])) {
                    foreach($enqueueConfig['js'] as $js)
                        wp_enqueue_script($js,$this->jsPath.$js,['jquery'],false,true);
                }

                if (isset($enqueueConfig['css'])) {
                    foreach($enqueueConfig['css'] as $css)
                        wp_enqueue_style($css,$this->cssPath.$css);
                }
            }
            else
                $this->enqueueManifest();
        });

        if (isset($this->config['clean']['wp_head']))
        {
            foreach($this->config['clean']['wp_head'] as $what)
                remove_action('wp_head', $what);
        }

        if (isset($this->config['clean']['headers']))
        {
            // Unset some junky ass wordpress headers
            add_filter( 'wp_headers', function($headers){
                foreach($this->config['clean']['headers'] as $header)
                    if (isset($headers[$header]))
                        unset($headers[$header]);

                return $headers;
            });
        }

        // call the user supplied callback
        if ($this->setupCallback)
            call_user_func($this->setupCallback);

        if (isset($this->config['clean']['wp_head']))
        {
            if (in_array('adjacent_posts_rel_link_wp_head',$this->config['clean']['wp_head']))
            {
                // Fix Yoast link rel=next
                if (!function_exists('genesis'))
                    eval("function genesis(){}");
                add_filter('wpseo_genesis_force_adjacent_rel_home', function ($value)
                {
                    return false;
                });
            }
        }


        if (isset($this->config['permalinks']['relative']) && $this->config['permalinks']['relative']) {
            add_filter('wp_nav_menu',[$this,'make_relative_url']);
        }

        $this->setupPostFilter();
    }

    private function enqueueManifest() {
        if (isset($this->config['enqueue']['manifest']))
        {
            if (file_exists($this->rootPath.'/'.$this->config['enqueue']['manifest']))
            {
                $manifest=JSONParser::parse(file_get_contents($this->rootPath.'/'.$this->config['enqueue']['manifest']),true);
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
    }

    /**
     * Sets a callable for pre_get_posts filter.
     * @param $callable callable
     */
    public function onPreGetPosts($callable) {
        $this->preGetPostsCallback=$callable;
    }

    /**
     * Sets up post filtering, enabling options for searching by tag and including custom post types in
     * query results automatically.
     */
    private function setupPostFilter() {
        add_action( 'pre_get_posts', function($query){

            if (($query->is_home() && $query->is_main_query()) || ($query->is_search()) || ($query->is_tag())) {
                if ($query->is_search())
                {
                    if (isset($this->config['search_options']['post_types']))
                        $query->set('post_type', $this->config['search_options']['post_types']);
                }
                else
                {
                    if (isset($this->config['post_types']))
                        $query->set('post_type', $this->config['post_types']);
                }

                if ($this->preGetPostsCallback)
                    call_user_func($this->preGetPostsCallback,$query);
            }
        });

        $search_tags=(isset($this->config['search_options']['search_tags']) && $this->config['search_options']['search_tags']);

        // Below alter the way wordpress searches
        if ($search_tags)
        {
            add_filter('posts_join', function ($join, $query)
            {
                global $wpdb;
                if ($query->is_main_query() && $query->is_search())
                {
                    $join .= "
                LEFT JOIN
                (
                    {$wpdb->term_relationships}
                    INNER JOIN
                        {$wpdb->term_taxonomy} ON {$wpdb->term_taxonomy}.term_taxonomy_id = {$wpdb->term_relationships}.term_taxonomy_id
                    INNER JOIN
                        {$wpdb->terms} ON {$wpdb->terms}.term_id = {$wpdb->term_taxonomy}.term_id
                )
                ON {$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id ";
                }
                return $join;
            }, 10, 2);

            // change the wordpress search
            add_filter('posts_where', function ($where, $query)
            {
                global $wpdb;
                if ($query->is_main_query() && $query->is_search())
                {
                    $user = wp_get_current_user();
                    $user_where = '';
                    $status = array("'publish'");
                    if (!empty($user->ID))
                    {
                        $status[] = "'private'";

                        $user_where .= " AND {$wpdb->posts}.post_author = " . esc_sql($user->ID);
                    }
                    $user_where .= " AND {$wpdb->posts}.post_status IN( " . implode(',', $status) . " )";

                    $where .= " OR (
                            {$wpdb->term_taxonomy}.taxonomy IN( 'category', 'post_tag' )
                            AND
                            {$wpdb->terms}.name LIKE '%" . esc_sql(get_query_var('s')) . "%'
                            {$user_where}
                        )";
                }
                return $where;
            }, 10, 2);

            // change the wordpress search
            add_filter('posts_groupby', function ($groupby, $query)
            {
                global $wpdb;
                if ($query->is_main_query() && $query->is_search())
                {
                    $groupby = "{$wpdb->posts}.ID";
                }
                return $groupby;
            }, 10, 2);
        }
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
     * Registers a shortcode
     * @param $shortcode string
     * @param $callable callable
     */
    public function registerShortcode($shortcode, $callable) {
        add_shortcode($shortcode, $callable);
    }


    /**
     * Creates the context for this theme.  Should be called in functions.php of the theme
     *
     * @param $rootPath string The root path to the theme
     * @return Context The new context
     */
    public static function initialize($rootPath) {
        $context=new Context($rootPath);
        self::$currentContext=$context;
        return $context;
    }

    /**
     * Returns the context for the theme's domain
     *
     * @param $domain string The name of the theme's domain, eg the name of the theme
     * @return Context The theme's context
     */
    public static function current() {
        return self::$currentContext;
    }

    /**
     * Set the factory function for creating this model for this post type.
     *
     * @param $post_type string
     * @param $callable callable
     */
    public function setCustomPostTypeModelFactory($post_type, $callable) {
        $this->modelFactories[$post_type]=$callable;
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
        if (!$post)
            return null;

        $result=null;

        if (isset($this->modelCache["m-$post->ID"]))
            return $this->modelCache["m-$post->ID"];

        if (isset($this->modelFactories[$post->post_type])) {
            $result=call_user_func_array($this->modelFactories[$post->post_type],[$this,$post]);
        }
        else if (isset($this->config['model-map'][$post->post_type])) {
            $className=$this->config['model-map'][$post->post_type];
            if (class_exists($className))
                $result=new $className($this,$post);
        }

        if (!$result) {
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

    /**
     * Set the factory for creating a controller for a given post type
     * @param $type
     * @param $callable
     */
    public function setControllerFactory($type,$callable) {
        $this->controllerFactories[$type]=$callable;
    }

    /**
     * Creates a controller for the given page type
     * @param $pageType string
     * @param $template string
     * @return PageController|PostController|PostsController|null
     */
    public function createController($pageType, $template) {
        $controller=null;

        // Use factories first
        if (isset($this->controllerFactories[$pageType])) {
            $callable=$this->controllerFactories[$pageType];
            $controller=$callable($this,'templates/' . $template);

            if ($controller)
                return $controller;
        }

        // See if a default controller exists in the theme namespace
        $class=null;
        if ($pageType=='posts')
            $class = $this->namespace.'\\Controllers\\PostsController';
        else if ($pageType=='post')
            $class = $this->namespace.'\\Controllers\\PostController';
        else if ($pageType=='page')
            $class = $this->namespace.'\\Controllers\\PageController';
        else if ($pageType=='term')
            $class = $this->namespace.'\\Controllers\\TermController';

        if (class_exists($class)) {
            $controller=new $class($this,'templates/' . $template);
            return $controller;
        }

        // Create a default controller from the stem namespace
        if ($pageType=='posts')
            $controller=new PostsController($this,'templates/' . $template);
        else if ($pageType=='post')
            $controller=new PostController($this,'templates/' . $template);
        else if ($pageType=='page')
            $controller=new PageController($this,'templates/' . $template);
        else if ($pageType=='search')
            $controller=new SearchController($this,'templates/' . $template);
        else if ($pageType=='term')
            $controller=new TermController($this,'templates/' . $template);

        return $controller;
    }

    /**
     * Maps a wordpress template to a controller
     * @param $wpTemplateName
     * @return null
     */
    public function mapController($wpTemplateName) {
        if (isset($this->controllerMap[$wpTemplateName])) {
            $class=$this->controllerMap[$wpTemplateName];
            if (class_exists($class)) {
                $controller=new $class($this,$wpTemplateName);
                return $controller;
            }
        }

        return null;
    }

    /**
     * Renders a view
     *
     * @param $view string The name of the view
     * @param $data array The data to display in the view
     * @return string The rendered view
     */
    public function render($view,$data) {
        try {
            ob_start();
            $result=View::render_view($this,$view,$data);
            ob_end_clean();
            return $result;
        }
        catch (ViewException $ex) {
            while (ob_get_level()>0)
                ob_end_clean();

            echo View::render_error_view($ex);
            die;
        }
    }

    /**
     * Outputs the Wordpress generated header html
     *
     * @return mixed|string
     */
    public function header() {

        ob_start();
        wp_head();
        $header=ob_get_clean();
        $header=preg_replace("/<!--\\s*(?:.*)Yoast(?:.*)-->/", "", $header);

        //fix yoast canonical URLs
        if (isset($this->config['clean']['fix']['yoast']['force-ssl-canonical']) && $this->config['clean']['fix']['yoast']['force-ssl-canonical']) {
            $header=str_replace('<link rel="canonical" href="http://','<link rel="canonical" href="https://',$header);
        }

        // strip hash tags from ngfb descriptions
        if (isset($this->config['clean']['fix']['ngfb']['strip-hash-tags']) && $this->config['clean']['fix']['ngfb']['strip-hash-tags']) {
            $matches=[];
            if (preg_match_all("/<meta property=\"og:description\" content=\"(.*)?\"\\/>/", $header, $matches, PREG_OFFSET_CAPTURE)) {
                $matchCount=count($matches[1]);
                for($i=$matchCount; $i--; $i>-1) {
                    $capture=$matches[1][$i];
                    $origStr=$capture[0];
                    $newStr=preg_replace("/(#[aA-zZ0-9-_]+)/", "", $origStr);
                    $newStr=trim($newStr);
                    $header=substr_replace($header,$newStr,$capture[1],strlen($origStr));
                }
            }

            $matches=[];
            if (preg_match_all("/<meta itemprop=\"description\" content=\"(.*)?\"\\/>/", $header, $matches, PREG_OFFSET_CAPTURE)) {
                $matchCount=count($matches[1]);
                for($i=$matchCount; $i--; $i>-1) {
                    $capture=$matches[1][$i];
                    $origStr=$capture[0];
                    $newStr=preg_replace("/(#[aA-zZ0-9-_]+)/", "", $origStr);
                    $newStr=trim($newStr);
                    $header=substr_replace($header,$newStr,$capture[1],strlen($origStr));
                }
            }
        }

        if (isset($this->config['clean']['remove'])) {
            foreach($this->config['clean']['remove'] as $toremove) {
                $header=str_replace($toremove,"",$header);
            }
        }


        if (isset($this->config['clean']['replace'])) {
            foreach($this->config['clean']['replace'] as $toreplace => $withwhat) {
                $header=str_replace($toreplace,$withwhat,$header);
            }
        }

        return $header;
    }


    /**
     * Outputs the Wordpress generated footer html
     *
     * @return string
     */
    public function footer() {

        ob_start();
        wp_footer();
        $footer=ob_get_clean();

        // Fix better analytics plug
        $footer=preg_replace("/<!-- This site uses the Better Analytics plugin.  (.*)? -->/", "", $footer);
        $footer=preg_replace("/http:\\/\\/$this->siteHost\\/app\\/plugins\\//", "/app/plugins/", $footer);

        // TODO: Relative URL filtering

        return $footer;
    }

    /**
     * Returns the image src to an image included in the theme
     *
     * @param $src
     * @return string
     */
    public function image($src) {
        return $this->imgPath.$src;
    }
    public function script($src) {
        return $this->jsPath.$src;
    }
    public function css($src) {
        return $this->cssPath.$src;
    }

    public function permalink($post_id) {
        $permalink=get_permalink($post_id);

        if (isset($this->config['permalinks']['relative']) && $this->config['permalinks']['relative']) {
            if ($permalink && !empty($permalink))
            {
                $parsed=parse_url($permalink);
                $plink=$parsed['path'];
                if (isset($parsed['query']) && !empty($parsed['query']))
                    $plink.='?'.$parsed['query'];

                return $plink;
            }
        }

        return $permalink;
    }

    public function make_relative_url($input) {
        if ($input && !empty($input)) {
            return preg_replace("/href=\"((http|https):\\/\\/$this->siteHost)(.*)\"/", "href=\"$3\"", $input);
        }

        return $input;
    }

    /**
     * Renders a Wordpress generated menu
     *
     * @param $name string
     * @param bool|false $stripUL
     * @param bool|false $removeText
     * @param string $insertGap
     * @param bool|false $array
     * @return false|mixed|object|string|void
     */
    public function menu($name, $stripUL=false, $removeText=false, $insertGap='', $array=false) {
        if ((!$stripUL) && ($insertGap == '')) {
            $menu = wp_nav_menu(['theme_location' => $name, 'echo' => false, 'container' => false]);
        }
        else if ((!$stripUL) && ($insertGap != '')) {
            $menu = wp_nav_menu(['theme_location' => $name, 'echo' => false, 'container' => false]);
            $matches=[];
            preg_match_all("/(<li\\s+class=\"[^\"]+\">.*<\\/li>)+/", $menu, $matches);
            $links = $matches[0];
            $gappedLinks = [];
            for($i = 0; $i < count($links)-1; $i++) {
                $gappedLinks[] = $links[$i];
                $gappedLinks[] = "<li class=\"{$insertGap}\" />";
            }

            $gappedLinks[] = $links[count($links)-1];

            $links = $gappedLinks;

            return "<ul>".implode("\n",$links)."</ul>";
        }
        else
        {
            $menu=wp_nav_menu(['theme_location'=>$name,'echo'=>false, 'container'=>false, 'items_wrap'=>'%3$s']);
            $matches=[];
            preg_match_all('#(<li\s+id=\"[aA-zZ0-9-]+\"\s+class=\"([^"]+)\"\s*>(.*)<\/li>)#',$menu,$matches);
            if (isset($matches[2]) && isset($matches[3]) && (count($matches[2])==0)) {
                $matches=[];
                preg_match_all('#(<li\s+class=\"([^"]+)\"\s*>(.*)<\/li>)#',$menu,$matches);
            }

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


                if ($insertGap != '') {
                    $gappedLinks = [];
                    for($i = 0; $i < count($links)-1; $i++) {
                        $gappedLinks[] = $links[$i];
                        $gappedLinks[] = "<ul class='{$insertGap}' />";
                    }

                    $gappedLinks[] = $links[count($links)-1];

                    $links = $gappedLinks;
                }

                if ($array) {
                        return $links;
                }

                $menu=implode("\n",$links);
            }
        }

        if ($removeText) {
            $menu=preg_replace("/(<a\\s*[^>]*>){1}(?:.*)(<\\/a>)/m", "$1$2", $menu);
        }

        return $menu;
    }

    /**
     * Performs a query for posts
     *
     * @param $args
     * @return array
     */
    public function findPosts($args){
        $query=new \WP_Query($args);

        $posts=[];
        foreach($query->posts as $post) {
            $posts[]=$this->modelForPost($post);
        }

        return $posts;
    }

    /**
     * Adds a route
     * @param $name string
     * @param $routeStr string
     * @param $destination callable|string
     */
    public function addRoute($name,$routeStr,$destination) {
        $this->router->addRoute($name, $routeStr, $destination);
    }

    public function __debugInfo() {
        return [
            'rootPath'=>$this->rootPath,
            'viewPath'=>$this->viewPath,
            'jsPath'=>$this->jsPath,
            'cssPath'=>$this->cssPath,
            'classPath'=>$this->classPath,
            'namespace'=>$this->namespace
       ];
    }
}