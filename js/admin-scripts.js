(function( $ ) {
    "use strict";

    $('#archive-control-edit-page #title').each( function() {
		var input = $(this), prompt = $('#' + this.id + '-prompt-text');

		if ( '' === this.value ) {
			prompt.removeClass('screen-reader-text');
		}

		prompt.click( function() {
			$(this).addClass('screen-reader-text');
			input.focus();
		});

		input.blur( function() {
			if ( '' === this.value ) {
				prompt.removeClass('screen-reader-text');
			}
		});

		input.focus( function() {
			prompt.addClass('screen-reader-text');
		});
	});

}(jQuery));
