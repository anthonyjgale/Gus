<?php
/**
 * Plugin Name: GEO Discovery Landing Page
 * Description: Adds public GEO landing pages for discovery routes.
 * Version: 1.1.0
 * Author: GEO Discovery
 */

if (!defined('ABSPATH')) {
    exit;
}

define('GUS_PLUGIN_DIR', plugin_dir_path(__FILE__));
require_once GUS_PLUGIN_DIR . 'includes/class-gus-block-schema.php';
require_once GUS_PLUGIN_DIR . 'includes/class-gus-utils.php';
require_once GUS_PLUGIN_DIR . 'includes/class-gus-generator.php';
require_once GUS_PLUGIN_DIR . 'includes/class-gus-seo.php';
require_once GUS_PLUGIN_DIR . 'includes/class-gus-resolver.php';
require_once GUS_PLUGIN_DIR . 'includes/class-gus-renderer.php';
require_once GUS_PLUGIN_DIR . 'includes/class-gus-routing.php';
require_once GUS_PLUGIN_DIR . 'includes/class-gus-admin.php';
require_once GUS_PLUGIN_DIR . 'includes/class-gus-migrator.php';
require_once GUS_PLUGIN_DIR . 'includes/class-gus-plugin.php';

function gus_bootstrap_plugin() {
    load_plugin_textdomain('geo-discovery', false, dirname(plugin_basename(__FILE__)) . '/languages');
    Gus_Migrator::migrate_generation_versions();
    $plugin = new Gus_Plugin();
    $plugin->init();
}
add_action('plugins_loaded', 'gus_bootstrap_plugin');

register_activation_hook(__FILE__, array('Gus_Plugin', 'activate'));
register_deactivation_hook(__FILE__, array('Gus_Plugin', 'deactivate'));
