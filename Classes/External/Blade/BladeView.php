<?php

namespace ILab\Stem\External\Blade;

use ILab\Stem\Core\UI;
use ILab\Stem\Core\View;
use ILab\Stem\Core\Context;
use ILab\Stem\Models\Theme;
use duncan3dc\Laravel\Blade;
use duncan3dc\Laravel\BladeInstance;
use ILab\Stem\Utilities\ArgumentParser;

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

        $additionalPaths = apply_filters('stem/additional_view_paths', []);
        if (is_array($additionalPaths)) {
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
            'menu' => '\\ILab\\Stem\\External\\Blade\\Directives\\MenuDirective',
            'enqueue' => '\\ILab\\Stem\\External\\Blade\\Directives\\EnqueueDirective',
            'cacheControl' => '\\ILab\\Stem\\External\\Blade\\Directives\\CacheControlDirective',
            'header' => '\\ILab\\Stem\\External\\Blade\\Directives\\HeaderDirective',
            'footer' => '\\ILab\\Stem\\External\\Blade\\Directives\\FooterDirective',
            'css' => '\\ILab\\Stem\\External\\Blade\\Directives\\CSSDirective',
            'image' => '\\ILab\\Stem\\External\\Blade\\Directives\\ImageDirective',
            'script' => '\\ILab\\Stem\\External\\Blade\\Directives\\ScriptDirective',
            'file' => '\\ILab\\Stem\\External\\Blade\\Directives\\FileDirective',
            'theme' => '\\ILab\\Stem\\External\\Blade\\Directives\\ThemeDirective',
        ];

        $directives = $this->context->ui->setting('options/views/directives', []);

        $directives = array_merge($defaultDirectives, $directives);

        foreach ($directives as $key => $class) {
            if (class_exists($class)) {
                $directive = new $class($this->context);
                $this->blade->directive($key, function ($expression) use ($directive) {
                    $expression = trim($expression, '()');
                    $args = ArgumentParser::Parse($expression);

                    return $directive->execute($args);
                });
            }
        }
    }
}
