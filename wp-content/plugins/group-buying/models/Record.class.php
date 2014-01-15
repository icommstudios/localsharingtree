<?php

/**
 * GBS Record Model
 *
 * @package GBS
 * @subpackage Record
 */
class Group_Buying_Record extends Group_Buying_Post_Type {

	const POST_TYPE = 'gb_record';
	const TAXONOMY = 'gb_record_type';
	const DEFAULT_TYPE = 'no_type_set';

	private static $instances = array();

	public static function init() {
		$post_type_args = array(
			'has_archive' => FALSE,
			'show_in_menu' => FALSE,
			'rewrite' => FALSE,
		);
		self::register_post_type( self::POST_TYPE, 'Record', 'Records', $post_type_args );

		// register Locations taxonomy
		$singular = 'Record Type';
		$plural = 'Record Types';
		$taxonomy_args = array(
			'hierarchical' => TRUE,
			'public' => FALSE,
			'show_ui' => FALSE
		);
		self::register_taxonomy( self::TAXONOMY, array( self::POST_TYPE ), $singular, $plural, $taxonomy_args );
	}

	protected function __construct( $id ) {
		parent::__construct( $id );
	}

	/**
	 *
	 *
	 * @static
	 * @param int     $id
	 * @return Group_Buying_Gift
	 */
	public static function get_instance( $id = 0 ) {
		if ( !$id )
			return NULL;

		if ( !isset( self::$instances[$id] ) || !self::$instances[$id] instanceof self )
			self::$instances[$id] = new self( $id );

		if ( !isset( self::$instances[$id]->post->post_type ) )
			return NULL;

		if ( self::$instances[$id]->post->post_type != self::POST_TYPE )
			return NULL;

		return self::$instances[$id];
	}

	public function activate() {
		$this->post->post_status = 'publish';
		$this->save_post();
		do_action( 'record_activated', $this );
	}

	/**
	 *
	 *
	 * @return int The ID of the content associated with this record
	 */
	public function get_associate_id() {
		$associate_id = $this->post->post_parent;
		return $associate_id;
	}

	/**
	 * Associate this record with content
	 *
	 * @param int     $id The new value
	 * @return int The ID of the content associated with this record
	 */
	public function set_associate_id( $associate_id ) {
		$this->post->post_parent = $associate_id;
		$this->save_post();
		return $associate_id;
	}

	/**
	 *
	 *
	 * @return array The data
	 */
	public function get_data() {
		return maybe_unserialize( $this->post->post_content );
	}

	/**
	 * Set data
	 *
	 * @param array   The data
	 * @return array The data
	 */
	public function set_data( $data ) {
		// maybe_serialize will create an warning about __sleep preventing an object from serializing,
		// in tests this doesn't create an error.
		$this->post->post_content = @maybe_serialize( $data );
		$this->save_post();
		return $data;
	}


	/**
	 *
	 *
	 * @return array The type
	 */
	public function get_type() {
		$terms = wp_get_object_terms( $this->ID, self::TAXONOMY );
		if ( empty( $terms ) ) {
			return self::set_type( self::DEFAULT_TYPE );	
		}
		$type_term = array_pop( $terms );
		return $type_term->slug;
	}

	/**
	 * Set type
	 *
	 * @param array   The type
	 * @return array The type
	 */
	public function set_type( $type ) {
		$slug = self::maybe_add_type( $type );
		wp_set_object_terms( $this->ID, $slug, self::TAXONOMY );
		return $slug;
	}

	/**
	 * Check if type exists as a term, if not create one.
	 *
	 * @param string  $type
	 * @return
	 */
	public static function maybe_add_type( $type = '', $name = '' ) {
		$type = ( $type == '' ) ? self::DEFAULT_TYPE : $type ;
		$term = get_term_by( 'slug', $type, self::TAXONOMY );
		if ( !empty( $term->slug ) ) {
			return $term->slug;
		} else {
			$name = ( $name != '' ) ? $name : $type;
			$insert = wp_insert_term(
				$name, // the term name
				self::TAXONOMY, // the taxonomy
				array( 'slug' => $type )
			);
			if ( isset( $insert->slug ) )
				return $insert->slug;
		}
	}

	public static function register_type( $type, $name = '' ) {
		$type_slug = self::maybe_add_type( $type, $name );
		return $type_slug;
	}

	/**
	 *
	 *
	 * @param int     $type the associate content id
	 * @return array List of IDs for records of this type
	 */
	public static function get_records_by_type_and_association( $associate_id, $type ) {
		// see if we've cached the result
		$cache_key = 'gbs_find_records_by_type_and_assoc_id';
		$cache_index = $type.$associate_id;
		$cache = wp_cache_get( $cache_key, 'gbs' );
		if ( is_array( $cache ) && isset( $cache[$cache_index] ) ) {
			return $cache[$cache_index];
		}

		$args = array(
			'post_type' => self::POST_TYPE,
			'post_status' => 'any',
			'posts_per_page' => -1,
			'post_parent' => $associate_id,
			'fields' => 'ids',
			'gb_bypass_filter' => TRUE,
			self::TAXONOMY => $type
		);

		$result = get_posts( $args );

		// Set cache
		$cache[$cache_index] = $result;
		wp_cache_set( $cache_key, $cache, 'gbs' );

		return $result;
	}


	/**
	 *
	 *
	 * @param int     $type the associate content id
	 * @return array List of IDs for records of this type
	 */
	public static function get_records_by_type( $type ) {
		// see if we've cached the result
		$cache_key = 'gbs_find_records_by_type';
		$cache_index = $type;
		$cache = wp_cache_get( $cache_key, 'gbs' );
		if ( is_array( $cache ) && isset( $cache[$cache_index] ) ) {
			return $cache[$cache_index];
		}

		$args = array(
			'post_type' => self::POST_TYPE,
			'post_status' => 'any',
			'posts_per_page' => -1,
			'fields' => 'ids',
			'gb_bypass_filter' => TRUE,
			self::TAXONOMY => $type
		);

		$result = get_posts( $args );

		// Set cache
		$cache[$cache_index] = $result;
		wp_cache_set( $cache_key, $cache, 'gbs' );

		return $result;
	}

	/**
	 *
	 *
	 * @param int     $associate_id the associate content id
	 * @return array List of IDs for records with this association
	 */
	public static function get_records_by_association( $associate_id ) {
		// see if we've cached the result
		$cache_key = 'gbs_find_records_by_assoc_id';
		$cache_index = $associate_id;
		$cache = wp_cache_get( $cache_key, 'gbs' );
		if ( is_array( $cache ) && isset( $cache[$cache_index] ) ) {
			return $cache[$cache_index];
		}

		$args = array(
			'post_type' => self::POST_TYPE,
			'post_status' => 'any',
			'posts_per_page' => -1,
			'post_parent' => $associate_id,
			'fields' => 'ids',
			'gb_bypass_filter' => TRUE
		);

		$result = get_posts( $args );

		// Set cache
		$cache[$cache_index] = $result;
		wp_cache_set( $cache_key, $cache, 'gbs' );

		return $result;
	}
}
