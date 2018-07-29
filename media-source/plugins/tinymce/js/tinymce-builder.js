/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(function ($) {
  'use strict';

  /**
     * Fake TinyMCE object to allow to use TinyMCE translation for the button labels
     *
     * @since  3.7.0
     */
  window.tinymce = {
    langCode: 'en',
    langStrings: {},
    addI18n(code, strings) {
      this.langCode = code;
      this.langStrings = strings || {};
    },
    translate(string) {
      return this.langStrings[string] ? this.langStrings[string] : string;
    },
  };

  /**
     * Joomla TinyMCE Builder
     *
     * @param {HTMLElement} container
     * @param {Object}      options
     * @constructor
     *
     * @since  3.7.0
     */
  const JoomlaTinyMCEBuilder = function (container, options) {
    this.$container = $(container);
    this.options = options;

    // Find source containers
    this.$sourceMenu = this.$container.find('.timymce-builder-menu.source');
    this.$sourceToolbar = this.$container.find('.timymce-builder-toolbar.source');

    // Find target containers
    this.$targetMenu = this.$container.find('.timymce-builder-menu.target');
    this.$targetToolbar = this.$container.find('.timymce-builder-toolbar.target');

    // Render Source elements
    this.$sourceMenu.each((i, element) => {
      this.renderBar(element, 'menu');
    });
    this.$sourceToolbar.each((i, element) => {
      this.renderBar(element, 'toolbar');
    });

    // Render Target elements
    this.$targetMenu.each((i, element) => {
      this.renderBar(element, 'menu', null, true);
    });
    this.$targetToolbar.each((i, element) => {
      this.renderBar(element, 'toolbar', null, true);
    });

    // Set up "drag&drop" stuff
    let $copyHelper = null; let removeIntent = false; const
      self = this;
    this.$sourceMenu.sortable({
      connectWith: this.$targetMenu,
      items: '.mce-btn',
      cancel: '',
      placeholder: 'mce-btn ui-state-highlight',
      start(event, ui) {
        self.$targetMenu.addClass('drop-area-highlight');
      },
      helper(event, el) {
        $copyHelper = el.clone().insertAfter(el);
        return el;
      },
      stop() {
        $copyHelper && $copyHelper.remove();
        self.$targetMenu.removeClass('drop-area-highlight');
      },
    });

    this.$sourceToolbar.sortable({
      connectWith: this.$targetToolbar,
      items: '.mce-btn',
      cancel: '',
      placeholder: 'mce-btn ui-state-highlight',
      start(event, ui) {
        self.$targetToolbar.addClass('drop-area-highlight');
      },
      helper(event, el) {
        $copyHelper = el.clone().insertAfter(el);
        return el;
      },
      stop() {
        $copyHelper && $copyHelper.remove();
        self.$targetToolbar.removeClass('drop-area-highlight');
      },
    });

    $().add(this.$targetMenu).add(this.$targetToolbar).sortable({
      items: '.mce-btn',
      cancel: '',
      placeholder: 'mce-btn ui-state-highlight',
      receive(event, ui) {
        $copyHelper = null;
        const $el = ui.item; const
          $cont = $(this);
        self.appendInput($el, $cont.data('group'), $cont.data('set'));
      },
      over(event, ui) {
        removeIntent = false;
      },
      out(event, ui) {
        removeIntent = true;
      },
      beforeStop(event, ui) {
        if (removeIntent) {
          ui.item.remove();
        }
      },
    });

    // Bind actions buttons
    this.$container.on('click', '.button-action', (event) => {
      const $btn = $(event.target); const action = $btn.data('action'); const
        options = $btn.data();

      if (this[action]) {
        this[action].call(this, options);
      } else {
        throw new Error(`Unsupported action ${action}`);
      }
    });
  };

    /**
     * Render the toolbar/menubar
     *
     * @param {HTMLElement} container  The toolbar container
     * @param {String}      type       The type toolbar or menu
     * @param {Array|null}  value      The value
     * @param {Boolean}     withInput  Whether append input
     *
     * @since  3.7.0
     */
  JoomlaTinyMCEBuilder.prototype.renderBar = function (container, type, value, withInput) {
    const $container = $(container);


    const group = $container.data('group');


    const set = $container.data('set');


    const items = type === 'menu' ? this.options.menus : this.options.buttons;


    var value = value || ($container.data('value') || []);


    let item; let name; let
      $btn;

    for (let i = 0, l = value.length; i < l; i++) {
      name = value[i];
      item = items[name];

      if (!item) {
        continue;
      }

      $btn = this.createButton(name, item, type);
      $container.append($btn);

      // Enable tooltip
      if ($btn.tooltip) {
        $btn.tooltip({ trigger: 'hover' });
      }

      // Add input
      if (withInput) {
        this.appendInput($btn, group, set);
      }
    }
  };

  /**
     * Create the element needed for renderBar()
     * @param {String} name
     * @param {Object} info
     * @param {String} type
     *
     * @return {jQuery}
     *
     * @since  3.7.0
     */
  JoomlaTinyMCEBuilder.prototype.createButton = function (name, info, type) {
    const $element = $('<div />', {
      class: 'mce-btn',
      'data-name': name,
      'data-toggle': 'tooltip',
      title: tinymce.translate(info.label),
    });
    const $btn = $('<button/>', {
      type: 'button',
    });
    $element.append($btn);

    if (type === 'menu') {
      $btn.html(`<span class="mce-txt">${tinymce.translate(info.label)}</span> <i class="mce-caret"></i>`);
    } else {
      $element.addClass('mce-btn-small');
      $btn.html(info.text ? tinymce.translate(info.text) : `<i class="mce-ico mce-i-${name}"></i>`);
    }

    return $element;
  };

  /**
     * Append input to the button item
     * @param {HTMLElement} element
     * @param {String}      group
     * @param {String}      set
     *
     * @since  3.7.0
     */
  JoomlaTinyMCEBuilder.prototype.appendInput = function (element, group, set) {
    const $el = $(element);


    const name = `${this.options.formControl}[${set}][${group}][]`;


    const $input = $('<input/>', {
      type: 'hidden',
      name,
      value: $el.data('name'),
    });

    $el.append($input);
  };

  /**
     * Set Selected preset to specific  set
     * @param {Object} options Options {set: 1, preset: 'presetName'}
     */
  JoomlaTinyMCEBuilder.prototype.setPreset = function (options) {
    const set = options.set; const
      preset = this.options.toolbarPreset[options.preset] || null;

    if (!preset) {
      throw new Error(`Unknown Preset "${options.preset}"`);
    }

    let $container; let
      type;
    for (const group in preset) {
      if (!preset.hasOwnProperty(group)) {
        continue;
      }

      // Find correct container for current set
      if (group === 'menu') {
        type = 'menu';
        $container = this.$targetMenu.filter(`[data-group="${group}"][data-set="${set}"]`);
      } else {
        type = 'toolbar';
        $container = this.$targetToolbar.filter(`[data-group="${group}"][data-set="${set}"]`);
      }

      // Reset existing values
      $container.empty();

      // Set new
      this.renderBar($container, type, preset[group], true);
    }
  };

  /**
     * Clear the pane for specific set
     * @param {Object} options Options {set: 1}
     */
  JoomlaTinyMCEBuilder.prototype.clearPane = function (options) {
    const set = options.set;

    this.$targetMenu.filter(`[data-set="${set}"]`).empty();
    this.$targetToolbar.filter(`[data-set="${set}"]`).empty();
  };


  // Init the builder
  $(document).ready(() => {
    const options = Joomla.getOptions ? Joomla.getOptions('plg_editors_tinymce_builder', {})
				: (Joomla.optionsStorage.plg_editors_tinymce_builder || {});

    new JoomlaTinyMCEBuilder($('#joomla-tinymce-builder'), options);

    $('#set-tabs a').on('click', function (event) {
      event.preventDefault();
      $(this).tab('show');
    });

    // Allow to select the group only once per the set
    const $accessSelects = $('#joomla-tinymce-builder').find('.access-select');
    toggleAvailableOption();
    $accessSelects.on('change', () => {
      toggleAvailableOption();
    });

    function toggleAvailableOption() {
      $accessSelects.find('option[disabled]').removeAttr('disabled');

      // Disable already selected options
      $accessSelects.each(function () {
        const $select = $(this); const val = $select.val() || []; const query = [];


        const $options = $accessSelects.not(this).find('option');

        for (let i = 0, l = val.length; i < l; i++) {
          if (!val[i]) continue;
          query.push(`[value="${val[i]}"]`);
        }

        if (query.length) {
          $options.filter(query.join(',')).attr('disabled', 'disabled');
        }
      });

      // Update Chosen
      $accessSelects.trigger('liszt:updated');
    }
  });
}(jQuery));
