<!-- START Calpress  Plugin - Version 1.0.0 -->
<script type="text/javascript" src="https://apis.google.com/js/plusone.js">
  {"parsetags": "explicit"}
</script>

<script type="text/javascript">
  jQuery( document ).ready( function( $ ) {
    jQuery('.calp-map-toggle').die('click').live('click', function() {
        var link = jQuery(this);
        var tooltip =  link.parent().parent().parent().parent().children('.calp-tooltip-contents');
        var open_link =  link.parent().parent().parent().parent().children('.calp-open-map');
        var map = jQuery('.calp-map-container#map'+link.attr('mid'));
        var fields = jQuery('.calp-map-containe.calp-tooltip-contents');
        if ( map.is(':hidden') ) {
            if(navigator.appName=="Microsoft Internet Explorer") {
                map.children('iframe').attr('src', map.children('iframe').attr('src') + + Math.random() );
            }
            map.slideDown();
            tooltip.slideUp();
            open_link.slideDown();
            link.html('<?php echo _e( 'Hide map', CALP_PLUGIN_NAME );?>');
        } else {
            map.slideUp();
            tooltip.slideDown();
            open_link.slideUp();
            link.html('<?php echo _e( 'Show map', CALP_PLUGIN_NAME );?>');
        }

        return true;
    });
      // show / hide categgories in filted
      $('#show-hide-categories').live('click', function() { 
          if ( $('#show-hide-categories').hasClass( 'show-all' ) ) {
              $('.calp-filter-selector-container li').removeClass( 'calp-selected' );
              $('#show-hide-categories').removeClass( 'show-all' );
              $('#show-hide-categories').html('<?php echo _e( 'Show All Calendars', CALP_PLUGIN_NAME );?>')
          } else {
              $('.calp-filter-selector-container li').addClass( 'calp-selected' );
              $('#show-hide-categories').addClass( 'show-all' );
              $('#show-hide-categories').html('<?php echo _e( 'Hide All Calendars', CALP_PLUGIN_NAME );?>')
          }
      } ); 
  });
</script>

<div id="calp-search-container" style="display:none;">
<div class="calp-dialogue-dark" id="calp-search-results">
  <div class="calp-bg-topl">
    <div class="calp-bg-topr">
      <div class="calp-bg-top">
        <div class="calp-tip-t"></div>
        <div class="calp-form-menu calp-unselectable">
          <div class="calp-title-popup"><?php echo _e( 'Results', CALP_PLUGIN_NAME );?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="calp-bgl">
    <div class="calp-bgr">
      <div class="calp-bg calp-ofhidden">
          <div class="calp-form-slide">
              <!-- Content -->
          </div>
      </div>
      <div style="clear:both;"></div>
    </div>
  </div>
  <div class="calp-bg-bottoml"><div class="calp-bg-bottomr"><div class="calp-bg-bottom"></div></div></div>
</div>
</div>

<div class="calp-bg-topl">
    <div class="calp-bg-topr">
        <div class="calp-bg-top calp-pt10">
            
					<div class="calp-s-container">
                    
                        <span class="calp-filter-selector-container">
                            <span class="calp-button-s"></span>
                            <a class="calp-button calp-dropdown"><span class="calp-icon-calendar"></span><?php _e( 'Calendar ', CALP_PLUGIN_NAME ) ?></a>
                            <span class="calp-button-e"></span>
                            <input class="calp-selected-terms" id="calp-selected-categories"	type="hidden" value="<?php echo $selected_cat_ids ?>" />
                            
                            <div id="calp-calendar-picker">
                            
                                <div class="calp-bg-topl">
                                <div class="calp-bg-topr">
                                  <div class="calp-bg-top">
                                    <div class="calp-tip-t"></div>
                                    <div class="calp-form-menu calp-unselectable">
                                      <div class="calp-title-popup"><?php echo _e( 'Show Calendars', CALP_PLUGIN_NAME ) ?></div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              
                              <?php if (!empty($categories)) : ?>
                              <div class="calp-bgl">
                                  <div class="calp-brg">
                                      <div class="calp-bg 
                                      calp-ofhidden">
                                          <div class="calp-filter-selector calp-category-filter-selector">
                                              <input class="calp-selected-terms"
                                                id="calp-selected-categories"
                                                type="hidden"
                                                value="<?php echo $selected_cat_ids ?>" />
                                            <ul>
                                                <?php if (!empty($categories)) : ?>
                                                    <li id="show-hide-categories" class="calp-category"><?php echo _e( 'Hide All Calendars', CALP_PLUGIN_NAME ) ?></li>
                                                <?php endif ;?>
                                                <?php foreach( $categories as $cat ): ?>
                                                    <li class="calp-category calp-selected"
                                                        <?php if( $cat->description ) echo 'title="' . esc_attr( $cat->description ) . '"' ?>
                                                        value="<?php echo $cat->term_id;?>">
                                                        <?php echo $cat->color ?>
                                                        <?php echo esc_html( $cat->name ) ?>
                                                        <span class="calp-icon-check calp-fright"></span>
                                                        <input class="calp-term-ids" name="calp-categories" type="hidden" value="<?php echo $cat->term_id ?>" />
                                                    </li>
                                                <?php endforeach ?>
                                            </ul>
                                        </div>
                                      </div>   
                                  </div>
                              </div>
                              <?php endif ;?>
                              
                              <div class="calp-bg-bottoml">
                                  <div class="calp-bg-bottomr">
                                      <div class="calp-bg-bottom"></div>
                                  </div>
                              </div>
                            
                            </div>
                        </span>
                        
					</div>

				<ul class="calp-view-tabs">
          <span class="calp-button-s"></span>
          <li id="calp-view-today-li">
            <a id="calp-view-today" class="calp-load-view calp-button"
              href="#action=calp_today">
              <?php _e( 'Day', CALP_PLUGIN_NAME ) ?>
            </a>
                        <span class="calp-button-separator"></span>
          </li>
          <li id="calp-view-week-li">
            <a id="calp-view-week" class="calp-load-view calp-button"
              href="#action=calp_week">
              <?php _e( 'Week', CALP_PLUGIN_NAME ) ?>
            </a>
                        <span class="calp-button-separator"></span>
          </li>
                    <li id="calp-view-month-li">
            <a id="calp-view-month" class="calp-load-view calp-button"
              href="#action=calp_month">
              <?php _e( 'Month', CALP_PLUGIN_NAME ) ?>
            </a>
                        <span class="calp-button-separator"></span>
          </li>
          <li id="calp-view-agenda-li">
            <a id="calp-view-agenda" class="calp-load-view calp-button"
              href="#action=calp_agenda">
              <?php _e( 'List', CALP_PLUGIN_NAME ) ?>
            </a>
          </li>
          <span class="calp-button-e"></span>
        </ul>
        
            <div id="calp-search-controls" class="">
              <span id="calp-searh-img"></span>
              <input type="text" name="search" id="calp-search-field" defaulttext="<?php _e('Search', CALP_PLUGIN_NAME);?>" value="<?php _e('Search', CALP_PLUGIN_NAME);?>" onfocus="if(this.value == this.defaultValue) this.value = ''">
              <div id="calp-search-helpers">
                <div id="calp-search-clear" onclick="javascript:CALPSearch.doClear()" style="display: none;"></div>
                <div id="calp-search-unhide" onclick="javascript:CALPSearch.show()" style="display: none;"></div>
                <div id="calp-search-loading" style="display: none;"></div>
              </div>
            </div>
            
            <div id="calp-fullscreen-button">
              <a href="javascript:;" onclick="CALPFull.toggle();" title="<?php _e('Click here to toggle full-screen mode on/off', CALP_PLUGIN_NAME);?>" id="calp-full-toggle" class="calp-fullscreen-off"></a>
            </div>
            
        </div>
    </div>
</div>

<div id="calp-calendar-container">
    <div id="calp-calendar-view-container">
        <div id="calp-calendar-view-loading" class="calp-loading"></div>
        <div class="calp-list-container-lb">
        <div class="calp-list-container-rb">
        <div class="calp-list-container-bb">
        <div class="calp-list-container-blc">
        <div class="calp-list-container-brc">
        <div class="calp-list-container-tb">
        <div class="calp-list-container-tlc">
        <div class="calp-list-container-rtc">
            <div id="calp-calendar-frame-wrapper">
                <div id="calp-calendar-view">
                    <?php echo $view; ?>
                </div>
            </div>
        </div>
        </div>
        </div>
        </div>
        </div>
        </div>
        </div>
        </div>
    </div>
</div>

<span id="calp-calender-bottom">
<span class="calp-clear"></span>
<div class="calp-bg-bottoml calp-unselectable">
      <div class="calp-bg-bottomr">
        <div class="calp-bg-bottom" id="calp-footer-container">
            <span id="calp-bottom-today">
            <span class="calp-button-s"></span>
            <span class="calp-title-buttons">
            
                <a id="calp-today" class="calp-load-view calp-button" href="#action=calp_month">
                    <?php _e( 'Today', CALP_PLUGIN_NAME ) ?>
                </a>
            </span>
            <span class="calp-button-e"></span>
            </span>
            
                <div id="calp-date-navigator" class="calp-navigation-monthly">
                      <div id="calp-navigator-prev"></div>
                      <div class="calp-footer-dates-s"></div>
                      
                        <span id="calp-paginator-place"></span>
  
                      <div class="calp-footer-dates-e"></div>
                      <div id="calp-navigator-next"></div>
                </div>

                <div class="calp-export-add">

                    <?php if ( !$hide_subscribe ): ?>
                      <div class="calp-export calp-subscribe-bnt">
                          <span class="calp-button-s"></span>
                          <span class="calp-title-buttons">
                          <a class="calp-load-view calp-button" target="_blank" href="<?php echo $export_url;?>">
                              <?php echo _e( 'Subscribe', CALP_PLUGIN_NAME );?>
                          </a>
                          </span>
                          <span class="calp-button-e"></span>
                      </div>
                    <?php endif ?>
                </div>
          </div>
        </div>
      </div>
</span>
