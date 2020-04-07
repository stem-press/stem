<?php

namespace Stem\External\Blade\Directives;

use Stem\Core\ViewDirective;
use Stem\Models\User;

/**
 * Class AvatarDirective.
 *
 * Displays the current user's avatar
 *
 * Usage:
 *
 * ```
 * @avatar(96)
 * ```
 */
class AvatarDirective extends ViewDirective
{
	public static function Avatar($size)
	{
		$avatar = User::currentUserAvatar($size);
		if (empty($avatar)) {
			return '';
		} else {
			return $avatar;
		}
	}

	public function execute($args) {
		if (count($args) == 0) {
			$size = 96;
		} else {
			$size = (int)$args[0];
		}

		return "<?php echo Stem\\External\\Blade\\Directives\\AvatarDirective::Avatar({$size}); ?>";
	}
}
