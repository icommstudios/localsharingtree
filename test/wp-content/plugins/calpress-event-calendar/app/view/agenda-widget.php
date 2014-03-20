<?php echo $args['before_widget'] ?>

<div class="calp-agenda-widget-view">
    <div class="calp-widget-loading"></div>
    <div class="calp-widget-title">

        <h2>
            <img src="<?php echo CALP_IMAGE_URL ?>/widget_icon.png" />
        <?php if ( $title  ) {
            echo $title;
        } else {
            echo _e( 'Calpress Events', CALP_PLUGIN_NAME );
        }?>
        </h2>
    </div>
    
	<?php if( ! $dates ): ?>
		<p class="calp-no-results">
			<?php _e( 'There are no events.', CALP_PLUGIN_NAME ) ?>
		</p>
	<?php else: ?>
		<ol>
			<?php foreach( $dates as $timestamp => $date_info ): ?>
				<li class="calp-date <?php if( isset( $date_info['today'] ) && $date_info['today'] ) echo 'calp-today' ?>">
					<h3 class="calp-date-title">
						<div class="calp-month"><?php echo date_i18n( 'M', $timestamp, true ) ?></div>
						<div class="calp-day"><?php echo date_i18n( 'j', $timestamp, true ) ?></div>
						<div class="calp-weekday"><?php echo date_i18n( 'D', $timestamp, true ) ?></div>
					</h3>
					<ol class="calp-date-events">
						<?php foreach( $date_info['events'] as $events ): ?>
							<?php foreach( $events as $event ): ?>
								<li class="calp-event
									calp-event-id-<?php echo $event->post_id ?>
									calp-event-instance-id-<?php echo $event->instance_id ?>
									<?php if( $event->allday ) echo 'calp-allday' ?>">

									<?php // Insert post ID for use by JavaScript filtering later ?>
									<input type="hidden" class="calp-post-id" value="<?php echo $event->post_id ?>" />
									<a href="<?php echo $event_url . $event->instance_id ?>">
										<?php if( ! $event->allday ): ?>
											<span class="calp-event-time">
												<?php echo esc_html( $event->start_time ) ?></span>
											</span>
										<?php else : ?>
                                            <span class="calp-widget-allday"> <?php _e('(all-day)', CALP_PLUGIN_NAME);?></span>
										<?php endif ?>
                                        
                                        <span class="calp-widget-details">- <?php _e('View Details', CALP_PLUGIN_NAME);?></span>
                                        
                                        <br />
										<span class="calp-event-title">
                                            <?php echo strip_tags($event->post->post_title) ?>
										</span>
									</a>

								</li>
							<?php endforeach ?>
						<?php endforeach ?>
					</ol>
				</li>
			<?php endforeach ?>
		</ol>
	<?php endif ?>

    <?php if ( $show_calendar_navigator ): ?>
        <div id="calp-widget-calendar" class="calp-date-events">
            <table>
              <thead>
              <tr>
                  <?php foreach( $weekdays as $day ) : ?>
                      <td><?php echo mb_substr($day,0,3, "utf-8");?></td>
                  <?php endforeach;?>
              </tr>
              </thead>
              <tbody>
                  <?php foreach( $weeks as $week ) : ?>
                    <tr>
                        <?php foreach( $week as $day ) : ?>
                            <td><?php if ($day['date'])  echo '<a class="calp-widget-nav calp-cal-date '.($day['today']? 'calp-cal-current': null).'
                             '.($day['has_events']? 'calp-has-events': null).'" 
                                offset="'.$day['offset'].'" href="#">'.$day['date'].'</a>';?></td>
                        <?php endforeach;?>
                    </tr>
                  <?php endforeach;?>
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="3" id="prev">
                        <a class="calp-widget-nav" offset="<?php echo $links['previous']['offset'] ?>" href="#">« <?php echo $links['previous']['text'] ?></a>
                    </td>
                    <td class="pad">&nbsp;</td>
                    <td colspan="3" id="next">
                        <a class="calp-widget-nav" offset="<?php echo $links['next']['offset'] ?>" href="#"><?php echo $links['next']['text'] ?> »</a>
                    </td>
                </tr>
                </tfoot>
            </table>
        </div>
    <?php endif ?>
    
    <div class="calp-subscribe-buttons">
        
        <?php if( $show_subscribe_buttons ): ?>
            <a class="calp-button calp-subscribe-google" target="_blank" href="<?php echo $subscribe_url ?>">
                <span class='calp-widget-button-s'></span>
                <span class='calp-widget-button-m'><?php echo _e('Subscribe');?></span>
                <span class='calp-widget-button-e'></span>
            </a>
        <?php endif ?>
        
          <?php if( $show_calendar_button ): ?>
            <a class="calp-button calp-calendar-link calp-widget-add-to" href="<?php echo $calendar_url ?>">
                <span class='calp-widget-button-s'></span>
                <span class='calp-widget-button-m'><?php echo _e('View Calendar');?></span>
                <span class='calp-widget-button-e'></span>
            </a>
        <?php endif ?>
        
    </div>

</div>

<?php echo $args['after_widget'] ?>
