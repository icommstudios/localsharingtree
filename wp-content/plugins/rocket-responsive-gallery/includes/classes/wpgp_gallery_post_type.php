<?php

class Wpgp_Gallery_Post_Type {

	function __construct( $do_start = false ) {

		if ( $do_start )
			$this->start();

	}

	function start() {

		$this->hooks();
		$this->filters();

	}

	function hooks() {

		add_action( 'init', array( $this, 'register_post_type' ) );

		//print stylesheet only if post type is GALLERY_PLUGIN_GALLERY_POST_TYPE
		//add_action( 'admin_head', array( $this, 'admin_head_stylesheet' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );

		//print script only if post type is GALLERY_PLUGIN_GALLERY_POST_TYPE
		add_action( 'admin_head', array( $this, 'admin_head_script' ) );

		//print custom column data, for slides column the number of slides in a
		//slider
		add_action( sprintf( 'manage_%s_posts_custom_column',
				GALLERY_PLUGIN_GALLERY_POST_TYPE ),
			array( $this, 'custom_columns' ),
			10, 2  );


		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		//remove meta boxes from GALLERY_PLUGIN_GALLERY_POST_TYPE post type
		add_action( 'admin_menu', array( $this, 'remove_meta_boxes' ) );

		add_action( 'save_post', array( $this, 'save_post' ) );

		add_action( 'admin_footer', array( $this, 'intro_box' ) );

	}

	function filters() {

		add_filter( 'post_updated_messages',
			array( $this, 'custom_update_messages' ) );

		add_filter( 'post_row_actions',
			array( $this, 'remove_post_row_actions' ) );

		//remove the column date and add new custom column slides
		add_filter( sprintf( 'manage_edit-%s_columns',
				GALLERY_PLUGIN_GALLERY_POST_TYPE ),
			array( $this, 'modify_custom_columns' ) );


		add_filter( 'attachment_fields_to_edit',
			array( $this, 'remove_media_upload_fields' ), 10, 2 );

		add_filter( 'attachment_fields_to_save',
			array( $this,  '_save_attachment_url' ), 10, 2 );

		add_filter( 'attachment_fields_to_edit',
			array( $this, '_replace_attachment_url' ), 10, 2 );

		/**
		 * Show the 'Insert into Post' button for visual editor in
		 * slider post type for HTML slide type
		 * */
		add_filter( 'get_media_item_args',
			array( $this, 'add_insert_into_post_button' ) );

		add_filter( 'media_upload_tabs', 
			array( $this, 'remove_tab_from_media_upload_modal' ) );

	}

	function register_post_type() {

		$labels = array(
			'name' => _x( 'Gallery', 'post type general name' ),
			'singular_name' => _x( 'Gallery', 'post type singular name' ),
			'add_new' => _x( 'Add New', 'Gallery' ),
			'add_new_item' => __( 'Add New Gallery' ),
			'edit_item' => __( 'Edit Gallery' ),
			'new_item' => __( 'New Gallery' ),
			'all_items' => __( 'All Gallery' ),
			'view_item' => __( 'View Gallery' ),
			'search_items' => __( 'Search Gallery' ),
			'not_found' =>  __( 'No galleries found' ),
			'not_found_in_trash' => __( 'No galleries found in Trash' ),
			'parent_item_colon' => '',
			'menu_name' => __( 'Gallery' )

		);

		$args = array(
			'labels' => $labels,
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_menu' => false,
			'query_var' => false,
			'rewrite' => false,
			'capability_type' => 'post',
			'has_archive' => false,
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array( 'title' )
		);

		register_post_type( GALLERY_PLUGIN_GALLERY_POST_TYPE, $args );

	}

	function remove_meta_boxes() {

		remove_meta_box( 'submitdiv', GALLERY_PLUGIN_GALLERY_POST_TYPE, 'side' );

	}

	function add_meta_boxes() {

		add_meta_box(
			'wpgp_slides_meta_box',
			__( 'Slides', 'wpgp' ),
			array( $this, 'slides_meta_box' ),
			GALLERY_PLUGIN_GALLERY_POST_TYPE,
			'normal',
			'high'
		);

		add_meta_box(
			'wpgp_custom_publish_meta_box',
			__( 'Save', 'wpgp' ),
			array( $this, 'custom_publish_meta_box' ),
			GALLERY_PLUGIN_GALLERY_POST_TYPE,
			'side'
		);

		add_meta_box(
			'wpgp_shortcode_meta_box',
			__( 'Shortcode', 'wpgp' ),
			array( $this, 'shortcode_meta_box' ),
			GALLERY_PLUGIN_GALLERY_POST_TYPE,
			'side'
		);

		add_meta_box(
			'wpgp_banner_meta_box',
			__( 'Premium Version', 'wpgp' ),
			array( $this, 'banner_meta_box' ),
			GALLERY_PLUGIN_GALLERY_POST_TYPE,
			'side'
		);

		add_meta_box(
			'wpgp_slider_options_metabox',
			__( 'Options', 'wpgp' ),
			array( $this, 'options_meta_box' ),
			GALLERY_PLUGIN_GALLERY_POST_TYPE
		);

	}

	function slides_meta_box( $post ) {

		$gallery_id = $post->ID;

		$slides = get_post_meta( $gallery_id, 'slides', true );

		$default_slide = array(
			'label' => '',
			'type' => 'image',
			'html' => '',
			'attachment' => '',
			'alt' => '',
			'caption' => '',
			'link' => ''
		);

		if ( ! $slides )
			$slides = array();

		if ( ! $slides )
			$style_display = 'display: block';
		else
			$style_display = 'display: none';


		include wpgp_view_path( __FUNCTION__ );

	}

	function custom_publish_meta_box( $post ) {

		$gallery_id = $post->ID;

		$post_status = get_post_status( $gallery_id );
		$delete_link = get_delete_post_link( $gallery_id );

		$nonce = wp_create_nonce( 'wpgp_gallery_nonce' );

		include wpgp_view_path( __FUNCTION__ );

	}

	function shortcode_meta_box( $post ) {

		$gallery_id = $post->ID;

		if ( get_post_status( $gallery_id ) !== 'publish' ) {

			echo __( '<p>Click on the Create Gallery button to get the gallery shortcode. Insert that shortcode in your post or page content using the WP visual editor.</p>', 'wpgp' );

			return;

		}

		$gallery_title = get_the_title( $gallery_id );

		$shortcode = sprintf( "[%s id='%s' name='%s']", GALLERY_PLUGIN_GALLERY_SHORTCODE, $gallery_id, $gallery_title );

		include wpgp_view_path( __FUNCTION__ );
	}

	function banner_meta_box( $post ) {

		include wpgp_view_path( __FUNCTION__ );

	}

	function options_meta_box( $post ) {

		$gallery_id = $post->ID;

		$active_skin = get_post_meta( $gallery_id, 'skin', true );

		$skins = array();

		//default image slider skin
		$skins[] = array(
			'name' => __( 'Default Lightbox Skin', 'wpgp' ),
			'path' => 'default',
			'description' => 'Responsive lightbox'
		);

		$gallery_options = get_post_meta( $gallery_id, 'options', true );

		if ( ! $gallery_options )
			$gallery_options = self::default_options();

		$skins = $this->get_skins( $skins );

		$skins = apply_filters( 'wpgp_skins_array', $skins );

		include wpgp_view_path( __FUNCTION__ );

	}

	function save_post( $post_id ) {

		if ( ! current_user_can( 'edit_post', $post_id ) )
			return;

		if ( wp_is_post_revision( $post_id ) )
			return;
		
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;
		
		if ( ! isset( $_POST['post_type_is_wpgp_gallery'] ) )
			return;
		
		if ( ! wp_verify_nonce( $_POST['wpgp_gallery_nonce'], 'wpgp_gallery_nonce' ) )
			return;

		$gallery_id = $post_id;

		if ( ! $this->validate_page() )
			return FALSE;

		$gallery_skin = $_POST['skin'];

		$slides = array();

		$slide = array(
			'label' => NULL,
			'url' => NULL,
			'alt' => NULL,
			'link' => NULL,
			'caption' => NULL,
			'html' => NULL,
			'open_in' => 'window'
		);

		$gallery_options = $_POST['gallery_options'];

		foreach ( $gallery_options as $key => $option ):

			if ( $option === "true" )
				$gallery_options[$key] = true;

			if ( $option === "false" )
				$gallery_options[$key] = false;

		endforeach;


		if ( ! isset( $gallery_options['enableEscapeKey'] ) )
			$gallery_options['enableEscapeKey'] = false;

		if ( ! isset( $gallery_options['keyboard_nav'] ) )
			$gallery_options['keyboard_nav'] = false;

		if ( ! isset( $gallery_options['touch_nav'] ) )
			$gallery_options['touch_nav'] = false;

		if ( ! isset( $gallery_options['closeOnContentClick'] ) )
			$gallery_options['closeOnContentClick'] = false;

		if ( ! isset( $gallery_options['closeOnBgClick'] ) )
			$gallery_options['closeOnBgClick'] = false;

		if ( ! isset( $gallery_options['pause_on_hover'] ) )
			$gallery_options['pause_on_hover'] = false;

		$gallery_options = apply_filters( 'wpgp_before_save_slider_options' , $gallery_options, $gallery_id );

		if ( ! isset( $_POST['slides'] )  )
			$_POST['slides'] = $slides;


		foreach ( $_POST['slides'] as $key => $_slide ) {


			if ( ! isset( $_slide['html'] ) )
				$_slide['html'] = '';

			$slide['caption'] = $_slide['caption'];

			$slide['url'] = $_slide['attachment'];

			$slide['alt'] = $_slide['alt'];

			$slide['link'] = $_slide['link'];

			$slide['html'] = $_slide['html'];

			$slide['label'] = $_slide['label'];

			$slides[] = $slide;

		}



		update_post_meta( $gallery_id, 'slides', $slides );
		update_post_meta( $gallery_id, 'skin', $gallery_skin );
		update_post_meta( $gallery_id, 'options', $gallery_options );

		do_action( 'wpgp_save_gallery_data', $gallery_id );

	}

	function custom_update_messages( $messages ) {

		global $post;
		$messages[GALLERY_PLUGIN_GALLERY_POST_TYPE] = array(
			0 => '',
			1 =>  __( 'Gallery updated.' ),
			2 => __( 'Custom field updated.' ),
			3 => __( 'Custom field deleted.' ),
			4 => __( 'Gallery updated.' ),
			5 => isset( $_GET['revision'] ) ? sprintf( __( 'Gallery restored to revision from %s' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => __( 'Gallery created.' ),
			7 => __( 'Gallery saved.' ),
			8 => '',
			9 => sprintf( __( 'Gallery scheduled for: <strong>%1$s</strong>.' ),
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) ),
			10 => __( 'Gallery draft updated.' )
		);

		return $messages;

	}

	function remove_post_row_actions( $actions ) {

		if ( ! $this->validate_page() )
			return $actions;

		unset( $actions['view'] );
		unset( $actions['inline hide-if-no-js'] );
		unset( $actions['pgcache_purge'] );

		return $actions;

	}

	function modify_custom_columns( $columns ) {

		unset( $columns['date'] );

		return array_merge( $columns,
			array(
				'slides' => __( 'Images', 'wpgp' )
			)
		);
		
	}

	function custom_columns( $column, $gallery_id ) {

		switch ( $column ) {

		case 'slides':
			if ( ! wpgp_get_slides( $gallery_id ) )
				echo "0";
			else
				echo count( wpgp_get_slides( $gallery_id ) );

			break;

		}

	}

	function admin_head_stylesheet() {

		if ( ! $this->validate_page() )
			return FALSE;

		echo '<style>';

		include GALLERY_PLUGIN_CSS_DIRECTORY . 'wpgp_gallery.css';

		echo '</style>';

	}

	function admin_head_script() {

		if ( ! $this->validate_page() )
			return FALSE;

		echo '<script>';

		include GALLERY_PLUGIN_JS_DIRECTORY . 'wpgp_gallery.js';

		echo '</script>';

	}

	function admin_enqueue() {

		if ( ! $this->validate_page() )
			return FALSE;

		wp_enqueue_style( 'wpgp_gallery.css',
			plugins_url( 'css/wpgp_gallery.css', GALLERY_PLUGIN_MAIN_FILE ) );

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_script( 'media-upload' );

		wp_dequeue_script( 'autosave' );

	}

	function remove_media_upload_fields( $fields, $post ) {

		unset( $fields['post_content'] );
		return $fields;

	}

	function _save_attachment_url( $post, $attachment ) {

		if ( isset( $attachment['url'] ) )
			update_post_meta( $post['ID'], '_wp_attachment_url', esc_url_raw( $attachment['url'] ) );

		return $post;

	}

	function _replace_attachment_url( $form_fields, $post ) {

		if ( isset( $form_fields['url']['html'] ) ) {

			$url = get_post_meta( $post->ID, '_wp_attachment_url', true );
			if ( ! empty( $url ) )
				$form_fields['url']['html'] = preg_replace( "/value='.*?'/", "value='$url'", $form_fields['url']['html'] );

		}

		return $form_fields;

	}

	function validate_page() {

		if ( isset( $_GET['post_type'] ) )
			if ( $_GET['post_type'] == GALLERY_PLUGIN_GALLERY_POST_TYPE )
				return TRUE;

		if ( get_post_type() === GALLERY_PLUGIN_GALLERY_POST_TYPE )
				return TRUE;

		return FALSE;

	}

	public static function default_options() {

		$default_options = array(
			'slideshow' => true,
			'height' => '',
			'direction_nav' => true,
			'keyboard_nav' => true,
			'cycle_speed' => 5,
			'animation_speed' => 0.6,
			'pause_on_hover' => true,
			'width' => '',
			'closeOnContentClick' => false,
			'closeOnBgClick' => true,
			'enableEscapeKey' => true

		);

		return apply_filters( 'wpgp_default_slider_options', $default_options );

	}

	function add_insert_into_post_button( $args ) {

		if ( get_post_type() == GALLERY_PLUGIN_GALLERY_POST_TYPE )
			$args['send'] = true;

		return $args;

	}

	function get_skins( $skins = NULL ) {

		//return the skins from the wp-content/ssp_skins directory

		if ( ! $skins )
			$skins = array();

		$skins_dir_path = WP_CONTENT_DIR .
			DIRECTORY_SEPARATOR . 'ssp_skins' ;

		try {
			$skins_iterator = new RecursiveDirectoryIterator( $skins_dir_path );

			foreach ( new RecursiveIteratorIterator( $skins_iterator ) as $skin ) {
				
				if ( basename( $skin ) == 'slider.php' ) {

					$skin_data = get_plugin_data( $skin );

					$temp_skin = array();

					if ( $skin_data['Name'] == ''  )
						$temp_skin['name'] = basename( dirname( $skin ) );
					else
						$temp_skin['name'] = $skin_data['Name'];

					$temp_skin['path'] = dirname( $skin );
					$temp_skin['description'] = $skin_data['Description'];

					$skins[] = $temp_skin;

				}

			}
		} catch ( UnexpectedValueException $e ) { }

		return $skins;

	}

	function remove_tab_from_media_upload_modal( $tabs ) {

		if ( ! isset( $_GET['slider_id'] ) ) return $tabs;

		$post_type = get_post_type( $_GET['slider_id'] );

		if ( $post_type !== GALLERY_PLUGIN_GALLERY_POST_TYPE )
			return $tabs;

		unset( $tabs['type_url'] );
		unset( $tabs['gallery'] );

		return $tabs;

	}

	function intro_box() {

		if ( get_post_type() !== GALLERY_PLUGIN_GALLERY_POST_TYPE )
			return FALSE;

		include wpgp_view_path( __FUNCTION__ );

	}

}