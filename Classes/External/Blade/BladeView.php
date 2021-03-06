<?php

namespace Stem\External\Blade;

use Stem\Core\UI;
use Stem\Core\View;
use Stem\Core\Context;
use Stem\Core\ViewDirective;
use Stem\External\Blade\Directives\AvatarDirective;
use Stem\External\Blade\Directives\InlineCSSDirective;
use Stem\Models\Theme;
use duncan3dc\Laravel\Blade;
use duncan3dc\Laravel\BladeInstance;
use Stem\Utilities\ArgumentParser;

/**
 * Class BladeView.
 *
 * Class for rendering laravel blade views
 */
class BladeView extends View
{
    private $blade = null;

    public function __construct(Context $context = null, UI $ui = null, $viewName = null)
    {
        if (strpos($viewName, 'stem-system.') === 0) {
            $viewPath = ILAB_STEM_VIEW_DIR;
            $viewName = str_replace('stem-system.', '', $viewName);
        } else {
            $viewPath = $context->rootPath.'/views/';
        }

        parent::__construct($context, $ui, $viewName);

        $cache = $ui->setting('options/views/cache');

        $this->blade = new BladeInstance($viewPath, $cache);

	    $additionalPaths = apply_filters('heavymetal/views/paths', []);
	    if (!empty($additionalPaths)) {
		    foreach ($additionalPaths as $path) {
			    $this->blade->addPath($path);
		    }
	    }

        $additionalPaths = apply_filters('stem/additional_view_paths', []);
        if (!empty($additionalPaths)) {
            foreach ($additionalPaths as $path) {
                $this->blade->addPath($path);
            }
        }

        $this->registerDirectives();
    }

    public function render($data)
    {
        return $this->blade->render($this->viewName, $data);
    }

    public static function renderView(Context $context, UI $ui, $view, $data)
    {
        $view = new self($context, $ui, $view);

        return $view->render($data);
    }

    public static function viewExists(UI $ui, $view)
    {
        $exists = file_exists($ui->viewPath.$view.'.blade.php');

        if (! $exists) {
            return file_exists($ui->viewPath.$view.'.html.blade.php');
        }

        return $exists;
    }

    protected function registerDirectives()
    {
        $defaultDirectives = [
            'menu' => '\\Stem\\External\\Blade\\Directives\\MenuDirective',
            'enqueue' => '\\Stem\\External\\Blade\\Directives\\EnqueueDirective',
            'cacheControl' => '\\Stem\\External\\Blade\\Directives\\CacheControlDirective',
            'header' => '\\Stem\\External\\Blade\\Directives\\HeaderDirective',
            'footer' => '\\Stem\\External\\Blade\\Directives\\FooterDirective',
            'css' => '\\Stem\\External\\Blade\\Directives\\CSSDirective',
            'image' => '\\Stem\\External\\Blade\\Directives\\ImageDirective',
            'script' => '\\Stem\\External\\Blade\\Directives\\ScriptDirective',
            'file' => '\\Stem\\External\\Blade\\Directives\\FileDirective',
            'theme' => '\\Stem\\External\\Blade\\Directives\\ThemeDirective',
            "flatmenu" => "\\Stem\\External\\Blade\\Directives\\FlatMenuDirective",
            "svg" => "\\Stem\\External\\Blade\\Directives\\InlineSVGDirective",
	        "widgets" => "\\Stem\\External\\Blade\\Directives\\WidgetsDirective",
	        "nonce" => "\\Stem\\External\\Blade\\Directives\\NonceDirective",
	        "inlinecss" => InlineCSSDirective::class,
	        'avatar' => AvatarDirective::class,
        ];

        $directives = $this->context->ui->setting('options/views/directives', []);
        $directives = array_merge($defaultDirectives, $directives);
        $directives = apply_filters('heavymetal/ui/directives', $directives);

        foreach ($directives as $key => $class) {
            if (class_exists($class)) {
            	/** @var ViewDirective $directive */
                $directive = new $class($this->context);
                $this->blade->directive($key, function ($expression) use ($directive) {
                    $expression = trim($expression, '()');

                    if ($directive->parseArgs()) {
                        $args = ArgumentParser::Parse($expression);
                    } else {
                    	$args = $expression;
                    }

                    return $directive->execute($args);
                });
            }
        }
    }
}
