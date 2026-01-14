<?php
/**
 * Plugin Name: GEO Discovery Landing Page
 * Description: Adds dynamic GEO discovery landing pages.
 * Version: 1.0.0
 * Author: GEO Discovery
 */

if (!defined('ABSPATH')) {
    exit;
}

define('GUS_GEO_VERSION', '1.0.0');
define('GUS_GEO_PATH', plugin_dir_path(__FILE__));
define('GUS_GEO_URL', plugin_dir_url(__FILE__));

require_once GUS_GEO_PATH . 'includes/class-gus-utils.php';
require_once GUS_GEO_PATH . 'includes/class-gus-seo.php';
require_once GUS_GEO_PATH . 'includes/class-gus-resolver.php';
require_once GUS_GEO_PATH . 'includes/class-gus-renderer.php';
require_once GUS_GEO_PATH . 'includes/class-gus-routing.php';
require_once GUS_GEO_PATH . 'includes/class-gus-plugin.php';

$gus_plugin = new Gus_Plugin();
$gus_plugin->init();

register_activation_hook(__FILE__, array('Gus_Plugin', 'activate'));
