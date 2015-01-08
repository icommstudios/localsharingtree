<div class="sf_offer_filters_wrapper">
<div id="sf_filter_ajax_loading"><img src="<?php echo get_admin_url(); ?>/images/ajax-loader.gif" id="sf_filter_loading_ajax_gif"></div>
<form id="sf-offer-filters-form" name="sf-offer-filters-form" action="" method="get">
<input type="hidden" name="sf-offer-filter-submitted" value="1">
<?php
//Territory (nav menu with custom walker) 
echo '<div class="sf_filter filter_territories">';
echo '<h2 class="sf_filter_label widget-title toggle-btn active">Territories</h2> ';
echo '<div class="toggle-content territory">';
echo '<div class="select_all_toggles"><a class="filter_select_all">Select all</a> &nbsp;|&nbsp; <a class="filter_select_none">Deselect all</a></div>';
//Get territory menu (custom Wordpress menu for territories)
$locations = get_nav_menu_locations();
$menu = wp_get_nav_menu_object( $locations[ 'sf_territory_menu' ] );
$visible_in_parent_menu_items = array();
$visible_in_starting_child_items = array();
$all_child_menu_items = array();
$top_menu_items = array();
$selected_default_territories = array();
$selected_territories = ( !empty($_GET['sf_filter_ter']) ) ? (array)$_GET['sf_filter_ter'] : array();
//Set default territory (if first view)
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
//Loop menu items to organize menu items into top level (territories) and children (locations)
if ( $menu && ! is_wp_error($menu) && !isset($menu_items) ) {
	$menu_items = wp_get_nav_menu_items( $menu->term_id, array( 'update_post_term_cache' => false ) );
	//Get array of location terms with posts ( deals )
	$locations_with_deals = get_terms("gb_location", array('hide_empty' => TRUE, 'fields' => 'ids'));
	foreach ( (array) $menu_items as $menu_item ) {
		if ( !$menu_item->menu_item_parent ) {
			$top_menu_items[ (int)$menu_item->ID ] = $menu_item;
		}
	}
	foreach ( (array) $menu_items as $menu_item ) {
		//Only add if has deals
		if ( !$menu_item->object_id || !in_array($menu_item->object_id, $locations_with_deals) ) {
			continue;
		}
		//If not one of the top level menu items
		if ( $menu_item->menu_item_parent ) {
			if ( in_array( $top_menu_items[$menu_item->menu_item_parent]->object_id, (array)$selected_territories) || in_array( $top_menu_items[$menu_item->menu_item_parent]->object_id, (array)$selected_default_territories)  ) {
				$visible_in_starting_child_items[(int)$menu_item->object_id] = $menu_item; //add to list of child items to start showing
			}
			$visible_in_parent_menu_items[(int)$menu_item->object_id][] = (int)$menu_item->menu_item_parent; //add to list of parents to show this in
			$all_child_menu_items[(int)$menu_item->object_id] = $menu_item; //add only once
		} 
	}
	
}
//Show territories (the top level menu items)
foreach ( (array) $top_menu_items as $menu_item ) {
	//echo $menu_item->ID.' - '.$menu_item->title;
	if ( in_array( $menu_item->object_id, $selected_territories) || in_array( $menu_item->object_id, $selected_default_territories) ) {
		$checked_territory = 'checked="checked"';
	} else {
		$checked_territory = '';
	}
	echo '<label for="territory_filter_'.(int)$menu_item->ID.'" class="sf_filter_item_label sf_filter_territory_label"><input id="territory_filter_'.(int)$menu_item->ID.'" '.$checked_territory.' type="checkbox" name="sf_filter_ter[]" data-menu_id="'.(int)$menu_item->ID.'" class="sf_filter_item sf_filter_territory sf_filter_checkbox" value="'.(int)$menu_item->object_id.'"> '.$menu_item->title.'</label> ';
}
echo '</div>';
echo '</div>';

//Show only child locations from menu items
$selected_locations = ( !empty($_GET['sf_filter_loc']) ) ? (array)$_GET['sf_filter_loc'] : array();
echo '<div class="sf_filter filter_by_location">';
echo '<h2 class="sf_filter_label widget-title toggle-btn">Location</h2>';
echo '<div class="toggle-content location">';
echo '<div class="select_all_toggles"><a class="filter_select_all">Select all</a> &nbsp;|&nbsp; <a class="filter_select_none">Deselect all</a></div>';
$count_item = 0;
foreach ( (array) $all_child_menu_items as $menu_item ) {
	$count_item++;
	//If in array of territory parents
	if ( isset($visible_in_starting_child_items[(int)$menu_item->object_id]) ) {
		$item_styles = '';
	} else {
		$item_styles = 'display: none;';
	}
	if ( isset( $_GET['sf_filter_loc']) && in_array( $menu_item->object_id, $selected_locations) ) {
		$checked_location = 'checked="checked"';
	} elseif ( !isset( $_GET['sf_filter_loc']) && isset($visible_in_starting_child_items[(int)$menu_item->object_id]) ) {
		$checked_location = 'checked="checked"'; //first time and in parents child items
	} else {
		$checked_location = '';
	}
	//Classes for each parent to show this item in
	$add_class_parent_territories = '';
	if ( isset($visible_in_parent_menu_items[$menu_item->object_id]) ) {
		$json_parent_territory_ids = implode(',', (array)$visible_in_parent_menu_items[$menu_item->object_id] );
		foreach ( (array)$visible_in_parent_menu_items[$menu_item->object_id] as $parent_menu_item_id ) {
			$add_class_parent_territories .= 'sf_ter_parent_'.$parent_menu_item_id.' ';
		}
	}
	echo '<label style="'.$item_styles .'" for="location_filter_'.(int)$menu_item->object_id.'" class="sf_filter_item_label"><input id="location_filter_'.(int)$menu_item->object_id.'" data-json_parent_ids="'.$json_parent_territory_ids.'" '.$checked_location.' type="checkbox" name="sf_filter_loc[]" class="sf_filter_item sf_filter_checkbox '.$add_class_parent_territories.'" value="'.(int)$menu_item->object_id.'"> '.$menu_item->title.'</label> ';
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
		echo '<div class="select_all_toggles"><a class="filter_select_all">Select all</a> &nbsp;|&nbsp; <a class="filter_select_none">Deselect all</a></div>';
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
	echo '<div class="select_all_toggles"><a class="filter_select_all">Select all</a> &nbsp;|&nbsp; <a class="filter_select_none">Deselect all</a></div>';
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
<div style="display: none;">
<button type="submit" class="button font_small"> Apply Filter</button>
</div>
</form>

<script type="text/javascript">
jQuery(document).ready( function($){
	$('.sf_filter_item').bind('click', function(e){
	 	//e.preventDefault();
		
		//Show locations under this territory
		/*
		if ( $(this).hasClass('sf_filter_territory') ) {
			if ( $(this).is(':checked') ) {
				$('.sf_ter_parent_' + $(this).data('menu_id')).each(function(index, value){
					$(this).attr('checked', true);
					$(this).closest('label').show();
				});
			} else {
				//Only uncheck and hide if all territories that have this child are unchecked
				var this_menu_id = $(this).data('menu_id');
				$('.sf_ter_parent_' + this_menu_id).each(function(index, value){
					var blnHideit = true;
					
					var parent_ids_array = new Array();
					var parent_ids_array_string = $(this).data('json_parent_ids');
					parent_ids_array_string.toString();
					if ( parent_ids_array_string.length > 0 ) {
						if ( parent_ids_array_string.indexOf(',') != -1 ) {
							var parent_ids_array = parent_ids_array_string.split(',');
						} else {
							var parent_ids_array = new Array(parent_ids_array_string); //only one
						}
					}
					
					if ( parent_ids_array.length > 0 ) {
						//alert( 'looping: ' +  $(this).attr('id') + ' ' + parent_ids_array );
						$.each(parent_ids_array, function(key, parent_id) {
							if ( this_menu_id != parent_id ) {
								var parent_id_string = '#territory_filter_' + parent_id;
								if ( $(parent_id_string).length > 0 && $(parent_id_string).is(':checked') ) {
									blnHideit = false;
								}
							}
						});
					} 
					
					if ( blnHideit == true ) {
						$(this).attr('checked', false);
						$(this).closest('label').hide();
					}
				});
				
			}
			$load_sf_filter_results(this);
		} else {
			$load_sf_filter_results(this);	
		}
		*/
		
		$handle_territory_change(this); //runs if item type is terriory
		$load_sf_filter_results(this);	//runs for all item types
		
	});
	
	$handle_territory_change = function(elem) {
		//Show locations under this territory
		if ( $(elem).hasClass('sf_filter_territory') ) {
			if ( $(elem).is(':checked') ) {
				$('.sf_ter_parent_' + $(elem).data('menu_id')).each(function(index, value){
					$(this).attr('checked', true);
					$(this).closest('label').show();
				});
			} else {
				//Only uncheck and hide if all territories that have this child are unchecked
				var this_menu_id = $(elem).data('menu_id');
				$('.sf_ter_parent_' + this_menu_id).each(function(index, value){
					var blnHideit = true;
					
					var parent_ids_array = new Array();
					var parent_ids_array_string = $(this).data('json_parent_ids');
					parent_ids_array_string.toString();
					if ( parent_ids_array_string.length > 0 ) {
						if ( parent_ids_array_string.indexOf(',') != -1 ) {
							var parent_ids_array = parent_ids_array_string.split(',');
						} else {
							var parent_ids_array = new Array(parent_ids_array_string); //only one
						}
					}
					
					if ( parent_ids_array.length > 0 ) {
						//alert( 'looping: ' +  $(this).attr('id') + ' ' + parent_ids_array );
						$.each(parent_ids_array, function(key, parent_id) {
							if ( this_menu_id != parent_id ) {
								var parent_id_string = '#territory_filter_' + parent_id;
								if ( $(parent_id_string).length > 0 && $(parent_id_string).is(':checked') ) {
									blnHideit = false;
								}
							}
						});
					} 
					
					if ( blnHideit == true ) {
						$(this).attr('checked', false);
						$(this).closest('label').hide();
					}
				});
				
			}
			//$load_sf_filter_results(elem);
		}
	}
	
	
	$load_sf_filter_results = function(elem) {
		
		//Show loading
		$('#sf_filter_ajax_loading').show();
		
		var call_url = '<?php
		 $current_url = $_SERVER['REQUEST_URI'];
		 $current_url_parts = explode('?', $current_url);
		 if ( $current_url_parts[0] ) {
			 $current_url = $current_url_parts[0];
		 }
		 //Remove page number (always start at page 0 )
		 if ( stripos($current_url, '/page/') !== false && stripos($current_url, '/page/') > 0 ) {
			$current_url_parts = explode('/page/', $current_url);
		 }
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
	
	//Show all & Show none
	$(".filter_select_all").bind('click', function() {
		var parentelem = $(this).closest('.sf_filter');
		var save_changed_elem = false;
		$('.sf_filter_item_label', parentelem).each(function(index){
			if ( $(this).is(':visible') && $('input[type=checkbox]', this).attr('checked') != true ) {
				$('input[type=checkbox]', this).attr('checked', true);
				save_changed_elem = $('input[type=checkbox]', this);
				//if item type is territory, then update locations shown
				if ( $(save_changed_elem).hasClass('sf_filter_territory') ) {
					$handle_territory_change(save_changed_elem);
				}
			}
		});
		//if changes, then trigger update using last changed element as trigger
		if ( save_changed_elem ) {
			$load_sf_filter_results(save_changed_elem);
		}
	});
	$(".filter_select_none").bind('click', function() {
		var parentelem = $(this).closest('.sf_filter');
		var save_changed_elem = false;
		$('.sf_filter_item_label', parentelem).each(function(index){
			if ( $(this).is(':visible') && $('input[type=checkbox]', this).attr('checked') != false ) {
				$('input[type=checkbox]', this).attr('checked', false);
				save_changed_elem = $('input[type=checkbox]', this);
				//if item type is territory, then update locations shown
				if ( $(save_changed_elem).hasClass('sf_filter_territory') ) {
					$handle_territory_change(save_changed_elem);
				}
			}
		});
		//if changes, then trigger update using last changed element as trigger
		if ( save_changed_elem ) {
			$load_sf_filter_results(save_changed_elem);
		}
	});

	
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