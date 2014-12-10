<div class="sf_offer_filters_wrapper">
<div id="sf_filter_ajax_loading"><img src="<?php echo get_admin_url(); ?>/images/ajax-loader.gif" id="sf_filter_loading_ajax_gif"></div>
<form id="sf-offer-filters-form" name="sf-offer-filters-form" action="" method="get">
<?php
//Territory (nav menu with custom walker) 
echo '<div class="sf_filter filter_territories">';
echo '<h2 class="sf_filter_label widget-title toggle-btn active">Territories</h2> ';
echo '<div class="toggle-content territory">';
//wp_nav_menu( array('theme_location'=>'sf_territory_menu', 'walker' => new SF_Territory_Menu_Walker(), 'depth' => 0) );

$locations = get_nav_menu_locations();
$menu = wp_get_nav_menu_object( $locations[ 'sf_territory_menu' ] );
$child_menu_items = array();
$top_menu_items = array();
$selected_default_territories = array();
$selected_territories = ( !empty($_GET['sf_filter_ter']) ) ? (array)$_GET['sf_filter_ter'] : array();
//Set default territory
if ( empty( $selected_territories ) ) {
	if ( is_tax('gb_location') ) {
		global $wp_query;
		$term_object = $wp_query->get_queried_object();
		$selected_default_territories[] = $term_object->term_id;
	} elseif ( isset($_COOKIE[ 'gb_location_preference' ] ) ) {
		$term_object = get_term_by( 'slug', $_COOKIE[ 'gb_location_preference' ], gb_get_deal_location_tax() );
		$selected_default_territories[] = $term_object->term_id;
	}
}
if ( $menu && ! is_wp_error($menu) && !isset($menu_items) ) {
	$menu_items = wp_get_nav_menu_items( $menu->term_id, array( 'update_post_term_cache' => false ) );
	foreach ( (array) $menu_items as $menu_item ) {
		if ( !$menu_item->menu_item_parent ) {
			$top_menu_items[ $menu_item->ID ] = $menu_item;
		}
	}
	foreach ( (array) $menu_items as $menu_item ) {
		if ( $menu_item->menu_item_parent ) {
			if ( in_array( $menu_item->menu_item_parent, (array)$selected_territories) || in_array( $top_menu_items[$menu_item->menu_item_parent]->object_id, (array)$selected_default_territories)  ) {
				$visible_child_menu_items[$menu_item->object_id] = $menu_item; //add only once
			}
			$all_child_menu_items[$menu_item->object_id] = $menu_item; //add only once
		} 
	}
	
}
//Show territories (the top level menu items)
foreach ( (array) $top_menu_items as $menu_item ) {
	//echo $menu_item->ID.' - '.$menu_item->title;
	if ( in_array( $menu_item->ID, $selected_territories) || in_array( $menu_item->object_id, $selected_default_territories) ) {
		$checked_territory = 'checked="checked"';
	} else {
		$checked_territory = '';
	}
	echo '<label for="territory_filter_'.$menu_item->ID.'" class="sf_filter_item_label sf_filter_territory_label"><input '.$checked_territory.' type="checkbox" name="sf_filter_ter[]" class="sf_filter_item sf_filter_territory sf_filter_checkbox" value="'.$menu_item->ID.'"> '.$menu_item->title.'</label> ';
}
echo '</div>';
echo '</div>';

//Show only child locations from menu items
$selected_locations = ( !empty($_GET['sf_filter_loc']) ) ? (array)$_GET['sf_filter_loc'] : array();
echo '<div class="sf_filter filter_by_location">';
echo '<h2 class="sf_filter_label widget-title toggle-btn">Location</h2>';
echo '<div class="toggle-content location">';
$count_item = 0;
foreach ( (array) $all_child_menu_items as $menu_item ) {
	$count_item++;
	//If in territory parent
	if ( isset($visible_child_menu_items[$menu_item->object_id]) ) {
		$item_styles = '';
	} else {
		$item_styles = 'display: none;';
	}
	if ( isset( $_GET['sf_filter_loc']) && in_array( $menu_item->object_id, $selected_locations) ) {
		$checked_location = 'checked="checked"';
	} elseif ( !isset( $_GET['sf_filter_loc']) && isset($visible_child_menu_items[$menu_item->object_id]) ) {
		$checked_location = 'checked="checked"'; //first time and in parents child items
	} else {
		$checked_location = '';
	}
	echo '<label style="'.$item_styles .'" for="location_filter_'.$menu_item->object_id.'" class="sf_filter_item_label sf_ter_parent_'.$menu_item->menu_item_parent.'"><input '.$checked_location.' type="checkbox" name="sf_filter_loc[]" class="sf_filter_item sf_filter_checkbox" value="'.$menu_item->object_id.'"> '.$menu_item->title.'</label> ';
}
echo '</div>';
echo '</div>';

//Locations filter
/*
$locations = get_terms("gb_location", array('hide_empty' => TRUE, 'fields' => 'all'));

$selected_locations = ( !empty($_GET['sf_filter_loc']) ) ? (array)$_GET['sf_filter_loc'] : array();
echo '<div class="sf_filter filter_by_location">';
echo '<h4 class="sf_filter_label">Location: </h4>';
$count_item = 0;
foreach ( $locations as $location ) {
	$count_item++;
	
	if ( isset( $_GET['sf_filter_loc']) && in_array( $location->term_id, $selected_locations) ) {
		$checked_location = 'checked="checked"';
	} else {
		$checked_location = '';
	}
	echo '<label for="location_filter_'.$location->term_id.'" class="sf_filter_item_label"><input '.$checked_location.' type="checkbox" name="sf_filter_loc[]" class="sf_filter_item sf_filter_checkbox" value="'.$location->term_id.'"> '.$location->name.'</label> ';
}
echo '</div>';
*/

//Show prices filter

//Custom tax filters
if ( class_exists('SF_CustomTaxonomies') ) {
	//Average price
	$terms = get_terms(SF_CustomTaxonomies::TAX_AVG_PRICE, array('hide_empty' => TRUE, 'fields' => 'all'));
	if ( !empty($terms) && !is_wp_error($terms) ) {
		$selected_terms = ( !empty($_GET['sf_filter_avg_price']) ) ? (array)$_GET['sf_filter_avg_price'] : array();
		echo '<div class="sf_filter filter_by_avg_price">';
		echo '<h2 class="sf_filter_label widget-title toggle-btn">Price</h2>';
		echo '<div class="toggle-content avg_price">';
		$count_item = 0;
		foreach ( $terms as $term ) {
			$count_item++;
			if ( isset( $_GET['sf_filter_avg_price']) && in_array( $term->term_id, $selected_terms) ) {
				$checked_term = 'checked="checked"';
			} else {
				$checked_term = '';
			}
			echo '<label for="avg_price_filter_'.$term->term_id.'" class="sf_filter_item_label"><input '.$checked_term.' type="checkbox" name="sf_filter_avg_price[]" class="sf_filter_item sf_filter_checkbox" value="'.$term->term_id.'"> '.$term->name.'</label> ';
		}
		echo '</div>';
		echo '</div>';
	}
	
}

//Category tax filters
$terms = get_terms("gb_category", array('hide_empty' => TRUE, 'fields' => 'all'));
if ( !empty($terms) && !is_wp_error($terms) ) {
	$selected_terms = ( !empty($_GET['sf_filter_cat']) ) ? (array)$_GET['sf_filter_cat'] : array();
	echo '<div class="sf_filter filter_by_cat">';
	echo '<h2 class="sf_filter_label widget-title toggle-btn">Category</h2>';
	echo '<div class="toggle-content category">';
	$count_item = 0;
	foreach ( $terms as $term ) {
		$count_item++;
		
		if ( isset( $_GET['sf_filter_cat']) && in_array( $term->term_id, $selected_terms) ) {
			$checked_term = 'checked="checked"';
		} else {
			$checked_term = '';
		}
		echo '<label for="cat_filter_'.$term->term_id.'" class="sf_filter_item_label"><input '.$checked_term.' type="checkbox" name="sf_filter_cat[]" class="sf_filter_item sf_filter_checkbox" value="'.$term->term_id.'"> '.$term->name.'</label> ';
	}
	echo '</div>';
	echo '</div>';
}

?>
<div>
<button type="submit" class="button font_small"> Apply Filter</button>
</div>
</form>

<script type="text/javascript">
jQuery(document).ready( function($){
	$('.sf_filter_item').bind('click', function(e){
	 	//e.preventDefault();
		
		//Show locations under this territory
		if ( $(this).hasClass('sf_filter_territory') ) {
			if ( $(this).is(':checked') ) {
				$('.sf_ter_parent_' + $(this).val() + ' input' ).attr('checked', true);
				$('.sf_ter_parent_' + $(this).val() ).show();
			} else {
				$('.sf_ter_parent_' + $(this).val() + ' input' ).attr('checked', false);
				$('.sf_ter_parent_' + $(this).val() ).hide();
			}
			$load_sf_filter_results(this);
		} else {
			$load_sf_filter_results(this);	
		}
	});
	
	$load_sf_filter_results = function(elem) {
		
		//Show loading
		$('#sf_filter_ajax_loading').show();
		
		var call_url = '<?php
		 $current_url = $_SERVER['REQUEST_URI'];
		 $current_url_parts = explode('?', $current_url);
		 if ( $current_url_parts[0] ) {
			 $current_url = $current_url_parts[0];
		 }
		 echo $current_url;
		 //echo get_post_type_archive_link('gb_deal'); 
		 ?>' + '?sf_filter_ajax=1&' + $('#sf-offer-filters-form').serialize();
		//$(call_url).insertBefore('#content');
		console.log( call_url );
		$('#content').load(call_url + ' #content', function() {
			$(this).children(':first').unwrap();
			$('#sf_filter_ajax_loading').hide();
		});
		
		
	}
	// Hide toggle content by default
	setTimeout(
  function() 
  {
    $(".toggle-content.category, .toggle-content.avg_price, .toggle-content.location").slideToggle( "fast" );
  }, 500);

	$( "h2.toggle-btn" ).click(function() {
	  $(this).parent().children( ".toggle-content" ).slideToggle( "fast" );
	  $(this).parent().children( "h2.toggle-btn" ).toggleClass( "active" );
	});
});
</script>
</div>