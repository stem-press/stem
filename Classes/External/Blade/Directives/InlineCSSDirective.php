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
class InlineCSSDirective extends ViewDirective
{
	public static function InlineCSS($cssFile)
	{
		$file = get_template_directory().'/public/css/'.$cssFile;

		if (file_exists($file)) {
			$css = file_get_contents($file);
			return "<style>\n {$css} \n</style>";
		} else {
			return '';
		}
	}

	public function parseArgs() {
		return false;
	}

	public function execute($args) {
		return "<?php echo Stem\\External\\Blade\\Directives\\InlineCSSDirective::InlineCSS({$args}); ?>";
	}
}
