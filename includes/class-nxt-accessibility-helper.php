<?php
/**
 * The main plugin class that handles all core functionality.
 *
 * @since      1.0.0
 * @package    NXT_Accessibility_Helper
 * @subpackage NXT_Accessibility_Helper/includes
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

class NXT_Accessibility_Helper {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Plugin options.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array $options The plugin options.
	 */
	protected $options;

	/**
	 * Skip link handler instance.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      NXT_Skip_Link $skip_link The skip link handler instance.
	 */
	protected $skip_link;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->plugin_name = 'nxt-accessibility';
		$this->version = NXT_ACCESSIBILITY_VERSION;
		$this->options = get_option('nxt_accessibility_options');
		
		$this->load_dependencies();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		// Skip Link Manager
		$this->skip_link = new NXT_Skip_Link($this->options);
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		// Initialize skip link
		$this->skip_link->init();
		
		// Enqueue CSS for accessibility improvements
		add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
		
		// Add the ID to the target element if needed
		add_action('wp_footer', array($this, 'ensure_target_element_has_id'), 5);
		
		// Add body class for focus improvements if enabled
		add_filter('body_class', array($this, 'add_focus_improvements_body_class'));
		
		// Add custom CSS variables
		add_action('wp_head', array($this, 'add_custom_css_variables'));
	}
	
	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		// Enqueue the CSS
		wp_enqueue_style(
			$this->plugin_name,
			NXT_ACCESSIBILITY_URL . 'assets/css/nxt-accessibility-public.css',
			array(),
			$this->version,
			'all'
		);
		
		// Add custom styles if defined
		if (!empty($this->options['custom_styles'])) {
			wp_add_inline_style($this->plugin_name, $this->options['custom_styles']);
		}
		
		// Only enqueue the target element script if needed
		if (empty($this->options['target_id']) && (!empty($this->options['target_element']) || !empty($this->options['target_class']))) {
			wp_enqueue_script(
				$this->plugin_name,
				NXT_ACCESSIBILITY_URL . 'assets/js/nxt-accessibility-public.js',
				array(),
				$this->version,
				true
			);
			
			// Localize the script with our data
			wp_localize_script(
				$this->plugin_name,
				'nxtA11yFrontend',
				array(
					'targetClass' => !empty($this->options['target_class']) ? esc_js($this->options['target_class']) : '',
					'targetElement' => !empty($this->options['target_element']) ? esc_js($this->options['target_element']) : '',
					'skipTargetId' => 'nxt-skip-target',
					'skipLinkClass' => 'nxt-skip-link'
				)
			);
		}
	}
	
	/**
	 * Add the focus improvements body class if the option is enabled.
	 *
	 * @since    1.0.0
	 * @param    array    $classes    The body classes.
	 * @return   array                The modified body classes.
	 */
	public function add_focus_improvements_body_class($classes) {
		if (!empty($this->options['enable_focus_improvements']) && $this->options['enable_focus_improvements'] === 'yes') {
			$classes[] = 'nxt-a11y-focus-improvements-enabled';
		}
		return $classes;
	}
	
	/**
	 * Add custom CSS variables to the head based on user settings.
	 *
	 * @since    1.0.0
	 */
	public function add_custom_css_variables() {
		$css_vars = array();
		
		// Map options to CSS variables
		if (!empty($this->options['primary_color'])) {
			$css_vars['--nxt-a11y-primary-color'] = esc_attr($this->options['primary_color']);
		}
		
		if (!empty($this->options['text_color'])) {
			$css_vars['--nxt-a11y-text-color'] = esc_attr($this->options['text_color']);
		}
		
		if (!empty($this->options['background_color'])) {
			$css_vars['--nxt-a11y-bg-color'] = esc_attr($this->options['background_color']);
		}
		
		if (!empty($this->options['highlight_color'])) {
			$css_vars['--nxt-a11y-highlight-color'] = esc_attr($this->options['highlight_color']);
		}
		
		if (!empty($this->options['outline_width'])) {
			$css_vars['--nxt-a11y-outline-width'] = esc_attr($this->options['outline_width'] . 'px');
		}
		
		if (!empty($this->options['outline_offset'])) {
			$css_vars['--nxt-a11y-outline-offset'] = esc_attr($this->options['outline_offset'] . 'px');
		}
		
		// Only output if we have variables to set
		if (!empty($css_vars)) {
			echo '<style id="nxt-accessibility-custom-vars">';
			echo ':root {';
			
			foreach ($css_vars as $var => $value) {
				echo esc_html($var) . ': ' . esc_html($value) . ';';
			}
			
			echo '}';
			echo '</style>';
		}
	}
} 