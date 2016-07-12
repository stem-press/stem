<?php
namespace ILab\Stem\External\Twig;

use ILab\Stem\Core\Context;
use ILab\Stem\Core\UI;
use ILab\Stem\Core\View;
use ILab\Stem\External\Twig\Extensions\EnqueueTokenParser;
use ILab\Stem\External\Twig\Extensions\HeaderFooterTokenParser;
use ILab\Stem\External\Twig\Extensions\WordPressExtension;

/**
 * Class TwigView
 *
 * Class for rendering twig views
 *
 * @package ILab\Stem\External\Twig
 */
class TwigView extends View {

    private $twig = null;

    public function __construct(Context $context=null, UI $ui=null, $viewName=null) {
        parent::__construct($context, $ui, $viewName);

        $loader = new \Twig_Loader_Filesystem($context->rootPath.'/views/');

        $args = [
            'autoescape' => false
        ];
        
        $cache = $context->setting('options/views/cache');

        if ($cache && !$context->debug) {
            $args['cache'] = $context->rootPath.'/'.trim($cache,'/').'/';
        }

        $this->twig = new \Twig_Environment($loader, $args);
        $this->twig->addExtension(new WordPressExtension());
        $this->twig->addTokenParser(new EnqueueTokenParser($context));
        $this->twig->addTokenParser(new HeaderFooterTokenParser($context, 'header'));
        $this->twig->addTokenParser(new HeaderFooterTokenParser($context, 'footer'));

        if (file_exists($context->ui->viewPath.$this->viewName.'.html.twig'))
            $this->viewName.='.html';
    }
    
    public function render($data) {
        return $this->twig->render($this->viewName.'.twig', $data);
    }
    
    public static function renderView(Context $context, UI $ui, $view, $data) {
        $view=new TwigView($context, $ui, $view);
        return $view->render($data);
    }

    public static function viewExists(UI $ui, $view) {
        $exists = file_exists($ui->viewPath.$view.'.twig');

        if (!$exists) {
            return file_exists($ui->viewPath.$view.'.html.twig');
        }

        return $exists;
    }
}
