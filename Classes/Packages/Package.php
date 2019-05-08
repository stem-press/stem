<?php

namespace Stem\Packages;

/**
 * A Package is usually a set of models, controllers, etc that are provided in a plugin.
 *
 * @package Stem\Core
 */
class Package {
	protected $title = null;
	protected $description = null;
	protected $rootPath = null;
	protected $config = null;

	protected $packagePriority = 1;

	/**
	 * Package constructor.
	 *
	 * @param string $rootPath The root path of the package
	 * @param string|null $title The title of the package
	 * @param string|null $description The description of the package
	 */
	public function __construct($rootPath, $title = null, $description = null) {
		$this->title = $title;
		$this->rootPath = $rootPath;
		$this->description = $description;

		if (file_exists($rootPath.'/config/config.php')) {
			$this->config = include $rootPath.'/config/config.php';
			$this->packagePriority = arrayPath($this->config, 'priority', 1);
			$this->addConfigFilters();
		}

		if (file_exists($rootPath.'/views')) {
			add_filter('heavymetal/views/paths', function($paths) {
				return array_merge($paths, [ $this->rootPath.'/views' ]);
			}, $this->packagePriority);
		}

		if (file_exists($rootPath.'/config/fields/')) {
			add_filter('heavymetal/acf/json/save_path', function($path, $group) {
				$groupKey = $group['key'];

				if (file_exists($this->rootPath.'/config/fields/'.$groupKey.'.json')) {
					return $this->rootPath.'/config/fields/';
				}

				return false;
			}, $this->packagePriority, 2);

			add_filter('heavymetal/acf/json/load_paths', function($paths) {
				$paths[] = $this->rootPath.'/config/fields/';
				return $paths;
			}, $this->packagePriority);
		}

		if (file_exists($this->rootPath.'/config/routes.php')) {
			add_filter('heavymetal/app/routes', function($routes) {
				$appRoutes = include $this->rootPath . '/config/routes.php';
				return array_merge($appRoutes, $routes);
			}, $this->packagePriority);
		}

		PackageManager::registerPackage($this);
	}

	/**
	 * Returns the db migration path for the package if any.
	 *
	 * @return string|null
	 */
	public function migrationsPath() {
		if (file_exists($this->rootPath.'/migrations')) {
			return $this->rootPath.'/migrations';
		}

		return null;
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
		}, $this->packagePriority);
	}

	/**
	 * Adds the filters for overloading configuration
	 */
	protected function addConfigFilters() {
		$this->addFilter('admin/pages');

		$this->addFilter('app/models');
		$this->addFilter('app/template-controllers');
		$this->addFilter('app/controllers');
		$this->addFilter('app/taxonomies');
		$this->addFilter('app/commands');

		$this->addFilter('ui/columns');
		$this->addFilter('ui/fields');
		$this->addFilter('ui/widgets');
		$this->addFilter('ui/shortcodes');
		$this->addFilter('ui/blocks');
		$this->addFilter('ui/directives');
		$this->addFilter('ui/metaboxes');

		$this->addFilter('ui/enqueue/admin/js');
		$this->addFilter('ui/enqueue/admin/css');
		$this->addFilter('ui/enqueue/public/js');
		$this->addFilter('ui/enqueue/public/css');
	}
}