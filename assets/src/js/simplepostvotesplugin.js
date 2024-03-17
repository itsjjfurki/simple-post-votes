jQuery(document).ready(function() {
  // AJAX request to get vote results when the page loads
  jQuery.ajax({
    url: spv_js_vars.site_url+'wp-json/spv/v1/vote-result',
    method: 'POST',
    data: {
      'action': 'spv_nonce',
      'nonce': spv_js_vars.nonce,
      'post_id': spv_js_vars.post_id,
    },
    success: function(response) {
      if(response.success)
      {
        // Update UI with vote results
        jQuery('label[for="spv-vote-btn-yes"] > span').text(response.vote_results.yes+'%');
        jQuery('label[for="spv-vote-btn-no"] > span').text(response.vote_results.no+'%');
        jQuery('input[name="spv-vote-btn"]').prop('disabled', true);

        var selection = Boolean(response.selection);

        // Check the appropriate radio button based on the user's previous selection
        if(selection)
        {
          jQuery('#spv-vote-btn-yes').prop('checked', true);
          jQuery('#spv-vote-btn-no').prop('checked', false);
          jQuery('#spv-vote-btn-no + label').addClass('spv-no-border');
        }
        else
        {
          jQuery('#spv-vote-btn-no').prop('checked', true);
          jQuery('#spv-vote-btn-yes').prop('checked', false);
          jQuery('#spv-vote-btn-yes + label').addClass('spv-no-border');
        }
      }
      // Show the vote container
      jQuery('.spv-container').show();
    },
    error: function(xhr, textStatus, errorThrown) {

    }
  });

  // Handle change event for vote buttons
  jQuery('input[name="spv-vote-btn"]').change(function() {
    if (jQuery(this).is(':checked')) {
      // Get the selected value
      var value = jQuery(this).val();
      // Disable vote buttons and hide container
      jQuery('input[name="spv-vote-btn"]').prop('disabled', true);
      jQuery('.spv-container').hide();
      // AJAX request to submit vote
      jQuery.ajax({
        url: spv_js_vars.site_url+'wp-json/spv/v1/vote',
        method: 'POST',
        data: {
          'action': 'spv_nonce',
          'nonce': spv_js_vars.nonce,
          'value': value,
          'post_id': spv_js_vars.post_id,
          'token': spv_js_vars.token,
        },
        success: function(response) {
          if(response.success)
          {
            // Update UI with vote results and show container
            jQuery('.spv-message').html(spv_js_vars.feedback+'.')
            jQuery('label[for="spv-vote-btn-yes"] > span').text(response.vote_results.yes+'%');
            jQuery('label[for="spv-vote-btn-no"] > span').text(response.vote_results.no+'%');
            jQuery('.spv-container').show();
          }
        },
        error: function(xhr, textStatus, errorThrown) {

        }
      });
    }
  });
});