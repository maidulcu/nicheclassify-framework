jQuery(document).ready(function ($) {
  let mediaFrame;

  // Media field
  $('.nc-select-media').on('click', function (e) {
    e.preventDefault();
    const button = $(this);
    const target = button.data('target');
    const input = $('input[name="' + target + '"]');
    const preview = input.siblings('.nc-media-preview');

    if (mediaFrame) {
      mediaFrame.open();
      return;
    }

    mediaFrame = wp.media({
      title: 'Select Image',
      button: { text: 'Use this image' },
      multiple: false,
    });

    mediaFrame.on('select', function () {
      const attachment = mediaFrame.state().get('selection').first().toJSON();
      input.val(attachment.id);
      preview.html(
        '<div class="nc-media-item">' +
          '<img src="' + attachment.url + '" alt="">' +
          '<button type="button" class="nc-remove-media">×</button>' +
        '</div>'
      );
    });

    mediaFrame.open();
  });

  $(document).on('click', '.nc-remove-media', function () {
    const wrapper = $(this).closest('.nc-media-preview');
    wrapper.empty();
    wrapper.siblings('input.nc-media-field').val('');
  });

  // Gallery field
  $('.nc-select-gallery').on('click', function (e) {
    e.preventDefault();
    const button = $(this);
    const target = button.data('target');
    const input = $('input[name="' + target + '[]"]');
    const preview = input.siblings('.nc-gallery-preview');

    const galleryFrame = wp.media({
      title: 'Select Images',
      button: { text: 'Add to Gallery' },
      multiple: true,
    });

    galleryFrame.on('select', function () {
      const selection = galleryFrame.state().get('selection');
      const ids = [];
      preview.empty();

      selection.each(function (attachment) {
        const data = attachment.toJSON();
        ids.push(data.id);
        preview.append(
          '<div class="nc-gallery-item" data-id="' + data.id + '">' +
            '<img src="' + data.url + '" alt="">' +
            '<button type="button" class="nc-remove-gallery-item">×</button>' +
          '</div>'
        );
      });

      input.val(ids.join(','));
    });

    galleryFrame.open();
  });

  $(document).on('click', '.nc-remove-gallery-item', function () {
    const container = $(this).closest('.nc-gallery-item');
    const gallery = container.closest('.nc-gallery-preview');
    container.remove();
    const remainingIds = [];
    gallery.find('.nc-gallery-item').each(function () {
      remainingIds.push($(this).data('id'));
    });
    gallery.siblings('input.nc-gallery-field').val(remainingIds.join(','));
  });

  // Group field repeater logic
  $(document).on('click', '.nc-add-group-entry', function (e) {
    e.preventDefault();
    const button = $(this);
    const groupKey = button.data('group');
    const container = button.closest('.nc-group-field');
    const template = container.find('.nc-group-template').first().clone();
    const newIndex = container.find('.nc-group-entry').length - 1;

    template
      .removeClass('nc-group-template')
      .show();

    template.find('input, textarea, select').each(function () {
      const originalName = $(this).attr('name');
      if (originalName) {
        const updatedName = originalName.replace('__index__', newIndex);
        $(this).attr('name', updatedName).val('');
      }
    });

    container.find('.nc-group-template').before(template);
  });

  // Remove a group entry
  $(document).on('click', '.nc-remove-group-entry', function (e) {
    e.preventDefault();
    const entry = $(this).closest('.nc-group-entry');
    const container = entry.closest('.nc-group-field');
    const template = container.find('.nc-group-template');

    // Ensure at least one template remains
    if (container.find('.nc-group-entry').not(template).length > 1) {
      entry.remove();
    } else {
      entry.find('input, textarea, select').val('');
    }
  });
});
