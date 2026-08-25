(function ($) {
  'use strict';

  window.addEventListener('elementor/init', () => {
    const BaseData = window.elementor?.modules?.controls?.BaseData;
    if (!BaseData || !window.elementor?.addControlView || !window.wp?.media) return;

    const strings = window.agrisMediaFilesControl || {};
    const MediaFilesControl = BaseData.extend({
      ui() {
        const ui = BaseData.prototype.ui.apply(this, arguments);
        ui.selectFiles = '.agris-media-files-select';
        ui.clearFiles = '.agris-media-files-clear';
        ui.fileList = '[data-agris-media-files-list]';
        ui.status = '[data-agris-media-files-status]';
        ui.buttonLabel = '[data-agris-media-files-button]';
        return ui;
      },

      events() {
        return _.extend(BaseData.prototype.events.apply(this, arguments), {
          'click @ui.selectFiles': 'onSelectFiles',
          'click @ui.clearFiles': 'onClearFiles',
          'click .agris-media-files-remove': 'onRemoveFile',
        });
      },

      applySavedValue() {
        this.renderFiles();
      },

      files() {
        const value = this.getControlValue();
        return Array.isArray(value) ? value : [];
      },

      fileName(file) {
        if (file.title) return file.title;
        if (file.filename) return file.filename.replace(/\.[^.]+$/, '');
        const path = (file.url || file.file_url?.url || '').split(/[?#]/)[0];
        return decodeURIComponent(path.split('/').pop() || 'Fájl');
      },

      fileType(file) {
        if (file.file_type) return String(file.file_type).replace(/[^a-z0-9]/gi, '').toUpperCase().slice(0, 6);
        const filename = file.filename || (file.url || file.file_url?.url || '').split(/[?#]/)[0];
        const extension = filename.includes('.') ? filename.split('.').pop() : '';
        if (extension) return extension.replace(/[^a-z0-9]/gi, '').toUpperCase().slice(0, 6);
        const mimePart = (file.mime || '').split('/').pop() || 'FILE';
        return mimePart.replace(/[^a-z0-9]/gi, '').toUpperCase().slice(0, 6) || 'FILE';
      },

      renderFiles() {
        if (!this.ui?.fileList?.length) return;
        const files = this.files();
        this.ui.fileList.empty();

        files.forEach((file, index) => {
          const row = $('<div>', { class: 'agris-media-files-item', role: 'listitem' });
          $('<span>', { class: 'agris-media-files-type', text: this.fileType(file) }).appendTo(row);
          $('<span>', { class: 'agris-media-files-name', text: this.fileName(file) }).appendTo(row);
          $('<button>', {
            type: 'button',
            class: 'agris-media-files-remove',
            'data-index': index,
            'aria-label': strings.removeFile || 'Fájl eltávolítása',
          }).append($('<i>', { class: 'eicon-close', 'aria-hidden': 'true' })).appendTo(row);
          this.ui.fileList.append(row);
        });

        const count = files.length;
        const countText = (strings.selectedCount || '%d fájl kiválasztva').replace('%d', String(count));
        this.ui.status.text(count ? countText : (strings.empty || 'Nincs kiválasztott fájl.'));
        this.ui.buttonLabel.text(count ? (strings.changeFiles || 'Kijelölés módosítása') : (strings.chooseFiles || 'Fájlok kiválasztása'));
        this.ui.clearFiles.prop('hidden', !count);
      },

      onSelectFiles(event) {
        event.preventDefault();
        if (this.frame) {
          this.frame.open();
          return;
        }

        this.frame = wp.media({
          frame: 'select',
          title: strings.chooseTitle || 'Letölthető fájlok kiválasztása',
          button: { text: strings.chooseButton || 'Kijelölt fájlok használata' },
          multiple: true,
        });

        this.frame.on('open', () => {
          const selection = this.frame.state().get('selection');
          selection.reset();
          this.files().forEach((file) => {
            if (file.id) selection.add(wp.media.attachment(file.id));
          });
        });

        this.frame.on('select', () => {
          const files = this.frame.state().get('selection').map((attachment) => {
            const data = attachment.toJSON();
            return {
              id: Number(data.id) || 0,
              url: data.url || '',
              title: data.title || '',
              filename: data.filename || '',
              mime: data.mime || '',
              filesize: Number(data.filesizeInBytes) || 0,
            };
          }).filter((file) => file.url);
          this.setValue(files);
          this.renderFiles();
        });

        this.frame.open();
      },

      onRemoveFile(event) {
        event.preventDefault();
        const index = Number(event.currentTarget.dataset.index);
        const files = this.files().filter((file, fileIndex) => fileIndex !== index);
        this.setValue(files);
        this.renderFiles();
      },

      onClearFiles(event) {
        event.preventDefault();
        this.setValue([]);
        this.renderFiles();
      },

      onBeforeDestroy() {
        if (this.frame) {
          this.frame.off();
          this.frame.remove();
          this.frame = null;
        }
      },
    });

    window.elementor.addControlView('agris_media_files', MediaFilesControl);
  });
})(jQuery);
