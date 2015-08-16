<?php

namespace ILab\Stem\Core;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Controller
 *
 * Abstract base class for theme controllers.  Every controller must implement `getIndex($request)`.  You can also
 * create new methods to be called from forms or ajax in your themes using the standard {http method}{action} naming
 * convention.  For example, `postComment($request)` would handle an HTTP post with the action 'comment'.  Because
 * Wordpress controls the URL structure of your site, you will need to post to the current URL of the page and pass
 * the query parameter `_action` with the name of the action for the controller method to be called.
 *
 * @package ILab\Stem\Core
 */
abstract class Controller {
    private $context;

    /**
     * @param $context
     */
    public function __construct(Context $context) {
        $this->context=$context;
    }

    /**
     * Every controller needs to implement this
     *
     * @param Request $request
     * @return mixed
     */
    public abstract function getIndex(Request $request);
}