<?php
namespace ILab\Stem\Core;

/**
 * Class View
 *
 * Base class for rendering views
 *
 * @package ILab\Stem\Core
 */
abstract class View {
    protected $debug;
    protected $context;
    protected $viewName;

    public function __construct(Context $context=null, $viewName=null) {
        $this->context=$context;
        $this->viewName=$viewName;

        $this->debug=($context!=null) && (defined(WP_DEBUG) || (getenv('WP_ENV')=='development'));
    }

    public abstract static function renderView(Context $context, $view, $data);

    public abstract static function viewExists(Context $context, $view);
}
