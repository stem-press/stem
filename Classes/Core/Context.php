<?php

namespace ILab\Stem\Core;

/**
 * Class Context
 *
 * This class represents the current request Context and acts like the orchestrator for everything.
 *
 * @package ILab\Stem\Core
 */
class Context {
    private static $contexts=[];

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

    private $setupCallback;
    private $dispatcher;

    /**
     * Constructor
     *
     * Throws an exception if config.json file is missing.
     *
     * @param $rootPath The root path to the theme
     * @throws \Exception
     */
    public function __construct($rootPath) {
        if (!file_exists($rootPath.'/config.json'))
            throw new \Exception('Missing config.json for theme.');

        $this->config=json_decode(file_get_contents($rootPath.'/config.json'),true);

        // Setup our paths
        $this->classPath=$rootPath.'/classes/';
        $this->viewPath=$rootPath.'/views/';
        $this->jsPath=$rootPath.'/js/';
        $this->cssPath=$rootPath.'/css/';
        $this->imgPath=$rootPath.'/img/';
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
        add_action('after_setup_theme', [$this,'setup']);
    }

    public function setup()
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

        }

        // call the user supplied callback
        if ($this->setupCallback)
            call_user_func($this->setupCallback);
    }

    /**
     * Sets a user supplied callback to call when doing the theme setup
     * @param $callback
     */
    public function onSetup($callback) {
        $this->setupCallback=$callback;
    }

    /**
     * Creates the context for this theme.  Should be called in functions.php of the theme
     *
     * @param $domain Name of the theme's domain, eg the name of the theme
     * @param $rootPath The root path to the theme
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
     * @param $domain The name of the theme's domain, eg the name of the theme
     * @return Context The theme's context
     */
    public static function get($domain) {
        return self::$contexts[$domain];
    }

    /**
     * Dispatches a controller, or renders a template, for the current Wordpress request.
     * This should be called in your index.php file in your theme:
     *
     * ```
     * $context=Context::get('mytheme');
     * $context->dispatch();
     * ```
     */
    public function dispatch() {
        $this->dispatcher->dispatch();
    }

    /**
     * Renders a view
     *
     * @param $view The name of the view
     * @param $data The data to display in the view
     * @return string The rendered view
     */
    public function render($view,$data) {
        return View::render_view($this,$view,$data);
    }

}