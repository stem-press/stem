<?php

namespace ILab\Stem\External\Twig\Extensions;

use ILab\Stem\Core\Context;

class HeaderFooterNode extends \Twig_Node {
	protected $context;
	protected $type;

	public function __construct(Context $context, $type, $lineno, $tag) {
		parent::__construct([], [], $lineno, $tag);

		$this->context = $context;
		$this->type = $type;
	}

	public function compile(\Twig_Compiler $compiler) {
		if ($this->type == 'header')
			$body = $this->context->ui->header();
		else
			$body = $this->context->ui->footer();

		$compiler->addDebugInfo($this)
			->write('echo ')
			->string($body)
			->raw(";\n");
	}
}