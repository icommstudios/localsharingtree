<div class="wrap">

	<?php screen_icon(); ?>

	<h2><?php _e( 'CalPress Event Calendar', CALP_PLUGIN_NAME ) ?></h2>

	<div id="poststuff">

		<form method="post" action="">
			<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
			<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>

			<div class="metabox-holder">
				<div class="post-box-container column-1-calp left-side">
					<?php do_meta_boxes( $settings_page, 'left-side', null ); ?>
					<?php submit_button( esc_attr__( 'Update Settings', CALP_PLUGIN_NAME ), 'primary', 'calp_save_settings' ); ?>
				</div>
				<div class="post-box-container column-2-calp right-side"><?php do_meta_boxes( $settings_page, 'right-side', null ); ?></div>
			</div>
		</form>

	</div><!-- #poststuff -->

</div><!-- .wrap -->
