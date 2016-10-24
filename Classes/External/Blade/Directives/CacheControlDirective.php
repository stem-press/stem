<?php
namespace ILab\Stem\External\Blade\Directives;

use ILab\Stem\Core\ViewDirective;

/**
 * Class CacheControlDirective
 *
 * Adds an `@cacheControl` directive to Blade templates.
 *
 * Usage:
 * ```
 * @cacheControl('cache-control-type','max-age', 's-max-age')
 * ```
 *
 * @package ILab\Stem\External\Blade\Directives
 */
class CacheControlDirective extends ViewDirective {
	public function execute($args) {
		$cc = null;
		$ma = null;
		$sma = null;

		if (count($args)>0)
			$cc = $args[0];
		if (count($args)>1)
			$ma = $args[1];
		if (count($args)>2)
			$sma = $args[2];

		$this->context->cacheControl->setCacheControlHeaders($cc, $ma, $sma);
	}
}