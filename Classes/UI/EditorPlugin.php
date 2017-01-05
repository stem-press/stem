<?php

namespace ILab\Stem\UI;

use ILab\Stem\Core\Context;

/**
 * Class EditorPlugin
 *
 * Wraps a plugin for the TinyMCE editor.
 *
 * @package ILab\Stem\UI
 */
abstract class EditorPlugin {
	protected $context;
	protected $config;

	/**
	 * EditorPlugin constructor.
	 *
	 * @param Context $context
	 * @param array $config
	 */
	public function __construct(Context $context, $config = []) {
		$this->context = $context;
		$this->config = $config;
	}

	/**
	 * Returns the identifier for the plugin.
	 * @return string
	 */
	abstract function identifier();

	/**
	 * Returns a string or array of CSS stylesheet URLs to enqueue
	 *
	 * @return string|array|null
	 */
	public function styles() {
		return [];
	}

	/**
	 * Returns a string or array of script URLs to enqueue
	 *
	 * @return array|string|null
	 */
	public function scripts() {
		return [];
	}

	/**
	 * Array of buttons to add to the editor UI.
	 * @return array
	 */
	public function buttons() {
		return [];
	}

	/**
	 * This is triggered before the TinyMCE editor settings are output to the client.
	 * @param $mceSettings The TinyMCE settings
	 */
	public function onBeforeInit($mceSettings) {

	}

	/**
	 * This is triggered after the TinyMCE js is loaded, but before any editors are created.
	 * @param $mceSettings The TinyMCE settings
	 */
	public function onInit($mceSettings) {

	}

	/**
	 * This is triggered after the TinyMCE editor settings are output to the client.
	 * @param $mceSettings The TinyMCE settings
	 */
	public function onAfterInit($mceSettings) {

	}
}