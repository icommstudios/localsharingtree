<?php
//This file has stored global variables that the plugin uses.
//Altough they can be changed, don't forget to change them back if you upgrade this file.


//ORDER OF CONTENT
//MAIN ORDER - name, details, social
$ts_display_order = array (
	1 => 'name',
	2 => 'details',
	3 => 'social'	
);
//CONTENT ORDER
//position,location,telephone,email,html,website
$ts_content_order = array (
	1 => 'position',
	2 => 'location',
	3 => 'telephone',
	4 => 'email',
	5 => 'html',
	6 => 'website'	
);

//SOCIAL ICONS ORDER
//linkedin,facebook,twitter,gplus,pinterest,youtube,vimeo,dribbble
$ts_social_order = array (
	1 => 'linkedin',
	2 => 'facebook',
	3 => 'twitter',
	4 => 'gplus',
	5 => 'youtube',
	6 => 'vimeo',
	7 => 'instagram',
	8 => 'email',
	9 => 'pinterest', //not being used in the current version
	
);

//ICONS
//see more at http://fortawesome.github.io/Font-Awesome/icons/
$ts_small_icons = array(
		'position' => '<i class="icon-chevron-sign-right"></i> ',
		'email' => '<i class="icon-envelope-alt"></i> ',
		'telephone' => '<i class="icon-phone-sign"></i> ',
		'html' => '<i class="icon-align-justify"></i> ',
		'website' => '<i class="icon-external-link"></i> ',
		'location' => '&nbsp;<i class="icon-map-marker"></i>&nbsp;'
);


//Labels

$ts_labels = array (

	'titles' => array(
				'info' => 'Aditional Information',
				'social' => 'Social Profile Links'	
				),
	'help' => array(
				'social' => 'Use the complete URL to the profile page. Example: http://www.facebook.com/profile'	
				),
	
	'html' => array (
				'key' => 'html',
				'meta_name' => '_tsbio',
				'label' => 'Free HTML',
				'description' => 'Short bio or tag line. Might be used when listing members with short descriptions.'
				),
	'position' => array (
				'key' => 'position',
				'meta_name' => '_tsposition',
				'label' => 'Job Title',
				'description' => 'The job description, position or functions of this member.'
				),
	'email' => array (
				'key' => 'email',
				'meta_name' => '_tsemail',
				'label' => 'Email',
				'description' => 'Contact email for this member. Might be visible to public.'
				),
	'location' => array (
				'key' => 'location',
				'meta_name' => '_tslocation',
				'label' => 'Location',
				'description' => 'Location/Origin/Adress of this member. Might be visible to public.'
				),
	'telephone' => array (
				'key' => 'telephone',
				'meta_name' => '_tstel',
				'label' => 'Telephone',
				'description' => 'Telephone contact. Might be visible to public.'
				),
	'user' => array (
				'key' => 'user',
				'meta_name' => '_tsuser',
				'label' => 'User/Author Profile',
				'description' => 'If this member is associated with a user account select it here. Might be used to fetch latest published posts in the single member page.'
				),
	'website' => array (
				'key' => 'website',
				'meta_name' => '_tspersonal',
				'label' => 'Personal Website',
				'description' => 'URL to personal website. Might be visible to public.'
				),
	'name' => array (
				'key' => 'name',
				'meta_name' => 'title',
				'label' => 'Name/Title',
				'description' => 'Name of the entry.'
				),
	'photo' => array (
				'key' => 'photo',
				'meta_name' => 'featured_image',
				'label' => 'Photo/Image',
				'description' => 'Featured Image of the entry.'
				),
	'smallicons' => array (
				'key' => 'smallicons',
				'label' => 'Small Icons',
				'description' => 'Small CSS Icons.'
				),
	'socialicons' => array (
				'key' => 'socialicons',
				'label' => 'Social Icons',
				'description' => 'Social Icons.'
				),
	'filter' => array (
				'label' => 'Filter',
				'all-entries-label' => 'All',
	),
	'search' => array (
				'all-taxonomies' => 'All',
				'search' => 'Search',
				'results-for' => 'Results for '
		),
	'pagination' => array (
				'next_page' => 'Next Page >',
				'previous_page' => '< Previous Page',
				
		)

);

//Change to false if you don't want the help text on the title input to be changed on the Add New Entry screen
$ts_change_default_title_en = false;


//array of styles for the images and text
//takes the corresponding shortcode code and the css class
//wrap styles for tables and grid should go here also

$ts_wrapstyles = array(
	'normal-float' => 'ts-normal-float-wrap',
	'1-columns' => 'ts-responsive-wrap',
	'2-columns' => 'ts-responsive-wrap',
	'3-columns' => 'ts-responsive-wrap',
	'4-columns' => 'ts-responsive-wrap',
	'5-columns' => 'ts-responsive-wrap',
	'6-columns' => 'ts-responsive-wrap',
	'retro-box-theme' => 'ts-retro-style',
	'white-box-theme' => 'ts-white-box-style',
	'card-theme' => 'ts-theme-card-style',
	'odd-colored' => 'ts-table-odd-colored'
	);


$ts_boxstyles = array(
	'img-left'=>'ts-float-left',
	'img-right'=>'ts-float-right',
	'normal-float' => 'ts-normal-float',
	'1-column' => 'ts-col_1',
	'2-columns' => 'ts-col_2',
	'3-columns' => 'ts-col_3',
	'4-columns' => 'ts-col_4',
	'5-columns' => 'ts-col_5',
	'6-columns' => 'ts-col_6'
	);
	
$ts_innerboxstyles = array(
	'img-left'=>'ts-float-left',
	'img-right'=>'ts-float-right'
	);

$ts_txtstyles = array(
	'text-left'=>'ts-align-left',
	'text-right'=>'ts-align-right',
	'text-center'=>'ts-align-center'
	);

$ts_imgstyles = array(
		'img-rounded'=>'ts-rounded',
		'img-circle'=>'ts-circle',
		'img-square'=>'ts-square',
		'img-grayscale' =>'ts-grayscale',
		'img-grayscale-shadow' =>'ts-grayscale-shadow',
		'img-shadow' =>'ts-shadow',
		'img-left' =>'ts-img-left',
		'img-right' =>'ts-img-right',
		'img-white-border' => 'ts-white-border'
		);

$ts_infostyles = array(
	'img-left'=>'ts-float-left',
	'img-right'=>'ts-float-right'
	);
	
$ts_pagerstyles = array(
	'thumbs-left'=>'ts-pager-left',
	'thumbs-right'=>'ts-pager-right',
	'thumbs-below'=>'ts-pager-below'
	);

$ts_pagerboxstyles = array(
	'thumbs-left'=>'ts-pager-box-right',
	'thumbs-right'=>'ts-pager-box-left',
	'thumbs-below'=>'ts-pager-box-above'
	);





$ts_theme_names = array (

						'grid' => array(
										
										'default' => array (
															'key' => 'default',
															'name' => 'tshowcase-default-style',
															'link' => 'css/normal.css',
															'label' => 'Default'
															),
										'retro-box-theme' => array (
															'key' => 'retro-box-theme',
															'name' => 'tshowcase-retro-style',
															'link' => 'css/retro.css',
															'label' => 'Retro boxes'
															
															),
										'white-box-theme' => array (
															'key' => 'white-box-theme',
															'name' => 'tshowcase-white-box-style',
															'link' => 'css/white-box.css',
															'label' => 'White Box with Shadow'
															),
										'card-theme' => array (
															'key' => 'card-theme',
															'name' => 'tshowcase-card-theme-style',
															'link' => 'css/theme-card.css',
															'label' => 'Simple Card'
															)
										),
										
						'hover' => array(
									
									'default' => array (
														'key' => 'default',
														'name' => 'tshowcase-default-hover-style',
														'link' => 'css/normal-hover.css',
														'label' => 'Default'
														),
									'white-hover' => array (
														'key' => 'white-hover',
														'name' => 'tshowcase-white-hover-style',
														'link' => 'css/white-hover.css',
														'label' => 'White Hover'
														
														)
									),	
						'table' => array(
									
									'default' => array (
														'key' => 'default',
														'name' => 'tshowcase-default-table-style',
														'link' => 'css/table.css',
														'label' => 'Default'
														),
									'odd-colored' => array (
														'key' => 'odd-colored',
														'name' => 'tshowcase-odd-colored-table-style',
														'link' => 'css/table-odd-colored.css',
														'label' => 'Odd Rows Colored'
														
														)
									),	
						'pager' => array(
									
									'default' => array (
														'key' => 'default',
														'name' => 'tshowcase-default-pager-style',
														'link' => 'css/pager.css',
														'label' => 'Default'
														)
									)	

	);





?>