<?php
/*
Plugin Name: Fast Media Gallery - Visual Composer Addon 
Plugin URI: http://plugins.ad-theme.com/fastmediagallery/
Description: Media gallery addon for visual composer
Author: Ad-theme.com
Version: 1.0
Author URI: http://www.ad-theme.com
Compatibility: WP 3.9.x - WP 4.x
*/

// Basic plugin definitions 
define ('PLG_NAME_VCFMG', 'fastmediagallery');
define( 'PLG_VERSION_VCFMG', '1.0' );
define( 'VC_FMG_URL', WP_PLUGIN_URL . '/' . str_replace( basename(__FILE__), '', plugin_basename(__FILE__) ));
define( 'VC_FMG_DIR', WP_PLUGIN_DIR . '/' . str_replace( basename(__FILE__), '', plugin_basename(__FILE__) ));

// LANGUAGE
add_action('init', 'vcfmg_localization_init');
function vcfmg_localization_init() {
    $path = dirname(plugin_basename( __FILE__ )) . '/languages/';
    $loaded = load_plugin_textdomain( 'fastmediagallery', false, $path);
}

// don't load directly
if (!defined('ABSPATH')) die('-1');

require('class/fastmediagallery_class.php');
require('function/fastmediagallery_function.php');