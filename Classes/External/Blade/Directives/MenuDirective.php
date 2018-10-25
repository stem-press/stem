<?php

namespace Stem\External\Blade\Directives;

use Stem\Core\ViewDirective;

/**
 * Class MenuDirective.
 *
 * Adds an `@menu` directive to Blade templates for outputting WordPress's menu
 *
 * Usage:
 * ```
 * @menu('menu-slug')
 * @menu('menu-slug',true)
 * @menu('menu-slug',true,false)
 * @menu('menu-slug',true,false,'')
 * ```
 *
 * First argument is the slug of the menu
 * Second argument is a bool that controls stripping out the ul/li wrappers WordPress adds
 * Third argument is a bool that controls if the anchor text is removed (for icon only menus, though better done with CSS)
 * Fourth argument is the class name to use for gap elements that should be inserted between menu items, blank for no gap elements
 */
class MenuDirective extends ViewDirective
{
    public function execute($args)
    {
        if (count($args) == 0) {
            throw new \Exception('Missing menu slug argument for @menu directive.');
        }

        $slug = $args[0];
        $stripUL = (count($args) > 1) ? ($args[1] != 'false') : false;
        $removeText = (count($args) > 2) ? ($args[2] != 'false') : false;
        $insertGap = (count($args) > 3) ? $args[3] : '';
        $array = (count($args) > 4) ? ($args[4] != 'false') : false;

        $stripUL = var_export($stripUL,true);
        $removeText = var_export($removeText,true);
        $array = var_export($array,true);

        $result = "<?php echo Stem\\Core\\Context::current()->ui->menu('{$slug}',{$stripUL},{$removeText},'{$insertGap}',{$array}); ?>";

        return $result;
    }
}
