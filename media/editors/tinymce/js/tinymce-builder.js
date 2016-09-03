/**
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

;(function($) {
    "use strict";

    /**
     * Joomla TinyMCE Builder
     *
     * @param {HTMLElement} container
     * @param {Object}      options
     * @constructor
     *
     * @since  __DEPLOY_VERSION__
     */
    var JoomlaTinyMCEBuilder = function(container, options) {
        this.$container = $(container);
        this.options    = options;

        // Find source containers
        this.$sourceMenu    = this.$container.find('.timymce-builder-menu.source');
        this.$sourceToolbar = this.$container.find('.timymce-builder-toolbar.source');

        // Find target containers
        this.$targetMenu    = this.$container.find('.timymce-builder-menu.target');
        this.$targetToolbar = this.$container.find('.timymce-builder-toolbar.target');

        // Render Source elements
        this.$sourceMenu.each(function(i, element){
            this.renderBar(element, 'menu');
        }.bind(this));
        this.$sourceToolbar.each(function(i, element){
            this.renderBar(element, 'toolbar');
        }.bind(this));

        // Render Target elements
        this.$targetMenu.each(function(i, element){
            this.renderBar(element, 'menu', null, true);
        }.bind(this));
        this.$targetToolbar.each(function(i, element){
            this.renderBar(element, 'toolbar', null, true);
        }.bind(this));

        // Set up "drag&drop" stuff
        var $copyHelper = null, removeIntent = false, self = this;
        this.$sourceMenu.sortable({
            connectWith: this.$targetMenu,
            items: '.mce-btn',
            cancel: '',
            helper: function(event, el) {
                $copyHelper = el.clone().insertAfter(el);
                return el;
            },
            stop: function() {
                $copyHelper && $copyHelper.remove();
            }
        });

        this.$sourceToolbar.sortable({
            connectWith: this.$targetToolbar,
            items: '.mce-btn',
            cancel: '',
            helper: function(event, el) {
                $copyHelper = el.clone().insertAfter(el);
                return el;
            },
            stop: function() {
                $copyHelper && $copyHelper.remove();
            }
        });

        $().add(this.$targetMenu).add(this.$targetToolbar).sortable({
            items: '.mce-btn',
            cancel: '',
            receive: function(event, ui) {
                $copyHelper = null;
                var $el = ui.item, $cont = $(this);
                self.appendInput($el, $cont.data('group'), $cont.data('level'))
            },
            over: function (event, ui) {
                removeIntent = false;
            },
            out: function (event, ui) {
                removeIntent = true;
            },
            beforeStop: function (event, ui) {
                if(removeIntent){
                    ui.item.remove();
                }
            }
        });

        // Bind actions buttons
        this.$container.on('click', '.button-action', function(event){
            var $btn = $(event.target), action = $btn.data('action'), options = $btn.data();

            if (this[action]) {
                this[action].call(this, options);
            } else {
                throw new Error('Unsupported action ' + action);
            }
        }.bind(this));

        console.log(this);
    };

    /**
     * Render the toolbar/menubar
     *
     * @param {HTMLElement} container  The toolbar container
     * @param {String}      type       The type toolbar or menu
     * @param {Array|null}  value      The value
     * @param {Boolean}     withInput  Whether append input
     *
     * @since  __DEPLOY_VERSION__
     */
    JoomlaTinyMCEBuilder.prototype.renderBar = function(container, type, value, withInput) {
        var $container = $(container),
            group = $container.data('group'),
            level = $container.data('level'),
            items = type === 'menu' ? this.options.menus : this.options.buttons,
            value = value ? value : ($container.data('value') || []),
            item, name, $btn;

        for ( var i = 0, l = value.length; i < l; i++ ) {
            name = value[i];
            item = items[name];

            if (!item) {
                continue;
            }

            $btn = this.createButton(name, item, type);
            $container.append($btn);

            if (withInput) {
                this.appendInput($btn, group, level);
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
     * @since  __DEPLOY_VERSION__
     */
    JoomlaTinyMCEBuilder.prototype.createButton = function(name, info, type){
        var $element = $('<div />', {
            'class': 'mce-btn',
            'data-name': name
        });
        var $btn = $('<button/>', {
            'type': 'button'
        });
        $element.append($btn);

        if (type === 'menu') {
            $btn.html('<span class="mce-txt">' + info.label + '</span> <i class="mce-caret"></i>');
        } else {
            $element.addClass('mce-btn-small');
            $btn.html(info.text ? info.text : '<span class="mce-ico mce-i-' + name + '"></span>');
        }

        return $element;
    };

    /**
     * Append input to the button item
     * @param {HTMLElement} element
     * @param {String}      group
     * @param {String}      level
     *
     * @since  __DEPLOY_VERSION__
     */
    JoomlaTinyMCEBuilder.prototype.appendInput = function (element, group, level) {
        var $el    = $(element),
            name   = this.options.formControl + '[' + level + '][' + group + '][]',
            $input = $('<input/>', {
                type: 'hidden',
                name:  name,
                value: $el.data('name')
            });

        $el.append($input);
    };

    /**
     * Set Selected preset to specific view level
     * @param {Object} options Options {level: 1, preset: 'presetName'}
     */
    JoomlaTinyMCEBuilder.prototype.setPreset = function (options) {
        var level = options.level, preset = this.options.toolbarPreset[options.preset] || null;

        if (!preset) {
            throw new Error('Unknown Presset "' + options.preset + '"');
        }

        var $container, type;
        for (var group in preset) {
            if (!preset.hasOwnProperty(group)) {
                continue;
            }

            // Find correct container for current level
            if (group === 'menu') {
                type = 'menu';
                $container = this.$targetMenu.filter('[data-group="' + group + '"][data-level="' + level + '"]');
            } else {
                type = 'toolbar'
                $container = this.$targetToolbar.filter('[data-group="' + group + '"][data-level="' + level + '"]');
            }

            // Reset existing values
            $container.empty();

            // Set new
            this.renderBar($container, type, preset[group], true);
        }
    };

    /**
     * Clear the pane for specific view level
     * @param {Object} options Options {level: 1}
     */
    JoomlaTinyMCEBuilder.prototype.clearPane = function (options) {
        console.log(options);
    };


    // Init the builder
    $(document).ready(function(){
        var options = Joomla.getOptions ? Joomla.getOptions('plg_editors_tinymce_builder', {})
        			:  (Joomla.optionsStorage.plg_editors_tinymce_builder || {});

        new JoomlaTinyMCEBuilder($('#joomla-tinymce-builder'), options);

        $("#view-level-tabs a").on('click', function (event) {
            event.preventDefault();
            $(this).tab("show");
        });
    });
}(jQuery));
