<?php
namespace ILab\Stem\External\Blade\Directives;

use ILab\Stem\Core\ViewDirective;

/**
 * Class ImageDirective
 *
 * Adds an `@image('name-of-image.extension')` directive to Blade templates that outputs the URL to an image in the
 * theme's public/img directory.
 *
 * Usage:
 *
 * ```
 * @image('name-of-image.extension')
 * ```
 *
 * @package ILab\Stem\External\Blade\Directives
 */
class ImageDirective extends ViewDirective {
	public function execute($args) {
		if (count($args)==0)
			throw new \Exception("Missing image file name for @image directive.");

		$file = $args[0];

		return "<?php echo ILab\\Stem\\Core\\Context::current()->ui->image('{$file}'); ?>";
	}
}