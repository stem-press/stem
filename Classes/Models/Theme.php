<?php

namespace ILab\Stem\Models;
use ILab\Stem\Core\Context;
use ILab\Stem\Core\Log;

/**
 * Class Theme
 *
 * Represents the various theme options users have configured with customizer.
 * This class is automatically passed to all rendered views.
 *
 * @package ILab\Stem\Models
 */
final class Theme {
	private $values = [];
	public $context = null;

	public function __construct(Context $context) {
		$this->context = $context;
		Log::info('Post', $_POST);
	}

	public function __get($name) {
		assert(($this->context != null), 'Context can not be null for a Theme model.');

		if (isset($_POST['wp_customize']) && ($_POST['wp_customize']=='on')) {
			$value = arrayPath($_POST,"customized/$name", false);
			if ($value)
				return $value;
		}

		if (!isset($this->values[$name])) {
			$val = get_option($name);

			if (!$val) {
				$val = $this->context->themeSetting("settings/$name/default", null);
			}

			$this->values[$name] = $val;
		}

		return $this->values[$name];
	}
}
