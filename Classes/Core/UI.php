<?php

namespace ILab\Stem\Core;
use ILab\Stem\Models\Theme;

/**
 * Class Theme
 *
 * This class represents the manager for the front end
 *
 * @package ILab\Stem\Core
 */
class UI {

	/**
	 * Current context
	 * @var Context
	 */
	protected $context;

	/**
	 * Theme configuration
	 * @var array
	 */
	public $config = [];

	/**
	 * Path to views
	 * @var string
	 */
	public $viewPath;

	/**
	 * The text domain for internationalization
	 * @var string
	 */
	public $textdomain;

	/**
	 * Root url to the theme
	 * @var string
	 */
	public $themePath;

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
	 * Path to images
	 * @var string
	 */
	public $imgPath;

	/**
	 * Determine if relative links should be used anywhere applicable.
	 * @var bool
	 */
	public $useRelative = true;

	/**
	 * Determines if ILAB Media Tool's imgix tool is enabled
	 * @var bool
	 */
	protected $imgixEnabled = false;

	/**
	 * The current image size being manipulated by wordpress
	 * @var null|string
	 */
	protected $currentImageSize = null;

	/**
	 * Config for srcset attributes on images
	 * @var array
	 */
	protected $srcsetConfig = [];

	/**
	 * Array of text to remove from final output
	 * @var array
	 */
	protected $removeText = [];

	/**
	 * Array of regexes to remove text from final output
	 * @var array
	 */
	protected $removeRegexes = [];

	/**
	 * Array of text replacements for final output
	 * @var array
	 */
	protected $replaceText = [];

	/**
	 * Array of regexes to replace text in final output
	 * @var array
	 */
	protected $replaceRegexes = [];

	/**
	 * The forced domain
	 * @var string
	 */
	public $forcedDomain = null;

	/**
	 * View class
	 * @var string
	 */
	protected $viewClass = 'ILab\Stem\Core\StemView';

	/**
	 * Amp manager
	 * @var AMP
	 */
	public $amp = null;

	/**
	 * Constructor
	 *
	 * @param $context Context The current context
	 */
	public function __construct(Context $context) {
		$this->context = $context;

		$this->imgixEnabled = apply_filters('ilab_imgix_enabled', false);

		if (!$this->imgixEnabled) {
			add_action('ilab_imgix_setup',function(){
				$this->imgixEnabled = true;
			});
		}

		if (file_exists($context->rootPath . '/config/ui.json')) {
			$this->config = JSONParser::parse(file_get_contents($context->rootPath . '/config/ui.json'));
		}

		// Paths
		$this->viewPath  = $context->rootPath . '/views/';
		$this->themePath = get_template_directory_uri() . '/';
		$this->jsPath  = get_template_directory_uri() . '/js/';
		$this->cssPath = get_template_directory_uri() . '/css/';
		$this->imgPath = get_template_directory_uri() . '/img/';

		// Options
		$this->useRelative = $this->setting('options/relative-links', true);

		$this->forcedDomain = $this->setting('options/force-domain', null);
		if ($this->forcedDomain)
			$this->forcedDomain = trim($this->forcedDomain, '/') . '/';

		$viewEngine = $this->setting('options/views/engine');
		if ($viewEngine == 'twig') {
			$this->viewClass = '\ILab\Stem\External\Twig\TwigView';
		} else if ($viewEngine == 'blade') {
			$this->viewClass = '\ILab\Stem\External\Blade\BladeView';
		}


		// Load our clean up options
		$this->removeText = $this->setting('clean/remove/text',[]);
		$this->removeRegexes = $this->setting('clean/remove/regex',[]);
		$this->replaceText = $this->setting('clean/replace/text',[]);
		$this->replaceRegexes = $this->setting('clean/replace/regex',[]);

		// For AMP pages
		$this->amp = new AMP($this->context, $this);

		// Image related
		add_filter('image_downsize', [$this, 'imageDownsize'], 3, 3 );
		add_filter('wp_calculate_image_srcset',[$this,'calculateSrcSet'], 9999, 5);
		add_filter('wp_calculate_image_sizes',[$this,'calculateImageSizes'],3, 5);

		add_filter('ilab_s3_can_calculate_srcset',function(){
			return (!$this->imgixEnabled);
		});
	}

	public function setup() {
		// configures theme support
		if (isset($this->config['support'])) {
			foreach ($this->config['support'] as $feature => $params) {
				if (is_array($params))
					add_theme_support($feature, $params);
				else
					add_theme_support($feature);
			}
		}

		// Load image sizes
		$this->loadImageSizes();

		// Enqueue scripts and css
		add_action('wp_enqueue_scripts', function() {
			if (isset($this->config['enqueue'])) {
				$enqueueConfig = $this->config['enqueue'];
				if (isset($enqueueConfig['use-manifest']) && $enqueueConfig['use-manifest'])
					$this->enqueueManifest();

				if (isset($enqueueConfig['js'])) {
					foreach ($enqueueConfig['js'] as $js) {
						wp_enqueue_script($js, $this->jsPath . $js, ['jquery'], false, true);
					}
				}

				if (isset($enqueueConfig['css'])) {
					foreach ($enqueueConfig['css'] as $css) {
						wp_enqueue_style($css, $this->cssPath . $css);
					}
				}
			}
			else
				$this->enqueueManifest();
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
			add_filter('wp_headers', function($headers) {
				foreach ($this->config['clean']['headers'] as $header) {
					if (isset($headers[$header]))
						unset($headers[$header]);
				}

				return $headers;
			});
		}


		if (isset($this->config['clean']['wp_head'])) {
			if (in_array('adjacent_posts_rel_link_wp_head', $this->config['clean']['wp_head'])) {
				// Fix Yoast link rel=next
				if (!function_exists('genesis'))
					eval("function genesis(){}");
				add_filter('wpseo_genesis_force_adjacent_rel_home', function($value) {
					return false;
				});
			}
		}

		if ($this->useRelative) {
			add_filter('wp_nav_menu', function($input) {
				if ($input && !empty($input)) {
					$input = preg_replace("/href=\"((http|https):\\/\\/{$this->context->siteHost})(.*)\"/", "href=\"$3\"", $input);

					return preg_replace("/href=\"((http|https):\\/\\/{$this->context->httpHost})(.*)\"/", "href=\"$3\"", $input);
				}

				return $input;
			});
		}

		$this->setupTheme();
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
	public function setting($settingPath, $default = false) {
		return arrayPath($this->config, $settingPath, $default);
	}

	/**
	 * Enqueues the css and js defined in whatever manifest file
	 */
	private function enqueueManifest() {
		if (isset($this->config['enqueue']['manifest'])) {
			if (file_exists($this->rootPath . '/' . $this->config['enqueue']['manifest'])) {
				$manifest = JSONParser::parse(file_get_contents($this->rootPath . '/' . $this->config['enqueue']['manifest']), true);
				if (isset($manifest['dependencies'])) {
					foreach ($manifest['dependencies'] as $key => $info) {
						$ext = pathinfo($key, PATHINFO_EXTENSION);
						if ($ext == 'js')
							wp_enqueue_script($key, $this->jsPath . $key, ['jquery'], false, true);
						else if ($ext == 'css')
							wp_enqueue_style($key, $this->cssPath . $key);
					}
				}
			}
		}
	}

	/**
	 * Load custom image sizes
	 */
	private function loadImageSizes() {
		// configure image sizes
		if (file_exists($this->context->rootPath . '/config/sizes.json')) {
			$sizesConfig = JSONParser::parse(file_get_contents($this->context->rootPath . '/config/sizes.json'));

			if (isset($sizesConfig['srcset']))
				$this->srcsetConfig = $sizesConfig['srcset'];

			if (isset($sizesConfig['sizes'])) {
				$customSizes = [];

				foreach ($sizesConfig['sizes'] as $key => $info) {
					if ($key == 'post-thumbnail') {
						set_post_thumbnail_size($info['width'], $info['height'], $info['crop']);
					}
					else {
						add_image_size($key, $info['width'], $info['height'], $info['crop']);
					}

					if (isset($info['display']) && $info['display'])
						$customSizes[] = $key;
				}

				if (count($customSizes) > 0) {
					add_filter('image_size_names_choose', function($sizes) use ($customSizes) {
						foreach ($customSizes as $size) {
							$sizes[$size] = ucwords(str_replace('_', ' ', str_replace('-', ' ', $size)));
						}

						return $sizes;
					});
				}
			}

			if (isset($sizesConfig['disable-wp-sizes'])) {
				$disabled = $sizesConfig['disable-wp-sizes'];
				add_filter('intermediate_image_sizes_advanced', function($sizes) use ($disabled) {
					foreach($sizes as $disabled)
						unset($sizes[$disabled]);

					return $sizes;
				}, 10000);
			}
		}
	}


	/**
	 * Sets up the theme settings/widgets/menus
	 */
	private function setupTheme() {
		// configure menus
		if (isset($this->config['menu'])) {
			$menus = [];
			foreach ($this->config['menu'] as $key => $title) {
				$menus[$key] = __($title, $this->textdomain);
			}

			register_nav_menus($menus);
		}

		// configure sidebars
		add_action( 'widgets_init', function() {
			if (isset($this->config['sidebars'])) {
				foreach ($this->config['sidebars'] as $key => $settings) {
					$settings['id'] = $key;
					register_sidebar($settings);
				}
			}
		});

		// Configure customizer
		$useKirki = $this->setting('useKirki', true);
		if ($useKirki)
			$useKirki = class_exists('\Kirki');

		if ($useKirki) {
			add_action('customize_register', function(\WP_Customize_Manager $wp_customize) {
				$this->setupKirkiCustomizer($wp_customize);
			}, 11);
		} else {
			add_action('customize_register', function(\WP_Customize_Manager $wp_customize) {
				$this->setupWPCustomizer($wp_customize);
			}, 11);
		}
	}

	/**
	 * Set up the WP Customizer
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 */
	private function setupWPCustomizer(\WP_Customize_Manager $wp_customize) {
		if (isset($this->config['panels'])) {
			foreach($this->config['panels'] as $key => $data) {
				$wp_customize->add_panel($key, $data);
			}
		}

		if (isset($this->config['sections'])) {
			foreach($this->config['sections'] as $key => $data) {
				$wp_customize->add_section($key, $data);
			}
		}

		if (isset($this->config['settings'])) {
			foreach($this->config['settings'] as $setting => $data) {
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
					} else if ($control['type'] == 'image') {
						unset($control['type']);
						$wp_customize->add_control(new \WP_Customize_Image_Control($wp_customize, $setting, $control));
					}  else if ($control['type'] == 'cropped') {
						unset($control['type']);
						$wp_customize->add_control(new \WP_Customize_Cropped_Image_Control($wp_customize, $setting, $control));
					}  else if ($control['type'] == 'media') {
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
	 * Set up the Kirki Customizer
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 */
	public function setupKirkiCustomizer(\WP_Customize_Manager $wp_customize) {
		add_filter( 'kirki/control_types', function( $controls ) {
			$controls['media'] = '\WP_Customize_Media_Control';
			$controls['cropped_image'] = '\WP_Customize_Cropped_Image_Control';
			return $controls;
		} );

		\Kirki::add_config($this->textdomain, array(
			'capability'    => 'edit_theme_options',
			'option_type'   => 'option',
		) );

		if (isset($this->config['panels'])) {
			foreach($this->config['panels'] as $key => $data) {
				\Kirki::add_panel($key, $data);
			}
		}
		if (isset($this->config['sections'])) {
			foreach($this->config['sections'] as $key => $data) {
				\Kirki::add_section($key, $data);
			}
		}

		if (isset($this->config['settings'])) {
			foreach($this->config['settings'] as $setting => $data) {
				$control = null;
				if (isset($data['control'])) {
					$control = $data['control'];
					unset($data['control']);
				}

				if ($control)
					$data = array_merge($data, $control);

				$data['settings'] = $setting;

				\Kirki::add_field($this->textdomain, $data);
			}
		}
	}

	/**
	 * Registers a shortcode
	 *
	 * @param $shortcode string
	 * @param $callable callable
	 */
	public function registerShortcode($shortcode, $callable) {
		add_shortcode($shortcode, $callable);
	}


	/**
	 * Determines if a view exists in the file system
	 *
	 * @param $view
	 *
	 * @return bool
	 */
	public function viewExists($view) {
		$vc = $this->viewClass;

		return $vc::viewExists($this, $view);
	}

	/**
	 * Renders a view
	 *
	 * @param $view string The name of the view
	 * @param $data array The data to display in the view
	 *
	 * @return string The rendered view
	 */
	public function render($view, $data) {
		if ($data==null)
			$data=[];

		if (!isset($data['context']))
			$data['context']=$this->context;

		if (!isset($data['ui']))
			$data['ui']=$this;

		if (!isset($data['theme']))
			$data['theme']=new Theme($this->context);

		$vc     = $this->viewClass;
		$result = $vc::renderView($this->context, $this, $view, $data);
		$result = $this->cleanupOutput($result);

		return $result;
	}

	/**
	 * Cleans up the output
	 *
	 * @param $output
	 *
	 * @return mixed
	 */
	private function cleanupOutput($output) {
		if ($this->useRelative) {
			$output = preg_replace('/(?:http|https):\/\/' . $this->context->siteHost . '\/app\//', "/app/", $output);
			$output = preg_replace('/(?:http|https):\/\/' . $this->context->httpHost . '\/app\//', "/app/", $output);
			$output = preg_replace('/(?:http|https):\/\/' . $this->context->siteHost . '\/wp\//', "/wp/", $output);
			$output = preg_replace('/(?:http|https):\/\/' . $this->context->httpHost . '\/wp\//', "/wp/", $output);
		}

		if ($this->forcedDomain) {
			$parsed = parse_url($this->forcedDomain);
			$forcedScheme = $parsed['scheme'];
			$forcedHost = $parsed['host'];

			$output = preg_replace('/(?:http|https):\/\/' . $this->context->siteHost . '\//', $this->forcedDomain, $output);
			$output = preg_replace('/(?:http|https):\/\/' . $this->context->httpHost . '\//', $this->forcedDomain, $output);
			$output = preg_replace('#(?:http|https):\\\/\\\/' . $this->context->siteHost . '\\\/#', "$forcedScheme:\\/\\/$forcedHost\\/", $output);
			$output = preg_replace('#(?:http|https):\\\/\\\/' . $this->context->httpHost . '\\\/#', "$forcedScheme:\\/\\/$forcedHost\\/", $output);

			$output = preg_replace('/(?:http|https)%3A%2F%2F' . $this->context->siteHost.'/', "$forcedScheme%3A%2F%2F$forcedHost", $output);
			$output = preg_replace('/(?:http|https)%3A%2F%2F' . $this->context->httpHost.'/', "$forcedScheme%3A%2F%2F$forcedHost", $output);

			$output = preg_replace('/"'.$this->context->siteHost.'"/', "\"$forcedHost\"", $output);
			$output = preg_replace('/"'.$this->context->httpHost.'"/', "\"$forcedHost\"", $output);

			$output = preg_replace('/(?:http|https):\/\/' . $this->context->siteHost . '/', "$forcedScheme://$forcedHost", $output);
			$output = preg_replace('/(?:http|https):\/\/' . $this->context->httpHost . '/', "$forcedScheme://$forcedHost", $output);
		}

		foreach($this->removeText as $search)
			$output = str_replace($search, '', $output);

		foreach($this->removeRegexes as $regex)
			$output = preg_replace($regex, '', $output);

		foreach($this->replaceText as $search => $replacement)
			$output = str_replace($search, $replacement, $output);

		foreach($this->replaceRegexes as $regex => $replacement)
			$output = preg_replace($regex, $replacement, $output);

		return $output;
	}

	/**
	 * Outputs the Wordpress generated header html
	 *
	 * @return mixed|string
	 */
	public function header() {
		ob_start();

		wp_head();
		$header = ob_get_clean();

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
		$footer = ob_get_clean();

		return $footer;
	}

	/**
	 * Returns the image src to an image included in the theme
	 *
	 * @param $src
	 *
	 * @return string
	 */
	public function image($src) {
		$output = $this->imgPath . $src;

		return $output;
	}


	/**
	 * Returns the url for a file in the theme.
	 *
	 * @param $src
	 *
	 * @return string
	 */
	public function theme($src) {
		$output = $this->themePath . $src;

		return $output;
	}

	/**
	 * Returns the script src to an image included in the theme
	 *
	 * @param $src
	 *
	 * @return string
	 */
	public function script($src) {
		$output = $this->jsPath . $src;

		return $output;
	}

	/**
	 * Returns the css src to an image included in the theme
	 *
	 * @param $src
	 *
	 * @return string
	 */
	public function css($src) {
		$output = $this->cssPath . $src;

		return $output;
	}

	/**
	 * Renders a Wordpress generated menu
	 *
	 * @param $name string
	 * @param bool|false $stripUL
	 * @param bool|false $removeText
	 * @param string $insertGap
	 * @param bool|false $array
	 *
	 * @return false|mixed|object|string|void
	 */
	public function menu($name, $stripUL = false, $removeText = false, $insertGap = '', $array = false) {
		if ((!$stripUL) && ($insertGap == '')) {
			$menu = wp_nav_menu(['theme_location' => $name, 'echo' => false, 'container' => false]);
		}
		else if ((!$stripUL) && ($insertGap != '')) {
			$menu    = wp_nav_menu(['theme_location' => $name, 'echo' => false, 'container' => false]);
			$matches = [];
			preg_match_all("/(<li\\s+class=\"[^\"]+\">.*<\\/li>)+/", $menu, $matches);
			$links       = $matches[0];
			$gappedLinks = [];
			for ($i = 0; $i < count($links) - 1; $i ++) {
				$gappedLinks[] = $links[$i];
				$gappedLinks[] = "<li class=\"{$insertGap}\" />";
			}

			if (count($links)==0)
				return $menu;

			$gappedLinks[] = $links[count($links) - 1];

			$links = $gappedLinks;

			return "<ul>" . implode("\n", $links) . "</ul>";
		}
		else {
			$menu    = wp_nav_menu([
				                       'theme_location' => $name,
				                       'echo'           => false,
				                       'container'      => false,
				                       'items_wrap'     => '%3$s'
			                       ]);
			$matches = [];
			preg_match_all('#(<li\s+id=\"[aA-zZ0-9-]+\"\s+class=\"([^"]+)\"\s*>(.*)<\/li>)#', $menu, $matches);
			if (isset($matches[2]) && isset($matches[3]) && (count($matches[2]) == 0)) {
				$matches = [];
				preg_match_all('#(<li\s+class=\"([^"]+)\"\s*>(.*)<\/li>)#', $menu, $matches);
			}

			if (isset($matches[2]) && isset($matches[3])) {
				$links = [];
				for ($i = 0; $i < count($matches[2]); $i ++) {
					$link           = $matches[3][$i];
					$classes        = [];
					$matchedClasses = explode(' ', $matches[2][$i]);
					foreach ($matchedClasses as $class) {
						if (strpos($class, 'menu-') !== 0)
							$classes[] = $class;
					}

					$links[] = substr_replace($link, 'class="' . implode(' ', $classes) . '" ', 3, 0);
				}


				if ($insertGap != '') {
					$gappedLinks = [];
					for ($i = 0; $i < count($links) - 1; $i ++) {
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
			$menu = preg_replace("/(<a\\s*[^>]*>){1}(?:.*)(<\\/a>)/m", "$1$2", $menu);
		}

		return $menu;
	}

	public function imageDownsize($fail,$id,$size)  {
		if (!is_array($size))
			$this->currentImageSize = $size;
		else
			$this->currentImageSize = null;

		return $fail;
	}

	public function calculateSrcSet($sources, $size_array, $image_src, $image_meta, $attachment_id) {
		if (!$this->imgixEnabled || !$this->currentImageSize)
			return $sources;

		$newsources=[];

		$src = apply_filters('imgix_build_srcset_url',$attachment_id, $this->currentImageSize, null);
		if (is_array($src)) {
			$newsources[$src[1]]=[
				'url' => $src[0],
				'descriptor' => 'w',
				'value' => $src[1]
			];
		}

		if (!isset($this->srcsetConfig[$this->currentImageSize]) || !isset($this->srcsetConfig[$this->currentImageSize]['srcset']))
			return $newsources;

		foreach($this->srcsetConfig[$this->currentImageSize]['srcset'] as $width => $sizeInfo) {
			$src = apply_filters('imgix_build_srcset_url',$attachment_id, $this->currentImageSize, $sizeInfo);
			if (is_array($src)) {
				$newsources[$src[1]]=[
					'url' => $src[0],
					'descriptor' => 'w',
					'value' => $src[1]
				];
			}
		}

		return $newsources;
	}

	public function calculateImageSizes($sizes,  $size,  $image_src,  $image_meta,  $attachment_id) {
		if (!isset($this->srcsetConfig[$this->currentImageSize]) || !isset($this->srcsetConfig[$this->currentImageSize]['sizes']))
			return $sizes;

		if ($this->srcsetConfig[$this->currentImageSize]['sizes'] == 'auto')
			return $sizes;

		return $this->srcsetConfig[$this->currentImageSize]['sizes'];
	}
}