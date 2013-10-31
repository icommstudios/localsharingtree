<?php

/*
 * Adds more control for Featured Deals (setting override by Location)
 * By StudioFidelis.com
 */
 
if ( class_exists( 'Group_Buying_Controller' ) ) {
 
	class SF_Featured_Deals extends Group_Buying_Controller {
		
		public static $todays_deal_path;
		
		public static function init() {
			//self::get_instance();
			
			// Location field
			add_action ( 'gb_location_edit_form_fields', array( get_class(), 'location_input_metabox' ), 11, 2 );
			add_action ( 'edited_terms', array( get_class(), 'save_location_meta_data' ) );
			
			//Change latest deal
			add_filter('get_gbs_latest_deal_link', array( get_class(), 'custom_get_gbs_latest_deal_link'), 999, 3);
			
			//Change dropdown to add location link
			add_filter( 'gb_list_locations_link', array( get_class(), 'custom_gb_list_locations_link'), 10, 2 );
			
			//Handle Locations (set defaults)
			add_action('wp',  array( get_class(), 'default_location_handler'), 11);
			
			// Callback to override todays deal url (bugfix, not correclty using the user's location)
			self::$todays_deal_path = trailingslashit( get_option( Group_Buying_UI::TODAYSDEAL_PATH_OPTION, 'todays-deal' ) );
			self::register_path_callback( self::$todays_deal_path, array( get_class(), 'todays_deal' ), Group_Buying_UI::TODAYSDEAL_PATH_OPTION );
		}
		
		//Bugfix, send todays deal page to home page (same as todays deal)
		public function todays_deal() {
			$featured_deal = site_url('/'); //Send to home page / featured deal
			$featured_deal = add_query_arg( array('featured' => 1), $featured_deal ); // See child theme functions for handling of ?featured=1
			wp_redirect( $featured_deal );
			exit();
		}
		
		//Change location drop down links to include query arg
		public static function custom_gb_list_locations_link($link, $slug) {
			
			return $link;
		}
		
		//Handle Locations (set defaults)
		public static function default_location_handler() {
			global $preferred_location;
			//Set to users preferred location (see: sf-account-fields)
			$current_location = $preferred_location;
			
			//change current location
			if ( isset($_GET['location']) && !empty( $_GET['location'] ) ) {
				if ( term_exists( $_GET['location'] ) ) {
					gb_set_location_preference($_GET['location']);
					$_COOKIE[ 'gb_location_preference' ] = $_GET['location']; //set for this page load too
				}
			} elseif ( isset($_COOKIE['gb_location_preference']) && !empty( $_COOKIE['gb_location_preference'] ) ) {
				$current_location = $_COOKIE['gb_location_preference'];
			} elseif ( !empty($preferred_location) ) {
				//gb_set_location_preference($preferred_location);
				$_COOKIE[ 'gb_location_preference' ] = $preferred_location; //set for this page load too
			}
		
		}
		
		/**
		 * Create Input field in deal (location) taxonomy add and edit.
		 *
		 * @return void
		 * @author Nathan Stryker
		 */
		public static function location_input_metabox( $tag ) {
			
			$override_featured_deal = get_metadata( 'location_terms', $tag->term_id, 'override_featured_deal', TRUE );
			?>
		   <?php
				$dropdown = wp_dropdown_pages( array(
					'echo' => 0,
					'post_type' => Group_Buying_Deal::POST_TYPE,
					'show_option_none' => gb__( ' -- Select a Deal -- ' ),
					'name' => 'override_featured_deal',
				) );
			?>
			<div id="location-set-featured">
				<h3><?php gb_e( 'Override Featured Deal for this Location' ); ?></h3>
				<?php if ( $dropdown != '' ): ?>
					<label style="margin-right: 25px;"><?php gb_e( 'Type a Deal ID' ); ?>: <?php echo $dropdown; ?></label>
				<?php else: ?>
					<script type="text/javascript">
						jQuery(document).ready( function($) {
							var $field = $('#location-set-featured input');
							var $span = $('#deals_name_ajax');
			
							var show_deal_name = function() {
								$span.addClass('loading_gif').empty();
								var user_id = $field.val();
								if ( !user_id ) {
									$span.removeClass('loading_gif');
									return;
								}
								$.ajax({
									type: 'POST',
									dataType: 'json',
									url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
									data: {
										action: 'gbs_ajax_get_deal_info',
										id: user_id
									},
									success: function(data) {
										$span.removeClass('loading_gif');
										$span.empty().append(data.title + ' <span style="color:silver">(deal id:' + data.deal_id + ')</span>');
									}
								});
							};
							if ( $('#location-set-featured input').length > 0 ) {
								$field.live('keyup',show_deal_name);
							}
							//run once at start
							if ( $('#location-set-featured input').length > 0 ) {
								show_deal_name();
							}
						});
					</script>
					<style type="text/css">
						.loading_gif {
							background: url( '<?php echo GB_URL; ?>/resources/img/loader.gif') no-repeat 0 center;
							width: auto;
							height: 16px;
							padding-right: 16px;
							padding-bottom: 2px;
							margin-left: 10px;
							margin-top: 10px;
						}
					</style>
					<label style="margin-right: 25px;"><?php gb_e( 'Type a Deal ID' ); ?>: <input type="text" size="8" value="<?php echo $override_featured_deal; ?>" name="override_featured_deal" placeholder="<?php gb_e('Deal ID') ?>" /></label>
				<?php endif ?>
				<br/><span id="deals_name_ajax">&nbsp;</span>
			</div>
		
			<?php
		}
	
		/**
		 * Create Save meta data to table.
		 *
		 * @return void
		 * @author Nathan Stryker
		 */
		public static function save_location_meta_data( $term_id ) {
			if ( isset( $_POST['override_featured_deal'] ) ) {
				$override_featured_deal = esc_attr( $_POST['override_featured_deal'] );
				update_metadata( 'location_terms', $term_id, 'override_featured_deal', $override_featured_deal );
			}
		}
		
	
		// Over ride featured deal
		public function custom_get_gbs_latest_deal_link( $link, $location, $return_id) {
			global $preferred_location;
			//Set to users preferred location (see: sf-account-fields)
			$current_location = $preferred_location;
			
			//Change current location
			$current_location = ($_GET[ 'location' ]) ? $_GET[ 'location' ] : ($_COOKIE[ 'gb_location_preference' ]) ? $_COOKIE[ 'gb_location_preference' ] : $current_location;
			
			if ( $current_location  ) {
				
				if ( term_exists( $current_location ) ) {
					$location = $current_location;
					
					//Get override (if any)
					if ( $location ) {
						
						$override_deal_id = self::get_override_deal($location);
						if ( $override_deal_id ) {
							if ( $return_id ) {
								return $override_deal_id;
							}
							$link = get_permalink( $override_deal_id );
							return $link;
						}
					}
					
				}
			}
			
			return $link;
		}
		
		public function get_override_deal($location) {
			$return_deal_id = false;
			$override_featured_deal = false;
			
			$term = get_term_by('slug', $location, gb_get_deal_location_tax());
			if ( $term && !is_wp_error($term) ) {
				$override_featured_deal = get_metadata( 'location_terms', $term->term_id, 'override_featured_deal', TRUE );
			}
			
			if ( $override_featured_deal ) {
				//Get the deal (also ensure its not expired yet)
				$args=array(
					'post_type' => gb_get_deal_post_type(),
					'p' => $override_featured_deal,
					'post_status' => 'publish',
					'showposts' => 1,
					'meta_query' => array(
						array(
							'key' => '_expiration_date',
							'value' => array( 0, current_time( 'timestamp' ) ),
							'compare' => 'NOT BETWEEN'
						)
					)
				);
				$latest_deal = get_posts( $args );
				if ( !empty( $latest_deal ) ) {
					foreach ( $latest_deal as $post ) :
						set_transient( 'gb_latest_deal_id_'.$location, $post->ID, 60*2 );
						$return_deal_id = $post->ID;
					endforeach;
				}
			}
			
			return $return_deal_id;
		}
		
		/*
		 * Singleton Design Pattern
		 * ------------------------------------------------------------- */
		private function __clone() {
			// cannot be cloned
			trigger_error( __CLASS__.' may not be cloned', E_USER_ERROR );
		}
		private function __sleep() {
			// cannot be serialized
			trigger_error( __CLASS__.' may not be serialized', E_USER_ERROR );
		}
		public static function get_instance() {
			if ( !( self::$instance && is_a( self::$instance, __CLASS__ ) ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}
	}
}
add_action( 'init', array( 'SF_Featured_Deals', 'init' ), 99 );