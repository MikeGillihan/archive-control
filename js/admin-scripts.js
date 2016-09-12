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

    $('#archive-control-options .archive-control-title').change(function() {
        var value = $(this).val();
        if (value == 'default') {
            $(this).parent().find('.archive-control-title-message').hide();
        } else {
            $(this).parent().find('.archive-control-title-message').show();
        }
    });

    $('#archive-control-options .archive-control-order-by').change(function() {
        var value = $(this).val();
        if (value == 'meta_value' || value == 'meta_value_num') {
            $(this).next('.archive-control-meta-key').show();
        } else {
            $(this).next('.archive-control-meta-key').hide();
        }
    });

    $('#archive-control-options .archive-control-before').change(function() {
        var value = $(this).val();
        var placement = $(this).parent().find('.archive-control-before-placement').val();
        if (value == 'textarea') {
            $(this).parent().find('.archive-control-before-pages').show();
            $(this).parent().find('.archive-control-before-placement').show();
            if (placement == 'automatic') {
                $(this).parent().find('.archive-control-before-automatic-message').show();
                $(this).parent().find('.archive-control-before-manual-message').hide();
            } else {
                $(this).parent().find('.archive-control-before-automatic-message').hide();
                $(this).parent().find('.archive-control-before-manual-message').show();
            }
        } else {
            $(this).parent().find('.archive-control-before-pages').hide();
            $(this).parent().find('.archive-control-before-placement').hide();
            $(this).parent().find('.archive-control-before-automatic-message').hide();
            $(this).parent().find('.archive-control-before-manual-message').hide();
        }
    });

    $('#archive-control-options .archive-control-before-placement').change(function() {
        var value = $(this).val();
        if (value == 'automatic') {
            $(this).parent().find('.archive-control-before-automatic-message').show();
            $(this).parent().find('.archive-control-before-manual-message').hide();
        } else {
            $(this).parent().find('.archive-control-before-automatic-message').hide();
            $(this).parent().find('.archive-control-before-manual-message').show();
        }
    });

    $('#archive-control-options .archive-control-after').change(function() {
        var value = $(this).val();
        var placement = $(this).parent().find('.archive-control-after-placement').val();
        if (value == 'textarea') {
            $(this).parent().find('.archive-control-after-pages').show();
            $(this).parent().find('.archive-control-after-placement').show();
            if (placement == 'automatic') {
                $(this).parent().find('.archive-control-after-automatic-message').show();
                $(this).parent().find('.archive-control-after-manual-message').hide();
            } else {
                $(this).parent().find('.archive-control-after-automatic-message').hide();
                $(this).parent().find('.archive-control-after-manual-message').show();
            }
        } else {
            $(this).parent().find('.archive-control-after-pages').hide();
            $(this).parent().find('.archive-control-after-placement').hide();
            $(this).parent().find('.archive-control-after-automatic-message').hide();
            $(this).parent().find('.archive-control-after-manual-message').hide();
        }
    });

    $('#archive-control-options .archive-control-after-placement').change(function() {
        var value = $(this).val();
        if (value == 'automatic') {
            $(this).parent().find('.archive-control-after-automatic-message').show();
            $(this).parent().find('.archive-control-after-manual-message').hide();
        } else {
            $(this).parent().find('.archive-control-after-automatic-message').hide();
            $(this).parent().find('.archive-control-after-manual-message').show();
        }
    });

    $('#archive-control-options .archive-control-pagination').change(function() {
        var value = $(this).val();
        if (value == 'custom') {
            $(this).parent().find('.archive-control-posts-per-page').show();
        } else {
            $(this).parent().find('.archive-control-posts-per-page').hide();
        }
    });

}(jQuery));
