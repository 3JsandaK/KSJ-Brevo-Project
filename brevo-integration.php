<?php
/**
 * Plugin Name: KSJ Brevo Integration
 * Description: Modular Brevo contact integration with shortcode UI.
 * Version: 2.0
 * Author: Screechy Cat Media
 */

if (!defined('ABSPATH')) exit;

// Load all classes
foreach (glob(plugin_dir_path(__FILE__) . 'includes/*.php') as $file) {
    require_once $file;
}

// Initialize features
add_action('plugins_loaded', function () {
    new Brevo_API();
    new Brevo_Contacts();
    new Brevo_Shortcodes();
    new Brevo_Settings();
});
