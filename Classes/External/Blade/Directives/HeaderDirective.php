<?php

namespace Stem\External\Blade\Directives;

use Stem\Core\ViewDirective;

/**
 * Class HeaderDirective.
 *
 * Adds an `@header` directive to Blade templates for outputting WordPress's header stuff.
 *
 * Usage:
 * ```
 * @header()
 * ```
 */
class HeaderDirective extends ViewDirective
{
    public function execute($args)
    {
        return '<?php echo Stem\\Core\\Context::current()->ui->header(); ?>';
    }
}
