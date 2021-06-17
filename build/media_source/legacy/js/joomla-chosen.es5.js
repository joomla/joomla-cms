(function($, Chosen, AbstractChosen) {
    $.fn.jchosen = function (options) {
        if (!AbstractChosen.browser_is_supported()) {
            return this;
        }
        return this.each(function (input_field) {
            var $this, chosen;
            $this = $(this);
            chosen = $this.data('chosen');
            if (options === 'destroy') {
                if (chosen instanceof JoomlaChosen) {
                    chosen.destroy();
                }
                return;
            }
            if (!(chosen instanceof JoomlaChosen)) {
                $this.data('chosen', new JoomlaChosen(this, options));
            }
        });
    };

    JoomlaChosen = (function (_super) {
        var __hasProp = {}.hasOwnProperty,
        __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };
        __extends(JoomlaChosen, _super);

        function JoomlaChosen() {
            _ref = JoomlaChosen.__super__.constructor.apply(this, arguments);
            return _ref;
        }

        JoomlaChosen.prototype.setup = function () {
            var return_value;
            return_value = JoomlaChosen.__super__.setup.apply(this, arguments);
            this.allow_custom_value = this.form_field_jq.hasClass("chosen-custom-value") || this.options.allow_custom_value;
            this.custom_value_prefix = this.form_field_jq.attr("data-custom_value_prefix") || this.custom_value_prefix;

            return return_value;
        };

        JoomlaChosen.prototype.set_default_text = function () {
            this.custom_group_text = this.form_field.getAttribute("data-custom_group_text") || this.options.custom_group_text || "Custom Value";

            return JoomlaChosen.__super__.set_default_text.apply(this, arguments);
        };

        JoomlaChosen.prototype.result_select = function (evt) {
            var group, option, value;

            if (!this.result_highlight && (!this.is_multiple) && this.allow_custom_value) {
                value = this.search_field.val();
                group = this.add_unique_custom_group();
                option = $('<option value="' + this.custom_value_prefix + value + '">' + value + '</option>');
                group.append(option);
                this.form_field_jq.append(group);
                this.form_field.options[this.form_field.options.length - 1].selected = true;
                if (!evt.metaKey) {
                    this.results_hide();
                }
                return this.results_build();
            }

            return JoomlaChosen.__super__.result_select.apply(this, arguments);
        };

        JoomlaChosen.prototype.find_custom_group = function () {
            var found, group, _i, _len, _ref;
            _ref = $('optgroup', this.form_field);
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                group = _ref[_i];
                if (group.getAttribute('label') === this.custom_group_text) {
                    found = group;
                }
            }
            return found;
        };

        JoomlaChosen.prototype.add_unique_custom_group = function () {
            var group;
            group = this.find_custom_group();
            if (!group) {
                group = $('<optgroup label="' + this.custom_group_text + '"></optgroup>');
            }
            return $(group);
        };

        /**
         * We choose to override this function so deliberately don't call super
         */
        JoomlaChosen.prototype.container_width = function () {
            if (this.options.width != null) {
                return this.options.width;
            } else {
                // Original: return "" + this.form_field.offsetWidth + "px";
                return this.form_field_jq.css("width") || "" + this.form_field.offsetWidth + "px";
            }
        };

        return JoomlaChosen;

    })(Chosen);
})(jQuery, document.Chosen, document.AbstractChosen);
