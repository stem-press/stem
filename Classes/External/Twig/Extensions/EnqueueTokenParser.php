<?php

namespace ILab\Stem\External\Twig\Extensions;

use ILab\Stem\Core\Context;

class EnqueueTokenParser extends \Twig_TokenParser {
	protected $context;

	public function __construct(Context $context) {
		$this->context = $context;
	}

	public function parse(\Twig_Token $token) {
		$parser = $this->parser;
		$stream = $parser->getStream();

		$type = $stream->expect(\Twig_Token::NAME_TYPE)->getValue();
		$resource = $stream->expect(\Twig_Token::STRING_TYPE)->getValue();
		$stream->expect(\Twig_Token::BLOCK_END_TYPE);

		// This has to happen before anything is generated.
		if (($type == 'js') || ($type == 'script'))
			wp_enqueue_script($resource, $this->context->ui->script($resource), ['jquery'], false, true);
		else if (($type == 'css') || ($type == 'style'))
			wp_enqueue_style($resource, $this->context->ui->css($resource));

		return new EnqueueNode($this->context, $type, $resource, $token->getLine(),  $this->getTag());
	}

	public function getTag() {
		return 'enqueue';
	}
}