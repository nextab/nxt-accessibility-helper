<?php
/**
 * The skip link functionality.
 *
 * @since      1.0.0
 * @package    NXT_Accessibility_Helper
 * @subpackage NXT_Accessibility_Helper/includes
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

class NXT_Skip_Link {

	/**
	 * Plugin options.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array $options The plugin options.
	 */
	private $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    array    $options    The plugin options.
	 */
	public function __construct($options) {
		$this->options = $options;
	}

	/**
	 * Initialize the skip link functionality.
	 *
	 * @since    1.0.0
	 */
	public function init() {
		add_action('wp_body_open', array($this, 'add_skip_link'), 1);
	}

	/**
	 * Add the skip link to the beginning of the body.
	 *
	 * @since    1.0.0
	 */
	public function add_skip_link() {
		$link_text = !empty($this->options['link_text']) ? 
			$this->options['link_text'] : 
			__('Skip to content', 'nxt-accessibility');
			
		$target_id = !empty($this->options['target_id']) ? 
			$this->options['target_id'] : 
			'nxt-skip-target';
			
		$link_class = 'nxt-skip-link';
		
		if (!empty($this->options['link_style'])) {
			$link_class .= ' nxt-style-' . $this->options['link_style'];
		}
		
		printf(
			'<a href="#%1$s" class="%2$s">%3$s</a>',
			esc_attr($target_id),
			esc_attr($link_class),
			esc_html($link_text)
		);
	}
} 