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

    $('.archive-control-title').change(function() {
        var value = $(this).val();
        if (value == 'default') {
            $(this).parent().find('.archive-control-title-message').hide();
        } else {
            $(this).parent().find('.archive-control-title-message').show();
        }
    });

    $('.archive-control-order-by').change(function() {
        var value = $(this).val();
        if (value == 'meta_value' || value == 'meta_value_num') {
            $(this).next('.archive-control-meta-key').show();
        } else {
            $(this).next('.archive-control-meta-key').hide();
        }
    });

    $('.archive-control-image').change(function() {
        var value = $(this).val();
        var placement = $(this).parent().find('.archive-control-image-placement').val();
        if (value == 'enabled') {
            $(this).parent().find('.archive-control-image-pages').show();
            $(this).parent().find('.archive-control-image-placement').show();
            if (placement == 'automatic') {
                $(this).parent().find('.archive-control-image-automatic-message').show();
                $(this).parent().find('.archive-control-image-manual-message').hide();
            } else {
                $(this).parent().find('.archive-control-image-automatic-message').hide();
                $(this).parent().find('.archive-control-image-manual-message').show();
            }
        } else {
            $(this).parent().find('.archive-control-image-pages').hide();
            $(this).parent().find('.archive-control-image-placement').hide();
            $(this).parent().find('.archive-control-image-automatic-message').hide();
            $(this).parent().find('.archive-control-image-manual-message').hide();
        }
    });

    $('.archive-control-image-placement').change(function() {
        var value = $(this).val();
        if (value == 'automatic') {
            $(this).parent().find('.archive-control-image-automatic-message').show();
            $(this).parent().find('.archive-control-image-manual-message').hide();
        } else {
            $(this).parent().find('.archive-control-image-automatic-message').hide();
            $(this).parent().find('.archive-control-image-manual-message').show();
        }
    });

    $('.archive-control-before').change(function() {
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

    $('.archive-control-before-placement').change(function() {
        var value = $(this).val();
        if (value == 'automatic') {
            $(this).parent().find('.archive-control-before-automatic-message').show();
            $(this).parent().find('.archive-control-before-manual-message').hide();
        } else {
            $(this).parent().find('.archive-control-before-automatic-message').hide();
            $(this).parent().find('.archive-control-before-manual-message').show();
        }
    });

    $('.archive-control-after').change(function() {
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

    $('.archive-control-after-placement').change(function() {
        var value = $(this).val();
        if (value == 'automatic') {
            $(this).parent().find('.archive-control-after-automatic-message').show();
            $(this).parent().find('.archive-control-after-manual-message').hide();
        } else {
            $(this).parent().find('.archive-control-after-automatic-message').hide();
            $(this).parent().find('.archive-control-after-manual-message').show();
        }
    });

    $('.archive-control-pagination').change(function() {
        var value = $(this).val();
        if (value == 'custom') {
            $(this).parent().find('.archive-control-posts-per-page').show();
        } else {
            $(this).parent().find('.archive-control-posts-per-page').hide();
        }
    });

    $("#buy-coffee-button").click( function() {
        $("#paypal-form").submit();
        e.preventDefault();
    });

    // Set all variables to be used in scope
    var frame,
    metaBox = $('#featured-image-archive'), // Your meta box id here
    addImgLink = metaBox.find('.upload-featured-image-archive-img'),
    delImgLink = metaBox.find( '.delete-featured-image-archive-img'),
    imgContainer = metaBox.find( '.featured-image-archive-container'),
    imgIdInput = metaBox.find( '.featured-image-id' );

    // ADD IMAGE LINK
    addImgLink.on( 'click', function( event ){

        event.preventDefault();

        // If the media frame already exists, reopen it.
        if ( frame ) {
            frame.open();
            return;
        }

        // Create a new media frame
        frame = wp.media({
            title: archive_control.media_upload_title_text,
            button: {
                text: archive_control.media_upload_button_text
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected in the media frame...
        frame.on( 'select', function() {
            // Get media attachment details from the frame state
            var attachment = frame.state().get('selection').first().toJSON();
            // Send the attachment URL to our custom image input field.
            imgContainer.append( '<img src="'+attachment.url+'" alt="" style="max-width:100%;"/>' );
            // Send the attachment id to our hidden input
            imgIdInput.val( attachment.id );
            // Hide the add image link
            addImgLink.addClass( 'hidden' );
            // Unhide the remove image link
            delImgLink.removeClass( 'hidden' );
        });

        // Finally, open the modal on click
        frame.open();
    });

    // DELETE IMAGE LINK
    delImgLink.on( 'click', function( event ){

        event.preventDefault();

        // Clear out the preview image
        imgContainer.html( '' );

        // Un-hide the add image link
        addImgLink.removeClass( 'hidden' );

        // Hide the delete image link
        delImgLink.addClass( 'hidden' );

        // Delete the image id from the hidden input
        imgIdInput.val( '' );

    });

}(jQuery));
