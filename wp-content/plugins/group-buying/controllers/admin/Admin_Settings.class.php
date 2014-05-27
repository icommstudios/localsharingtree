<?php 

/**
* Settings Controller
*/
class GB_Admin_Settings extends Group_Buying_Controller {
	const DEFAULT_SETTINGS = 'general';
	private static $admin_pages = array();
	private static $options = array();
	private static $option_tabs = array();
	protected static $settings_page;

	public static function get_admin_pages() {
		return self::$admin_pages;
	}

	public static function get_settings_page() {
		return self::$settings_page;
	}

	public static function get_option_tabs() {
		return self::$option_tabs;
	}

	public static function get_setting_options() {
		return self::$options;
	}

	public static function init() {

		// Register Admin Pages
		add_action( 'gb_settings_page', array( get_class(), 'register_page' ) );

		// Register Settings
		add_action( 'gb_settings', array( get_class(), 'register_settings' ), 10, 2 );

		// Build Menus
		add_action( 'admin_menu', array( get_class(), 'add_admin_page' ), 10, 0 );

		// Build Menus
		add_action( 'admin_init', array( get_class(), 'add_options' ), 5, 0 );

		// AJAX Actions
		add_action( 'wp_ajax_gb_save_options', array( get_class(), 'maybe_save_options_via_ajax' ) );

		// Meta Box API

		// Utility Actions
		add_action( 'gb_settings_tabs', array( get_class(), 'display_settings_tabs' ) );
	}

	//////////////////
	// Admin Pages //
	//////////////////

	/**
	 * Register a settings sub-page in the plugin's menu
	 *
	 * @static
	 * @param string  $slug
	 * @param string  $title
	 * @param string  $menu_title
	 * @param string  $weight
	 * @return string The menu slug that will be used for the page
	 */
	public function register_page( $args ) {

		$defaults = array(
			'parent' => '',
			'slug' => 'undefined_slug',
			'title' => 'Undefined Title',
			'menu_title' => 'Undefined Menu Title',
			'weight' => 10,
			'reset' => FALSE, 
			'section' => 'theme', 
			'show_tabs' => TRUE,
			'callback' => NULL,
			'ajax' => FALSE,
			'ajax_full_page' => FALSE
		);
		$parsed_args = wp_parse_args( $args, $defaults );
		extract( $parsed_args );

		$page = self::TEXT_DOMAIN.'/'.$slug;
		self::$option_tabs[] = array(
			'slug' => $slug,
			'title' => $menu_title,
			'weight' => $weight,
			'section' => $section
		);
		self::$admin_pages[$page] = array(
			'parent' => $parent,
			'title' => $title,
			'menu_title' => $menu_title,
			'weight' => $weight,
			'ajax' => $ajax,
			'ajax_full_page' => $ajax_full_page,
			'reset' => $reset,
			'section' => $section,
			'callback' => $callback
		);
		return $page;
	}

	public function register_settings( $settings = array(), $page = '' ) {
		if ( $page == '' ) {
			$page = self::DEFAULT_SETTINGS;
		}
		if ( !isset( self::$options[$page] ) ) {
			self::$options[$page] = array();
		}
		self::$options[$page] = wp_parse_args( self::$options[$page], $settings );
	}

	/**
	 * Creates the main admin page, and any registered sub-pages
	 *
	 * @static
	 * @return void
	 */
	public static function add_admin_page() {
		
		// Add parent menu for GBS
		self::$settings_page = add_menu_page( self::__( 'Group Buying Options' ), self::__( 'Group Buying' ), 'manage_options', self::TEXT_DOMAIN, array( get_class(), 'default_admin_page' ), GB_URL . '/resources/img/gbs.png', 3 );
		
		// Sort submenus
		uasort( self::$admin_pages, array( get_class(), 'sort_by_weight' ) );
		// Add submenus
		foreach ( self::$admin_pages as $page => $data ) {
			$parent = ( $data['parent'] != '' ) ? $data['parent'] : self::TEXT_DOMAIN ;
			$callback = ( is_callable( $data['callback'] ) ) ? $data['callback'] : array( get_class(), 'default_admin_page' ) ;
			$hook = add_submenu_page( $parent, $data['title'], self::__( $data['menu_title'] ), 'manage_options', $page, $callback );
			self::$admin_pages[$page]['hook'] = $hook;
		}
	}

	/**
	 * Displays an admin/settings page
	 *
	 * @static
	 * @return void
	 */
	public static function default_admin_page() {
		if ( !current_user_can( 'manage_options' ) ) {
			return; // not allowed to view this page
		}
		$plugin_page = $_GET['page'];
		$title = ( isset( self::$admin_pages[$plugin_page]['title'] ) ) ? self::$admin_pages[$plugin_page]['title'] : '' ;
		$ajax = isset(self::$admin_pages[$plugin_page]['ajax'])?self::$admin_pages[$plugin_page]['ajax']:'';
		$ajax_full_page = isset(self::$admin_pages[$plugin_page]['ajax_full_page'])?self::$admin_pages[$plugin_page]['ajax_full_page']:'';
		$reset = isset(self::$admin_pages[$plugin_page]['reset'])?self::$admin_pages[$plugin_page]['reset']:'';
		$section = isset(self::$admin_pages[$plugin_page]['section'])?self::$admin_pages[$plugin_page]['section']:'';
		self::load_view( 'admin/settings', array(
				'title' => self::__($title),
				'page' => $plugin_page,
				'ajax' => $ajax,
				'ajax_full_page' => $ajax_full_page,
				'reset' => $reset,
				'section' => $section
			), FALSE );
	}

	/**
	 * Build the tabs for all the admin settings
	 * @param  string $plugin_page slug for settings page
	 * @return string              html
	 */
	public function display_settings_tabs( $plugin_page = 0 ) {
		if ( !$plugin_page ) {
			$plugin_page = ( $_GET['page'] == self::TEXT_DOMAIN ) ? self::TEXT_DOMAIN.'/gb_settings' : $_GET['page'] ;
		}
		$tabs = apply_filters( 'gb_option_tabs', self::$option_tabs );
		uasort( $tabs, array( get_class(), 'sort_by_weight' ) );
		$section = self::$admin_pages[$plugin_page]['section'];
		$tabbed = array();
		foreach ( $tabs as $tab => $data ):
			if ( $data['section'] == $section && !in_array( $data['slug'], $tabbed ) ) {
				$current_page = ( isset( $_GET['page'] ) ) ? str_replace( 'group-buying/', '', $_GET['page'] ) : 'gb_settings';
				$new_title = self::__( str_replace( 'Settings', '', $data['title'] ) );
				$current = ( $current_page == $data['slug'] ) ? ' nav-tab-active' : '';
				echo '<a href="admin.php?page=group-buying/'.$data['slug'].'" class="nav-tab'.$current.'" id="gb_options_tab_'.$data['slug'].'">'.$new_title.'</a>';
				$tabbed[] = $data['slug'];
			}
		endforeach;
	}

	//////////////
	// Options //
	//////////////

	public function add_options() {
		foreach ( self::$options as $page => $sections ) {
			$page = self::TEXT_DOMAIN.'/'.$page;
			// Build Section
			uasort( $sections, array( get_class(), 'sort_by_weight' ) );
			foreach ( $sections as $section_id => $section_args ) {
				$display = ( isset( $section_args['callback'] ) && is_callable( $section_args['callback'] ) ) ? $section_args['callback'] : array( get_class(), 'display_settings_section' ) ;
				$title = ( isset( $section_args['title'] ) ) ? $section_args['title'] : '' ;
				add_settings_section( $section_id, $title, $display, $page );

				// Build settings
				foreach ( $section_args['settings'] as $setting => $setting_args ) {
					// register setting
					$sanitize_callback = ( isset( $setting_args['sanitize_callback'] ) && is_callable( $setting_args['sanitize_callback'] ) ) ? $setting_args['sanitize_callback'] : '' ;
					register_setting( $page, $setting, $sanitize_callback );
					// register display callback
					$title = ( isset( $setting_args['label'] ) ) ? $setting_args['label'] : '' ;
					$callback = ( is_callable( $setting_args['option'] ) ) ? $setting_args['option'] : array( get_class(), 'option_field' );
					$setting_args['name'] = $setting;
					add_settings_field( $setting, $title, $callback, $page, $section_id, $setting_args );
				}
			}
		}
	}

	/**
	 * For most settings sections, there's nothing special to display.
	 * This function will display just that. Use it as a callback for
	 * add_settings_section().
	 *
	 * @return void
	 */
	public function display_settings_section() {}

	public function option_field( $args ) {
		$name = $args['name'];
		$out = '';
		if ( $args['option']['type'] != 'checkbox' ) {
			$out .= self::setting_form_label( $name, $args['option'] );
			$out .= self::setting_form_field( $name, $args['option'] );
		}
		else {
			$out .= '<label for="'.$name.'">'.self::setting_form_field( $name, $args['option'] ).' '.$args['option']['label'].'</label>';
			if ( !empty( $args['option']['description'] ) ) {
				$out .= '<p class="description help_block">'.$args['option']['description'].'</p>';
			}
		}
		print apply_filters( 'gb_settings_option_field', $out, $name, $args );
	}

	function setting_form_label( $name, $data ) {
		$out = '';
		if ( isset( $data['label'] ) ) {
			$out = '<label for="'.$name.'">'.$data['label'].'</label>';	
		}
		return apply_filters( 'gb_admin_settings_form_label', $out, $name, $data );
	}

	public static function setting_form_field( $name, $data ) {
		if ( !isset( $data['attributes'] ) || !is_array( $data['attributes'] ) ) {
			$data['attributes'] = array();
		}
		ob_start(); ?>

		<?php if ( $data['type'] == 'textarea' ): ?>
			<textarea type="textarea" name="<?php echo $name; ?>" id="<?php echo $name; ?>" rows="<?php echo isset( $data['rows'] )?$data['rows']:4; ?>" cols="<?php echo isset( $data['cols'] )?$data['cols']:40; ?>" class="small-text code" <?php foreach ( $data['attributes'] as $attr => $attr_value ) { echo $attr.'="'.$attr_value.'" '; } ?> <?php if ( isset( $data['required'] ) && $data['required'] ) echo 'required'; ?>><?php echo $data['default']; ?></textarea>
		<?php elseif ( $data['type'] == 'select-state' ):  // FUTURE AJAX based on country selection  ?>
			<select type="select" name="<?php echo $name; ?>" id="<?php echo $name; ?>" class="regular-text" <?php foreach ( $data['attributes'] as $attr => $attr_value ) { echo $attr.'="'.$attr_value.'" '; } ?> <?php if ( isset( $data['required'] ) && $data['required'] ) echo 'required'; ?>>
				<?php foreach ( $data['options'] as $group => $states ) : ?>
					<optgroup label="<?php echo $group ?>">
						<?php foreach ( $states as $option_key => $option_label ): ?>
							<option value="<?php echo $option_key; ?>" <?php selected( $option_key, $data['default'] ) ?>><?php echo $option_label; ?></option>
						<?php endforeach; ?>
					</optgroup>
				<?php endforeach; ?>
			</select>
		<?php elseif ( $data['type'] == 'select' ): ?>
			<select type="select" name="<?php echo $name; ?>" id="<?php echo $name; ?>" <?php foreach ( $data['attributes'] as $attr => $attr_value ) { echo $attr.'="'.$attr_value.'" '; } ?> <?php if ( isset( $data['required'] ) && $data['required'] ) echo 'required'; ?>>
				<?php foreach ( $data['options'] as $option_key => $option_label ): ?>
				<option value="<?php echo $option_key; ?>" <?php selected( $option_key, $data['default'] ) ?>><?php echo $option_label; ?></option>
				<?php endforeach; ?>
			</select>
		<?php elseif ( $data['type'] == 'multiselect' ): ?>
			<select type="select" name="<?php echo $name; ?>[]" id="<?php echo $name; ?>" <?php foreach ( $data['attributes'] as $attr => $attr_value ) { echo $attr.'="'.$attr_value.'" '; } ?> multiple="multiple" <?php if ( isset( $data['required'] ) && $data['required'] ) echo 'required'; ?>>
				<?php foreach ( $data['options'] as $option_key => $option_label ): ?>
					<option value="<?php echo $option_key; ?>" <?php if ( in_array( $option_key, $data['default'] ) ) echo 'selected="selected"' ?>><?php echo $option_label; ?></option>
				<?php endforeach; ?>
			</select>
		<?php elseif ( $data['type'] == 'radios' ): ?>
			<?php foreach ( $data['options'] as $option_key => $option_label ): ?>
				<label for="<?php echo $name; ?>_<?php esc_attr_e( $option_key ); ?>"><input type="radio" name="<?php echo $name; ?>" id="<?php echo $name; ?>_<?php esc_attr_e( $option_key ); ?>" value="<?php esc_attr_e( $option_key ); ?>" <?php checked( $option_key, $data['default'] ) ?> />&nbsp;<?php _e( $option_label ); ?></label>
				<br />
			<?php endforeach; ?>
		<?php elseif ( $data['type'] == 'checkbox' ): ?>
			<input type="checkbox" name="<?php echo $name; ?>" id="<?php echo $name; ?>" <?php checked( $data['value'], $data['default'] ); ?> value="<?php echo isset( $data['value'] )?$data['value']:'On'; ?>" <?php foreach ( $data['attributes'] as $attr => $attr_value ) { echo $attr.'="'.$attr_value.'" '; } ?> <?php if ( isset( $data['required'] ) && $data['required'] ) echo 'required'; ?>/>
		<?php elseif ( $data['type'] == 'hidden' ): ?>
			<input type="hidden" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo $data['value']; ?>" <?php foreach ( $data['attributes'] as $attr => $attr_value ) { echo $attr.'="'.$attr_value.'" '; } ?> />
		<?php elseif ( $data['type'] == 'file' ): ?>
			<input type="file" name="<?php echo $name; ?>" id="<?php echo $name; ?>" <?php if ( isset( $data['required'] ) && $data['required'] ) echo 'required'; ?>/>
		<?php elseif ( $data['type'] == 'pages' ): ?>
			<?php 
				$defaults = array( 
					'name' => $name, 
					'echo' => 1, 
					'show_option_none' => self::__( '-- Select --' ), 
					'option_none_value' => '0', 
					'selected' => $data['default'] 
					);
				$parsed_args = wp_parse_args( $data['args'], $defaults );
				wp_dropdown_pages( $parsed_args ); ?>
		<?php elseif ( $data['type'] == 'bypass' ): ?>
			<?php echo $data['output']; ?>
		<?php else: ?>
			<input type="<?php echo $data['type']; ?>" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo $data['default']; ?>" placeholder="<?php echo isset( $data['placeholder'] )?$data['placeholder']:''; ?>" size="<?php echo isset( $data['size'] )?$data['size']:40; ?>" <?php foreach ( $data['attributes'] as $attr => $attr_value ) { echo $attr.'="'.$attr_value.'" '; } ?> <?php if ( isset( $data['required'] ) && $data['required'] ) echo 'required'; ?> class="text-input" />
		<?php endif; ?>

		<?php if ( $data['type'] != 'checkbox' && !empty( $data['description'] ) ): ?>
			<p class="description help_block"><?php echo $data['description'] ?></p>
		<?php endif; ?>
		<?php
		return apply_filters( 'gb_admin_settings_form_field', ob_get_clean(), $name, $data );
	}

	///////////////////
	// AJAX Methods //
	///////////////////

	public function maybe_save_options_via_ajax() {
		if ( is_admin() ) {
			if ( !isset( $_POST['options'] ) )
				return;
			
			// unserialize
			wp_parse_str( $_POST['options'], $options );
			// Confirm the form was an update
			if ( isset( $options['action'] ) && $options['action'] == 'update' ) {
				$option_page = ( isset( $options['option_page'] ) ) ? $options['option_page'] : 'general' ;

				// capability check
				$capability = apply_filters( "option_page_capability_{$option_page}", 'manage_options' );
				if ( !current_user_can( $capability ) )
					wp_die(__('Cheatin&#8217; uh?'));

				self::update_options( $options, $option_page );
				echo apply_filters( 'save_options_via_ajax_message', self::__('Saved'), $option_page );
				exit();
			}
		}
	}

	public function update_options( $submission = array(), $option_page = '' ) {
		global $wp_settings_fields;

		if ( !isset( $wp_settings_fields[$option_page] ) )
			return;

		if ( isset( $wp_settings_fields[$option_page] ) && !empty( $wp_settings_fields[$option_page] ) ) {
			foreach ( $wp_settings_fields[$option_page] as $section ) {
				foreach ( $section as $option => $values ) {
					$option = trim( $option );
					$value = null;
					if ( isset( $submission[ $option ] ) ) {
						$value = $submission[ $option ];
						if ( ! is_array( $value ) )
							$value = trim( $value );
						$value = wp_unslash( $value );
					}
					update_option( $option, $value );
				}
			}
		}
	}

}
