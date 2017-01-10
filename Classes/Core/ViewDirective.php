<?php

namespace ILab\Stem\Core;

/**
 * Class ViewDirective.
 *
 * Base class for extending the view templates with a custom directive.  This only works for Blade or similar views.
 * For Twig, you'll have to extend using Twig_TokenParser and Twig_Node.
 */
abstract class ViewDirective
{
    protected $context;

    public function __construct(Context $context = null)
    {
        $this->context = $context;
    }

    /**
     * Executes the directive.
     *
     * @param array $args Arguments for the directive
     *
     * @return mixed
     */
    abstract public function execute($args);
}
