<?php

namespace Stem\UI;

use Stem\Core\Context;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DashboardWidget.
 *
 * Represents a dashboard widget on the WordPress dashboard.
 */
abstract class DashboardWidget
{
    public $context;
    protected $request;
    protected $config;

    /**
     * @param $context
     */
    public function __construct(Context $context, Request $request, $config = [])
    {
        $this->context = $context;
        $this->request = $request;
        $this->config = $config;
    }

    /**
     * Renders the dashboard widget.
     * @return string
     */
    abstract public function render();
}
