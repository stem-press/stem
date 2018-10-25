<?php

namespace Stem\External\Blade\Directives;

use Stem\Core\Context;
use Stem\Core\ViewDirective;

/**
 * Class InlineSVGDirective.
 *
 * Inserts the contents of an svg file into the output html.
 *
 * Usage:
 *
 * ```
 * @svg('name-of-image.svg')
 * ```
 */
class InlineSVGDirective extends ViewDirective
{
    public function execute($args)
    {
        if (count($args) == 0) {
            throw new \Exception('Missing image file name for @image directive.');
        }

        $file = get_template_directory().'/public/img/'.$args[0];

        if (file_exists($file)) {
            return file_get_contents($file);
        } else {
            return '';
        }
    }
}
