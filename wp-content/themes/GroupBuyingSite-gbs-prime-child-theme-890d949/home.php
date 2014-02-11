<?php 
/**
* Template Name: Home page (landing page)
**/
get_header(); ?>

	<div id="home_page" class="container text-center clearfix">

		<img src="<?php gb_header_logo(); ?>" />
		
		<div id="content" class="home prime clearfix">
			
			<div class="mini_header home text-center">
			
				<div id="subscription_form" class="clearfix">
					<?php //gb_subscription_form() ?>
                    
                    	<form action="" id="gb_subscription_form" method="get" class="clearfix">
                          
                          <?php
							$locations = gb_get_locations( false );
							//$no_city_text = get_option( Group_Buying_List_Services::SIGNUP_CITYNAME_OPTION );
							if ( !empty( $locations ) ) {
								?>
									<span class="option location_options_wrap clearfix">
										<h2><label for="locations"><?php gb_e( 'Select a Location' ); ?></label></h2>
										<?php
											$current_location = null;
											if ( isset( $_COOKIE[ 'gb_location_preference' ] ) && $_COOKIE[ 'gb_location_preference' ] != '' ) {
												$current_location = $_COOKIE[ 'gb_location_preference' ];
											} elseif ( is_tax() ) {
												global $wp_query;
												$query_slug = $wp_query->get_queried_object()->slug;
												if ( isset( $query_slug ) && !empty( $query_slug ) ) {
													$current_location = $query_slug;
												}
											}
											echo '<select name="location" id="deal_location" size="1">';
											foreach ( $locations as $location ) {
												echo '<option value="'.$location->slug.'" '.selected( $current_location, $location->slug ).'>'.$location->name.'</option>';
											}
											if ( !empty( $no_city_text ) ) {
												echo '<option value="notfound">'.esc_attr( $no_city_text ).'</option>';
											}
											echo '</select>';
									?>
									</span>
								<?php
							} ?> 
                                 
                           <span class="submit clearfix"><input type="submit" class="button-primary" name="gb_subscription" id="gb_subscription" value="Continue →"></span>
                        </form>
					
				</div>

				<p style="text-align: center;"><a href="http://localsharingtree.com/test/wp-content/uploads/2013/07/Slide-6.jpg"><img class="alignnone  wp-image-339" alt="Slide 6" src="http://localsharingtree.com/test/wp-content/uploads/2013/07/Slide-6.jpg" width="305" height="230" data-id="339" /></a> <a href="http://localsharingtree.com/test/wp-content/uploads/2013/07/Slide-7.jpg"><img class="alignnone  wp-image-340" alt="Slide 7" src="http://localsharingtree.com/test/wp-content/uploads/2013/07/Slide-7.jpg" width="305" height="230" data-id="340" /></a> <a href="http://localsharingtree.com/test/wp-content/uploads/2013/07/Slide-8.jpg"><img class="alignnone  wp-image-341" alt="Slide 8" src="http://localsharingtree.com/test/wp-content/uploads/2013/07/Slide-8.jpg" width="305" height="230" data-id="341" /></a></p>

				<p>&nbsp;</p>
			
			</div><!-- // .mini_header -->
			
		</div>
		
	</div>		

<?php // get_footer(); Manually added footer code below ?>

	</div><!-- #wrapper -->	
	
<?php wp_footer(); ?>
</body>
</html>