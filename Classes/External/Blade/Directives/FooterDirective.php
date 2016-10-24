<?php
namespace ILab\Stem\External\Blade\Directives;

use ILab\Stem\Core\ViewDirective;

/**
 * Class FooterDirective
 *
 * Adds an `@footer` directive to Blade templates for outputting WordPress's footer stuff.
 *
 * Usage:
 * ```
 * @footer()
 * ```
 *
 * @package ILab\Stem\External\Blade\Directives
 */
class FooterDirective extends ViewDirective {
	public function execute($args) {
		return "<?php echo ILab\\Stem\\Core\\Context::current()->ui->footer(); ?>";
	}
}