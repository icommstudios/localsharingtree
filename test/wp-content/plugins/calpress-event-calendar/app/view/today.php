<div id="calp-daily-container">

    <!-- <list left side> -->
    <div id="calp-daily-left">

      <div id="calp-daily-header" class="">

        <div id="calp-daily-heading">
          <div class="calp-title"><?php echo esc_html($title);?></div>
          <div class="calp-subtitle"><?php echo esc_html($sub_title);?></div>
        </div>
        
        <table id="calp-daily-calendar">
          <thead>
          <tr>
              <?php foreach( $weekdays as $day ) : ?>
                  <td><?php echo $day;?></td>
              <?php endforeach;?>
          </tr>
          </thead>
          <tbody>
              <?php foreach( $weeks as $week ) : ?>
                <tr>
                    <?php foreach( $week as $day ) : ?>
                        <td><?php if ($day['date'])  echo '<a class="calp-cal-date '.($day['today']? 'calp-cal-current': null).'" 
                            href="#action=calp_today&calp_today_offset='.$day['offset'].'">'.$day['date'].'</a>';?></td>
                    <?php endforeach;?>
                </tr>
              <?php endforeach;?>
            </tbody>
        </table>

      </div>

      <div class="calp-clear"></div>
      <div class="calp-hr calp-mb20 calp-mt20 calp-ml20 calp-mr30"></div>

      <div id="calp-daily-list" class="calp-list ">
        <?php if ( $events['empty_day'] ) : ?>
            <div class="calp-no-wrapper">
              <div class="calp-no-events"><?php _e( 'No Events', CALP_PLUGIN_NAME ) ?></div>
            </div>
        <?php else :?>
            <?php foreach( $events['all'] as $event ) : ?>
                <div class="calp-item" popupid="<?php echo $event->instance_id;?>">
                    <?php if( $event->category_colors ): ?>
                        <div class="calp-category-colors popop-colors"><?php echo $event->category_colors ?></div>
                    <?php endif; ?>
                    <div class="calp-list-info">
                        <div class="calp-list-title">
                            <?php echo esc_html( apply_filters( 'the_title', $event->post->post_title ) ) ?>
                        </div>
                         <?php if ( $event->address ): ?>
                            <div class="calp-list-location"><?php echo $event->address; ?></div>
                        <?php endif; ?>
                    </div>
                <div class="calp-border"></div>
                <div class="calp-list-info">
                    <div class="calp-pb-10">
                        <?php if ( !empty($event->venue )): ?>
                            <div class="calp-list-data">
                                <div class="calp-list-property"><?php _e('Venue', CALP_PLUGIN_NAME);?></div>
                                <div class="calp-list-value"><?php echo esc_html( $event->venue ) ?></div>
                            </div>
                            <?php endif; ?>
                            <div class="calp-list-data">
                            <div class="calp-list-property"><?php _e('When', CALP_PLUGIN_NAME);?></div>
                                <div class="calp-list-value">
                                    <?php if( $event->allday ): ?>
                                        <?php echo _e( ' (all-day) ', CALP_PLUGIN_NAME );?>
                                        <?php echo esc_html( $event->short_start_date ); ?>
                                        <?php if( $event->short_end_date != $event->short_start_date ): ?>
                                             <br /><?php echo _e( 'to', CALP_PLUGIN_NAME );?>
                                             <?php echo esc_html( $event->short_end_date ) ?>
                                        <?php endif ?>
                                    <?php else: ?>
                                        <?php echo esc_html( $event->short_start_date.' '.$event->short_start_time ); ?>
                                        <br /><?php echo _e( 'to', CALP_PLUGIN_NAME );?>
                                        <?php echo esc_html( $event->short_end_date.' '.$event->short_end_time ); ?>
                                    <?php endif ?>
                                </div>
                            </div>
                            
                            <?php if( $event->post_excerpt ): ?>
                            <div class="calp-list-data">
                                <div class="calp-list-property"><?php _e('Notes', CALP_PLUGIN_NAME);?></div>
                                <div class="calp-list-value"><?php echo esc_html( $event->post_excerpt ) ?></div>
                            </div>
                            <?php endif; ?>
                    </div>
                </div>
                </div>
            <?php endforeach;?>
            
        <?php endif;?>
                  
      </div>
    </div>
    <div class="calp-clear"></div>
    <!-- </list left side> -->

    <div id="calp-daily-separator"></div>

    <!-- <contents right side> -->
    <div id="calp-daily-contents">

      <div class="calp-timesheet">
        <div id="calp-daily-timesheet-header">
        <table class="calp-alldaytable" cellpadding="0" cellspacing="0" border="0" width="100%">
          <tbody>
              <tr>
                <td style="border: none !important" class="calp-hour" valign="top" rowspan="2"><span style="color:white !important;" class="calp-fixer">00:00</span></td>
                <td class="calp-weekday" valign="top">
                    <div class="calp-allday-label"><?php _e('all-day events', CALP_PLUGIN_NAME);?></div>
                </td>
          </tr>
          <tr class="calp-last">
            <td class="calp-weekday">
                <?php foreach( $events['allday'] as $event ) : ?>
                    <div class="calp-entry">
                        <?php if( $event->category_colors ): ?>
                            <div class="calp-category-colors popop-colors"><?php echo $event->category_colors ?></div>
                        <?php endif; ?>
                        <span class="allday-title" popupid="<?php echo $event->instance_id;?>"><?php echo esc_html( apply_filters( 'the_title', $event->post->post_title ) ) ?></span>
                    </div>
                <?php endforeach;?>
                <?php if ( empty($events['allday']) ) echo '<div style="height:37px;"></div>' ;?>
            </td>
          </tr>
        </tbody></table>
        </div>

        <div class="calp-wrapper" id="calp-daily-timesheet">
          <table class="calp-timetable" cellpadding="0" cellspacing="0" border="0" width="100%"><tbody>
                <?php for ( $i = 0; $i < 48; $i++ ): ?>
                    <tr <?php echo $i == 47 ? " class='calp-last'" : ""?>>
                        <?php if ( $i == 0 ): ?><td width="30"></td><?php endif; ?>
                        <?php if ( $i%2 == 1 ): ?>
                            <td rowspan="2" class="calp-hour">
                                <?php global $calp_settings;
                                    if ( $calp_settings->input_24h_time ) {
                                        echo $i < 47 ? (strlen(($i+1)/2) == 1 ? "0" : "").(($i+1)/2).":00" : "";
                                    } else {
                                      if ($i < 47) {
                                        echo ($i+1)/2 - ($i < 24? 0 : 12);
                                        echo $i < 23 ? "am" : "pm";
                                      }  
                                    }
                                ?>
                            </td>
                        <?php endif; ?>
                        <td class="calp-weekday <?php echo $i%2 == 0 ? "calp-odd":""; ?>" width="100%"></td>

                    </tr>
                <?php endfor; ?>
          </tbody></table>
        
        <div id="calp-daily-timesheet-events" class="calp-none">
            <?php
                $size = 0;
                foreach( $events['notallday'] as $event ) :
                    extract( $event );
                    if ($top < 0) $top = 0;
            ?>
                    <div class="calp-activator">
                        <div class="calp-event" popupid="<?php echo $event->instance_id;?>" style="height: <?php echo $height; ?>px; left: <?php echo $indent * 8 ?>px; top: <?php echo $top ?>px; ">
                            <div class="calp-text"><?php echo $event->post->post_title;?></div>
                            <div style="<?php if (!is_null($event->color_style) && ($event->color_style)) echo 'border-color: '.$event->color_style.' !important;' ;?>" class="calp-frame calp-cal-<?php echo $event->post_id;?>"></div>
                            <div style="<?php if (!is_null($event->color_style) && ($event->color_style)) echo 'background-color: '.$event->color_style.' !important;' ;?> <?php if (!is_null($event->color_style) && ($event->color_style)) echo 'border-color: '.$event->color_style.' !important;' ;?>" class="calp-back calp-cal-<?php echo $event->post_id;?>"></div>
                        </div>
                    </div>
            <?php endforeach;?>
        </div>
      </div>
      <div class="calp-clear"></div>
    </div>

    </div>
    <!-- </contents right side> -->

  </div>

<script type="text/javascript">
    jQuery('#calp-navigator-prev').unbind();
    jQuery('#calp-navigator-prev').bind('click', function() {
        navigation_prev('<?php echo $pagination_links['previous']['2']['href'];?>');
    });
    jQuery('#calp-navigator-next').unbind();
    jQuery('#calp-navigator-next').bind('click', function() {
        navigation_next('<?php echo $pagination_links['middle']['1']['href'];?>');
    });
    var paginator = '<div class="calp-footer-bg calp-left-nav">';
        paginator += '<span id="calp-quick-prev"><a class="<?php echo $pagination_links['previous']['0']['class'] ?>" href="<?php echo esc_attr( $pagination_links['previous']['0']['href'] ) ?>">';
        paginator +=  '<?php echo esc_html( $pagination_links['previous']['0']['text'] ) ?>';
        paginator += '</a></span>';
    paginator += '<span class="calp-footer-dates-separator"></span>';
        paginator += '<span id="calp-nav-label"><a class="<?php echo $pagination_links['previous']['1']['class'] ?>" >';
        paginator +=  '<?php echo esc_html( $pagination_links['previous']['1']['text'] ) ?>';
        paginator += '</a></span>';
    paginator += '</div>';
    paginator += '<div id="calp-navigator-array" class="calp-footer-bg calp-today-navigation-array">';
    <?php foreach ($pagination_links['middle'] as $link ) : ?>
        paginator += '<span><a id="<?php echo $link['id'] ?>" class="<?php echo $link['class'] ?>" href="<?php echo esc_attr( $link['href'] ) ?>">';
        paginator +=  '<?php echo esc_html( $link['text'] ) ?>';
        paginator += '</a></span>';
    <?php endforeach ; ?>
    paginator += '</div>';
    paginator += '<div class="calp-footer-bg calp-right-nav">';
    paginator += '<span class="calp-footer-dates-separator"></span>';
        paginator += '<span id="calp-quick-next"><a class="<?php echo $pagination_links['next']['0']['class'] ?>" href="<?php echo esc_attr( $pagination_links['next']['0']['href'] ) ?>">';
        paginator +=  '<?php echo esc_html( $pagination_links['next']['0']['text'] ) ?>';
        paginator += '</a></span>';
    paginator += '</div>';
    jQuery('#calp-paginator-place').html(paginator);
    jQuery('#calp-today').attr("href", "#action=calp_today&calp_today=1");
    
    var alldayTableHeight = jQuery('.jtec-alldaytable').height();
    var timesheet = jQuery('#jtec-daily-timesheet');
    var heightDiff = alldayTableHeight - 50;

    if ( heightDiff > 0 ) {
      var timesheetHeight = timesheet.height();
      timesheetHeight = timesheetHeight - heightDiff + 20;
      timesheet.height(timesheetHeight);
    }
    
    // scrollbar
    jQuery(document).ready(function(){
        jQuery('#calp-daily-list').tinyscrollbar();        
        jQuery('#calp-daily-timesheet').tinyscrollbar();
    });
    
    jQuery('#calp-daily-list .calp-item, .allday-title, #calp-daily-timesheet-events .calp-event ')
        .die('click').live('click', function(e) {
            var id = jQuery(this).attr('popupid');
            jQuery(this).addClass('showed-popup');
            var x = e.pageX ;
            var y = e.pageY;
            show_item_popup( id, x, y );
    });

    // Resize event bubbles on today view
    if ( jQuery('#calp-daily-timesheet-events').length > 0 ) {
        var all_width = jQuery('#calp-daily-timesheet-events').width() - 3;
        var size = 0;
        jQuery('#calp-daily-timesheet-events .calp-event').each( function() {
            size++;
            width = Math.ceil( all_width/ size );
            jQuery(this).css('width', width);
        });
    }

 /* Set max width for the bubbles */
 jQuery( document ).ready( function( $ ) {
    var column_index = 0;
    $('#calp-daily-timesheet-events').each( function() {
      BubblesWidth( $(this) );
    });
  });
</script>
