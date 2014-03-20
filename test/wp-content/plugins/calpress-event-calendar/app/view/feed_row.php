<div class="calp-feed-container">
	<h4 class="calp_feed_h4">
		<?php _e( 'iCalendar/.ics Feed URL:', CALP_PLUGIN_NAME ); ?>
	</h4>
	<div class="calp-feed-url"><input type="text" class="calp-feed-url" readonly="readonly" value="<?php echo esc_attr( $feed_url ) ?>" /></div>
	<input type="hidden" name="feed_id" class="calp_feed_id" value="<?php echo $feed_id;?>" />
	<?php if( $event_category ): ?>
		<div class="calp-feed-category">
			<?php _e( 'Event category:', CALP_PLUGIN_NAME ); ?>
			<strong><?php echo $event_category; ?></strong>
		</div>
	<?php endif ?>
	<?php if( $tags ): ?>
		<div class="calp-feed-tags">
			<?php _e( 'Tag with', CALP_PLUGIN_NAME ); ?>:
			<strong><?php echo $tags; ?></strong>
		</div>
	<?php endif ?>
	<input type="button" class="button calp_delete_ics" value="<?php _e( '× Delete', CALP_PLUGIN_NAME ); ?>" />
	<input type="button" class="button calp_update_ics" value="<?php _e( 'Update', CALP_PLUGIN_NAME ); ?>" />
	<?php if( $events ): ?>
		<input type="button" class="button calp_flush_ics" value="<?php printf( _n( 'Flush 1 event', 'Flush %s events', $events, CALP_PLUGIN_NAME ), $events ) ?>" />
	<?php endif ?>
	<img src="images/wpspin_light.gif" class="ajax-loading" alt="" />
</div>
