//show/hide entry
(function($) {
    jQuery.fn.mostrarOcultar = function(){
			
        this.each(function() {
            var elem = $(this);

            if(elem.is(":checked")) {
                elem.nextAll().show();
            } else {
                elem.nextAll().hide();
            }
		});
	   return this;
    };
})(jQuery);

