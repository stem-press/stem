<?php

namespace Stem\External\Blade\Directives;

use Stem\Core\Context;
use Stem\Core\ViewDirective;

/**
 * Class FlatMenuDirective
 *
 * Renders a menu as a list of anchor tags.
 *
 * @package StemPress\Directives
 */
class NonceDirective extends ViewDirective  {
	public static function RenderNonce($action) {
		$nonce = wp_create_nonce($action);
		return "<input type='hidden' name='nonce' value='$nonce'>";
	}

	public function execute($args) {
		if (count($args) == 0) {
			throw new \Exception('Missing menu slug argument for @menu directive.');
		}

		return "<?php echo Stem\\External\\Blade\\Directives\\NonceDirective::RenderNonce('{$args[0]}'); ?>";
	}
}