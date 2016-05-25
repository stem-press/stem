<?php
namespace ILab\Stem\External\Twig;

use ILab\Stem\Core\Context;
use ILab\Stem\Core\View;

/**
 * Class TwigView
 *
 * Class for rendering twig views
 *
 * @package ILab\Stem\External\Twig
 */
class TwigView extends View {

    private $twig = null;

    public function __construct(Context $context=null, $viewName=null) {
        parent::__construct($context, $viewName);

        $loader = new \Twig_Loader_Filesystem($context->rootPath.'/views/');

        $args = [
            'autoescape' => false
        ];
        
        $cache = $context->setting('options/views/cache');

        if ($cache && !$context->debug) {
            $args['cache'] = $context->rootPath.'/'.trim($cache,'/').'/';
        }

        $this->twig = new \Twig_Environment($loader, $args);
        $this->twig->addExtension(new WordpressExtension());

        if (file_exists($context->viewPath.$this->viewName.'.html.twig'))
            $this->viewName.='.html';
    }
    
    public function render($data) {
        if ($data==null)
            $data=[];

        if (!isset($data['context']))
            $data['context']=$this->context;

        return $this->twig->render($this->viewName.'.twig', $data);
    }
    
    public static function renderView(Context $context, $view, $data) {
        $view=new TwigView($context, $view);
        return $view->render($data);
    }

    public static function viewExists(Context $context, $view) {
        $exists = file_exists($context->viewPath.$view.'.twig');

        if (!$exists) {
            return file_exists($context->viewPath.$view.'.html.twig');
        }

        return $exists;
    }
}
