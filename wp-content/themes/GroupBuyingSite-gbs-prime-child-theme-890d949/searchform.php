<?php
/**
* Template for displaying search forms
*/
?>
<form role="search" method="get" class="searchform" id="searchform" action="<?php echo home_url( '/' ); ?>">
    <div>
        <?php 
		/*
		//Add filter by location - DISABLED
		if ( is_singular('gb_deal') || is_post_type_archive( 'gb_deal' ) || is_tax( 'gb_category' ) || is_tax( 'gb_tag') || is_tax( 'gb_location') 
		|| is_page_template('page-currentdeals-location.php') || is_page_template('page-currentdeals.php') || is_page_template('page-currentdeals-random-location.php') 
		|| is_page_template('page-donedeals-location.php') || is_page_template('page-donedeals.php') || is_page_template('page-futuredeals-location.php')
		|| is_page_template('page-futuredeals.php') ) {
			$location_id = '';
			$search_placeholder = 'Search for deals';
			if ( is_tax('gb_location') ) {
				global $wp_query;
				$term_object = $wp_query->get_queried_object();
			} elseif ( isset($_COOKIE[ 'gb_location_preference' ] ) ) {
				$term_object = get_term_by( 'slug', $_COOKIE[ 'gb_location_preference' ], gb_get_deal_location_tax() );
			}
			if ( $term_object && !is_wp_error($term_object) ) {
				$location_id = $term_object->term_id;
				$search_placeholder = 'Search for deals in '.$term_object->name;
			}
			?>
        <input type="text" placeholder="<?php echo esc_attr($search_placeholder); ?>" value="" name="s" id="s" />
        <input type="hidden" value="<?php echo esc_attr($location_id); ?>" name="search_deal_loc" id="search_deal_loc" />
        <?php
		}
		*/
		?>
        <input type="text" placeholder="search..." value="" name="s" id="s" />
        
        <input type="submit" id="searchsubmit" value="Search" />
    </div>
</form>