<?php

namespace Stem\Core;

use Kint\Kint;
use Stem\Commands\Queue\QueueWorkerCommand;
use Stem\Models\Page;
use Stem\Models\Post;
use Stem\Models\Attachment;
use Stem\Controllers\PageController;
use Stem\Controllers\PostController;
use Stem\Controllers\TermController;
use Stem\Controllers\PostsController;
use Stem\Controllers\SearchController;
use Stem\Models\Taxonomy;
use Stem\Models\Utilities\CustomPostTypeBuilder;
use Stem\Queue\Queue;
use Stem\Utilities\Plugins\PluginManager;
use Symfony\Component\HttpFoundation\Request;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

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

    /** @var string|null The text domain for the app */
    public $textdomain;

    /** @var callable Callback for theme setup. */
    protected $setupCallback;

    /** @var callable Callback for post deployment.  You need to set 'stem-new-deploy' option to true via WP-CLI to trigger this. */
    protected $deployCallback;

    /** @var callable Callback for pre_get_posts hook. */
    protected $preGetPostsCallback;

    /** @var Dispatcher Dispatcher for requests.  */
    protected $dispatcher;

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

    /** @var array|null The last ACF group to be updated  */
    private $lastUpdatedACFGroup = null;

    /** @var null|string The system's timezone */
    private static $timezone = null;

    /** @var Taxonomy[] Custom taxonomies  */
    private $taxonomies = [];

    /**
     * Constructor.
     *
     * Throws an exception if config/app.json file is missing.
     *
     * @param $rootPath string The root path to the theme
     *
     * @throws \Exception
     */
    private function __construct($rootPath) {
        $this->siteHost = parse_url(site_url(), PHP_URL_HOST);
        $this->httpHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;

        // Load the config
        if (file_exists($rootPath.'/config/app.php')) {
            $this->config = include $rootPath.'/config/app.php';
        } else {
            throw new \Exception('Missing `app.php` configuration.');
        }

        // Create the request object
        $this->request = Request::createFromGlobals();

        // Configure environment
        $this->environment = getenv('WP_ENV') ?: 'development';
        $this->debug = (defined(WP_DEBUG) || ($this->environment == 'development'));
        $this->currentBuild = $this->setting('build',filectime(__FILE__));

        if ($this->debug) {
        	$whoops = new Run();
        	$whoops->pushHandler(new PrettyPageHandler());
        	$whoops->register();
        }

        Kint::$enabled_mode = $this->debug;

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

        // Setup our paths
        $this->rootPath = $rootPath;
        $this->classPath = $rootPath.'/classes/';
        if (! file_exists($this->classPath)) {
            $this->classPath = $rootPath.'/Classes/';
            if (! file_exists($this->classPath)) {
                throw new \Exception("Missing 'classes' directory in Stem application directory: {$rootPath}");
            }
        }

        // Set our text domain, not really used though.
        $this->textdomain = $this->config['text-domain'];

        // Set the app's namespace for autoloading
        $this->namespace = $this->config['namespace'];

        // Process Options
        $this->processOptions();

        // Setup autoloading
        $this->setupAutoloading();

        // Configure ACF
        $this->setupACF();

        // Load our custom post types
        add_action('init', function() {
            $this->setupCustomPostTypes();
        }, 10000);

        // Initialize the UI, CacheControl and Admin managers
        $this->ui = new UI($this);
        $this->admin = new Admin($this);
        $this->cacheControl = new CacheControl($this);

        // Load our models and controllers
        $this->setupControllers();
        $this->setupModels();

        // Create the router and controller/template dispatcher
        $this->router = new Router($this);
        $this->dispatcher = new Dispatcher($this);

        // Handle routing for routes marked 'early'.  These routes will execute before WordPress is completely
        // loaded so they should only be used for API style routes.
        add_filter('do_parse_request', function($do, \WP $wp) {
            if ($this->router->dispatch(true, $this->request)) {
                return false;
            }

            return $do;
        }, 100, 2);

        // This does the actual dispatching to Stem controllers
        // and templates.
        add_filter('template_include', function ($template) {
            if (!$this->router->dispatch(false, $this->request)) {
                $this->dispatch();
            }
        });

        // Register any command line commands
	    if (defined( 'WP_CLI' ) && class_exists('\WP_CLI')) {
	    	$commands = arrayPath($this->config, 'commands', []);
	    	$commands = apply_filters('heavymetal/app/commands', $commands);
	    	if (!empty($commands)) {
	    		foreach($commands as $commandClass) {
	    			if (class_exists($commandClass)) {
	    				call_user_func([$commandClass, 'Register']);
				    }
			    }
		    }
	    }

	    $queueConfig = $this->setting('queue', []);
	    if (!empty($queueConfig)) {
	    	Queue::instance()->configure($queueConfig);
		    if (defined( 'WP_CLI' ) && class_exists('\WP_CLI')) {
			    QueueWorkerCommand::Register();
		    }
	    }
    }

    //region Static Methods

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

        $context->addSetupHook();

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

    //endregion

    //region Setup

    /**
     * Configure ACF
     */
    private function setupACF() {
	    add_action('acf/trash_field_group', function($group) {
		    $this->lastUpdatedACFGroup = $group;
	    }, 1, 1);
	    
	    add_action('acf/untrash_field_group', function($group) {
		    $this->lastUpdatedACFGroup = $group;
	    }, 1, 1);

	    add_action('acf/delete_field_group', function($group) {
		    $this->lastUpdatedACFGroup = $group;
	    }, 1, 1);

    	add_action('acf/update_field_group', function($group){
			$this->lastUpdatedACFGroup = $group;
	    }, 1, 1);

        // Load/save ACF Pro JSON fields to our config directory
        add_filter('acf/settings/save_json', function ($path) {
        	if (!empty($this->lastUpdatedACFGroup)) {
        	    $newPath = apply_filters('heavymetal/acf/json/save_path', false, $this->lastUpdatedACFGroup);
        	    if (!empty($newPath)) {
        	    	return $newPath;
	            }
	        }

            $newpath = $this->rootPath.'/config/fields';
            if (file_exists($newpath)) {
                return $newpath;
            }

            Log::error("Saving ACF fields, missing $newpath directory.");

            return $path;
        });

        add_filter('acf/settings/load_json', function ($paths) {
            $newpath = $this->rootPath.'/config/fields';
            if (file_exists($newpath)) {
            	$paths[] = $newpath;
            } else {
	            Log::error("Loading ACF fields, missing $newpath directory.");
            }

            $paths = apply_filters('heavymetal/acf/json/load_paths', $paths);
            if (count($paths) > 1) {
	            unset($paths[0]);
            }

            return $paths;
        });
    }

    /**
     * Process additional configuration options
     */
    private function processOptions() {
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
    }

    /**
     * Parse routes from a PHP configuration file.
     */
    private function parseRoutes() {
        $routesConfig = include $this->rootPath.'/config/routes.php';
        $routesConfig = apply_filters('heavymetal/app/routes', $routesConfig);
        foreach ($routesConfig as $route => $routeInfo) {
            if (!is_array($routeInfo) && is_callable($routeInfo)) {
                $this->router->addRoute(false, $route, $route, $routeInfo);
            }
            else {
            	$routeName = $route;

            	$methods = [];
            	if (preg_match('/^(post|get|put|delete|patch):/m', $route, $matches)) {
            		$route = str_replace($matches[0], '', $route);
            		$methods = [$matches[1]];
	            }

                $early = arrayPath($routeInfo,'early', false);
                $defaults = (isset($routeInfo['defaults']) && is_array($routeInfo['defaults'])) ? $routeInfo['defaults'] : [];
                $requirements = (isset($routeInfo['requirements']) && is_array($routeInfo['requirements'])) ? $routeInfo['requirements'] : [];
                if (empty($methods)) {
	                $methods = (isset($routeInfo['methods']) && is_array($routeInfo['methods'])) ? $routeInfo['methods'] : [];
                }

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
                    $this->router->addRoute($early, $routeName, $route, $destination, $defaults, $requirements, $methods);
                } else {
                    Log::error("Invalid destination for route '$route'.");
                }
            }
        }
    }

    protected function addSetupHook() {
        // Theme setup action hook
        add_action('after_setup_theme', function () {
            $this->setup();
            $this->setupRequiredPlugins();
        });
    }

    /**
     * Final setup step
     */
    protected function setup() {
        // configure routes

        if (file_exists($this->rootPath.'/config/routes.php')) {
            $this->parseRoutes();
        }

        $this->ui->setup();

        // call the user supplied setup callback
        if ($this->setupCallback) {
            call_user_func($this->setupCallback);
        }

        $this->setupPostFilter();
    }

    /**
     * Build the controller map that maps the templates that wordpress is trying to "include" to Controller
     * classes in the stem app.  Additionally, we surface these as "page templates" in the wordpress admin UI.
     */
    private function setupControllers() {

    	$pageControllers = (!empty($this->config['page-controllers'])) ? $this->config['page-controllers'] : [];
    	$pageControllers = apply_filters('heavymetal/app/controllers', $pageControllers);

        if (!empty($pageControllers)) {
            foreach ($pageControllers as $key => $controller) {
                $this->controllerMap[strtolower(preg_replace('|[^aA-zZ0-9_]+|', '-', $key))] = $controller;
            }

            add_filter('theme_page_templates', function ($page_templates, $theme, $post) use ($pageControllers) {
                foreach ($pageControllers as $key => $controller) {
                    $page_templates[preg_replace('/\\s+/', '-', $key).'.php'] = $key;
                }

                return $page_templates;
            }, 10, 3);
        }
    }

    /**
     * Loads and configures the model map
     */
    private function setupModels() {
	    // Load any custom taxonomies
	    $taxonomies = arrayPath($this->config, 'taxonomies', []);
	    $taxonomies = apply_filters('heavymetal/app/taxonomies', $taxonomies);
	    foreach($taxonomies as $taxonomyClassname) {
		    if (class_exists($taxonomyClassname)) {
			    $this->taxonomies[] = new $taxonomyClassname();
		    }
	    }

        // Register the default model map, which can be overridden ;)
        $this->modelMap['post'] = '\\Stem\\Models\\Post';
        $this->modelMap['attachment'] = '\\Stem\\Models\\Attachment';
        $this->modelMap['page'] = '\\Stem\\Models\\Page';
        $this->modelMap['nav_menu_item'] = '\\Stem\\Models\\MenuItem';

        // DEPRECATED
        $models = arrayPath($this->config, 'model-map', []);
        foreach($models as $postType => $model) {
            $this->modelMap[$postType] = $model;
        }

        // Load the user declared models
        $models = arrayPath($this->config, 'models', []);
        $models = apply_filters('heavymetal/app/models', $models);
        foreach($models as $modelClassname) {
            if (class_exists($modelClassname)) {
                $this->modelMap[$modelClassname::postType()] = $modelClassname;
            }
        }

        foreach($this->modelMap as $key => $modelClassname) {
	        if (function_exists('acf_add_local_field_group')) {
		        $fields = $modelClassname::registerFields();
		        $modelClassname::updatePropertyMap($fields);

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
        }

        add_action('trashed_post', function($postId) {
			$post = $this->modelForPostID($postId);
			if (!empty($post)) {
				$post->trashed();
			}
        });

        add_action('untrash_post', function($postId) {
	        $post = $this->modelForPostID($postId);
	        if (!empty($post)) {
		        $post->restored();
	        }
        });

        add_action('before_delete_post', function($postId) {
	        $post = $this->modelForPostID($postId);
	        if (!empty($post)) {
		        $post->willDelete();
	        }
        });

        add_action('delete_post', function($postId) {
	        $post = $this->modelForPostID($postId);
	        if (!empty($post)) {
		        $post->didDelete();
	        }
        });
    }

    /**
     * Configure the "suggested" plugin manager for the app's suggested plugins
     */
    private function setupRequiredPlugins() {
        $args = [
            'page_title'  => __( 'Suggested by Stem', 'stem' ),
            'menu_title'  => __( 'Suggested', 'stem' ),
            'menu_slug'   => 'stem-suggested-plugins',
            'parent_slug' => 'plugins.php',
            'plugin_file' => ILAB_STEM, // Required for use in plugins.
        ];

        $plugins = $this->setting('plugins');
        if (is_array($plugins) && !empty($plugins)) {
            new PluginManager( $plugins, $args );
        }
    }

    /**
     * Install custom post types.
     */
    private function setupCustomPostTypes() {
    	foreach($this->taxonomies as $taxonomy) {
    		$taxonomy->register();
	    }

        foreach($this->modelMap as $postType => $modelClassname) {
        	$modelClassname::initialize();

        	add_filter('views_edit-'.$modelClassname::postType(), [$modelClassname, 'registerViews']);

        	/** @var CustomPostTypeBuilder $builder */
            $builder = $modelClassname::postTypeProperties();
            if ($builder != null) {
            	$builder->metaboxCallback(function() use ($postType) {
            		$this->ui->registerMetabox($postType);
	            });

                $builder->register();
            }
        }

        // Install CPTs from one giant PHP file
        if (file_exists($this->rootPath.'/config/types.php')) {
            $types = include $this->rootPath.'/config/types.php';
            foreach ($types as $cpt => $details) {
                register_post_type($cpt, $details);
            }
        }

        // Install individual PHP CPTs
        if (file_exists($this->rootPath.'/config/types/')) {
            $files = glob($this->rootPath.'/config/types/*.php');
            foreach ($files as $file) {
                $type = include $file;
                $name = (isset($type['name'])) ? $type['name'] : null;
                register_post_type($name, $type);
            }
        }
    }

    /**
     * Configures autoloading for the app
     */
    private function setupAutoloading() {
        // Autoload function for app classes
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

    //endregion

    //region Callbacks

    /**
     * Sets a callable for pre_get_posts filter.
     *
     * @param $callable callable
     */
    public function onPreGetPosts($callable) {
        $this->preGetPostsCallback = $callable;
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

    //endregion

    //region Config

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

    //endregion

    //region Dispatch

    /**
     * Dispatches the current request.
     */
    protected function dispatch() {
        try {
            $this->dispatcher->dispatch();
        } catch (\Exception $ex) {
            if (env('WP_ENV') != 'production') {
                $res = new \Symfony\Component\HttpFoundation\Response($this->ui->render('stem-system.error', ['ex'=>$ex, 'data' => \Stem\Core\Response::$lastData]), 500);
                $res->send();
                die;
            } else {
                $this->dispatcher->dispatchError(500, $ex);
            }
        }
    }

    //endregion

    //region Model Mapping/Filtering

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

        if (isset($this->modelMap[$post->post_type])) {
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

    //endregion

    //region Controllers

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

        return null;
    }

    //endregion

	//region Timezone
	/**
	 * Returns the system's timezone
	 * @return string
	 */
	public static function timezone() {
		if (!empty(static::$timezone)) {
			return static::$timezone;
		}

		$tz = get_option('timezone_string');
		if (empty($tz)) {
			$tz = 'UTC';
			if (is_link('/etc/localtime')) {
				// Mac OS X (and older Linuxes)
				// /etc/localtime is a symlink to the
				// timezone in /usr/share/zoneinfo.
				$filename = readlink('/etc/localtime');
				if (strpos($filename, '/usr/share/zoneinfo/') === 0) {
					$tz = substr($filename, 20);
				}
			} elseif (file_exists('/etc/timezone')) {
				// Ubuntu / Debian.
				$data = file_get_contents('/etc/timezone');
				if ($data) {
					$tz = $data;
				}
			} elseif (file_exists('/etc/sysconfig/clock')) {
				// RHEL / CentOS
				$data = parse_ini_file('/etc/sysconfig/clock');
				if (!empty($data['ZONE'])) {
					$tz = $data['ZONE'];
				}
			}
		}

		if (!empty($tz)) {
			static::$timezone = $tz;
			return static::$timezone;
		}

		return 'UTC';
	}

	//endregion
}
