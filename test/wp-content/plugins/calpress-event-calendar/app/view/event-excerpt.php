<div class="calp-excerpt">
	<div class="calp-time"><label class="calp-label"><?php _e( 'When:', CALP_PLUGIN_NAME ) ?></label>
        <?php echo $event->timespan_html ?></div>
    <div class="calp-time"><label class="calp-label"><?php _e( 'Notes:', CALP_PLUGIN_NAME ) ?></label>
	<?php if( $location ): ?>
		<div class="calp-location"><label class="calp-label"><?php _e( 'Where:', CALP_PLUGIN_NAME ) ?></label> <?php echo $location ?></div>
	<?php endif ?>
</div>
