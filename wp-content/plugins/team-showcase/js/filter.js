
jQuery.noConflict();


jQuery('#ts-all').addClass('ts-current-li');
    jQuery("#ts-filter-nav > li").click(function(){
        ts_show(this.id);
    });
	


//FILTER CODE
function ts_show(category) {	 
	
	if (category == "ts-all") {
        jQuery('#ts-filter-nav > li').removeClass('ts-current-li');
        jQuery('#ts-all').addClass('ts-current-li');
        jQuery('.tshowcase-filter-active').show('slow');
		}
	
	else {
		jQuery('#ts-filter-nav > li').removeClass('ts-current-li');
   		jQuery('#' + category).addClass('ts-current-li');  
		jQuery('.' + category).show('slow');
		jQuery('.tshowcase-filter-active:not(.'+ category+')').hide('slow');
	}
	
}