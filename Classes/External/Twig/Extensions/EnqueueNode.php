<?php

namespace ILab\Stem\External\Twig\Extensions;

use ILab\Stem\Core\Context;

/**
 * Class EnqueueNode
 * @package ILab\Stem\External\Twig\Extensions
 *
 * This does absolutely nothing but is necessary.
 */
class EnqueueNode extends \Twig_Node {
	protected $context;
	protected $type;
	protected $resource;

	public function __construct(Context $context, $type, $resource, $lineno, $tag) {
		parent::__construct([], [], $lineno, $tag);

		$this->type = $type;
		$this->resource = $resource;
		$this->context = $context;
	}

	public function compile(\Twig_Compiler $compiler) {
		$compiler->addDebugInfo($this);
	}
}