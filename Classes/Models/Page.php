<?php

namespace Stem\Models;

use Stem\Core\Context;

/**
 * Class Page.
 *
 * Represents a page
 */
class Page extends Post {
    protected static $postType='page';

	/**
	 * Returns a Page model for a given path, or null if not found.
	 *
	 * @param $path
	 *
	 * @return null|Page
	 */
    public static function pageForPath($path) {
    	$post = get_page_by_path($path);
    	if (empty($post)) {
    		return null;
	    }

	    return Context::current()->modelForPost($post);
    }
}
