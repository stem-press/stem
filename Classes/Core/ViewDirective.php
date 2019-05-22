<?php

namespace Stem\Core;

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
	 * Controls if directive arguments are parsed before being passed to the directive to execute.
	 * If this returns false, the directive must parse the argument string itself.
	 *
	 * @return bool
	 */
    public function parseArgs() {
    	return true;
    }

    /**
     * Executes the directive.
     *
     * @param array|string $args Arguments for the directive
     *
     * @return mixed
     */
    abstract public function execute($args);
}
