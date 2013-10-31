(function($) {
	$(document).ready( function() {
        $('#product').change(function() {
            //this.form.submit();
            $('#submit').trigger('click');
        });
	});
})(jQuery);
