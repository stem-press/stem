<?php
namespace ILab\Stem\External\Blade\Directives;

use ILab\Stem\Core\ViewDirective;

/**
 * Class HeaderDirective
 *
 * Adds an `@header` directive to Blade templates for outputting WordPress's header stuff.
 *
 * Usage:
 * ```
 * @header()
 * ```
 *
 * @package ILab\Stem\External\Blade\Directives
 */
class HeaderDirective extends ViewDirective {
	public function execute($args) {
		return "<?php echo ILab\\Stem\\Core\\Context::current()->ui->header(); ?>";
	}
}