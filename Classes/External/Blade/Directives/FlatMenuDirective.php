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
class FlatMenuDirective extends ViewDirective  {
	public static function renderMenuItem($menuItem) {
		$target = (!empty($menuItem['target'])) ? "target='{$menuItem['target']}'" : '';
		$attrs = implode(" ", $menuItem['attrs']);
		$classes = implode(" ", $menuItem['classes']);

		$submenu = '';
		if (isset($menuItem['sub-menu-css'])) {
			$submenu = "data-submenu-css='{$menuItem['sub-menu-css']}'";
		}

		$output = "\t\t\t\t<a href='{$menuItem['url']}' $target $attrs $submenu rel='{$menuItem['rel']}' class='$classes'>{$menuItem['title']}</a>\n";

		return $output;
	}

	public static function OutputFlatMenu($slug) {
        global $wp;
        $current_url = home_url(add_query_arg([], $wp->request));
        $current_url = trim($current_url, "/");
		$menuArray = wp_get_nav_menu_items($slug);

		if (empty($menuArray)) {
		    return "";
        }

		$menuItems = [];
		foreach($menuArray as $menu) {
			if ($menu instanceof \WP_Post) {
				$anchor = get_field('anchor', $menu) ?: '';
				$menuItem = [
				    'id' => $menu->ID,
					'title' => $menu->title,
					'url' => ($menu->url ?: get_permalink($menu->object_id)).$anchor,
					'target' => $menu->target,
					'attrs' => [
						"data-menu-id='{$menu->ID}'"
					],
					'classes' => [],
					'children' => []
				];

				if (trim($menuItem['url'],"/") == $current_url) {
				    $menuItem["classes"][] = "current";
                }

				if (strpos($menuItem['url'],'#') === false) {
					$menuItem['rel'] = "nofollow noopener";
				} else {
					$menuItem['rel'] = "";
				}

				foreach($menu->classes as $class)
					if (!empty($class)) {
						$menuItem['classes'][] = $class;
					}

				if (empty($menu->menu_item_parent)) {
					$subMenuCSS = get_field('sub_menu_css_classes', $menu->ID);

					if (!empty($subMenuCSS)) {
						$menuItem['sub-menu-css']=$subMenuCSS;
					}

					$menuItems[$menu->ID] = $menuItem;
				} else {
					if (!in_array('has-children', $menuItems[$menu->menu_item_parent]['classes'])) {
						$menuItems[$menu->menu_item_parent]['classes'][] = 'has-children';
					}

					$menuItem['attrs'][] = "data-parent-menu-id='{$menu->menu_item_parent}'";
					$menuItem['classes'][''] = 'sub-item';

					$children = $menuItems[$menu->menu_item_parent]['children'];
					$children[] = $menuItem;
					$menuItems[$menu->menu_item_parent]['children'] = $children;
				}
			}
		}

		$output = '';
		foreach($menuItems as $menuItem) {
			$output .= static::renderMenuItem($menuItem);

			foreach($menuItem['children'] as $child) {
				$output .= static::renderMenuItem($child);
			}
		}


		return $output;
	}

	public function execute($args) {
		if (count($args) == 0) {
			throw new \Exception('Missing menu slug argument for @menu directive.');
		}

		return "<?php echo Stem\\External\\Blade\\Directives\\FlatMenuDirective::OutputFlatMenu('{$args[0]}'); ?>";
	}
}