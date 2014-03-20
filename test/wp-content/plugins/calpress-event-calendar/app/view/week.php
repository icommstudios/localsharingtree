<div class="calp-navigation-header">
    <h2 class="calp-calendar-title"><?php echo esc_html( $title ) ?></h2>
</div>

<div class="calp-timesheet">
    <table class="calp-alldaytable" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td class="calp-hour" valign="top" rowspan="2"><span class="calp-allday">all-day</span><br><span class="calp-fixer">00:00</span></td>
        <?php foreach( $cell_array as $date => $day ) : ?>
        <td class="calp-weekday calp-header" valign="top">
          <label><span><?php echo date_i18n( 'j', $date, true ) ?></span> <?php echo date_i18n( 'D', $date, true ) ?></label>
            <?php foreach( $day['allday'] as $event ) : ?>
                <div class="calp-activator">
                    <div class="calp-event" popupid="<?php echo $event->instance_id;?>">
                        <div class="calp-body">
                            <div class="calp-text"><?php echo $event->post->post_title;?></div>
                            <div style="<?php if (!is_null($event->color_style) && ($event->color_style)) echo 'border-color: '.$event->color_style.' !important;' ;?>" class="calp-frame calp-cal-<?php echo $event->post_id;?>"></div>
                            <div style="<?php if (!is_null($event->color_style) && ($event->color_style)) echo 'background-color: '.$event->color_style.' !important;' ;?> <?php if (!is_null($event->color_style) && ($event->color_style)) echo 'border-color: '.$event->color_style.' !important;' ;?>" class="calp-back calp-cal-<?php echo $event->post_id;?>"></div>
                        </div>
                    </div>
                </div>
            <?php endforeach // events ?>
        </td>
        <?php endforeach // weekday ?>
    </tr>
      <?php for ( $i = 0; $i < 7; $i++ ): ?>
        <td class="calp-weekday"></td>
      <?php endfor; ?>
    </table>

<div class="calp-wrapper" id="calp-weekly-timesheet">
    <table class="calp-timetable" cellpadding="0" cellspacing="0" border="0">
        <?php for ( $i = 0; $i < 48; $i++ ): ?>
             <tr <?php echo $i == 47 ? " class='calp-last'" : ""?>>
          <?php if ( $i == 0 ): ?><td></td><?php endif; ?>
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
          <?php for ( $m = 0; $m < 7; $m++ ): ?>
            <td class="calp-weekday <?php echo $i%2 == 1 ? "calp-odd":""; ?>"></td>
          <?php endfor; ?>
        </tr>
        <?php endfor; ?>
    </table>

<div id="calp-weekly-timesheet-events" class="calp-none">
    <?php $i = 0; foreach( $cell_array as $day ): ?>
         <div class="calp-daysheet-container" style="left: <?php echo $i++*14.2;?>%;">
            <?php foreach( $day['notallday'] as $event ) : ?>
            <?php extract( $event ); ?>
                <div class="calp-activator">
                    <div class="calp-event" popupid="<?php echo $event->instance_id;?>" style="height: <?php echo $height; ?>px; left: <?php echo $indent * 9 ?>px; top: <?php echo $top ?>px; ">
                    <div class="calp-body">
                        <div class="calp-text"><?php echo $event->post->post_title;?></div>
                        <div style="<?php if (!is_null($event->color_style) && ($event->color_style)) echo 'border-color: '.$event->color_style.' !important;' ;?>" class="calp-frame calp-cal-<?php echo $event->post_id;?>"></div>
                        <div style="<?php if (!is_null($event->color_style) && ($event->color_style)) echo 'background-color: '.$event->color_style.' !important;' ;?> <?php if (!is_null($event->color_style) && ($event->color_style)) echo 'border-color: '.$event->color_style.' !important;' ;?>" class="calp-back calp-cal-<?php echo $event->post_id;?>"></div>
                    </div>
                    </div>
                </div>
             <?php endforeach // events ?>
         </div>
    <?php endforeach // days ?>
</div>
</div>
</div>

<div class="calp-clear"></div>

<script type="text/javascript">
    jQuery('#calp-navigator-prev').unbind();
    var paginator = '<div id="calp-navigator-array" class="calp-footer-bg calp-week-navigator">';
    jQuery('#calp-navigator-prev').bind('click', function() {
        navigation_prev('<?php echo $pagination_links['1']['href'];?>');
    });
    jQuery('#calp-navigator-next').unbind();
    jQuery('#calp-navigator-next').bind('click', function() {
        navigation_next('<?php echo $pagination_links['3']['href'];?>');
    });
    <?php foreach( $pagination_links as $link ): ?>
        paginator += '<span><a class="<?php echo $link['class'] ?>" href="<?php echo esc_attr( $link['href'] );?>">';
            paginator +=  '<?php echo esc_html( $link['text'] ) ?>';
        paginator += '</a></span>'
        paginator += '<span class="calp-footer-dates-separator"></span>';
    <?php endforeach; ?>
    paginator += '</div>';
    jQuery('#calp-paginator-place').html(paginator);
    jQuery('#calp-today').attr("href", "#action=calp_week");
    
    var alldayTableHeight = jQuery('.calp-alldaytable').height();
    var timesheet = jQuery('#calp-weekly-timesheet');
    var heightDiff = alldayTableHeight - 40;

    if ( heightDiff > 0 ) {
      var timesheetHeight = timesheet.height();
      timesheetHeight = timesheetHeight - alldayTableHeight + 40;
      timesheet.height(timesheetHeight);
    }

    timesheet.tinyscrollbar();
    
    jQuery('.calp-daysheet-container .calp-event, .calp-alldaytable .calp-event ')
        .die('click').live('click', function(e) {
            var id = jQuery(this).attr('popupid');
            jQuery(this).addClass('showed-popup');
            var x = e.pageX ;
            var y = e.pageY;
            show_item_popup( id, x, y );
    });

    // Resize event bubbles on week view
    if ( jQuery('#calp-weekly-timesheet-events').length > 0 ) {
        var all_width = jQuery('#calp-weekly-timesheet-events .calp-daysheet-container').width() - 1;
        
        jQuery('#calp-weekly-timesheet-events .calp-daysheet-container').each( function() {
          var size = 0;
          jQuery(this).find('.calp-event').each(function() {
            size++;
            width = Math.ceil( all_width/ size );
            jQuery(this).css('width', width);
          });

        });
    }

  /* Set max width for the bubbles */
  jQuery( document ).ready( function( $ ) {
    var column_index = 0;
    $( '#calp-weekly-timesheet-events .calp-daysheet-container' ).each( function() {
      BubblesWidth( $(this) );
    });
  });
</script>
