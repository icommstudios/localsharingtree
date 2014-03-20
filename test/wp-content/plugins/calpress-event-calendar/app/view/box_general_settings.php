<h2><?php _e( 'Viewing Events', CALP_PLUGIN_NAME ) ?></h2>

<label class="textinput" for="calendar_page_id"><?php _e( 'Calendar page:', CALP_PLUGIN_NAME ) ?></label>
<div class="alignleft"><?php echo $calendar_page ?></div>
<br class="clear" />

<label class="textinput" for="default_calendar_view"><?php _e( 'Default calendar view:', CALP_PLUGIN_NAME ) ?></label>
<?php echo $default_calendar_view ?>
<br class="clear" />

<label class="textinput" for="calender_theme"><?php _e( 'Calendar theme:', CALP_PLUGIN_NAME ) ?></label>
<?php echo $calendar_theme ?>
<br class="clear" />

<label class="textinput" for="default_calendar_view"><?php _e( 'Timezone:', CALP_PLUGIN_NAME ) ?></label>
<?php echo $timezone_control ?>
<br class="clear" />

<label class="textinput" for="week_start_day"><?php _e( 'Week starts on', CALP_PLUGIN_NAME ) ?></label>
<?php echo $week_start_day ?>
<br class="clear" />

<h2><?php _e( 'Adding/Editing Events', CALP_PLUGIN_NAME ) ?></h2>

<label class="textinput" for="input_date_format"><?php _e( 'Input dates in this format:', CALP_PLUGIN_NAME ) ?></label>
<?php echo $input_date_format ?>
<br class="clear" />

<label for="input_24h_time"> 
<input class="checkbox" name="input_24h_time" id="input_24h_time" type="checkbox" value="1" <?php echo $input_24h_time ?> /> 
<?php _e( 'Use <strong>24h time</strong> format', CALP_PLUGIN_NAME ) ?> 
</label> 
<br class="clear" />

<label for="geo_region_biasing">
<input class="checkbox" name="geo_region_biasing" id="geo_region_biasing" type="checkbox" value="1" <?php echo $geo_region_biasing ?> />
<?php _e( 'Use the configured <strong>region</strong> (WordPress locale) to bias the address autocomplete function', CALP_PLUGIN_NAME ) ?>
</label>
<br class="clear" />
