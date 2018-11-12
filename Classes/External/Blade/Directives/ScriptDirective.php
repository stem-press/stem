<?php

namespace Stem\External\Blade\Directives;

use Stem\Core\ViewDirective;

/**
 * Class ScriptDirective.
 *
 * Adds an `@script('name-of-script.js')` directive to Blade templates that outputs the URL to a script in the
 * theme's public/js directory.
 *
 * Usage:
 *
 * ```
 * @script('name-of-script.js')
 * ```
 */
class ScriptDirective extends ViewDirective
{
    public function execute($args)
    {
        if (count($args) == 0) {
            throw new \Exception('Missing file name for @script directive.');
        }
        $file = $args[0];

        return "<?php echo Stem\\Core\\Context::current()->ui->script('{$file}'); ?>";
    }
}
