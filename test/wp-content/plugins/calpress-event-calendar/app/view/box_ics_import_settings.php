<label class="textinput" for="ics_warning">
	<?php _e( 'Warning - please note that not all calendars use the latest and correct implementation of the ics format. Please double check your imported calendars to verify that they have imported correctly', CALP_PLUGIN_NAME ) ?>
</label>
<br class="clear" />
<label class="textinput" for="cron_freq">
  <?php _e( 'Auto-refresh', CALP_PLUGIN_NAME ) ?>:
</label>
<?php echo $cron_freq ?>
<br class="clear" />

<label for="hide_subscribe"> 
<input class="checkbox" name="hide_subscribe" type="checkbox" value="1" <?php echo $hide_subscribe ?> /> 
<?php _e( 'Hide <strong>Subscribe</strong> button', CALP_PLUGIN_NAME ) ?> 
</label> 
<br class="clear" />

<div id="calp-feeds-after" class="calp-feed-container">
	<h4 class="calp_feed_h4"><?php _e( 'iCalendar/.ics Feed URL:', CALP_PLUGIN_NAME ) ?></h4>
	<div class="calp-feed-url"><input type="text" name="calp_feed_url" id="calp_feed_url" /></div>
	<div class="calp-feed-category">
		<label for="calp_feed_category">
			<?php _e( 'Event category', CALP_PLUGIN_NAME ); ?>:
		</label>
		<?php echo $event_categories; ?>
	</div>
	<div class="calp-feed-tags">
		<label for="calp_feed_tags">
			<?php _e( 'Tag with', CALP_PLUGIN_NAME ); ?>:
		</label>
		<input type="text" name="calp_feed_tags" id="calp_feed_tags" />
	</div>
	<input type="button" id="calp_add_new_ics" class="button" value="<?php _e( '+ Add new subscription', CALP_PLUGIN_NAME ) ?>" />
</div>

<?php echo $feed_rows; ?>
<br class="clear" />
