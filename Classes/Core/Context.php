<?php

namespace ILab\Stem\Core;

use ILab\Stem\Models\Page;
use ILab\Stem\Models\Post;
use ILab\Stem\Models\Theme;
use ILab\Stem\Models\Attachment;
use ILab\Stem\Controllers\PageController;
use ILab\Stem\Controllers\PostController;
use ILab\Stem\Controllers\TermController;
use ILab\Stem\Controllers\PostsController;
use ILab\Stem\Controllers\SearchController;
use ILab\Stem\Utilities\Plugins\PluginManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Context.
 *
 * This class represents the current request context and acts like the orchestrator for everything.
 */
class Context {
    /** @var Context  Current context. */
    private static $currentContext;

    /** @var array Controller Map. */
    private $controllerMap = [];

    /** @var array Model cache. */
    private $modelCache = [];

    /** @var Router Collection of routes. */
    private $router;

    /** @var string Root path to the theme. */
    public $rootPath;

    /** @var string Path to classes. */
    public $classPath;

    /** @var string Classes namespace. */
    public $namespace;

    /** @var array App configuration. */
    public $config;

    /** @var callable Callback for theme setup. */
    protected $setupCallback;

    /** @var callable Callback for post deployment.  You need to set 'stem-new-deploy' option to true via WP-CLI to trigger this. */
    protected $deployCallback;

    /** @var callable Callback for pre_get_posts hook. */
    protected $preGetPostsCallback;

    /** @var Dispatcher Dispatcher for requests.  */
    protected $dispatcher;

    /** @var array Factory functions for creating models for a given post type. */
    protected $modelFactories = [];

    /** @var CacheControl|null CacheControl manager */
    public $cacheControl = null;

    /** @var bool Determines if the context is running in debug mode. */
    public $debug;

    /** @var string Site host. */
    public $siteHost = '';

    /** @var string Http host. */
    public $httpHost = '';

    /** @var null|Request Current request. */
    public $request = null;

    /** @var string The current environment.  */
    public $environment = 'development';

    /** @var UI The UI context. */
    public $ui = null;

    /** @var Admin The Admin context. */
    public $admin = null;

	/** @var int The current build as defined the app.php config. */
    public $currentBuild = 1;

    /** @var array Map of post_types to model classes */
    protected $modelMap = [];

    /**
     * Constructor.
     *
     * Throws an exception if config/app.json file is missing.
     *
     * @param $rootPath string The root path to the theme
     *
     * @throws \Exception
     */
    public function __construct($rootPath) {
        $this->siteHost = parse_url(site_url(), PHP_URL_HOST);
        $this->httpHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;

        // Load the config
        if (file_exists($rootPath.'/config/app.php')) {
            $this->config = include $rootPath.'/config/app.php';
        } elseif (file_exists($rootPath.'/config/app.json')) {
            $this->config = JSONParser::parse(file_get_contents($rootPath.'/config/app.json'));
        } else {
            throw new \Exception('Missing app.json or app.php configuration for theme.');
        }

        // Create the request object
        $this->request = Request::createFromGlobals();
        $this->environment = getenv('WP_ENV') ?: 'development';
        $this->debug = (defined(WP_DEBUG) || ($this->environment == 'development'));

        $this->currentBuild = $this->setting('build',filectime(__FILE__));

        // Initialize logging
        $loggingConfig = null;
        if (isset($this->config['logging'])) {
            if (isset($this->config['logging'][$this->environment])) {
                $loggingConfig = $this->config['logging'][$this->environment];
            } elseif (isset($this->config['logging']['development'])) {
                $loggingConfig = $this->config['logging']['development'];
            } elseif (isset($this->config['logging']['other'])) {
                $loggingConfig = $this->config['logging']['other'];
            }
        }
        Log::initialize($loggingConfig);

        // Set our text domain, not really used though.
        $this->textdomain = $this->config['text-domain'];

        // Setup our paths
        $this->rootPath = $rootPath;
        $this->classPath = $rootPath.'/classes/';
        if (! file_exists($this->classPath)) {
            $this->classPath = $rootPath.'/Classes/';
            if (! file_exists($this->classPath)) {
                throw new \xception("Missing 'classes' directory in Stem application directory: {$rootPath}");
            }
        }

        // Create the router for extra routes
        $this->router = new Router($this);

        $this->namespace = $this->config['namespace'];

        // Enable/disable XML RPC
        if ($this->setting('options/disable-xml-rpc', false)) {
            add_filter('xmlrpc_enabled', '__return_false', 10000);
        }

        // Enable/disable WordPress JSON API
        if ($this->setting('options/disable-wp-json-api', false)) {
            add_filter('json_enabled', '__return_false', 10000);
            add_filter('json_jsonp_enabled', '__return_false', 10000);
            add_filter('rest_jsonp_enabled', '__return_false', 10000);
            remove_action('xmlrpc_rsd_apis', 'rest_output_rsd');
            remove_action('wp_head', 'rest_output_link_wp_head', 10);
            remove_action('template_redirect', 'rest_output_link_header', 11);
        }

        // Enable/disable WordPress emoji crap
        if ($this->setting('options/disable-wp-json-api', false)) {
            add_action('init', function () {
                remove_action('admin_print_styles', 'print_emoji_styles');
                remove_action('wp_head', 'print_emoji_detection_script', 7);
                remove_action('admin_print_scripts', 'print_emoji_detection_script');
                remove_action('wp_print_styles', 'print_emoji_styles');
                remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
                remove_filter('the_content_feed', 'wp_staticize_emoji');
                remove_filter('comment_text_rss', 'wp_staticize_emoji');

                add_filter('disable-emoji', function ($plugins) {
                    if (is_array($plugins)) {
                        return array_diff($plugins, ['wpemoji']);
                    } else {
                        return [];
                    }
                }, 10000, 1);
            });
        }

        // Enable/disable RSS Feeds
        if ($this->setting('options/disable-rss', false)) {
            add_action('wp_loaded', function () {
                remove_action('wp_head', 'feed_links', 2);
                remove_action('wp_head', 'feed_links_extra', 3);
            });
        }

        // Create the controller/template dispatcher
        $this->dispatcher = new Dispatcher($this);

        // Autoload function for theme classes
        spl_autoload_register(function ($class) {
            if ('\\' == $class[0]) {
                $class = substr($class, 1);
            }

            $class = strtr($class, '\\', DIRECTORY_SEPARATOR);

            $classParts = explode(DIRECTORY_SEPARATOR, $class);
            if (count($classParts) > 1) {
                array_shift($classParts);
                $shortClass = implode(DIRECTORY_SEPARATOR, $classParts);
                if (file_exists($this->classPath.$shortClass.'.php')) {
                    require_once $this->classPath.$shortClass.'.php';

                    return true;
                }
            }

            if (file_exists($this->classPath.$class.'.php')) {
                require_once $this->classPath.$class.'.php';

                return true;
            }

            return false;
        });

        // Handle routing for routes marked 'early'.  These routes will execute before WordPress is completely
        // loaded so they should only be used for API style routes.
        add_filter('do_parse_request', function($do, \WP $wp) {
            if ($this->router->dispatch(true, $this->request)) {
                return false;
            }

            return $do;
        }, 100, 2);

        // Theme setup action hook
        add_action('after_setup_theme', function () {
            $this->setup();
        });

        // This does the actual dispatching to Stem controllers
        // and templates.
        add_filter('template_include', function ($template) {
            if (!$this->router->dispatch(false, $this->request)) {
                $this->dispatch();
            }
        });

        // Build the controller map that maps the templates that
        // wordpress is trying to "include" to Controller classes
        // in the stem app/theme.  Additionally, we surface these
        // as "page templates" in the wordpress admin UI.
        if (isset($this->config['page-controllers'])) {
            $this->templates = $this->config['page-controllers'];
            foreach ($this->config['page-controllers'] as $key => $controller) {
                $this->controllerMap[strtolower(preg_replace('|[^aA-zZ0-9_]+|', '-', $key))] = $controller;
            }

            add_filter('theme_page_templates', function ($page_templates, $theme, $post) {
                foreach ($this->config['page-controllers'] as $key => $controller) {
                    $page_templates[preg_replace('/\\s+/', '-', $key).'.php'] = $key;
                }

                return $page_templates;
            }, 10, 3);
        }

        // Load/save ACF Pro JSON fields to our config directory
        add_filter('acf/settings/save_json', function ($path) use ($rootPath) {
            $newpath = $rootPath.'/config/fields';
            if (file_exists($newpath)) {
                return $newpath;
            }

            Log::error("Saving ACF fields, missing $newpath directory.");

            return $path;
        });
        add_filter('acf/settings/load_json', function ($paths) use ($rootPath) {
            $newpath = $rootPath.'/config/fields';
            if (! file_exists($newpath)) {
                Log::error("Loading ACF fields, missing $newpath directory.");

                return $paths;
            }

            unset($paths[0]);
            $paths[] = $newpath;

            return $paths;
        });

        // Load our custom post types
        add_action('init', [$this, 'installCustomPostTypes'], 10000);


        // Require our plugins
        $this->setupRequiredPlugins();

        // Initialize cache control
        $this->cacheControl = new CacheControl($this);

        // Initialize the UI and Admin managers
        $this->ui = new UI($this);
        $this->admin = new Admin($this);

        // Load our models
        $this->loadModels();
    }

    /**
     * Creates the context for this theme.  Should be called in functions.php of the theme.
     *
     * @param $rootPath string The root path to the theme
     *
     * @return Context The new context
     * @throws \Exception
     */
    public static function initialize($rootPath) {
        $context = new self($rootPath);
        self::$currentContext = $context;

        return $context;
    }

    /**
     * Returns the current context.
     *
     * @return Context The current context
     */
    public static function current() {
        return self::$currentContext;
    }

    /**
     * Returns a setting using a path string, eg 'options/views/engine'.  Consider this
     * a poor man's xpath.
     *
     * @param string $settingPath The "path" in the config settings to look up.
     * @param bool|mixed $default The default value to return if the settings doesn't exist.
     *
     * @return bool|mixed The result
     */
    public function setting($settingPath, $default = false) {
        return arrayPath($this->config, $settingPath, $default);
    }

    /**
     * Parse routes from a JSON configuration file.
     *
     * @deprecated
     */
    private function parseRoutesJSON() {
        $routesConfig = JSONParser::parse(file_get_contents($this->rootPath.'/config/routes.json'));
        foreach ($routesConfig as $routeName => $routeInfo) {
            $defaults = (isset($routeInfo['defaults']) && is_array($routeInfo['defaults'])) ? $routeInfo['defaults'] : [];
            $requirements = (isset($routeInfo['requirements']) && is_array($routeInfo['requirements'])) ? $routeInfo['requirements'] : [];
            $methods = (isset($routeInfo['methods']) && is_array($routeInfo['methods'])) ? $routeInfo['methods'] : [];
            $early = arrayPath($routeInfo,'early', false);

            $this->router->addRoute($early, $routeName, $routeInfo['endPoint'], $routeInfo['controller'], $defaults, $requirements, $methods);
        }
    }

    /**
     * Parse routes from a PHP configuration file.
     */
    private function parseRoutesPHP() {
        $routesConfig = include $this->rootPath.'/config/routes.php';
        foreach ($routesConfig as $route => $routeInfo) {
            if (!is_array($routeInfo) && is_callable($routeInfo)) {
				$this->router->addRoute(false, $route, $route, $routeInfo);
	        }
	        else {
                $early = arrayPath($routeInfo,'early', false);
                $defaults = (isset($routeInfo['defaults']) && is_array($routeInfo['defaults'])) ? $routeInfo['defaults'] : [];
	            $requirements = (isset($routeInfo['requirements']) && is_array($routeInfo['requirements'])) ? $routeInfo['requirements'] : [];
	            $methods = (isset($routeInfo['methods']) && is_array($routeInfo['methods'])) ? $routeInfo['methods'] : [];
	            $destination = arrayPath($routeInfo, 'controller', null);
	            if (! $destination) {
	                $template = arrayPath($routeInfo, 'template', null);
	                if ($template) {
	                    $destination = function () use ($template) {
	                        return new Response($template, ['request' => $this->request]);
	                    };
	                } else {
	                    $destination = arrayPath($routeInfo, 'function', null);
	                }
	            }

	            if ($destination) {
	                $this->router->addRoute($early, $route, $route, $destination, $defaults, $requirements, $methods);
	            } else {
	                Log::error("Invalid destination for route '$route'.");
	            }
	        }
        }
    }

    /**
     * Additional setup.
     */
    protected function setup() {
        // configure routes

        if (file_exists($this->rootPath.'/config/routes.php')) {
            $this->parseRoutesPHP();
        } elseif (file_exists($this->rootPath.'/config/routes.json')) {
            $this->parseRoutesJSON();
        }

        $this->ui->setup();

        // call the user supplied setup callback
        if ($this->setupCallback) {
            call_user_func($this->setupCallback);
        }

        $this->setupPostFilter();

        if ($this->setting('options/flush-on-deploy', false)) {
            if (get_option('stem-new-deploy', false)) {
                update_option('stem-new-deploy', false);
                flush_rewrite_rules();
                // call the user supplied deploy callback
                if ($this->deployCallback) {
                    call_user_func($this->deployCallback);
                }
            }
        }
    }

    /**
     * Loads and configures the model map
     */
    private function loadModels() {
        // Register the default model map, which can be overridden ;)
        $this->modelMap['post'] = '\\ILab\\Stem\\Models\\Post';
        $this->modelMap['attachment'] = '\\ILab\\Stem\\Models\\Attachment';
        $this->modelMap['page'] = '\\ILab\\Stem\\Models\\Page';

        // DEPRECATED
        $models = arrayPath($this->config, 'model-map', []);
        foreach($models as $postType => $model) {
            $this->modelMap[$postType] = $model;
        }

        // Load the user declared models
        $models = arrayPath($this->config, 'models', []);
        foreach($models as $modelClassname) {
            if (class_exists($modelClassname)) {
                $this->modelMap[$modelClassname::postType()] = $modelClassname;
            }
        }
    }

    /**
     * Installs multiple custom post types from individual json files.
     */
    private function installMultipleCustomPostTypes() {
        if (! file_exists($this->rootPath.'/config/types/')) {
            return;
        }

        $files = glob($this->rootPath.'/config/types/*.json');
        foreach ($files as $file) {
            $type = JSONParser::parse(file_get_contents($file));
            $name = (isset($type['name'])) ? $type['name'] : null;
            register_post_type($name, $type);
        }

        $files = glob($this->rootPath.'/config/types/*.php');
        foreach ($files as $file) {
            $type = include $file;
            $name = (isset($type['name'])) ? $type['name'] : null;
            register_post_type($name, $type);
        }
    }

    /**
     * Configure the "suggested" plugin manager for the app's suggested plugins
     */
    private function setupRequiredPlugins() {
        add_action('after_setup_theme', function(){
            $args = array(
                'page_title'  => __( 'Suggested by Stem', 'stem' ),
                'menu_title'  => __( 'Suggested', 'stem' ),
                'menu_slug'   => 'stem-suggested-plugins',
                'parent_slug' => 'plugins.php',
                'plugin_file' => ILAB_STEM, // Required for use in plugins.
            );

            $plugins = $this->setting('plugins');
            if (is_array($plugins) && !empty($plugins)) {
                new PluginManager( $plugins, $args );
            }
        });
    }

    /**
     * Installs types from a single JSON file.
     * @deprecated
     */
    private function installCustomPostTypesFromJSON() {
        if (! file_exists($this->rootPath.'/config/types.json')) {
            return;
        }

        $types = JSONParser::parse(file_get_contents($this->rootPath.'/config/types.json'));

        foreach ($types as $cpt => $details) {
            register_post_type($cpt, $details);
        }
    }

    /**
     * Installs custom post types from a PHP config.
     */
    private function installCustomPostTypesFromPHP() {
        if (! file_exists($this->rootPath.'/config/types.php')) {
            return;
        }

        $types = include $this->rootPath.'/config/types.php';
        foreach ($types as $cpt => $details) {
            register_post_type($cpt, $details);
        }
    }

    /**
     * Install custom post types.
     */
    public function installCustomPostTypes() {
        foreach($this->modelMap as $postType => $modelClassname) {
            $builder = $modelClassname::postTypeProperties();
            if ($builder != null) {
                $builder->register();
            }

            $fields = $modelClassname::registerFields();
            if (!empty($fields)) {
                if (!isset($fields['location'])) {
                    $fields['location'] = [
                        [
                            [
                                'param' => 'post_type',
                                'operator' => '==',
                                'value' => $modelClassname::postType()
                            ]
                        ]
                    ];
                }

                acf_add_local_field_group($fields);
            }
        }

        $this->installCustomPostTypesFromJSON();
        $this->installCustomPostTypesFromPHP();
        $this->installMultipleCustomPostTypes();
    }

    /**
     * Sets a callable for pre_get_posts filter.
     *
     * @param $callable callable
     */
    public function onPreGetPosts($callable) {
        $this->preGetPostsCallback = $callable;
    }

    /**
     * Sets up post filtering, enabling options for searching by tag and including custom post types in
     * query results automatically.
     */
    private function setupPostFilter() {
        if (! is_admin()) {
            $post_types = $this->setting('search-options/post-types');
            if (! $post_types) {
                $post_types = $this->setting('post-types');
            }

            if ($post_types && (count($post_types) > 1)) {
                add_action('pre_get_posts', function ($query) use ($post_types) {
                    if (($query->is_home() && $query->is_main_query()) || ($query->is_search()) || ($query->is_tag())) {
                        if ($query->is_search()) {
                            if ($post_types) {
                                $query->set('post_type', $post_types);
                            }
                        } else {
                            if ($post_types) {
                                $query->set('post_type', $post_types);
                            }
                        }

                        if ($this->preGetPostsCallback) {
                            call_user_func($this->preGetPostsCallback, $query);
                        }
                    }
                });
            }
        }

        if ($this->setting('search-options/fulltext-search')) {
	        add_filter('posts_search', function($search, $query){
		        if ($query->is_main_query() && $query->is_search()) {
			        if (empty($query->get('s'))) {
				        return ' ';
			        }

			        global $wpdb;
			        return $wpdb->prepare(" AND MATCH($wpdb->posts.post_title, $wpdb->posts.post_content) AGAINST(%s)", '*' . $query->get('s') . '*');
		        } else {
			        return $search;
		        }
	        }, 10000, 2);


	        add_filter('posts_orderby', function($orderby, $query){
		        if ($query->is_main_query() && $query->is_search()) {
			        global $wpdb;

			        return " $wpdb->posts.post_date DESC";
		        } else {
			        return $orderby;
		        }
	        }, 10000, 2);
        }

        // Below alter the way wordpress searches
        $search_tags = $this->setting('search-options/search-tags');
        if ($search_tags) {
            add_filter('posts_join', function ($join, $query) {
                global $wpdb;
                if ($query->is_main_query() && $query->is_search()) {
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
            add_filter('posts_where', function ($where, $query) {
                global $wpdb;
                if ($query->is_main_query() && $query->is_search()) {
                    $user = wp_get_current_user();
                    $user_where = '';
                    $status = ["'publish'"];
                    if (! empty($user->ID)) {
                        $status[] = "'private'";

                        $user_where .= " AND {$wpdb->posts}.post_author = ".esc_sql($user->ID);
                    }
                    $user_where .= " AND {$wpdb->posts}.post_status IN( ".implode(',', $status).' )';

                    $where .= " OR (
                            {$wpdb->term_taxonomy}.taxonomy IN( 'category', 'post_tag' )
                            AND
                            {$wpdb->terms}.name LIKE '%".esc_sql(get_query_var('s'))."%'
                            {$user_where}
                        )";
                }

                return $where;
            }, 10, 2);

            // change the wordpress search
            add_filter('posts_groupby', function ($groupby, $query) {
                global $wpdb;
                if ($query->is_main_query() && $query->is_search()) {
                    $groupby = "{$wpdb->posts}.ID";
                }

                return $groupby;
            }, 10, 2);
        }
    }

    /**
     * Dispatches the current request.
     */
    protected function dispatch() {
        try {
            $this->dispatcher->dispatch();
        } catch (\Exception $ex) {
            if (env('WP_ENV') != 'production') {
                $res = new \Symfony\Component\HttpFoundation\Response($this->ui->render('stem-system.error', ['ex'=>$ex, 'data' => \ILab\Stem\Core\Response::$lastData]), 500);
                $res->send();
                die;
            } else {
                $this->dispatcher->dispatchError(500, $ex);
            }
        }
    }

    /**
     * Sets a user supplied callback to call when doing the theme setup.
     *
     * @param $callback callable
     */
    public function onSetup($callback) {
        $this->setupCallback = $callback;
    }

    /**
     * Sets a user supplied callback to call after a site has been deployed.  You need to set 'stem-new-deploy' option
     * to true via WP-CLI to trigger this.
     *
     * @param $callback callable
     */
    public function onDeploy($callback) {
        $this->deployCallback = $callback;
    }

    /**
     * Set the factory function for creating this model for this post type.
     *
     * @param $post_type string
     * @param $callable callable
     */
    public function setCustomPostTypeModelFactory($post_type, $callable) {
        $this->modelFactories[$post_type] = $callable;
    }

    /**
     * Sets the function for creating models for Posts.
     *
     * @param $callable callable
     */
    public function setPostModelFactory($callable) {
        $this->setCustomPostTypeModelFactory('post', $callable);
    }

    /**
     * Sets the function for creating models for Pages.
     *
     * @param $callable callable
     */
    public function setPageModelFactory($callable) {
        $this->setCustomPostTypeModelFactory('page', $callable);
    }

    /**
     * Sets the function for creating models for Attachments.
     *
     * @param $callable callable
     */
    public function setAttachmentModelFactory($callable) {
        $this->setCustomPostTypeModelFactory('attachment', $callable);
    }

    /**
     * Creates a model instance for the supplied WP_Post object.
     *
     * @param \WP_Post $post
     *
     * @return Attachment|Page|Post|null
     */
    public function modelForPost(\WP_Post $post) {
        if (empty($post)) {
            return null;
        }

        $result = null;

        if (isset($this->modelCache["m-$post->ID"])) {
            return $this->modelCache["m-$post->ID"];
        }

        if (isset($this->modelFactories[$post->post_type])) {
            $result = call_user_func_array($this->modelFactories[$post->post_type], [$this, $post]);
        } elseif (isset($this->modelMap[$post->post_type])) {
            $className = $this->modelMap[$post->post_type];
            if (class_exists($className)) {
                $result = new $className($this, $post);
            }
        }

        if ($result) {
            $this->modelCache["m-$post->ID"] = $result;
        }

        return $result;
    }

    /**
     * Creates a model instance for the supplied post ID.
     *
     * @param int $postId
     *
     * @return Attachment|Page|Post|null
     */
    public function modelForPostID($postId)
    {
        if (empty($postId)) {
            return null;
        }

        $result = null;

        if (isset($this->modelCache["m-$postId"])) {
            return $this->modelCache["m-$postId"];
        }

        $post = \WP_Post::get_instance($postId);
        if ($post === false) {
            return null;
        }

        return $this->modelForPost($post);
    }

    /**
     * Performs a query for posts.
     *
     * @param $args
     * @return array
     */
    public function findPosts($args) {
        $query = new \WP_Query($args);
        $posts = [];
        foreach ($query->posts as $post) {
            $posts[] = $this->modelForPost($post);
        }

        return $posts;
    }

    /**
     * Creates a controller for the given page type.
     *
     * @param $pageType string
     * @param $template string
     *
     * @return PageController|PostController|PostsController|null
     */
    public function createController($pageType, $template) {
        $controller = null;

        // See if a default controller exists in the theme namespace
        $class = null;
        if ($pageType == 'posts') {
            $class = $this->namespace.'\\Controllers\\PostsController';
        } elseif ($pageType == 'post') {
            $class = $this->namespace.'\\Controllers\\PostController';
        } elseif ($pageType == 'page') {
            $class = $this->namespace.'\\Controllers\\PageController';
        } elseif ($pageType == 'term') {
            $class = $this->namespace.'\\Controllers\\TermController';
        }

        if (class_exists($class)) {
            $controller = new $class($this, 'templates/'.$template);

            return $controller;
        }

        // Create a default controller from the stem namespace
        if ($pageType == 'posts') {
            $controller = new PostsController($this, 'templates/'.$template);
        } elseif ($pageType == 'post') {
            $controller = new PostController($this, 'templates/'.$template);
        } elseif ($pageType == 'page') {
            $controller = new PageController($this, 'templates/'.$template);
        } elseif ($pageType == 'search') {
            $controller = new SearchController($this, 'templates/'.$template);
        } elseif ($pageType == 'term') {
            $controller = new TermController($this, 'templates/'.$template);
        }

        return $controller;
    }

    /**
     * Maps a wordpress template to a controller.
     *
     * @param $wpTemplateName
     *
     * @return null
     */
    public function mapController($wpTemplateName) {
        if (isset($this->controllerMap[$wpTemplateName])) {
            $class = $this->controllerMap[$wpTemplateName];
            $template = null;

            if (is_array($class)) {
                $template = (isset($class['template'])) ? $class['template'] : null;
                $class = (isset($class['controller'])) ? $class['controller'] : null;
            }

            if (class_exists($class)) {
                $controller = new $class($this, $template);

                return $controller;
            }
        }
    }
}
