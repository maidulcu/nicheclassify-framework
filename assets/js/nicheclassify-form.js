jQuery(document).ready(function ($) {
  const form = $('.nc-submit-form.nc-ajax-enabled');

  if (!form.length) return;

  form.on('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('action', 'nc_submit_form');
    formData.append('nonce', ncFormData.nonce);

    form.find('.nc-form-success, .nc-form-error').remove();

    $.ajax({
      url: ncFormData.ajax_url,
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function (res) {
        if (res.success && res.data.html) {
          form[0].reset();
          form.prepend('<p class="nc-form-success">Listing submitted successfully.</p>');

          // Trigger custom event for analytics
          document.dispatchEvent(new CustomEvent('ncFormSubmitted', {
            detail: {
              formId: form.attr('id') || null,
              timestamp: Date.now(),
            }
          }));

          if (typeof gtag === 'function') {
            gtag('event', 'form_submit', {
              event_category: 'Listing',
              event_label: 'Submit Listing Form',
              value: 1
            });
          }
        } else {
          form.prepend('<p class="nc-form-error">An unexpected error occurred.</p>');
        }
      },
      error: function () {
        form.prepend('<p class="nc-form-error">Failed to submit form. Please try again.</p>');
      }
    });
  });
});
