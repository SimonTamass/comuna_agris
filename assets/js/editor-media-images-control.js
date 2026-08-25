(function ($) {
  'use strict';

  window.addEventListener('elementor/init', () => {
    const BaseData = window.elementor?.modules?.controls?.BaseData;
    if (!BaseData || !window.elementor?.addControlView || !window.wp?.media) return;

    const strings = window.agrisMediaImagesControl || {};
    const MediaImagesControl = BaseData.extend({
      ui() {
        const ui = BaseData.prototype.ui.apply(this, arguments);
        ui.selectImages = '.agris-media-images-select';
        ui.clearImages = '.agris-media-images-clear';
        ui.imageList = '[data-agris-media-images-list]';
        ui.status = '[data-agris-media-images-status]';
        ui.buttonLabel = '[data-agris-media-images-button]';
        return ui;
      },

      events() {
        return _.extend(BaseData.prototype.events.apply(this, arguments), {
          'click @ui.selectImages': 'onSelectImages',
          'click @ui.clearImages': 'onClearImages',
          'click .agris-media-images-remove': 'onRemoveImage',
        });
      },

      applySavedValue() {
        this.renderImages();
      },

      images() {
        const value = this.getControlValue();
        return (Array.isArray(value) ? value : []).map((item) => {
          const image = item?.image || item || {};
          return {
            id: Number(image.id) || 0,
            url: image.url || '',
          };
        }).filter((image) => image.url);
      },

      renderImages() {
        if (!this.ui?.imageList?.length) return;
        const images = this.images();
        this.ui.imageList.empty();

        images.forEach((image, index) => {
          const item = $('<div>', { class: 'agris-media-images-item', role: 'listitem' });
          $('<img>', { src: image.url, alt: '' }).appendTo(item);
          $('<button>', {
            type: 'button',
            class: 'agris-media-images-remove',
            'data-index': index,
            'aria-label': strings.removeImage || 'Kép eltávolítása',
          }).append($('<i>', { class: 'eicon-close', 'aria-hidden': 'true' })).appendTo(item);
          this.ui.imageList.append(item);
        });

        const count = images.length;
        const countText = (strings.selectedCount || '%d kép kiválasztva').replace('%d', String(count));
        this.ui.status.text(count ? countText : (strings.empty || 'Nincs kiválasztott kép.'));
        this.ui.buttonLabel.text(count ? (strings.changeImages || 'Kijelölés módosítása') : (strings.chooseImages || 'Több kép kiválasztása'));
        this.ui.clearImages.prop('hidden', !count);
      },

      onSelectImages(event) {
        event.preventDefault();
        if (this.frame) {
          this.frame.open();
          return;
        }

        this.frame = wp.media({
          frame: 'select',
          title: strings.chooseTitle || 'Galériaképek kiválasztása',
          button: { text: strings.chooseButton || 'Kijelölt képek használata' },
          library: { type: 'image' },
          multiple: true,
        });

        this.frame.on('open', () => {
          const selection = this.frame.state().get('selection');
          selection.reset();
          this.images().forEach((image) => {
            if (image.id) selection.add(wp.media.attachment(image.id));
          });
        });

        this.frame.on('select', () => {
          const images = this.frame.state().get('selection').map((attachment) => {
            const data = attachment.toJSON();
            return {
              id: Number(data.id) || 0,
              url: data.url || '',
            };
          }).filter((image) => image.url);
          this.setValue(images);
          this.renderImages();
        });

        this.frame.open();
      },

      onRemoveImage(event) {
        event.preventDefault();
        const index = Number(event.currentTarget.dataset.index);
        const images = this.images().filter((image, imageIndex) => imageIndex !== index);
        this.setValue(images);
        this.renderImages();
      },

      onClearImages(event) {
        event.preventDefault();
        this.setValue([]);
        this.renderImages();
      },

      onBeforeDestroy() {
        if (this.frame) {
          this.frame.off();
          this.frame.remove();
          this.frame = null;
        }
      },
    });

    window.elementor.addControlView('agris_media_images', MediaImagesControl);
  });
})(jQuery);
