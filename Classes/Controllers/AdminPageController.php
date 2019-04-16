<?php

namespace Stem\Controllers;

use Stem\Core\Context;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for single admin pages
 *
 * @package Stem\Controllers
 */
abstract class AdminPageController {
	/** @var Context|null  */
	protected $context = null;

	/**
	 * AdminPageController constructor.
	 *
	 * @param Context $context
	 */
	public function __construct(Context $context) {
		$this->context = $context;
	}

	/**
	 * The parent menu slug for the page
	 * @return null|string
	 */
	public function parentMenuSlug() {
		return null;
	}

	/**
	 * The title of the admin page
	 * @return string
	 */
	abstract public function pageTitle();

	/**
	 * The title for the page's menu item
	 * @return string
	 */
	abstract public function menuTitle();

	/**
	 * The menu's slug
	 * @return string
	 */
	abstract public function menuSlug();

	/**
	 * The user capability required to view the page
	 * @return string
	 */
	public function capability() {
		return 'edit_posts';
	}

	/**
	 * The menu icon, if any.
	 * @return null|string
	 */
	public function icon() {
		return null;
	}

	/**
	 * Menu position
	 * @return null|int
	 */
	public function menuPosition() {
		return null;
	}

	/**
	 * Renders the page and returns the result
	 *
	 * @param Request $request
	 * @return string
	 */
	public function execute(Request $request) {
		return '';
	}
}