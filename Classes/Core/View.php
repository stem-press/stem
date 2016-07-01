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
    protected $ui;
    protected $viewName;

    public function __construct(Context $context=null, UI $ui=null, $viewName=null) {
        $this->ui=$ui;
        $this->context=$context;
        $this->viewName=$viewName;

        $this->debug=($context!=null) && (defined(WP_DEBUG) || (getenv('WP_ENV')=='development'));
    }

    public abstract static function renderView(Context $context, UI $ui, $view, $data);

    public abstract static function viewExists(UI $ui, $view);
}
