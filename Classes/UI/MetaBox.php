<?php

namespace Stem\UI;

use Stem\Core\Context;
use Stem\Core\UI;
use Stem\Models\Post;

/**
 * Represents a metabox
 *
 * @package Stem\UI
 */
abstract class MetaBox {
	const CONTEXT_NORMAL = 'normal';
	const CONTEXT_SIDE = 'side';
	const CONTEXT_ADVANCED = 'advanced';

	const PRIORITY_HIGH = 'high';
	const PRIORITY_DEFAULT = 'default';
	const PRIORITY_LOW = 'low';

	/** @var null|Context Current context  */
	protected $context = null;

	/** @var null|UI Current UI  */
	protected $ui = null;

	public function __construct(Context $context, UI $ui) {
		$this->context = $context;
		$this->ui = $ui;
	}

	/**
	 * The id of the metabox
	 *
	 * @return string
	 */
	abstract public function id();

	/**
	 * The title of the metabox
	 *
	 * @return string
	 */
	abstract public function title();

	/**
	 * List of post types that can have this metabox
	 *
	 * @return string[]
	 */
	abstract public function postTypes();

	/**
	 * The screen to display the metabox on
	 *
	 * @return null|string|array|\WP_Screen
	 */
	abstract public function screen();

	/**
	 * The context within the screen where the boxes should display.
	 *
	 * @return string
	 */
	abstract public function context();

	/**
	 * The priority within the context where the boxes should show.
	 *
	 * @return string
	 */
	abstract public function priority();

	/**
	 * Renders a metabox and returns the rendered output as a string.
	 *
	 * @param Post $post
	 * @return string
	 */
	abstract public function render($post);
}
