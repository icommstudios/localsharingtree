<?php wp_nonce_field( 'calp', CALP_POST_TYPE ); ?>
<h4 class="calp-section-title"><?php _e( 'Event date and time', CALP_PLUGIN_NAME ); ?></h4>
<table class="calp-form">
	<tbody>
		<tr>
			<td class="calp-first">
				<label for="calp_all_day_event">
					<?php _e( 'All-day event', CALP_PLUGIN_NAME ); ?>?
				</label>
			</td>
			<td>
				<input type="checkbox" name="calp_all_day_event" id="calp_all_day_event" value="1" <?php echo $all_day_event; ?> />
			</td>
		</tr>
		<tr>
			<td>
				<label for="calp_start-date-input">
					<?php _e( 'Start date / time', CALP_PLUGIN_NAME ); ?>:
				</label>
			</td>
			<td>
				<input type="text" class="calp-date-input" id="calp_start-date-input" />
				<input type="text" class="calp-time-input" id="calp_start-time-input" />
				<small><?php echo $timezone ?></small>
				<input type="hidden" name="calp_start_time" id="calp_start-time" value="<?php echo $start_timestamp ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<label for="calp_end-date-input">
					<?php _e( 'End date / time', CALP_PLUGIN_NAME ) ?>:
				</label>
			</td>
			<td>
				<input type="text" class="calp-date-input" id="calp_end-date-input" />
				<input type="text" class="calp-time-input" id="calp_end-time-input" />
				<small><?php echo $timezone ?></small>
				<input type="hidden" name="calp_end_time" id="calp_end-time" value="<?php echo $end_timestamp ?>" />
			</td>
		</tr>
		<tr>
			<td>
			  <input type="checkbox" name="calp_repeat" id="calp_repeat" value="1" <?php echo $repeating_event ? 'checked="checked"' : '' ?>/>
			  <input type="hidden" name="calp_rrule" id="calp_rrule" value="<?php echo $rrule ?>" />
				<label for="calp_repeat" id="calp_repeat_label">
					<?php _e( 'Repeat', CALP_PLUGIN_NAME ); echo $repeating_event ? ':' : '...' ?>
				</label>
			</td>
			<td>
			  <div id="calp_repeat_text">
			    <a href="#calp_repeat_box"><?php echo $rrule_text ?></a>
			  </div>
			</td>
		</tr>
    
    <div id="calp_repeat_box">
      <ul class="calp_repeat_tabs">
        <li><a href="#calp_daily_content" id="calp_daily_tab" class="calp_tab calp_active"><?php _e( 'Daily', CALP_PLUGIN_NAME ) ;?></a></li>
        <li><a href="#calp_weekly_content" id="calp_weekly_tab" class="calp_tab"><?php _e( 'Weekly', CALP_PLUGIN_NAME ) ;?></a></li>
        <li><a href="#calp_monthly_content" id="calp_monthly_tab" class="calp_tab"><?php _e( 'Monthly', CALP_PLUGIN_NAME ) ;?></a></li>
        <li><a href="#calp_yearly_content" id="calp_yearly_tab" class="calp_tab"><?php _e( 'Yearly', CALP_PLUGIN_NAME ) ;?></a></li>
      </ul>
      <div style="clear:both;"></div>
      <div id="calp_daily_content" class="calp_tab_content" title="daily">
        <?php echo $row_daily ?>
        <div id="calp_repeat_tab_append">
          <div id="calp_ending_box" class="calp_repeat_centered_content">
        		<div id="calp_end_holder">
        		  <label for="calp_end">
        				<?php _e( 'End', CALP_PLUGIN_NAME ) ?>:
        			</label>
        			 <?php echo $end ?>
        		</div>
        		<div style="clear:both;"></div>
        		<div id="calp_count_holder">
        		  <label for="calp_count">
        				<?php _e( 'Ending after', CALP_PLUGIN_NAME ) ?>:
        			</label>
        			<?php echo $count; ?>
        		</div>
        		<div style="clear:both;"></div>
        		<div id="calp_until_holder">
        		  <label for="calp_until-date-input">
        				<?php _e( 'On date', CALP_PLUGIN_NAME ) ?>:
        			</label>
        			<input type="text" class="calp-date-input" id="calp_until-date-input" />
        			<input type="hidden" name="calp_until_time" id="calp_until-time" value="<?php echo !is_null( $until ) && $until > 0 ? $until : '' ?>" />
        		</div>
        		<div style="clear:both;"></div>
        	</div>
        	<div id="calp_apply_button_holder">
            <input type="button" name="calp_none_button" value="<?php _e( 'Apply', CALP_PLUGIN_NAME ) ;?>" class="calp_repeat_apply button button-highlighted" />
            <a href="#calp_cancel" class="calp_repeat_cancel"><?php _e( 'Cancel', CALP_PLUGIN_NAME ) ?></a>
          </div>
          <div style="clear:both;"></div>
        </div>
        <div style="clear:both;"></div>
      </div>
      <div id="calp_weekly_content" class="calp_tab_content" title="weekly">
        <?php echo $row_weekly ?>
      </div>
      <div id="calp_monthly_content" class="calp_tab_content" title="monthly">
        <?php echo $row_monthly ?>
      </div>
      <div id="calp_yearly_content" class="calp_tab_content" title="yearly">
        <?php echo $row_yearly ?>
      </div>
      <div style="clear:both;"></div>
    </div>
		
	</tbody>
</table>
