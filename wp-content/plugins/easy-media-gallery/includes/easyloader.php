<?php
error_reporting(0);ini_set('display_errors', 0);
$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );

if ( isset( $_GET['id'] ) && !empty( $_GET['id'] ) ) {
	$devmedia = '';
	if ( strpos( $_GET['id'],'-' ) ) {
		$devmedia = explode("-", $_GET['id']);
		ajax_req_handle( $devmedia[0] , $devmedia[1] );
	}
	else {
		ajax_req_handle( $_GET['id'], "" );
	}
}

else {
}

function ajax_req_handle( $id, $isdinamccntn ) {
	
	global $post;
	$usegalleryinfo = get_post_meta( $id, 'easmedia_metabox_media_gallery_opt2', true );
	
	if ( $isdinamccntn != '' ) {
		
		if ( $usegalleryinfo == 'on' ) {
			$img_info = get_post( $isdinamccntn );
			$boxmediattl = $img_info->post_title;
			$boxmediasbttl = $img_info->post_excerpt;		
		}
		else {
			$boxmediattl = get_post_meta( $id, 'easmedia_metabox_title', true );
			$boxmediasbttl = get_post_meta( $id, 'easmedia_metabox_sub_title', true );
		}
	}
	else {
		$boxmediattl = get_post_meta( $id, 'easmedia_metabox_title', true );
		$boxmediasbttl = get_post_meta( $id, 'easmedia_metabox_sub_title', true );
		}
	
	$imgsrc = get_post_meta( $id, 'easmedia_metabox_img', true );
	$mediatype = get_post_meta( $id, 'easmedia_metabox_media_type', true );
		
	switch ( $mediatype ) {
		case 'Single Image':
		$boxlink = $imgsrc;
	        break;		
			
		case 'Video':
		$boxlink = get_post_meta( $id, 'easmedia_metabox_media_video', true );
	        break;
			
		case 'Audio':
		$boxlink = get_post_meta( $id, 'easmedia_metabox_media_audio', true );
	        break;			
					
	}
	
	if ( $boxmediasbttl == '' ) {$boxmediasbttl = 'none';}
	if ( $boxmediattl == '' ) {$boxmediattl = 'none';}

$therest = array( $boxmediattl,$boxmediasbttl );
echo json_encode( $therest );
exit;
}

?>