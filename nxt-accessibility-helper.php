<?php
/**
 * Plugin Name: NXT Accessibility Helper
 * Plugin URI: https://nextab.de
 * Description: Improves website accessibility with customizable skip links and other accessibility enhancements
 * Version: 1.0.0
 * Author: nexTab
 * Author URI: https://nextab.de
 * Text Domain: nxt-accessibility
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Current plugin version.
 */
define('NXT_ACCESSIBILITY_VERSION', '1.0.0');
define('NXT_ACCESSIBILITY_PATH', plugin_dir_path(__FILE__));
define('NXT_ACCESSIBILITY_URL', plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function nxt_accessibility_activate() {
	// Save default options
	$default_options = array(
		// Skip link settings
		'target_id' => 'et-main-area', // Default to Divi's main area
		'target_element' => '', // Backup HTML element to target
		'target_class' => '', // Backup CSS class to target
		'link_text' => __('Skip to content', 'nxt-accessibility'),
		'link_style' => 'default',
		
		// Focus improvement settings
		'enable_focus_improvements' => 'yes',
		
		// Color settings
		'primary_color' => '#6200ff',
		'text_color' => '#ffffff',
		'background_color' => '#000000',
		'highlight_color' => '#ffff00',
		
		// Size and appearance settings
		'outline_width' => '2',
		'outline_offset' => '2',
		
		// Custom CSS
		'custom_styles' => '',
	);
	
	add_option('nxt_accessibility_options', $default_options);
}
register_activation_hook(__FILE__, 'nxt_accessibility_activate');

/**
 * The code that runs during plugin deactivation.
 */
function nxt_accessibility_deactivate() {
	// No specific deactivation tasks needed
}
register_deactivation_hook(__FILE__, 'nxt_accessibility_deactivate');

/**
 * Add Settings link to plugin actions.
 *
 * @param array $links Plugin action links.
 * @return array Modified plugin action links.
 */
function nxt_accessibility_add_action_links($links) {
	$settings_link = '<a href="' . admin_url('options-general.php?page=nxt-accessibility') . '">' . __('Settings', 'nxt-accessibility') . '</a>';
	array_unshift($links, $settings_link);
	return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'nxt_accessibility_add_action_links');

/**
 * Load plugin text domain for translation.
 */
function nxt_accessibility_load_textdomain() {
	load_plugin_textdomain('nxt-accessibility', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'nxt_accessibility_load_textdomain');

/**
 * Include required files.
 */
require_once NXT_ACCESSIBILITY_PATH . 'includes/class-nxt-accessibility-helper.php';
require_once NXT_ACCESSIBILITY_PATH . 'includes/class-nxt-skip-link.php';

// Admin area
if (is_admin()) {
	require_once NXT_ACCESSIBILITY_PATH . 'admin/class-nxt-accessibility-admin.php';
}

/**
 * Initialize the main plugin class.
 */
function nxt_accessibility_run() {
	$plugin = new NXT_Accessibility_Helper();
	$plugin->run();
}
nxt_accessibility_run();

/**
 * Initialize the admin class.
 */
function nxt_accessibility_admin_init() {
	if (is_admin()) {
		$admin = new NXT_Accessibility_Admin();
	}
}
add_action('init', 'nxt_accessibility_admin_init');
