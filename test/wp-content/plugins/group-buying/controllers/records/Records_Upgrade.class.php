<?php


class Group_Buying_Records_Upgrade extends Group_Buying {

	/**
	 * Deprecated Meta
	 * @var array
	 */
	private static $meta_keys = array(
		'associate_id' => '_associate', // int
		'data' => '_data', // string
		'type' => '_type', // string
	);

	public static function upgrade_4_6() {
		
		$records = self::get_old_records();
		
		if ( empty( $records ) )
			return TRUE;

		foreach ( $records as $post_id ) {
			$record = Group_Buying_Record::get_instance( $post_id );
			
			// get old post meta
			$associate_id = $record->get_post_meta( self::$meta_keys['associate_id'] );
			$data = $record->get_post_meta( self::$meta_keys['data'] );
			$type = $record->get_post_meta( self::$meta_keys['type'] );

			$record->set_associate_id( $associate_id );
			$record->set_data( $data );
			$record->set_type( $type );

			// delete post meta
			$record->delete_post_meta( array(
					self::$meta_keys['associate_id'] => $associate_id
				) );
			$record->delete_post_meta( array(
					self::$meta_keys['data'] => $data
				) );
			$record->delete_post_meta( array(
					self::$meta_keys['type'] => $type
				) );
		}
		return FALSE;
	}

	public function get_old_records() {
		return get_posts( array(
				'numberposts' => 50,
				'post_status' => 'any',
				'post_type' => Group_Buying_Record::POST_TYPE,
				'fields' => 'ids',
				'meta_query' => array(
					array(
						'key' => self::$meta_keys['type'],
						'compare' => 'EXISTS'
					)
				)
			) );
	}
}
