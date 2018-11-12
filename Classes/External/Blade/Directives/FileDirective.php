<?php

namespace Stem\External\Blade\Directives;

use Stem\Core\ViewDirective;

/**
 * Class FileDirective.
 *
 * Adds an `@file('name-of-file.extension')` directive to Blade templates that outputs the URL to any file in the
 * theme's root directory.
 *
 * Usage:
 *
 * ```
 * @file('name-of-file.extension')
 * ```
 */
class FileDirective extends ViewDirective
{
    public function execute($args)
    {
        if (count($args) == 0) {
            throw new \Exception('Missing file name for @file directive.');
        }
        $file = $args[0];

        return "<?php echo Stem\\Core\\Context::current()->ui->file('{$file}'); ?>";
    }
}
