jQuery(document).ready(function($) {

  jQuery('.wwa-admin').on('click', '.wwa-add-profile-button', function(event){

    var add_profile_frame;

    event.preventDefault();

    // If the media frame already exists, reopen it.
    if ( add_profile_frame ) {
        add_profile_frame.open();
        return;
    }

    add_profile_frame = wp.media.frames.file_frame = wp.media({

      multiple: 'add',
      frame: 'post',
      library: {type: 'image'}

    });

    // When an image is selected, run a callback.
    add_profile_frame.on('insert', function() {

        var selection = add_profile_frame.state().get('selection');
        var image_ids = [];

        selection.map(function(attachment) {
            attachment = attachment.toJSON();
            image_ids.push(attachment.id);
        });

        var data = {
            action: 'create_profile',
            selection: image_ids,
            _ajax_nonce: wwa.addprofile_nonce
        };

        jQuery.post(wwa.ajaxurl, data, function(response) {
            jQuery(".wwa-profiles").prepend(response);
            jQuery(".no-profiles").remove();
        });
    });

    add_profile_frame.open();

  });



  jQuery('.wwa-admin').on('click', '.wwa-image', function(event){

    var replace_image_frame;

    event.preventDefault();

    var profile_id = event.target.parentNode.id.match(/\d+/);

    profile_id = profile_id[0];

    // If the media frame already exists, reopen it.
    if ( replace_image_frame ) {
        replace_image_frame.open();
        return;
    }

    replace_image_frame = wp.media.frames.file_frame = wp.media({

      title: 'Replace Image',
      multiple: false,
      frame: 'post',
      library: {type: 'image'},
      button: {text: 'Replace Image'}

    });

    // When an image is selected, run a callback.
    replace_image_frame.on('insert', function() {

        var selection = replace_image_frame.state().get('selection');
        var image_ids = [];

        selection.map(function(attachment) {
            attachment = attachment.toJSON();
            image_ids.push(attachment.id);
        });

        var data = {
            action: 'replace_image',
            selection: image_ids,
            profile_id: profile_id,
            _ajax_nonce: wwa.replaceimage_nonce
        };

        jQuery.post(wwa.ajaxurl, data, function(response) {
            jQuery("#wwa-id-" + profile_id + " .wwa-image").replaceWith(response);
        });
    });

    replace_image_frame.open();

  });




  jQuery('.wwa-admin').on('click', '.wwa-delete-profile-button', function(event) {

    event.preventDefault();

    if (confirm("Are you sure? This cannot be undone.")) {

      var data = {
        action: 'delete_profile',
        selection: event.target.value,
        _ajax_nonce: wwa.deleteprofile_nonce
      };

      jQuery.post(wwa.ajaxurl, data, function(response) {
          jQuery("#wwa-id-" + response).remove();
      });

    }

  });




  jQuery('.wwa-admin').on('click', '.wwa-save-profiles-button', function(event) {

    event.preventDefault();

    var form_data = jQuery('.wwa-admin form').serializeArray();

    var data = {
      action: 'save_profiles',
      data: form_data,
      _ajax_nonce: wwa.saveprofiles_nonce
    };

    jQuery.post(wwa.ajaxurl, data, function(response) {
        alert(response);
        window.location.reload();
    });

  });

});
