<?php
/*
Plugin Name: ILAB Stem App Framework
Plugin URI: https://github.com/jawngee/stem
Description: Framework for building applications using Wordpress and Symfony
Author: Jon Gilkison
Version: 0.6.11
Author URI: http://interfacelab.com
*/

define('ILAB_STEM', __FILE__);
define('ILAB_STEM_DIR', dirname(__FILE__));
define('ILAB_STEM_VIEW_DIR', ILAB_STEM_DIR.'/views');
define('ILAB_STEM_VERSION', '0.6.11');

if (file_exists(ILAB_STEM_DIR.'/vendor/autoload.php')) {
    require_once ILAB_STEM_DIR.'/vendor/autoload.php';
}

$plug_url = plugin_dir_url(__FILE__);
define('ILAB_STEM_PUB_JS_URL', $plug_url.'public/js');
define('ILAB_STEM_PUB_CSS_URL', $plug_url.'public/css');

register_activation_hook(__FILE__, function () {
    if (! defined('WP_CLI')) {
	    global $wp_rewrite;
	    if ($wp_rewrite) {
		    $wp_rewrite->flush_rules(true);
	    }
    }
});

register_deactivation_hook(__FILE__, function () {
    if (! defined('WP_CLI')) {
	    global $wp_rewrite;
	    if ($wp_rewrite) {
		    $wp_rewrite->flush_rules(true);
	    }
    }
});
