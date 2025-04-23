<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    NXT_Accessibility_Helper
 * @subpackage NXT_Accessibility_Helper/admin
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

class NXT_Accessibility_Admin {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	private $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of the plugin.
	 */
	private $version;

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
	 */
	public function __construct() {
		$this->plugin_name = 'nxt-accessibility';
		$this->version = NXT_ACCESSIBILITY_VERSION;
		$this->options = get_option('nxt_accessibility_options');
		
		// Add settings page and fields
		add_action('admin_menu', array($this, 'add_settings_page'));
		add_action('admin_init', array($this, 'register_settings'));
		
		// Add color picker scripts
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
	}
	
	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @since    1.0.0
	 * @param    string    $hook    The current admin page.
	 */
	public function enqueue_admin_scripts($hook) {
		// Only load on our settings page
		if ($hook != 'settings_page_' . $this->plugin_name) {
			return;
		}
		
		// Add the color picker
		wp_enqueue_style('wp-color-picker');
		
		// Add our admin script
		wp_enqueue_script(
			$this->plugin_name . '-admin',
			NXT_ACCESSIBILITY_URL . 'admin/js/nxt-accessibility-admin.js',
			array('jquery', 'wp-color-picker'),
			$this->version,
			true
		);
		
		// Localize the script with our data
		wp_localize_script(
			$this->plugin_name . '-admin',
			'nxtA11ySettings',
			array(
				'customStyleMessage' => __('Please define your custom style in the Custom CSS section below.', 'nxt-accessibility'),
				'selectors' => array(
					'linkStyle' => 'select[name="nxt_accessibility_options[link_style]"]',
					'customStyleNoteId' => 'custom-style-note',
				),
				'errorColor' => '#d63638'
			)
		);
	}

	/**
	 * Add the settings page to the admin menu.
	 *
	 * @since    1.0.0
	 */
	public function add_settings_page() {
		add_options_page(
			__('Accessibility Settings', 'nxt-accessibility'),
			__('Accessibility', 'nxt-accessibility'),
			'manage_options',
			$this->plugin_name,
			array($this, 'display_settings_page')
		);
	}

	/**
	 * Register the settings for the plugin.
	 *
	 * @since    1.0.0
	 */
	public function register_settings() {
		register_setting(
			$this->plugin_name,
			'nxt_accessibility_options',
			array($this, 'validate_options')
		);

		// Skip Link Settings Section
		add_settings_section(
			'nxt_accessibility_skip_link',
			__('Skip Link Settings', 'nxt-accessibility'),
			array($this, 'skip_link_section_callback'),
			$this->plugin_name
		);

		// Skip link target element ID
		add_settings_field(
			'target_id',
			__('Target Element ID', 'nxt-accessibility'),
			array($this, 'target_id_callback'),
			$this->plugin_name,
			'nxt_accessibility_skip_link'
		);

		// Skip link target element type (as backup)
		add_settings_field(
			'target_element',
			__('Target Element Type (Backup)', 'nxt-accessibility'),
			array($this, 'target_element_callback'),
			$this->plugin_name,
			'nxt_accessibility_skip_link'
		);

		// Skip link target class (as backup)
		add_settings_field(
			'target_class',
			__('Target Element Class (Backup)', 'nxt-accessibility'),
			array($this, 'target_class_callback'),
			$this->plugin_name,
			'nxt_accessibility_skip_link'
		);

		// Skip link text
		add_settings_field(
			'link_text',
			__('Skip Link Text', 'nxt-accessibility'),
			array($this, 'link_text_callback'),
			$this->plugin_name,
			'nxt_accessibility_skip_link'
		);

		// Skip link style
		add_settings_field(
			'link_style',
			__('Skip Link Style', 'nxt-accessibility'),
			array($this, 'link_style_callback'),
			$this->plugin_name,
			'nxt_accessibility_skip_link'
		);
		
		// Focus Improvements Section
		add_settings_section(
			'nxt_accessibility_focus',
			__('Focus Improvements', 'nxt-accessibility'),
			array($this, 'focus_section_callback'),
			$this->plugin_name
		);
		
		// Enable focus improvements
		add_settings_field(
			'enable_focus_improvements',
			__('Enable Focus Improvements', 'nxt-accessibility'),
			array($this, 'enable_focus_improvements_callback'),
			$this->plugin_name,
			'nxt_accessibility_focus'
		);
		
		// Appearance Section
		add_settings_section(
			'nxt_accessibility_appearance',
			__('Appearance Settings', 'nxt-accessibility'),
			array($this, 'appearance_section_callback'),
			$this->plugin_name
		);
		
		// Primary color
		add_settings_field(
			'primary_color',
			__('Primary Color', 'nxt-accessibility'),
			array($this, 'primary_color_callback'),
			$this->plugin_name,
			'nxt_accessibility_appearance'
		);
		
		// Text color
		add_settings_field(
			'text_color',
			__('Text Color', 'nxt-accessibility'),
			array($this, 'text_color_callback'),
			$this->plugin_name,
			'nxt_accessibility_appearance'
		);
		
		// Background color
		add_settings_field(
			'background_color',
			__('Background Color', 'nxt-accessibility'),
			array($this, 'background_color_callback'),
			$this->plugin_name,
			'nxt_accessibility_appearance'
		);
		
		// Highlight color
		add_settings_field(
			'highlight_color',
			__('Highlight Color', 'nxt-accessibility'),
			array($this, 'highlight_color_callback'),
			$this->plugin_name,
			'nxt_accessibility_appearance'
		);
		
		// Outline width
		add_settings_field(
			'outline_width',
			__('Outline Width (px)', 'nxt-accessibility'),
			array($this, 'outline_width_callback'),
			$this->plugin_name,
			'nxt_accessibility_appearance'
		);
		
		// Outline offset
		add_settings_field(
			'outline_offset',
			__('Outline Offset (px)', 'nxt-accessibility'),
			array($this, 'outline_offset_callback'),
			$this->plugin_name,
			'nxt_accessibility_appearance'
		);
		
		// Advanced Section
		add_settings_section(
			'nxt_accessibility_advanced',
			__('Advanced Settings', 'nxt-accessibility'),
			array($this, 'advanced_section_callback'),
			$this->plugin_name
		);

		// Custom CSS
		add_settings_field(
			'custom_styles',
			__('Custom CSS', 'nxt-accessibility'),
			array($this, 'custom_styles_callback'),
			$this->plugin_name,
			'nxt_accessibility_advanced'
		);
	}

	/**
	 * Validate the options before saving.
	 *
	 * @since    1.0.0
	 * @param    array    $input    The options to validate.
	 * @return   array              The validated options.
	 */
	public function validate_options($input) {
		$valid = array();

		// Sanitize the target ID
		$valid['target_id'] = sanitize_text_field($input['target_id']);

		// Sanitize the target element
		$valid['target_element'] = sanitize_text_field($input['target_element']);

		// Sanitize the target class
		$valid['target_class'] = sanitize_text_field($input['target_class']);

		// Sanitize the link text
		$valid['link_text'] = sanitize_text_field($input['link_text']);

		// Sanitize the link style
		$valid['link_style'] = sanitize_text_field($input['link_style']);
		
		// Sanitize the enable focus improvements option
		$valid['enable_focus_improvements'] = isset($input['enable_focus_improvements']) ? sanitize_text_field($input['enable_focus_improvements']) : 'no';
		
		// Sanitize the color options
		$valid['primary_color'] = sanitize_hex_color($input['primary_color']);
		$valid['text_color'] = sanitize_hex_color($input['text_color']);
		$valid['background_color'] = sanitize_hex_color($input['background_color']);
		$valid['highlight_color'] = sanitize_hex_color($input['highlight_color']);
		
		// Sanitize the size options
		$valid['outline_width'] = absint($input['outline_width']);
		$valid['outline_offset'] = absint($input['outline_offset']);

		// Sanitize the custom styles
		$valid['custom_styles'] = sanitize_textarea_field($input['custom_styles']);

		return $valid;
	}

	/**
	 * Render the skip link section description.
	 *
	 * @since    1.0.0
	 */
	public function skip_link_section_callback() {
		echo '<p>' . __('Configure the skip link settings below. The skip link allows keyboard users to bypass repetitive navigation elements.', 'nxt-accessibility') . '</p>';
	}
	
	/**
	 * Render the focus improvements section description.
	 *
	 * @since    1.0.0
	 */
	public function focus_section_callback() {
		echo '<p>' . __('Configure focus improvement settings. These settings enhance the visibility of focused elements for keyboard users.', 'nxt-accessibility') . '</p>';
	}
	
	/**
	 * Render the appearance section description.
	 *
	 * @since    1.0.0
	 */
	public function appearance_section_callback() {
		echo '<p>' . __('Customize the appearance of accessibility features.', 'nxt-accessibility') . '</p>';
	}
	
	/**
	 * Render the advanced section description.
	 *
	 * @since    1.0.0
	 */
	public function advanced_section_callback() {
		echo '<p>' . __('Advanced settings for custom CSS and additional customizations.', 'nxt-accessibility') . '</p>';
	}

	/**
	 * Render the target ID field.
	 *
	 * @since    1.0.0
	 */
	public function target_id_callback() {
		$target_id = isset($this->options['target_id']) ? $this->options['target_id'] : 'et-main-area';
		?>
		<input type="text" name="nxt_accessibility_options[target_id]" value="<?php echo esc_attr($target_id); ?>" class="regular-text">
		<p class="description">
			<?php _e('The ID of the element that the skip link should jump to (e.g., main-content, content, etc.)', 'nxt-accessibility'); ?>
		</p>
		<?php
	}

	/**
	 * Render the target element field.
	 *
	 * @since    1.0.0
	 */
	public function target_element_callback() {
		$target_element = isset($this->options['target_element']) ? $this->options['target_element'] : '';
		?>
		<input type="text" name="nxt_accessibility_options[target_element]" value="<?php echo esc_attr($target_element); ?>" class="regular-text">
		<p class="description">
			<?php _e('HTML element to target if no ID is found (e.g., main, article, .etc). Used only if the Target ID is not found.', 'nxt-accessibility'); ?>
		</p>
		<?php
	}

	/**
	 * Render the target class field.
	 *
	 * @since    1.0.0
	 */
	public function target_class_callback() {
		$target_class = isset($this->options['target_class']) ? $this->options['target_class'] : '';
		?>
		<input type="text" name="nxt_accessibility_options[target_class]" value="<?php echo esc_attr($target_class); ?>" class="regular-text">
		<p class="description">
			<?php _e('CSS class to target if no ID is found (without the dot). Used only if the Target ID is not found.', 'nxt-accessibility'); ?>
		</p>
		<?php
	}

	/**
	 * Render the link text field.
	 *
	 * @since    1.0.0
	 */
	public function link_text_callback() {
		$link_text = isset($this->options['link_text']) ? $this->options['link_text'] : __('Skip to content', 'nxt-accessibility');
		?>
		<input type="text" name="nxt_accessibility_options[link_text]" value="<?php echo esc_attr($link_text); ?>" class="regular-text">
		<p class="description">
			<?php _e('The text that will be displayed in the skip link.', 'nxt-accessibility'); ?>
		</p>
		<?php
	}

	/**
	 * Render the link style field.
	 *
	 * @since    1.0.0
	 */
	public function link_style_callback() {
		$link_style = isset($this->options['link_style']) ? $this->options['link_style'] : 'default';
		?>
		<select name="nxt_accessibility_options[link_style]">
			<option value="default" <?php selected($link_style, 'default'); ?>><?php _e('Default (Hidden until focused)', 'nxt-accessibility'); ?></option>
			<option value="visible" <?php selected($link_style, 'visible'); ?>><?php _e('Always Visible', 'nxt-accessibility'); ?></option>
			<option value="minimal" <?php selected($link_style, 'minimal'); ?>><?php _e('Minimal', 'nxt-accessibility'); ?></option>
			<option value="high-contrast" <?php selected($link_style, 'high-contrast'); ?>><?php _e('High Contrast', 'nxt-accessibility'); ?></option>
			<option value="custom" <?php selected($link_style, 'custom'); ?>><?php _e('Custom (Use custom CSS)', 'nxt-accessibility'); ?></option>
		</select>
		<p class="description">
			<?php _e('The visual style of the skip link.', 'nxt-accessibility'); ?>
		</p>
		<?php
	}
	
	/**
	 * Render the enable focus improvements field.
	 *
	 * @since    1.0.0
	 */
	public function enable_focus_improvements_callback() {
		$enable_focus_improvements = isset($this->options['enable_focus_improvements']) ? $this->options['enable_focus_improvements'] : 'yes';
		?>
		<label>
			<input type="checkbox" name="nxt_accessibility_options[enable_focus_improvements]" value="yes" <?php checked($enable_focus_improvements, 'yes'); ?>>
			<?php _e('Enable focus outline improvements for keyboard navigation', 'nxt-accessibility'); ?>
		</label>
		<p class="description">
			<?php _e('When enabled, adds visible outlines to focused elements for better keyboard navigation.', 'nxt-accessibility'); ?>
		</p>
		<?php
	}
	
	/**
	 * Render the primary color field.
	 *
	 * @since    1.0.0
	 */
	public function primary_color_callback() {
		$primary_color = isset($this->options['primary_color']) ? $this->options['primary_color'] : '#6200ff';
		?>
		<input type="text" name="nxt_accessibility_options[primary_color]" value="<?php echo esc_attr($primary_color); ?>" class="nxt-color-picker">
		<p class="description">
			<?php _e('Primary color used for focus outlines and accents.', 'nxt-accessibility'); ?>
		</p>
		<?php
	}
	
	/**
	 * Render the text color field.
	 *
	 * @since    1.0.0
	 */
	public function text_color_callback() {
		$text_color = isset($this->options['text_color']) ? $this->options['text_color'] : '#ffffff';
		?>
		<input type="text" name="nxt_accessibility_options[text_color]" value="<?php echo esc_attr($text_color); ?>" class="nxt-color-picker">
		<p class="description">
			<?php _e('Text color for the skip link.', 'nxt-accessibility'); ?>
		</p>
		<?php
	}
	
	/**
	 * Render the background color field.
	 *
	 * @since    1.0.0
	 */
	public function background_color_callback() {
		$background_color = isset($this->options['background_color']) ? $this->options['background_color'] : '#000000';
		?>
		<input type="text" name="nxt_accessibility_options[background_color]" value="<?php echo esc_attr($background_color); ?>" class="nxt-color-picker">
		<p class="description">
			<?php _e('Background color for the skip link.', 'nxt-accessibility'); ?>
		</p>
		<?php
	}
	
	/**
	 * Render the highlight color field.
	 *
	 * @since    1.0.0
	 */
	public function highlight_color_callback() {
		$highlight_color = isset($this->options['highlight_color']) ? $this->options['highlight_color'] : '#ffff00';
		?>
		<input type="text" name="nxt_accessibility_options[highlight_color]" value="<?php echo esc_attr($highlight_color); ?>" class="nxt-color-picker">
		<p class="description">
			<?php _e('Highlight color used for high contrast elements.', 'nxt-accessibility'); ?>
		</p>
		<?php
	}
	
	/**
	 * Render the outline width field.
	 *
	 * @since    1.0.0
	 */
	public function outline_width_callback() {
		$outline_width = isset($this->options['outline_width']) ? $this->options['outline_width'] : '2';
		?>
		<input type="number" min="1" max="10" name="nxt_accessibility_options[outline_width]" value="<?php echo esc_attr($outline_width); ?>" class="small-text">
		<p class="description">
			<?php _e('Width of the focus outline in pixels.', 'nxt-accessibility'); ?>
		</p>
		<?php
	}
	
	/**
	 * Render the outline offset field.
	 *
	 * @since    1.0.0
	 */
	public function outline_offset_callback() {
		$outline_offset = isset($this->options['outline_offset']) ? $this->options['outline_offset'] : '2';
		?>
		<input type="number" min="0" max="10" name="nxt_accessibility_options[outline_offset]" value="<?php echo esc_attr($outline_offset); ?>" class="small-text">
		<p class="description">
			<?php _e('Offset of the focus outline in pixels.', 'nxt-accessibility'); ?>
		</p>
		<?php
	}

	/**
	 * Render the custom styles field.
	 *
	 * @since    1.0.0
	 */
	public function custom_styles_callback() {
		$custom_styles = isset($this->options['custom_styles']) ? $this->options['custom_styles'] : '';
		?>
		<textarea name="nxt_accessibility_options[custom_styles]" rows="10" cols="50" class="large-text code"><?php echo esc_textarea($custom_styles); ?></textarea>
		<p class="description">
			<?php _e('Custom CSS for further customization. Use the .nxt-skip-link class to target the skip link.', 'nxt-accessibility'); ?>
		</p>
		<?php
	}

	/**
	 * Render the settings page.
	 *
	 * @since    1.0.0
	 */
	public function display_settings_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields($this->plugin_name);
				do_settings_sections($this->plugin_name);
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
} 