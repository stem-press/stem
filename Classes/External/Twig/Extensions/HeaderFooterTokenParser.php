<?php

namespace Stem\External\Twig\Extensions;

use Stem\Core\Context;

class HeaderFooterTokenParser extends \Twig_TokenParser
{
    protected $context;
    protected $tag;

    public function __construct(Context $context, $tag = 'header')
    {
        $this->context = $context;
        $this->tag = $tag;
    }

    public function parse(\Twig_Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return new HeaderFooterNode($this->context, $this->tag, $token->getLine(), $this->tag);
    }

    public function getTag()
    {
        return $this->tag;
    }
}
