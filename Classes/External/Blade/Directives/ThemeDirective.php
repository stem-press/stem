<?php

namespace ILab\Stem\External\Blade\Directives;

use ILab\Stem\Core\ViewDirective;

/**
 * Class ThemeDirective.
 *
 * Adds an `@theme('name-of-theme-seting')` directive to Blade templates that outputs the
 * value of a theme setting that has been configured via the customizer.
 *
 * Usage:
 *
 * ```
 * @theme('name-of-theme-setting')
 * ```
 */
class ThemeDirective extends ViewDirective
{
    public function execute($args)
    {
        if (count($args) == 0) {
            throw new \Exception('Missing setting name for @theme directive.');
        }
        $setting = $args[0];

        return "<?php echo ILab\\Stem\\Core\\Context::current()->ui->theme->{$setting}; ?>";
    }
}
