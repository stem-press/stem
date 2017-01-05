<?php

namespace ILab\Stem\Core;

/**
 * Class Controller.
 *
 * Abstract base class for theme controllers.  Every controller should implement `getIndex($request)` as this is the
 * default method.  You can also create new methods to be called from forms or ajax in your themes using the standard
 * {http method}{action} naming convention.  For example, `postComment($request)` would handle an HTTP post with the
 * action 'comment'.  If you don't define a route, because Wordpress controls the URL structure of your site, you will
 * need to post to the current URL of the page and pass the query parameter `_action` with the name of the action for
 * the controller method to be called.
 *
 * For example, without defining a route, to call the method postComment on your controller from the front end, your
 * form will have to look like:
 *
 * ```
 * <form method="post">
 *      <input type="hidden" name="_action" value="comment">
 *      ... other form elements here ...
 * </form>
 * ```
 *
 * Note this form is lacking an action attribute meaning it will post itself to the page that it is on.  The _action
 * hidden input will be used by Stem to figure out that *postComment* is the method to call.
 *
 * You should use routes though.
 */
abstract class Controller
{
    public $context;
    public $template = null;

    /**
     * @param $context
     */
    public function __construct(Context $context, $template = null)
    {
        $this->context = $context;
        $this->template = $template;
    }
}
