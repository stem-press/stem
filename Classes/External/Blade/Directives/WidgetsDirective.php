<?php

namespace Stem\External\Blade\Directives;

use Stem\Core\ViewDirective;

/**
 * Class ScriptDirective.
 *
 * Adds a `@widget('name-of-script.js')` directive to Blade templates that displays a sidebar widget area
 *
 * Usage:
 *
 * ```
 * @widgets('side-bar-identifier')
 * ```
 */
class WidgetsDirective extends ViewDirective
{
    public function execute($args) {
        if (count($args) == 0) {
            throw new \Exception('Missing sidebar identifier for @widgets directive.');
        }

        $sidebar = $args[0];

        return "<?php dynamic_sidebar('$sidebar'); ?>";
    }
}
