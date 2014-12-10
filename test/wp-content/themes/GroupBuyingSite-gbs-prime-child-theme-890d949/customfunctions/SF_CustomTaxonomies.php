<?php 
// SF_CustomTaxonomies
// By Daniel Schuring (Studio Fidelis)
class SF_CustomTaxonomies extends Group_Buying_Controller {
	
	const TAX_AVG_PRICE = 'sf_gb_avg_price';
	const REWRITE_SLUG_AVG_PRICE = 'price-category';
	
	public static function init() {
		
		add_action( 'init', array(get_class(), 'init_tax'), 0 );

	}
	
	public static function init_tax() {
		// register taxonomy
		$taxonomy_args = array(
			'hierarchical' => TRUE,
			'labels' => array('name' => gb__('Average Price Category')),
			'show_ui' => TRUE,
			'rewrite' => array(
				'slug' => self::REWRITE_SLUG_AVG_PRICE,
				'with_front' => TRUE,
				'hierarchical' => TRUE,
			),
			'has_archive' => TRUE,
			'publicly_queryable' => TRUE,
		);
		register_taxonomy( self::TAX_AVG_PRICE, array(Group_Buying_Deal::POST_TYPE), $taxonomy_args );
		
	}

}
SF_CustomTaxonomies::init();