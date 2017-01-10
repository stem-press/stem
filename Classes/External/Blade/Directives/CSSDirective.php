<?php

namespace ILab\Stem\External\Blade\Directives;

use ILab\Stem\Core\ViewDirective;

/**
 * Class CSSDirective.
 *
 * Adds an `@css('name-of-stylesheet.css')` directive to Blade templates that outputs the URL to a stylesheet in the
 * theme's public/css directory.  If you want to enqueue the stylesheet, use the `@enqueue` directive.
 *
 * Usage:
 *
 * ```
 * @css('name-of-stylesheet.css')
 * ```
 */
class CSSDirective extends ViewDirective
{
    public function execute($args)
    {
        if (count($args) == 0) {
            throw new \Exception('Missing css stylesheet name for @css directive.');
        }
        $file = $args[0];

        return "<?php echo ILab\\Stem\\Core\\Context::current()->ui->css('{$file}'); ?>";
    }
}
