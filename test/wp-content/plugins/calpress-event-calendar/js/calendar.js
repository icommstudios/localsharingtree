// Used to ensure that Entities used in L10N strings are correct
function calp_convert_entities( o ) {
	var c, v;

	c = function( s ) {
		if( /&[^;]+;/.test( s ) ) {
			var e = document.createElement( 'div' );
			e.innerHTML = s;
			return ! e.firstChild ? s : e.firstChild.nodeValue;
		}
		return s;
	}

	if( typeof o === 'string' ) {
		return c( o );
	} else if( typeof o === 'object' ) {
		for( v in o ) {
			if( typeof o[v] === 'string' ) {
				o[v] = c( o[v] );
			}
		}
	}
	return o;
}

jQuery( document ).ready( function( $ ) {

	// =====================================
	// = Calendar CSS selector replacement =
	// =====================================

	if( calp_calendar.selector != undefined && calp_calendar.selector != '' &&
	    $( calp_calendar.selector ).length == 1 )
	{
		// Try to find an <h#> element containing the title
		var $title = $( ":header:contains(" + calp_calendar.title + "):first" );
		// If none found, create one
		if( ! $title.length ) {
			$title = $( '<h1 class="page-title"></h1>' );
			$title.text( calp_calendar.title ); // Do it this way to automatically generate HTML entities
		}

		var $calendar = $( '#calp-container' )
			.detach()
			.before( $title );

		$( calp_calendar.selector )
			.empty()
			.append( $calendar )
			.hide()
			.css( 'visibility', 'visible' )
			.fadeIn( 'fast' );
	}

	// =================================
	// = General script initialization =
	// =================================

	// Variable storing currently displayed view
	var current_hash = '';

	// Check whether appropriate classes have been added to <body> (some themes
	// don't respect the WP body_class() function). If not, add them, or our app
	// won't function properly.
	var classes = $('body').attr( 'class' );
	if( classes == undefined ) classes = '';
	if( classes.match( /\s?\bcalp-[\w-]+\b/ ) == null ) {
		// Add action body class(es)
		classes += ' ' + calp_calendar.body_class;
		$('body').attr( 'class', classes );
	}

	/**
	 * Function used to update view if user has clicked back/forward in the
	 * browser.
	 */
	function check_hash() {
		var live_hash = document.location.hash;
		var default_hash = calp_convert_entities( calp_calendar.default_hash );
		// If current_hash doesn't match live hash, and the document's live hash
		// isn't empty, or if it is, the current_hash isn't equivalent to empty
		// (i.e., default hash), the page needs to be updated.
		if(  (current_hash != live_hash &&
		    ( live_hash != '' || current_hash != default_hash ) )
            || live_hash == '' && current_hash == '' ) {
			// If hash is empty, resort to original requested action
			var hash = live_hash;
			if( ! hash )
				hash = default_hash;
			load_view( hash );
		}
	}

	// Monitor browser navigation between different URL #hash values
	setInterval( check_hash, 150 );

	/**
	 * Load a calendar view represented by the given hash value.
	 */
	function load_view( hash ) {

		// Reveal loader behind view
		$('#calp-calendar-view-loading').fadeIn( 'fast' );
		$('#calp-calendar-view').fadeTo( 'fast', 0.3,
			// After loader is visible, fetch new content
			function() {
                var cat_ids = apply_filters();
				var query = hash.substring( 1 ) ;//+ '&calp_cat_ids=1,2,3,4';
                if ( cat_ids )
                    query += '&calp_cat_ids='+cat_ids;
                
				// Fetch AJAX result
				$.post( calp_calendar.ajaxurl, query, function( data )
					{
						// Replace action body class with new one
						var classes = $('body').attr( 'class' );
						classes = classes.replace( /\s?\bcalp-[\w-]+\b/g, '' );
						classes += ' ' + data.body_class;
						$('body').attr( 'class', classes );

						// Animate vertical height of container between HTML replacement
						var $container = $('#calp-calendar-view-container');
						$container.height( $container.height() );
						var new_height = $('#calp-calendar-view').html( data.html );
                        $container.height( 'auto' );

						// Do any general view initialization after loading
						initialize_view();
					},
					'json'
				);
			} );

		// Update stored hash
		current_hash = hash;
	}

    $('a.calp-load-view').die('click');
	// Register navigation click handlers
	$('a.calp-load-view').live( 'click', function() {
		// Load requested view
		load_view( $(this).attr( 'href' ) );
	} );
    
	/**
	 * Callback for mouseenter event on .calp-event element
	 */
	function show_popup() {
		var $popup = $(this).prev();

		// If not already done, position popup so that it does not exceed
		// right/left bounds of container.
		if( ! $popup.data( 'calp_offset' ) ) {
			// Keep popup hidden but positionable
			$popup.css( 'visibility', 'hidden' ).show();

			var $container = $('#calp-calendar-view-container');
			var popup_width = $popup.width();
			var popup_offset = $popup.offset();
			var container_offset = $container.offset();
			var container_x2 = container_offset.left + $container.width();
			// Respect leflt-side bounds
            if( $( '.calp-event-summary', $popup ).offset().left - 500 < container_offset.left )
				$popup.addClass( 'calp-shifted-right' );
            // Respect right-side bounds
            if( popup_offset.left + popup_width - 50 > container_x2 )
				$popup.offset( { left:  popup_offset.left - popup_width - 112, top: popup_offset.top } );
			// Restore popup to 'display: none'
			$popup.hide().css( 'visibility', 'visible' );
			// Flag the object so we don't calculate twice.
			$popup.data( 'calp_offset', true );
		}
	}
    
	function hide_popup() {
		$(this)
			.fadeOut( 100, function() { $(this).parent().css( { zIndex: 'auto' } ); } )
			.data( 'calp_mouseinside', false );
	}

	// Register popup click handlers for month/week views
	$('.calp-month-view .calp-event, .calp-week-view .calp-event, .calp-week-view .calp-event-container ')
        .live ('click', function(e) {
            // Get click coordinates
            var x = e.pageX ;//- this.offsetLeft;
            var y = e.pageY ;//- this.offsetTop;
            
            // Get popup
            if ( $(this).hasClass('calp-event-container') 
                || $(this).hasClass('calp-item')
            ) {
                var $popup = $(this).children('.calp-event-popup');
            } else {
                var $popup = $(this).prev();
            }
			// Keep popup hidden but positionable
			$popup.css( 'visibility', 'hidden' ).show();
			var $container = $('#calp-calendar-view-container');
            var $tooltip = $('.calp-left-arrow');
			var popup_width = $popup.width();
			var popup_height = $popup.height();
            var arrow_top = Math.abs( popup_height / 2 - $tooltip.height() );
            // Respect leflt-side bounds
			if( x - popup_width < $container.offset().left ) {
				$popup.addClass( 'calp-shifted-right' );
                $( '.calp-left-arrow', $popup ).css('top', arrow_top );
                $popup.offset( { left:  x , top: y - popup_height / 2 } );
            } else {
                $popup.offset( { left:  x - popup_width,  top: y - popup_height / 2  } );
                $( '.calp-right-arrow', $popup ).css('top', arrow_top );
            }
			// Restore popup to 'display: none'
            $popup.show().css( 'visibility', 'visible' );
			// Flag the object so we don't calculate twice.
			$popup.data( 'calp_offset', true );
    } );
    
    // hide all popups
    $('body').click(function (e) {
        if ($(e.target).closest('.calp-event-popup').length == 0) {
            $('.calp-event-popup:visible').each( hide_popup );
        }
        
        $('.showed-popup').removeClass('showed-popup');
        
        if ( $(e.target).closest('#calp-calendar-picker').length == 0 
            && $('#calp-calendar-picker').css('display') != 'none'
            ) {
                $('#calp-calendar-picker').fadeOut( 'fast' );
                $('.calp-dropdown').parent().removeClass('calp-calander-showed');
                
                 load_view( document.location.hash );
        }

        if ( $(e.target).closest('#calp-search-unhide').length == 0
            && $(e.target).closest('#calp-search-clear').length == 0
            && $(e.target).closest('#calp-search-container').length == 0
            && $(e.target).closest('#calp-search-field').length == 0 ) {
            CALPSearch.close();
        }
    });
    
    // hide popup if escape pressed
    $(document).on('keydown.closeEsc', function(e){
        if(e == null) {
            k = event.keyCode;
        } else {
            k = e.which;
        }
        if(k == 27) {
            $('.calp-event-popup:visible').each( hide_popup );
            
            $('.showed-popup').removeClass('showed-popup');
            
            if( $('#calp-calendar-picker').css('display') != 'none' ) {
                $('#calp-calendar-picker').fadeOut( 'fast' );
                $('.calp-dropdown').parent().removeClass('calp-calander-showed');
                 load_view( document.location.hash );
            }
        }
    });
    
    // Close popup button click
    $('.calp-close-popup').live('click', function () {
        $('.calp-event-popup:visible').each( hide_popup );
    });
    
    // Hide popup on read more click
    $('.calp-popup-read-more').die('click').live('click', function() {
        $('.calp-event-popup:visible').each( hide_popup );
    });

	// ========================
	// = Category/tag filters =
	// ========================
    $( '.calp-category-filter-selector li' ).click( function() {
        var data = new Array();
        if( $( this ).hasClass( 'calp-selected' ) ) {
            // Element deselected, remove class
            $( this ).removeClass( 'calp-selected' );
        } else {
            // Element selected, add class
            $( this ).addClass( 'calp-selected' );
        }
    } );

	/**
	 * Applies the active category/tag filters to the current view.
	 * (Shows/hides events as necessary.)
	 */
	function apply_filters()
	{
        var query = {
			'action': 'calp_term_filter',
			'calp_term_ids': selected_ids
		};
        
		// Submit the selected term IDs via AJAX and filter the visible list of
		// post IDs. Only include filter selectors that have a selection.
		var selected_ids = new Array();
        var selected_cats = '';
        var cat_item = 0;
        $('.calp-category-filter-selector ul li.calp-selected').each(function() {
            cat_item = $(this).val();
            if ( cat_item > 0 ) {  
                selected_ids.push( cat_item );
            }
        })
        
        if ( selected_ids.length ) {
             selected_cats = '&calp_cat_ids=' + selected_ids.join();
        }
        
		selected_tags =
			$('.calp-filters-container .calp-dropdown.calp-selected + #calp-selected-tags').val();
		if( selected_tags ) {
			selected_ids.push( selected_tags );
			selected_tags = '&calp_tag_ids=' + selected_tags;
		} else {
			selected_tags = '';
		}

		selected_ids = selected_ids.join();

		// Modify export URL
		var export_url = calp_convert_entities( calp_calendar.export_url );
		if( selected_ids.length ) {
			export_url += selected_cats + selected_tags;
		}

        return selected_ids;
	}
    
    // display filter block
    $('.calp-dropdown').live('click', function() {
        if( $('#calp-calendar-picker').css('display') == 'none' ) {
            $('#calp-calendar-picker').fadeIn( 'fast' );
            $('.calp-dropdown').parent().addClass('calp-calander-showed');
        } else {
			$('#calp-calendar-picker').fadeOut( 'fast' );
            $('.calp-dropdown').parent().removeClass('calp-calander-showed');
            
            load_view( document.location.hash );
        }
    } );
    
    // show / hide export popup
    $('.calp-export-add').live('click', function() {
        if( $('#calp-export-tooltip').css('display') == 'none' ) {
            $('#calp-export-tooltip').fadeIn( 'fast' );
        }
    } );

	/**
	 * function initialize_view
	 *
	 * General initialization function to execute whenever any view is loaded
	 * (this is also called at the end of load_view()).
	 */
	function initialize_view()
	{
        // Hide loader
        $('#calp-calendar-view-loading').fadeOut( 'fast' );
        $('#calp-calendar-view').fadeTo( 'fast', 1.0 );
	}

	initialize_view();
    
    // initialize Search
    CALPSearch.init();

} );

    /**
     * Navigation previous link
     */
    function navigation_prev(href)
    {
        window.location.href = href;
    }
    
     /**
     * Navigation next link
     */
    function navigation_next(href)
    {
        window.location.href = href;
    }
    
    /**
     * Go to event on agenda page
     */
    function go_to_event(event_id)
    {
        var query = {
			'action': 'calp_agenda_item',
			'calp_item_id':  event_id
		};

        // Delay loading animation so that it doesn't appear if the AJAX turnover
		// is quick enough
        jQuery('#calp-calendar-view-loading').fadeIn( 'fast' );
		jQuery('#calp-calendar-view').fadeTo( 'fast', 0.3);
		
        jQuery.post( calp_calendar.ajaxurl, query, function( data ) {
            if ( data.html ) {
                jQuery('#calp-event-single').html( data.html );
            } else {
                jQuery('#calp-event-single').html( '<div class="calp-no-events"><div style="padding-top: 230px">No Events</div></div>' );
            }
            
            // Hide loader
            jQuery('#calp-calendar-view-loading').fadeOut( 'fast' );
            jQuery('#calp-calendar-view').fadeTo( 'fast', 1.0 );
            
        }, 'json');
  }
  
    /**
   * Show Item popup
   */
    function show_item_popup(event_id, x, y)
    {
        var query = {
			'action': 'calp_popup',
			'calp_item_id':  event_id
		};
		
        jQuery.post( calp_calendar.ajaxurl, query, function( data ) {
            if ( data.html ) {
                var container = jQuery('#calp-calendar-view-container');
                var popup = jQuery(data.html);
                jQuery('body').append( popup );
                var tooltip = jQuery('.calp-left-arrow');
                var popup_width = 350;
                var popup_height = popup.height();
                var arrow_top = Math.abs( popup_height / 2 - tooltip.height() );
                jQuery('body .calp-event-popup').remove();
                var popup = jQuery(data.html);
                if( x - popup_width < container.offset().left ) {
                    popup.addClass( 'calp-shifted-right' );
                    jQuery( '.calp-left-arrow', popup ).css('left','1px').css('top', arrow_top );
                    //popup.offset( { left:  10 , top: 20 } );
                    popup.css('left', x+'px').css('top', y - popup_height / 2 + 'px' );
                } else {
                    popup.css('left',   x - popup_width +'px').css('top', y - popup_height / 2 + 'px' );
                    //popup.offset( { left:  x - popup_width,  top: y - popup_height / 2  } );
                    jQuery( '.calp-right-arrow', popup ).css('left','329px').css('top', arrow_top );
                }
                
                popup.css('position', 'absolute').css('z-index', '10002').css('visibility', 'visible');
                popup.css('visibility', 'visible');
                jQuery('body').append( popup ).show();
            }
        }, 'json');
  }
  
    var CALPFull = {
      offClass            : "calp-fullscreen-off",
      onClass             : "calp-fullscreen-on",
      mainFrame           : "calp-container",
      cover               : "calp-fullscreen-cover",
      fullscreenClass     : "calp-fullscreen",
      fullscreenBigClass  : "calp-fullscreen-extra",
      fullScrButtonId     : "calp-full-toggle",
      maxwidth            : 1200,
      frameHeight         : 600,
      zIndexFull          : 10001,
      zIndexNormal        : 1,
      cookieName          : "calp-full-screen",
    
      /*init: function() {
        var cookie = jQuery.cookie(this.cookieName);
        if ( cookie ) {
          this.toggle();
        }
      }, */ 
    
      toggle: function() {
        var toggleButton = jQuery('#'+this.fullScrButtonId);
        var toggle;
    
        var toggle = toggleButton.hasClass(this.offClass) ? true : false;
        var frame = jQuery('.'+this.mainFrame);
        var cover = jQuery('#'+this.cover);
    
        var setClass = jQuery('body').width() > this.maxwidth ? this.fullscreenBigClass : this.fullscreenClass;
        //var setCookie = toggle ? toggle : null;
    
        //jQuery.cookie(this.cookieName, setCookie, {expire: 7, path: '/'});
        
     if ( toggle ) {
          toggleButton.removeClass(this.offClass).addClass(this.onClass);
    
          frame.addClass(setClass);
          var wHeight = jQuery(window).height();
          var top = Math.floor((wHeight - frame.height())/2);
          frame.css('width',  jQuery(window).width() / 1.1 );
          frame.css('left',  (jQuery(window).width() - jQuery(window).width() / 1.1) / 2 );
          frame.css('position', 'fixed');
          frame.css('top', top);
          frame.css('z-index', this.zIndexFull);
          cover.show();
        } else {
          toggleButton.removeClass(this.onClass).addClass(this.offClass);
          frame.removeClass(this.fullscreenClass+" "+this.fullscreenBigClass);
          frame.css('position', '');
          frame.css('top', '');
          frame.css('left', '');
          frame.css('width',  '');
          frame.css('z-index', this.zIndexNormal);
          cover.hide();
        }
      }
    }

/* Set max width for the bubbles */
function BubblesWidth( bubble_column ) {
    var column_index = 0;
    var block_width = bubble_column.width();
    var columns = [];
    var lastEventEnding = null;

    // Create an array of all events
    var events = jQuery('.calp-event', bubble_column ).map(function(index, o) {
      o = jQuery(o);
      var top = o.offset().top;
      return {
        'obj': o,
        'top': top,
        'bottom': top + o.height()
      };
    }).get();
    // Sort it by starting time, and then by ending time.
    events = events.sort(function(e1,e2) {
      if (e1.top < e2.top) return -1;
      if (e1.top > e2.top) return 1;
      if (e1.bottom < e2.bottom) return -1;
      if (e1.bottom > e2.bottom) return 1;
      return 0;
    });

    // Iterate over the sorted array
   jQuery(events).each(function(index, e) {

      if (lastEventEnding !== null && e.top >= lastEventEnding) {
        PackEvents( columns, block_width );
        columns = [];
        lastEventEnding = null;
      }

      var placed = false;
      for (var i = 0; i < columns.length; i++) {                   
        var col = columns[ i ];
        if (!collidesWith( col[col.length-1], e ) ) {
          col.push(e);
          placed = true;
          break;
        }
      }

      if (!placed) {
        columns.push([e]);
      }

      if (lastEventEnding === null || e.bottom > lastEventEnding) {
        lastEventEnding = e.bottom;
      }
    });

    if (columns.length > 0) {
      PackEvents( columns, block_width );
    }
}

function PackEvents( columns, block_width )
{
  var n = columns.length;
  for (var i = 0; i < n; i++) {
    var col = columns[ i ];
    for (var j = 0; j < col.length; j++)
    {
      var bubble = col[j];
      bubble.obj.css( 'left', (i / n)*100 + '%' );
      bubble.obj.css( 'width', block_width/n - 1 );
    }
  }
}

function collidesWith( a, b )
{
  return a.bottom > b.top && a.top < b.bottom;
}

var CALPSearch = {
  searchFieldId: "calp-search-field",
  searchContainerId: "calp-search-container",
  loadingId: "calp-search-loading",
  unhideId: "calp-search-unhide",
  clearId: "calp-search-clear",

  isVisible: false,
  hasResults: false,
  debounceTimeout: 250,
  slideTime: 300,
  zIndex: 5000,
  showOlder: 0,
  searchQuery: null,

  init: function() {

    this.container = jQuery('#'+this.searchContainerId);
    this.loading = jQuery('#'+this.loadingId);
    this.unhide = jQuery('#'+this.unhideId);
    this.clear = jQuery('#'+this.clearId);
    this.searchField = jQuery('#'+this.searchFieldId);
    this.defaultText = this.searchField.attr('defaulttext');

    // create environment variable
    var e = this;
    // keypress
    this.searchField.die('keyup');
    this.searchField.live('keyup', function() {
      var s = e.getSearchString();
      if ( !e.isLoading ) {
        if ( e.length > 0 ) {
          e.unhide.hide();
          e.clear.show();
        } else {
          e.clear.hide();
        }
      }
      
      setTimeout( function(e) {
        CALPSearch.doSearch();
      }, this.debounceTimeout );
      
    });
    // focus
    this.searchField.die('focus');
    this.searchField.live('focus', function() {
      var s = e.getSearchString();
      if ( e.hasResults && !e.isVisible && s.length > 0 ) {
        e.show();
      }
    })
  },

  getSearchString: function() {
    return jQuery.trim(this.searchField.val());
  },
  
   showLoading: function() {
    var e = CALPSearch;
    e.clear.hide();
    e.unhide.hide();
    e.loading.show();
    e.isLoading = true;
  },

  hideLoading: function() {
    var e = CALPSearch;
    e.loading.hide();
    e.clear.show();
    e.isLoading = false;
  },

  doSearch: function() {
    var searchValue = this.getSearchString();
    if ( !searchValue.length )
      return;
    
    var selected_ids = new Array();
    var cat_item = 0;
    jQuery('.calp-category-filter-selector ul li.calp-selected').each(function() {
        cat_item = jQuery(this).val();
        if ( cat_item > 0 ) {  
            selected_ids.push( cat_item );
        }
    });
    
    var query = {
        'action': 'calp_search',
        'calp_search_text':  escape(searchValue),
        'calp_cat_ids': selected_ids.join()
    };
    
    // show loader
    CALPSearch.showLoading();
    
    jQuery.post( calp_calendar.ajaxurl, query, function( data ) {
        if ( data.html ) {
           jQuery('#calp-search-container .calp-form-slide').html( data.html );
        } else {
            jQuery('#calp-search-container .calp-form-slide').html('<div class="calp-property calp-no-results">No Results</div>');
        }
        
        CALPSearch.setContent();
    }, 'json');
  },

  setContent: function(data) {
    var e = CALPSearch; // declare environment variable - REQUIRED
    e.hideLoading();
    e.hasResults = true;
    e.container.html(data);
    e.show();
  },
  show: function() {
    var e = CALPSearch;

    if ( !e.isVisible ) {
      if ( e.hasResults ) {
        e.container.show();
      } else {
        e.container.slideDown(e.slideTime);
      }
    }

    e.isVisible = true;
    e.unhide.hide();
    e.clear.show();
  },

  close: function(noUnhide) {
    var e = CALPSearch;

    e.isVisible = false;
    e.container.hide();
    e.clear.hide();
    if ( noUnhide != true  && this.searchField.val() != this.defaultText && this.searchField.val() != '' )
      e.unhide.show();
  },

  doClear: function() {
    this.searchField.val('');
    this.hasResults = false;
    this.close(true);
  }
}
