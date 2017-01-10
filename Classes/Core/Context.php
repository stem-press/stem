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
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Context.
 *
 * This class represents the current request context and acts like the orchestrator for everything.
 */
class Context
{
    /**
     * Current context.
     * @var Context
     */
    private static $currentContext;

    /**
     * Controller Map.
     * @var array
     */
    private $controllerMap = [];

    /**
     * Model cache.
     * @var array
     */
    private $modelCache = [];

    /**
     * Collection of routes.
     * @var Router
     */
    private $router;

    /**
     * Root path to the theme.
     * @var string
     */
    public $rootPath;

    /**
     * Path to classes.
     * @var string
     */
    public $classPath;

    /**
     * Classes namespace.
     * @var string
     */
    public $namespace;

    /**
     * App configuration.
     * @var array
     */
    public $config;

    /**
     * Callback for theme setup.
     * @var callable
     */
    protected $setupCallback;

    /**
     * Callback for post deployment.  You need to set 'stem-new-deploy' option to true via WP-CLI to trigger this.
     * @var callable
     */
    protected $deployCallback;

    /**
     * Callback for pre_get_posts hook.
     * @var callable
     */
    protected $preGetPostsCallback;

    /**
     * Dispatcher for requests.
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * Factory functions for creating models for a given post type.
     * @var array
     */
    protected $modelFactories = [];

    /**
     * Factory functions for creating controllers.
     * @var array
     */
    protected $controllerFactories = [];

    public $cacheControl = null;

    /**
     * Determines if the context is running in debug mode.
     * @var bool
     */
    public $debug;

    /**
     * Site host.
     * @var string
     */
    public $siteHost = '';

    /**
     * Http host.
     * @var string
     */
    public $httpHost = '';

    /**
     * Current request.
     * @var null|Request
     */
    public $request = null;

    /**
     * The current environment.
     * @var string
     */
    public $environment = 'development';

    /**
     * The UI context.
     * @var UI
     */
    public $ui = null;

    /**
     * The Admin context.
     * @var Admin
     */
    public $admin = null;

    /**
     * Constructor.
     *
     * Throws an exception if config/app.json file is missing.
     *
     * @param $rootPath string The root path to the theme
     *
     * @throws \Exception
     */
    public function __construct($rootPath)
    {
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
            add_filter('rest_enabled', '__return_false', 10000);
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

        // Theme setup action hook
        add_action('after_setup_theme', function () {
            $this->setup();
        });

        // This does the actual dispatching to Stem controllers
        // and templates.
        add_filter('template_include', function ($template) {
            $this->dispatch();
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
        //$this->setupRequiredPlugins();

        $this->cacheControl = new CacheControl($this);

        $this->ui = new UI($this);

        $this->admin = new Admin($this);
    }

    /**
     * Creates the context for this theme.  Should be called in functions.php of the theme.
     *
     * @param $rootPath string The root path to the theme
     *
     * @return Context The new context
     */
    public static function initialize($rootPath)
    {
        $context = new self($rootPath);
        self::$currentContext = $context;

        return $context;
    }

    /**
     * Returns the context for the theme's domain.
     *
     * @param $domain string The name of the theme's domain, eg the name of the theme
     *
     * @return Context The theme's context
     */
    public static function current()
    {
        return self::$currentContext;
    }

    /**
     * Returns a setting using a path string, eg 'options/views/engine'.  Consider this
     * a poor man's xpath.
     *
     * @param $settingPath The "path" in the config settings to look up.
     * @param bool|mixed $default The default value to return if the settings doesn't exist.
     *
     * @return bool|mixed The result
     */
    public function setting($settingPath, $default = false)
    {
        return arrayPath($this->config, $settingPath, $default);
    }

    /**
     * Parse routes from a JSON configuration file.
     *
     * @deprecated
     */
    private function parseRoutesJSON()
    {
        $routesConfig = JSONParser::parse(file_get_contents($this->rootPath.'/config/routes.json'));
        foreach ($routesConfig as $routeName => $routeInfo) {
            $defaults = (isset($routeInfo['defaults']) && is_array($routeInfo['defaults'])) ? $routeInfo['defaults'] : [];
            $requirements = (isset($routeInfo['requirements']) && is_array($routeInfo['requirements'])) ? $routeInfo['requirements'] : [];
            $methods = (isset($routeInfo['methods']) && is_array($routeInfo['methods'])) ? $routeInfo['methods'] : [];
            $this->router->addRoute($routeName, $routeInfo['endPoint'], $routeInfo['controller'], $defaults, $requirements, $methods);
        }
    }

    /**
     * Parse routes from a PHP configuration file.
     */
    private function parseRoutesPHP()
    {
        $routesConfig = include $this->rootPath.'/config/routes.php';
        foreach ($routesConfig as $route => $routeInfo) {
            $defaults = (isset($routeInfo['defaults']) && is_array($routeInfo['defaults'])) ? $routeInfo['defaults'] : [];
            $requirements = (isset($routeInfo['requirements']) && is_array($routeInfo['requirements'])) ? $routeInfo['requirements'] : [];
            $methods = (isset($routeInfo['methods']) && is_array($routeInfo['methods'])) ? $routeInfo['methods'] : [];
            $this->router->addRoute($route, $route, $routeInfo['controller'], $defaults, $requirements, $methods);
        }
    }

    /**
     * Additional setup.
     */
    protected function setup()
    {
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
     * Installs multiple custom post types from individual json files.
     */
    private function installMultipleCustomPostTypes()
    {
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

//    private function setupRequiredPlugins()
//    {
//        add_action('tgmpa_register', function () {
//            $plugins = [
//                [
//                    'name'      => 'Kirki',
//                    'slug'      => 'kirki',
//                    'required'  => false,
//                ],
//                [
//                    'name'      => 'Advanced Custom Fields',
//                    'slug'      => 'advanced-custom-fields',
//                    'required'  => false,
//                ],
//            ];
//
//            $otherPlugins = $this->setting('plugins');
//            if ($otherPlugins) {
//                $plugins = array_merge($plugins, $otherPlugins);
//            }
//
//            $config = [
//                'id'           => 'stem',                 // Unique ID for hashing notices for multiple instances of TGMPA.
//                'default_path' => '',                      // Default absolute path to bundled plugins.
//                'menu'         => 'tgmpa-install-plugins', // Menu slug.
//                'parent_slug'  => 'plugins.php',            // Parent menu slug.
//                'capability'   => 'manage_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
//                'has_notices'  => true,                    // Show admin notices or not.
//                'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
//                'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
//                'is_automatic' => false,                   // Automatically activate plugins after installation or not.
//                'message'      => '',                      // Message to output right before the plugins table.
//
//                'strings'      => [
//                    'notice_can_install_recommended'  => _n_noop(
//                        'Stem recommends the following plugin: %1$s.',
//                        'Stem recommends the following plugins: %1$s.',
//                        'stem'
//                    ),
//                    'notice_can_install_required'  => _n_noop(
//                        'Stem requires the following plugin: %1$s.',
//                        'Stem requires the following plugins: %1$s.',
//                        'stem'
//                    ),
//                ],
//            ];
//
//            tgmpa($plugins, $config);
//        });
//    }

    /**
     * Installs types from a single JSON file.
     * @deprecated
     */
    private function installCustomPostTypesFromJSON()
    {
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
    private function installCustomPostTypesFromPHP()
    {
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
    public function installCustomPostTypes()
    {
        $this->installCustomPostTypesFromJSON();
        $this->installCustomPostTypesFromPHP();
        $this->installMultipleCustomPostTypes();
    }

    /**
     * Sets a callable for pre_get_posts filter.
     *
     * @param $callable callable
     */
    public function onPreGetPosts($callable)
    {
        $this->preGetPostsCallback = $callable;
    }

    /**
     * Sets up post filtering, enabling options for searching by tag and including custom post types in
     * query results automatically.
     */
    private function setupPostFilter()
    {
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
    protected function dispatch()
    {
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
    public function onSetup($callback)
    {
        $this->setupCallback = $callback;
    }

    /**
     * Sets a user supplied callback to call after a site has been deployed.  You need to set 'stem-new-deploy' option
     * to true via WP-CLI to trigger this.
     *
     * @param $callback callable
     */
    public function onDeploy($callback)
    {
        $this->deployCallback = $callback;
    }

    /**
     * Set the factory function for creating this model for this post type.
     *
     * @param $post_type string
     * @param $callable callable
     */
    public function setCustomPostTypeModelFactory($post_type, $callable)
    {
        $this->modelFactories[$post_type] = $callable;
    }

    /**
     * Sets the function for creating models for Posts.
     *
     * @param $callable callable
     */
    public function setPostModelFactory($callable)
    {
        $this->setCustomPostTypeModelFactory('post', $callable);
    }

    /**
     * Sets the function for creating models for Pages.
     *
     * @param $callable callable
     */
    public function setPageModelFactory($callable)
    {
        $this->setCustomPostTypeModelFactory('page', $callable);
    }

    /**
     * Sets the function for creating models for Attachments.
     *
     * @param $callable callable
     */
    public function setAttachmentModelFactory($callable)
    {
        $this->setCustomPostTypeModelFactory('attachment', $callable);
    }

    /**
     * Creates a model instance for the supplied WP_Post object.
     *
     * @param \WP_Post $post
     *
     * @return Attachment|Page|Post
     */
    public function modelForPost(\WP_Post $post)
    {
        if (! $post) {
            return;
        }

        $result = null;

        if (isset($this->modelCache["m-$post->ID"])) {
            return $this->modelCache["m-$post->ID"];
        }

        if (isset($this->modelFactories[$post->post_type])) {
            $result = call_user_func_array($this->modelFactories[$post->post_type], [$this, $post]);
        } elseif (isset($this->config['model-map'][$post->post_type])) {
            $className = $this->config['model-map'][$post->post_type];
            if (class_exists($className)) {
                $result = new $className($this, $post);
            }
        }

        if (! $result) {
            if ($post->post_type == 'attachment') {
                $result = new Attachment($this, $post);
            } elseif ($post->post_type == 'page') {
                $result = new Page($this, $post);
            } else {
                $result = new Post($this, $post);
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
     * @return Attachment|Page|Post
     */
    public function modelForPostID($postId)
    {
        if (! $postId) {
            return;
        }

        $result = null;

        if (isset($this->modelCache["m-$postId"])) {
            return $this->modelCache["m-$postId"];
        }

        $post = \WP_Post::get_instance($postId);
        if (! $post) {
            return false;
        }

        return $this->modelForPost($post);
    }

    /**
     * Performs a query for posts.
     *
     * @param $args
     * @return array
     */
    public function findPosts($args)
    {
        $query = new \WP_Query($args);
        $posts = [];
        foreach ($query->posts as $post) {
            $posts[] = $this->modelForPost($post);
        }

        return $posts;
    }

    /**
     * Set the factory for creating a controller for a given post type.
     *
     * @param $type
     * @param $callable
     */
    public function setControllerFactory($type, $callable)
    {
        $this->controllerFactories[$type] = $callable;
    }

    /**
     * Creates a controller for the given page type.
     *
     * @param $pageType string
     * @param $template string
     *
     * @return PageController|PostController|PostsController|null
     */
    public function createController($pageType, $template)
    {
        $controller = null;

        // Use factories first
        if (isset($this->controllerFactories[$pageType])) {
            $callable = $this->controllerFactories[$pageType];
            $controller = $callable($this, 'templates/'.$template);

            if ($controller) {
                return $controller;
            }
        }

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
    public function mapController($wpTemplateName)
    {
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
