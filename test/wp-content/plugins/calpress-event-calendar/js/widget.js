jQuery( function( $ ) {
    // Show/hide the multiselect containers when user clicks on "limit by" widget options
    $( '.calp-limit-by-cat, .calp-limit-by-tag, .calp-limit-by-event' ).live( 'click', function() {
        $( this ).parent().next( '.calp-limit-by-options-container' ).toggle();
    } );

    $('.widget_show_upcoming_events').live('click', function() {
        if ( $(this).attr('checked') == 'checked' ) {
            $('.widget_show_calendar_navigator').attr('disabled', true);
            $('.widget_show_calendar_navigator_lable').css('color', 'grey');
        } else {
            $('.widget_show_calendar_navigator').attr('disabled', false);
            $('.widget_show_calendar_navigator_lable').css('color', '');
        }
    })
} );