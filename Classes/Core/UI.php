<?php

namespace ILab\Stem\Core;

use ILab\Stem\Models\Theme;

/**
 * Class Theme.
 *
 * This class represents the manager for the front end
 */
class UI
{
    /**
     * Current context.
     * @var Context
     */
    protected $context;

    /**
     * Theme configuration.
     * @var array
     */
    public $config = [];

    /**
     * Path to views.
     * @var string
     */
    public $viewPath;

    /**
     * The text domain for internationalization.
     * @var string
     */
    public $textdomain;

    /**
     * Root url to the theme.
     * @var string
     */
    public $themePath;

    /**
     * Path to javascript.
     * @var string
     */
    public $jsPath;

    /**
     * Path to CSS.
     * @var string
     */
    public $cssPath;

    /**
     * Path to images.
     * @var string
     */
    public $imgPath;

    /**
     * Determine if relative links should be used anywhere applicable.
     * @var bool
     */
    public $useRelative = true;

    /**
     * Determines if ILAB Media Tool's imgix tool is enabled.
     * @var bool
     */
    protected $imgixEnabled = false;

    /**
     * The current image size being manipulated by wordpress.
     * @var null|string
     */
    protected $currentImageSize = null;

    /**
     * Config for srcset attributes on images.
     * @var array
     */
    protected $srcsetConfig = [];

    /**
     * Array of text to remove from final output.
     * @var array
     */
    protected $removeText = [];

    /**
     * Array of regexes to remove text from final output.
     * @var array
     */
    protected $removeRegexes = [];

    /**
     * Array of text replacements for final output.
     * @var array
     */
    protected $replaceText = [];

    /**
     * Array of regexes to replace text in final output.
     * @var array
     */
    protected $replaceRegexes = [];

    /**
     * Array of ShortCode classes.
     * @var array
     */
    protected $shortCodes = [];

    /**
     * The forced domain.
     * @var string
     */
    public $forcedDomain = null;

    /**
     * View class.
     * @var string
     */
    protected $viewClass = '\ILab\Stem\External\Blade\BladeView';

    /**
     * Amp manager.
     * @var AMP
     */
    public $amp = null;

    /**
     * Customizer theme manager.
     * @var Theme
     */
    public $theme = null;

    public $enqueueConfig = null;

    /**
     * Constructor.
     *
     * @param $context Context The current context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;

        $this->imgixEnabled = apply_filters('ilab_imgix_enabled', false);

        if (! $this->imgixEnabled) {
            add_action('ilab_imgix_setup', function () {
                $this->imgixEnabled = true;
            });
        }

        if (file_exists($context->rootPath.'/config/ui.php')) {
            $this->config = include $context->rootPath.'/config/ui.php';
        } elseif (file_exists($context->rootPath.'/config/ui.json')) {
            $this->config = JSONParser::parse(file_get_contents($context->rootPath.'/config/ui.json'));
        }

        $this->parseEnqueue();

        // Paths
        $pub = '/'.trim($this->enqueueSetting('public-path', ''), '/');
        if ($pub == '/') {
            $pub = '';
        }
        $this->viewPath = $context->rootPath.'/views/';
        $this->themePath = get_template_directory_uri();
        $this->jsPath = get_template_directory_uri().$pub.'/js/';
        $this->cssPath = get_template_directory_uri().$pub.'/css/';
        $this->imgPath = get_template_directory_uri().$pub.'/img/';

        // Options
        $this->useRelative = $this->setting('options/relative-links', true);

        $this->forcedDomain = $this->setting('options/force-domain', null);
        if ($this->forcedDomain) {
            $this->forcedDomain = trim($this->forcedDomain, '/').'/';
        }

        $viewEngine = $this->setting('options/views/engine');
        if ($viewEngine == 'twig') {
            $this->viewClass = '\ILab\Stem\External\Twig\TwigView';
        }

        $resetCache = $this->setting('options/views/reset-cache',false);
        if ($resetCache) {
        	$cacheDir = $this->setting('options/views/cache');
        	if (!empty($cacheDir)) {
        		nukeDir($cacheDir);
	        }
        }

        // Load our clean up options
        $this->removeText = $this->setting('clean/remove/text', []);
        $this->removeRegexes = $this->setting('clean/remove/regex', []);
        $this->replaceText = $this->setting('clean/replace/text', []);
        $this->replaceRegexes = $this->setting('clean/replace/regex', []);

        // For AMP pages
        $this->amp = new AMP($this->context, $this);

        // Image related
        add_filter('image_downsize', [$this, 'imageDownsize'], 3, 3);
        add_filter('wp_calculate_image_srcset', [$this, 'calculateSrcSet'], 9999, 5);
        add_filter('wp_calculate_image_sizes', [$this, 'calculateImageSizes'], 3, 5);

        add_filter('ilab_s3_can_calculate_srcset', function () {
            return ! $this->imgixEnabled;
        });

        $this->theme = new Theme($this->context);
    }

    protected function parseEnqueue() {
    	$routed = arrayPath($this->config,'enqueue_routes');
    	if (!empty($routed)) {
		    foreach($routed as $key => $enqConfig) {
		    	if (preg_match("#$key#",$_SERVER['REQUEST_URI'])) {
		    		$this->enqueueConfig = $enqConfig;
		    		return;
			    }
		    }
	    }

	    $this->enqueueConfig = arrayPath($this->config,'enqueue');
    }

    public function setup()
    {
        // configures theme support
        if (isset($this->config['support'])) {
            foreach ($this->config['support'] as $feature => $params) {
                if (is_array($params)) {
                    add_theme_support($feature, $params);
                } else {
                    add_theme_support($feature);
                }
            }
        }

        // Load image sizes
        $this->loadImageSizes();

        // Enqueue scripts and css
        add_action('wp_enqueue_scripts', function () {
            if (!empty($this->enqueueConfig)) {
                if (isset($this->enqueueConfig['use-manifest']) && $this->enqueueConfig['use-manifest']) {
                    $this->enqueueManifest();
                }

                if (isset($this->enqueueConfig['js'])) {
                    foreach ($this->enqueueConfig['js'] as $js) {
                        wp_enqueue_script($js, $this->jsPath.$js, ['jquery'], false, true);
                    }
                }

                if (isset($this->enqueueConfig['css'])) {
                    foreach ($this->enqueueConfig['css'] as $css) {
                        wp_enqueue_style($css, $this->cssPath.$css);
                    }
                }
            } else {
                $this->enqueueManifest();
            }
        });

        // Clean out any junk that wordpress adds to the html head section.
        if (isset($this->config['clean']['wp_head'])) {
            foreach ($this->config['clean']['wp_head'] as $what) {
                remove_action('wp_head', $what);
            }
        }

        // Clean up any junk http headers that wordpress adds.
        if (isset($this->config['clean']['headers'])) {
            // Unset some junky ass wordpress headers
            add_filter('wp_headers', function ($headers) {
                foreach ($this->config['clean']['headers'] as $header) {
                    if (isset($headers[$header])) {
                        unset($headers[$header]);
                    }
                }

                return $headers;
            });
        }

        if (isset($this->config['clean']['wp_head'])) {
            if (in_array('adjacent_posts_rel_link_wp_head', $this->config['clean']['wp_head'])) {
                // Fix Yoast link rel=next
                if (! function_exists('genesis')) {
                    eval('function genesis(){}');
                }
                add_filter('wpseo_genesis_force_adjacent_rel_home', function ($value) {
                    return false;
                });
            }
        }

        if ($this->useRelative) {
            add_filter('wp_nav_menu', function ($input) {
                if ($input && ! empty($input)) {
                    $input = preg_replace("/href=\"((http|https):\\/\\/{$this->context->siteHost})(.*)\"/", 'href="$3"', $input);

                    return preg_replace("/href=\"((http|https):\\/\\/{$this->context->httpHost})(.*)\"/", 'href="$3"', $input);
                }

                return $input;
            });
        }

        if ($this->enqueueSetting('defer-all') && ! is_admin()) {
            add_filter('script_loader_tag', function ($tag, $handle) {
                if (is_admin()) {
                    return $tag;
                }

                return str_replace(' src', ' defer src', $tag);
            }, 10, 2);
        }

        $this->setupTheme();
        $this->setupShortCodes();
        $this->setupEditor();
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
	 * Returns an enqueue setting using a path string, eg 'options/views/engine'.  Consider this
	 * a poor man's xpath.
	 *
	 * @param $settingPath The "path" in the config settings to look up.
	 * @param bool|mixed $default The default value to return if the settings doesn't exist.
	 *
	 * @return bool|mixed The result
	 */
	public function enqueueSetting($settingPath, $default = false)
	{
		return arrayPath($this->enqueueConfig, $settingPath, $default);
	}


	/**
     * Enqueues the css and js defined in whatever manifest file.
     */
    private function enqueueManifest()
    {
        if (isset($this->enqueueConfig['manifest'])) {
            if (file_exists($this->rootPath.'/'.$this->enqueueConfig['manifest'])) {
                $manifest = JSONParser::parse(file_get_contents($this->rootPath.'/'.$this->enqueueConfig['manifest']), true);
                if (isset($manifest['dependencies'])) {
                    foreach ($manifest['dependencies'] as $key => $info) {
                        $ext = pathinfo($key, PATHINFO_EXTENSION);
                        if ($ext == 'js') {
                            wp_enqueue_script($key, $this->jsPath.$key, ['jquery'], false, true);
                        } elseif ($ext == 'css') {
                            wp_enqueue_style($key, $this->cssPath.$key);
                        }
                    }
                }
            }
        }
    }

    /**
     * Load custom image sizes.
     */
    private function loadImageSizes()
    {

        // configure image sizes
        if (file_exists($this->context->rootPath.'/config/sizes.php')) {
            $sizesConfig = include $this->context->rootPath.'/config/sizes.php';
        } elseif (file_exists($this->context->rootPath.'/config/sizes.json')) {
            $sizesConfig = JSONParser::parse(file_get_contents($this->context->rootPath.'/config/sizes.json'));
        } else {
            return;
        }

        if (isset($sizesConfig['srcset'])) {
            $this->srcsetConfig = $sizesConfig['srcset'];
        }

        if (isset($sizesConfig['sizes'])) {
            $customSizes = [];

            foreach ($sizesConfig['sizes'] as $key => $info) {
                if ($key == 'original') {
                    Log::warning("Your sizes config has specified a size for 'original'.  This is a keyword and cannot be used.  Skipping for now.");
                    continue;
                }

                if ($key == 'post-thumbnail') {
                    set_post_thumbnail_size($info['width'], $info['height'], $info['crop']);
                } else {
                    add_image_size($key, $info['width'], $info['height'], $info['crop']);
                }

                $display = arrayPath($info, 'display', false);
                $name = arrayPath($info, 'name', null);

                if ($display && empty($name)) {
                    $customSizes[$key] = ucwords(str_replace('_', ' ', str_replace('-', ' ', $key)));
                } elseif (! empty($name)) {
                    $customSizes[$key] = $name;
                }
            }

            if (count($customSizes) > 0) {
                add_filter('image_size_names_choose', function ($sizes) use ($customSizes) {
                    foreach ($customSizes as $key => $size) {
                        $sizes[$key] = $size;
                    }

                    return $sizes;
                });
            }
        }

        if (isset($sizesConfig['disable-wp-sizes'])) {
            $disabled = $sizesConfig['disable-wp-sizes'];
            add_filter('intermediate_image_sizes_advanced', function ($sizes) use ($disabled) {
                foreach ($disabled as $size) {
                    unset($sizes[$size]);
                }

                return $sizes;
            }, 10000);
        }
    }

    private function setupEditor()
    {
        $styles = $this->setting('editor/styles', []);
        if (is_array($styles) && (count($styles) > 0)) {
            add_filter('admin_enqueue_scripts', function () use ($styles) {
                foreach ($styles as $style) {
                    add_editor_style($this->css($style));
                }
            });
        }

        $plugins = $this->setting('editor/plugins', []);
        foreach ($plugins as $pluginData) {
            $plugin = null;

            if (is_array($pluginData)) {
                $class = arrayPath($pluginData, 'class', null);
                if ($class && class_exists($class)) {
                    $config = arrayPath($pluginData, 'config', []);
                    $plugin = new $class($this->context, $config);
                }
            } elseif (is_string($pluginData)) {
                if (class_exists($pluginData)) {
                    $plugin = new $pluginData($this->context, []);
                }
            }

            if ($plugin) {
                $styles = $plugin->styles();
                if ($styles) {
                    if (is_string($styles)) {
                        $styles = [$styles];
                    }

                    add_filter('admin_enqueue_scripts', function () use ($styles) {
                        foreach ($styles as $style) {
                            add_editor_style($style);
                        }
                    });
                }

                $scripts = $plugin->scripts();
                if ($scripts) {
                    if (is_string($scripts)) {
                        $scripts = [$plugin->identifier() => $scripts];
                    }

                    add_filter('mce_external_plugins', function ($externalPlugins) use ($scripts) {
                        $externalPlugins = array_merge($externalPlugins, $scripts);

                        return $externalPlugins;
                    }, 1000, 1);
                }

                $buttons = $plugin->buttons();
                if ($buttons) {
                    if (is_string($buttons)) {
                        $buttons = [$buttons];
                    }

                    add_filter('mce_buttons', function ($mceButtons) use ($buttons) {
                        $mceButtons = array_merge($mceButtons, $buttons);

                        return $mceButtons;
                    }, 1000, 1);
                }

                add_action('before_wp_tiny_mce', function ($mceSettings) use ($plugin) {
                    $plugin->onBeforeInit($mceSettings);
                });

                add_action('wp_tiny_mce_init', function ($mceSettings) use ($plugin) {
                    $plugin->onInit($mceSettings);
                });

                add_action('after_wp_tiny_mce', function ($mceSettings) use ($plugin) {
                    $plugin->onAfterInit($mceSettings);
                });
            }
        }
    }

    private function setupShortCodes()
    {
        $shortCodes = $this->setting('shortcodes', []);
        foreach ($shortCodes as $key => $data) {
            $shortCode = null;
            $uiConfig = null;

            if (is_array($data)) {
                $class = arrayPath($data, 'class', null);
                if ($class && class_exists($class)) {
                    $config = arrayPath($data, 'config', []);
                    $uiConfig = arrayPath($data, 'ui', null);
                    $shortCode = new $class($this->context, $config);
                }
            } elseif (is_string($data)) {
                if (class_exists($data)) {
                    $shortCode = new $data($this->context, []);
                }
            }

            if ($shortCode) {
                add_shortcode($key, function ($attrs, $content = null) use ($shortCode) {
                    return $shortCode->render($attrs, $content);
                });

                if (function_exists('shortcode_ui_register_for_shortcode')) {
                    if (! $uiConfig) {
                        $shortCode->registerUI($key);
                    } else {
                        // TODO: Shortcode ui registration
                    }
                }
            }
        }
    }

    /**
     * Sets up the theme settings/widgets/menus.
     */
    private function setupTheme()
    {
        // configure menus
        $menus = $this->setting('menu', []);
        if (count($menus) > 0) {
            foreach ($this->config['menu'] as $key => $title) {
                $menus[$key] = __($title, $this->textdomain);
            }

            register_nav_menus($menus);
        }

        $sidebars = $this->setting('sidebars', []);
        if (count($sidebars) > 0) {
            add_action('widgets_init', function () use ($sidebars) {
                foreach ($sidebars as $key => $settings) {
                    $settings['id'] = $key;
                    register_sidebar($settings);
                }
            });
        }

        // Configure customizer
        $useKirki = $this->setting('customizer/use_kirki', true);
        if ($useKirki) {
            $useKirki = class_exists('\Kirki');
        }

        if ($useKirki) {
            $this->setupKirkiCustomizer();
        } else {
            add_action('customize_register', function (\WP_Customize_Manager $wp_customize) {
                $this->setupWPCustomizer($wp_customize);
            }, 11);
        }
    }

    /**
     * Set up the WP Customizer.
     *
     * @param \WP_Customize_Manager $wp_customize
     */
    private function setupWPCustomizer(\WP_Customize_Manager $wp_customize)
    {
        $panels = $this->setting('customizer/panels', []);
        if (count($panels) > 0) {
            foreach ($panels as $key => $data) {
                $wp_customize->add_panel($key, $data);
            }
        }

        $sections = $this->setting('customizer/sections', []);
        if (count($sections) > 0) {
            foreach ($sections as $key => $data) {
                $wp_customize->add_section($key, $data);
            }
        }

        $settings = $this->setting('customizer/settings', []);
        if (count($settings) > 0) {
            foreach ($settings as $setting => $data) {
                $control = null;
                if (isset($data['control'])) {
                    $control = $data['control'];
                    unset($data['control']);
                }

                $wp_customize->add_setting($setting, $data);

                if ($control) {
                    if ($control['type'] == 'color') {
                        unset($control['type']);
                        $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, $setting, $control));
                    } elseif ($control['type'] == 'image') {
                        unset($control['type']);
                        $wp_customize->add_control(new \WP_Customize_Image_Control($wp_customize, $setting, $control));
                    } elseif (($control['type'] == 'cropped_image') || ($control['type'] == 'cropped')) {
                        unset($control['type']);
                        $wp_customize->add_control(new \WP_Customize_Cropped_Image_Control($wp_customize, $setting, $control));
                    } elseif ($control['type'] == 'media') {
                        unset($control['type']);
                        $wp_customize->add_control(new \WP_Customize_Media_Control($wp_customize, $setting, $control));
                    } else {
                        $wp_customize->add_control($setting, $control);
                    }
                }
            }
        }
    }

    /**
     * Set up the Kirki Customizer.
     */
    public function setupKirkiCustomizer()
    {
        \Kirki::add_config($this->textdomain, [
            'capability'    => 'edit_theme_options',
            'option_type'   => 'option',
        ]);

        add_filter('kirki/config', function ($config) {
            $config['styles_priority'] = 100000;

            return $config;
        });

        add_filter('kirki/control_types', function ($controls) {
            $controls['media'] = '\WP_Customize_Media_Control';
            $controls['cropped_image'] = '\WP_Customize_Cropped_Image_Control';

            return $controls;
        });

        $panels = $this->setting('customizer/panels', []);
        if (count($panels) > 0) {
            foreach ($panels as $key => $data) {
                \Kirki::add_panel($key, $data);
            }
        }

        $sections = $this->setting('customizer/sections', []);
        if (count($sections) > 0) {
            foreach ($sections as $key => $data) {
                \Kirki::add_section($key, $data);
            }
        }

        $settings = $this->setting('customizer/settings', []);
        if (count($settings) > 0) {
            foreach ($settings as $setting => $data) {
                $control = null;
                if (isset($data['control'])) {
                    $control = $data['control'];
                    unset($data['control']);
                }

                if ($control) {
                    $data = array_merge($data, $control);
                }

                $data['settings'] = $setting;

                \Kirki::add_field($this->textdomain, $data);
            }
        }
    }

    /**
     * Registers a shortcode.
     *
     * @param $shortcode string
     * @param $callable callable
     */
    public function registerShortcode($shortcode, $callable)
    {
        add_shortcode($shortcode, $callable);
    }

    /**
     * Determines if a view exists in the file system.
     *
     * @param $view
     *
     * @return bool
     */
    public function viewExists($view)
    {
        $vc = $this->viewClass;

        return $vc::viewExists($this, $view);
    }

    /**
     * Renders a view.
     *
     * @param $view string The name of the view
     * @param $data array The data to display in the view
     *
     * @return string The rendered view
     */
    public function render($view, $data)
    {
        if ($data == null) {
            $data = [];
        }

        if (! isset($data['context'])) {
            $data['context'] = $this->context;
        }

        if (! isset($data['ui'])) {
            $data['ui'] = $this;
        }

        if (! isset($data['theme'])) {
            $data['theme'] = $this->theme;
        }

        $vc = $this->viewClass;
        $result = $vc::renderView($this->context, $this, $view, $data);
        $result = $this->cleanupOutput($result);

        return $result;
    }

    /**
     * Cleans up the output.
     *
     * @param $output
     *
     * @return mixed
     */
    private function cleanupOutput($output)
    {
        if ($this->useRelative) {
            $output = preg_replace('/(?:http|https):\/\/'.$this->context->siteHost.'\/app\//', '/app/', $output);
            $output = preg_replace('/(?:http|https):\/\/'.$this->context->httpHost.'\/app\//', '/app/', $output);
            $output = preg_replace('/(?:http|https):\/\/'.$this->context->siteHost.'\/wp\//', '/wp/', $output);
            $output = preg_replace('/(?:http|https):\/\/'.$this->context->httpHost.'\/wp\//', '/wp/', $output);
        }

        if ($this->forcedDomain) {
            $parsed = parse_url($this->forcedDomain);
            $forcedScheme = $parsed['scheme'];
            $forcedHost = $parsed['host'];

            $output = preg_replace('/(?:http|https):\/\/'.$this->context->siteHost.'\//', $this->forcedDomain, $output);
            $output = preg_replace('/(?:http|https):\/\/'.$this->context->httpHost.'\//', $this->forcedDomain, $output);
            $output = preg_replace('#(?:http|https):\\\/\\\/'.$this->context->siteHost.'\\\/#', "$forcedScheme:\\/\\/$forcedHost\\/", $output);
            $output = preg_replace('#(?:http|https):\\\/\\\/'.$this->context->httpHost.'\\\/#', "$forcedScheme:\\/\\/$forcedHost\\/", $output);

            $output = preg_replace('/(?:http|https)%3A%2F%2F'.$this->context->siteHost.'/', "$forcedScheme%3A%2F%2F$forcedHost", $output);
            $output = preg_replace('/(?:http|https)%3A%2F%2F'.$this->context->httpHost.'/', "$forcedScheme%3A%2F%2F$forcedHost", $output);

            $output = preg_replace('/"'.$this->context->siteHost.'"/', "\"$forcedHost\"", $output);
            $output = preg_replace('/"'.$this->context->httpHost.'"/', "\"$forcedHost\"", $output);

            $output = preg_replace('/(?:http|https):\/\/'.$this->context->siteHost.'/', "$forcedScheme://$forcedHost", $output);
            $output = preg_replace('/(?:http|https):\/\/'.$this->context->httpHost.'/', "$forcedScheme://$forcedHost", $output);
        }

        foreach ($this->removeText as $search) {
            $output = str_replace($search, '', $output);
        }

        foreach ($this->removeRegexes as $regex) {
            $output = preg_replace($regex, '', $output);
        }

        foreach ($this->replaceText as $search => $replacement) {
            $output = str_replace($search, $replacement, $output);
        }

        foreach ($this->replaceRegexes as $regex => $replacement) {
            $output = preg_replace($regex, $replacement, $output);
        }

	    return apply_filters('stem/output', $output);
    }

	/**
	 * Outputs the Wordpress generated header html
	 *
	 * @return mixed|string
	 */
	public function header() {
		ob_start();

        wp_head();

	    return apply_filters('stem/header', ob_get_clean());
    }

	/**
	 * Outputs the Wordpress generated footer html
	 *
	 * @return string
	 */
	public function footer() {
		ob_start();

        wp_footer();

	    return apply_filters('stem/footer', ob_get_clean());
    }

    /**
     * Returns the image src to an image included in the theme.
     *
     * @param $src
     *
     * @return string
     */
    public function image($src)
    {
        $output = $this->imgPath.$src;

        return $output;
    }

    /**
     * Returns the url for a file in the theme.
     *
     * @param $src
     *
     * @return string
     */
    public function file($src)
    {
        $output = $this->themePath.$src;

        return $output;
    }

    /**
     * Returns the script src to an image included in the theme.
     *
     * @param $src
     *
     * @return string
     */
    public function script($src)
    {
        $output = $this->jsPath.$src;

        return $output;
    }

    /**
     * Returns the css src to an image included in the theme.
     *
     * @param $src
     *
     * @return string
     */
    public function css($src)
    {
        $output = $this->cssPath.$src;

        return $output;
    }

    /**
     * Renders a Wordpress generated menu.
     *
     * @param $name string
     * @param bool|false $stripUL
     * @param bool|false $removeText
     * @param string $insertGap
     * @param bool|false $array
     *
     * @return false|mixed|object|string|void
     */
    public function menu($name, $stripUL = false, $removeText = false, $insertGap = '', $array = false)
    {
        if ((! $stripUL) && ($insertGap == '')) {
            $menu = wp_nav_menu(['theme_location' => $name, 'echo' => false, 'container' => false]);
        } elseif ((! $stripUL) && ($insertGap != '')) {
            $menu = wp_nav_menu(['theme_location' => $name, 'echo' => false, 'container' => false]);
            $matches = [];
            preg_match_all('/(<li\\s+class="[^"]+">.*<\\/li>)+/', $menu, $matches);
            $links = $matches[0];
            $gappedLinks = [];
            for ($i = 0; $i < count($links) - 1; $i++) {
                $gappedLinks[] = $links[$i];
                $gappedLinks[] = "<li class=\"{$insertGap}\" />";
            }

            if (count($links) == 0) {
                return $menu;
            }

            $gappedLinks[] = $links[count($links) - 1];

            $links = $gappedLinks;

            return '<ul>'.implode("\n", $links).'</ul>';
        } else {
            $menu = wp_nav_menu([
                                       'theme_location' => $name,
                                       'echo'           => false,
                                       'container'      => false,
                                       'items_wrap'     => '%3$s',
                                   ]);

            $matches = [];
            preg_match_all('#(<li\s+id=\"[aA-zZ0-9-]+\"\s+class=\"([^"]+)\"\s*>(.*)<\/li>)#', $menu, $matches);
            if (isset($matches[2]) && isset($matches[3]) && (count($matches[2]) == 0)) {
                $matches = [];
                preg_match_all('#(<li\s+class=\"([^"]+)\"\s*>(.*)<\/li>)#', $menu, $matches);
            }

            if (isset($matches[2]) && isset($matches[3])) {
                $links = [];
                for ($i = 0; $i < count($matches[2]); $i++) {
                    $link = $matches[3][$i];
                    $classes = [];
                    $matchedClasses = explode(' ', $matches[2][$i]);
                    foreach ($matchedClasses as $class) {
                        if (strpos($class, 'menu-') !== 0) {
                            $classes[] = $class;
                        }
                    }

                    $links[] = substr_replace($link, 'class="'.implode(' ', $classes).'" ', 3, 0);
                }

                if ($insertGap != '') {
                    $gappedLinks = [];
                    for ($i = 0; $i < count($links) - 1; $i++) {
                        $gappedLinks[] = $links[$i];
                        $gappedLinks[] = "<ul class='{$insertGap}' />";
                    }

                    $gappedLinks[] = $links[count($links) - 1];

                    $links = $gappedLinks;
                }

                if ($array) {
                    return $links;
                }

                $menu = implode("\n", $links);
            }
        }

        if ($removeText) {
            $menu = preg_replace('/(<a\\s*[^>]*>){1}(?:.*)(<\\/a>)/m', '$1$2', $menu);
        }

        return $menu;
    }

    public function imageDownsize($fail, $id, $size)
    {
        if (! is_array($size)) {
            $this->currentImageSize = $size;
        } else {
            $this->currentImageSize = null;
        }

        return $fail;
    }

    public function calculateSrcSet($sources, $size_array, $image_src, $image_meta, $attachment_id)
    {
        if (! $this->imgixEnabled) {
            return $sources;
        }

        if (! $this->currentImageSize) {
            $is_crop = (strpos($image_src, 'fit=crop') > 0);
            foreach ($sources as $key => $source) {
                $src = apply_filters('imgix_build_srcset_url', $attachment_id, [$source['value'], ($is_crop) ? $source['value'] : 15000, ($is_crop) ? 'crop' : 'fit'], null);
                $source['url'] = $src[0];
                $sources[$key] = $source;
            }

            return $sources;
        }

        $newsources = [];

        $src = apply_filters('imgix_build_srcset_url', $attachment_id, $this->currentImageSize, null);
        if (is_array($src)) {
            $newsources[$src[1]] = [
                'url' => $src[0],
                'descriptor' => 'w',
                'value' => $src[1],
            ];
        }

        if (! isset($this->srcsetConfig[$this->currentImageSize]) || ! isset($this->srcsetConfig[$this->currentImageSize]['srcset'])) {
            return $newsources;
        }

        foreach ($this->srcsetConfig[$this->currentImageSize]['srcset'] as $width => $sizeInfo) {
            $src = apply_filters('imgix_build_srcset_url', $attachment_id, $this->currentImageSize, $sizeInfo);
            if (is_array($src)) {
                $newsources[$src[1]] = [
                    'url' => $src[0],
                    'descriptor' => 'w',
                    'value' => $src[1],
                ];
            }
        }

        return $newsources;
    }

    public function calculateImageSizes($sizes, $size, $image_src, $image_meta, $attachment_id)
    {
        if (! isset($this->srcsetConfig[$this->currentImageSize]) || ! isset($this->srcsetConfig[$this->currentImageSize]['sizes'])) {
            return $sizes;
        }

        if ($this->srcsetConfig[$this->currentImageSize]['sizes'] == 'auto') {
            return $sizes;
        }

        return $this->srcsetConfig[$this->currentImageSize]['sizes'];
    }
}
