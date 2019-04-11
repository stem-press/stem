<?php

namespace Stem\Core;

/**
 * A Package is usually a set of models, controllers, etc that are provided in a plugin.
 *
 * @package Stem\Core
 */
class Package {
	protected $rootPath = null;
	protected $config = null;

	/**
	 * Package constructor.
	 *
	 * @param string $rootPath The root path of the package
	 */
	public function __construct($rootPath) {
		$this->rootPath = $rootPath;

		if (file_exists($rootPath.'/config/config.php')) {
			$this->config = include $rootPath.'/config/config.php';
			$this->addConfigFilters();
		}

		if (file_exists($rootPath.'/views')) {
			add_filter('heavymetal/views/paths', function($paths) {
				return [ $this->rootPath.'/views' ];
			});
		}

		if (file_exists($this->rootPath.'/config/routes.php')) {
			add_filter('heavymetal/app/routes', function($routes) {
				$appRoutes = include $this->rootPath . '/config/routes.php';
				return array_merge($appRoutes, $routes);
			});
		}
	}

	/**
	 * Adds a filter for a heavy metal configuration
	 * @param $for
	 */
	protected function addFilter($for) {
		if (empty($this->config)) {
			return;
		}

		$appData = arrayPath($this->config, $for, []);
		if (empty($appData)) {
			return;
		}

		add_filter("heavymetal/{$for}", function($data) use ($for, $appData) {
			return array_merge($appData, $data);
		});
	}

	/**
	 * Adds the filters for overloading configuration
	 */
	protected function addConfigFilters() {
		$this->addFilter('app/models');
		$this->addFilter('app/controllers');

		$this->addFilter('ui/columns');
		$this->addFilter('ui/fields');
		$this->addFilter('ui/widgets');
		$this->addFilter('ui/shortcodes');
		$this->addFilter('ui/blocks');
		$this->addFilter('ui/directives');
	}
}