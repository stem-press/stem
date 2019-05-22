<?php

namespace Stem\External\Blade\Directives;

use Stem\Core\Context;
use Stem\Core\ViewDirective;
use Stem\Utilities\ArgumentParser;

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
    public static function InlineSVG($svgFile)
    {
        $file = get_template_directory().'/public/img/'.$svgFile;

        if (file_exists($file)) {
        	$svg = file_get_contents($file);
	        $svg = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $svg);
	        $svg = str_replace("<?xml version='1.0' encoding='UTF-8'?>", '', $svg);
            return $svg;
        } else {
            return '';
        }
    }

    public function parseArgs() {
    	return false;
    }

	public function execute($args) {
		return "<?php echo Stem\\External\\Blade\\Directives\\InlineSVGDirective::InlineSVG({$args}); ?>";
	}
}
