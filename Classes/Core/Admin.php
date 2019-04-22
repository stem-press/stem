<?php

namespace Stem\Core;

use Stem\Controllers\AdminPageController;

/**
 * Class Admin.
 *
 * This class process the admin configuration, adjusting the WordPress admin.
 */
class Admin
{
    /**
     * Current context.
     * @var Context
     */
    protected $context;

    /** @var AdminPageController[] */
    protected $adminPages = [];

    /**
     * Admin configuration.
     * @var array
     */
    public $config = [];

    /**
     * Constructor.
     *
     * @param $context Context The current context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;

        if (file_exists($context->rootPath.'/config/admin.php')) {
            $this->config = include $context->rootPath.'/config/admin.php';
        } elseif (file_exists($context->rootPath.'/config/admin.json')) {
            $this->config = JSONParser::parse(file_get_contents($context->rootPath.'/config/admin.json'));
        } else {
            return;
        }

        $this->setup();
    }

    /**
     * Performs basic setup.
     */
    protected function setup()
    {
        add_action('admin_init', function () {
            $this->configureWidgets();
            $this->configureAdminBar();
            $this->configureFooter();
        });

        $this->configureCustomization();
	    $this->setupAdminPages();
    }

    protected function configureAdminBar()
    {
        $adminBarOptions = $this->setting('customize/admin-bar', []);
        if (count($adminBarOptions) > 0) {
            add_action('admin_bar_menu', function ($wp_admin_bar) use ($adminBarOptions) {
                foreach ($adminBarOptions as $key => $option) {
                    if (! $option) {
                        $wp_admin_bar->remove_node($key);
                    } else {
                        $option['id'] = $key;
                        $wp_admin_bar->add_node($option);
                    }
                }
            }, 5000);
        }
    }

    protected function configureFooter()
    {
        $footerText = $this->setting('customize/footer/text', null);
        if ($footerText) {
            add_filter('admin_footer_text', function () use ($footerText) {
                echo $footerText;
            });
        }

        add_filter('update_footer', function () {
            $wpVersion = get_bloginfo('version');
            $stemData = get_plugin_data(ILAB_STEM_DIR.'/stem.php');
            $stemVersion = arrayPath($stemData, 'Version');
            echo "WordPress {$wpVersion} running Stem {$stemVersion}";
        });
    }

    protected function configureCustomization()
    {
        $logo = $this->setting('customize/login/logo', null);
        if ($logo) {
            $src = arrayPath($logo, 'src');
            $width = arrayPath($logo, 'width');
            $height = arrayPath($logo, 'height');

            if ($src && $width && $height) {
                $src = $this->context->ui->image($src);
                add_action('login_head', function () use ($src, $width, $height) {
                    echo "<style>.login h1 a { background-image: url({$src}) !important; background-size: {$width}px {$height}px; width:{$width}px; height:{$height}px; display:block; }</style>";
                });
            }
        }

        $js = $this->setting('customize/enqueue/js', []);
        $js = apply_filters('heavymetal/ui/enqueue/admin/js', $js);
        if (count($js) > 0) {
            add_action('admin_enqueue_scripts', function () use ($js) {
                foreach ($js as $key => $script) {
	                if (!filter_var($script, FILTER_VALIDATE_URL)) {
		                $script = $this->context->ui->script($script);
	                }

                    wp_register_script($key, $script);
                    wp_enqueue_script($key);
                }
            });
        }

        $css = $this->setting('customize/enqueue/css', []);
	    $css = apply_filters('heavymetal/ui/enqueue/admin/css', $css);
        if (count($css) > 0) {
            add_action('login_enqueue_scripts', function () use ($css) {
                foreach ($css as $key => $stylesheet) {
                	if (!filter_var($stylesheet, FILTER_VALIDATE_URL)) {
                		$stylesheet = $this->context->ui->css($stylesheet);
	                }

                    wp_register_style($key, $stylesheet);
                    wp_enqueue_style($key);
                }
            });

            add_action('admin_enqueue_scripts', function () use ($css) {
                foreach ($css as $key => $stylesheet) {
	                if (!filter_var($stylesheet, FILTER_VALIDATE_URL)) {
		                $stylesheet = $this->context->ui->css($stylesheet);
	                }

	                wp_register_style($key, $stylesheet);
                    wp_enqueue_style($key);
                }
            });
        }
    }

    /**
     * Configures dashboard widget.
     */
    protected function configureWidgets()
    {
        $removedWidgets = $this->setting('dashboard/remove', []);
        if (count($removedWidgets) > 0) {
            add_action('wp_dashboard_setup', function () use ($removedWidgets) {
                global $wp_meta_boxes;
                foreach ($removedWidgets as $removedWidget) {
                    unsetArrayPath($wp_meta_boxes, $removedWidget);
                }
            });
        }

        $addedWidgets = $this->setting('dashboard/add', []);
        if (count($addedWidgets) > 0) {
            add_action('wp_dashboard_setup', function () use ($addedWidgets) {
                foreach ($addedWidgets as $slug => $addedWidget) {
                    $title = arrayPath($addedWidget, 'title');
                    $class = arrayPath($addedWidget, 'class');
                    $config = arrayPath($addedWidget, 'config');

                    if ($title && $class && class_exists($class)) {
                        $widget = new $class($this->context, $this->context->request, $config);
                        wp_add_dashboard_widget($slug, $title, function () use ($widget) {
                            echo $widget->render();
                        });
                    }
                }
            });
        }
    }

	/**
	 * Sets up admin page controllers
	 */
	private function setupAdminPages() {
		if (!is_admin()) {
			return;
		}

		$pages = $this->setting('pages', []);
		$pages = apply_filters('heavymetal/admin/pages', $pages);
		if (count($pages) == 0) {
			return;
		}

		foreach($pages as $pageClass) {
			if (class_exists($pageClass) && is_subclass_of($pageClass, AdminPageController::class)) {
				/** @var AdminPageController $adminPage */
				$adminPage = new $pageClass($this->context);
				$this->adminPages[] = $adminPage;

				add_action('admin_menu', function() use ($adminPage) {
					if ($adminPage->parentMenuSlug() == null) {
						add_menu_page($adminPage->pageTitle(), $adminPage->menuTitle(), $adminPage->capability(), $adminPage->menuSlug(), function() use ($adminPage) {
							echo $adminPage->execute($this->context->request);
						}, $adminPage->icon(), $adminPage->position());
					} else {
						add_submenu_page($adminPage->parentMenuSlug(), $adminPage->pageTitle(), $adminPage->menuTitle(), $adminPage->capability(), $adminPage->menuSlug(), function() use ($adminPage) {
							echo $adminPage->execute($this->context->request);
						});
					}
				}, 10001);
			}
		}
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
}
