<?php

/**
 * Get the featured content for the deal
 *
 * @param integer $offer_id Offer ID
 * @return string           Content, shortcodes are executed
 */
if ( !function_exists( 'gb_get_featured_content' ) ) {
	function gb_get_featured_content( $offer_id = null ) {
		if ( null === $offer_id ) {
			global $post;
			$offer_id = $post->ID;
		}
		$content = Group_Buying_Featured_Content::get_featured_content( $offer_id );
		return apply_filters( 'gb_get_featured_content', do_shortcode( $content ) );
	}
}

/**
 * Print the featured content for a deal
 *
 * @param deal    $post_id Offer ID
 * @return string          content, shortcodes are executed
 */
if ( !function_exists( 'gb_featured_content' ) ) {
	function gb_featured_content( $offer_id = null ) {
		echo apply_filters( 'gb_featured_content', gb_get_featured_content( $offer_id ) );
	}
}

/**
 * Does the deal have featured content available
 *
 * @param integer $offer_id Offer ID
 * @return BOOL          TRUE|FALSE
 */
if ( !function_exists( 'gb_has_featured_content' ) ) {
	function gb_has_featured_content( $offer_id = null ) {
		$content = gb_get_featured_content( $offer_id );
		$has = ( $content == '' ) ? FALSE : TRUE ;
		return apply_filters( 'gb_has_featured_content', $has );

	}
}
