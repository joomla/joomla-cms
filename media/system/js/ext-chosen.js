AbstractChosen.prototype.set_default_values = function() {
	var _this = this;
	this.click_test_action = function(evt) {
		return _this.test_active_click(evt);
	};
	this.activate_action = function(evt) {
		return _this.activate_field(evt);
	};
	this.active_field = false;
	this.mouse_on_container = false;
	this.results_showing = false;
	this.result_highlighted = null;
	/*<JUI>*/
	/* Original: not exist */
	this.allow_custom_value = false;
	/*</JUI>*/
	this.allow_single_deselect = (this.options.allow_single_deselect != null) && (this.form_field.options[0] != null) && this.form_field.options[0].text === "" ? this.options.allow_single_deselect : false;
	this.disable_search_threshold = this.options.disable_search_threshold || 0;
	this.disable_search = this.options.disable_search || false;
	this.enable_split_word_search = this.options.enable_split_word_search != null ? this.options.enable_split_word_search : true;
	this.group_search = this.options.group_search != null ? this.options.group_search : true;
	this.search_contains = this.options.search_contains || false;
	this.single_backstroke_delete = this.options.single_backstroke_delete != null ? this.options.single_backstroke_delete : true;
	this.max_selected_options = this.options.max_selected_options || Infinity;
	this.inherit_select_classes = this.options.inherit_select_classes || false;
	this.display_selected_options = this.options.display_selected_options != null ? this.options.display_selected_options : true;
	this.display_disabled_options = this.options.display_disabled_options != null ? this.options.display_disabled_options : true;
	return this.include_group_label_in_selected = this.options.include_group_label_in_selected || false;
};


AbstractChosen.prototype.set_default_text = function() {
	if (this.form_field.getAttribute("data-placeholder")) {
		this.default_text = this.form_field.getAttribute("data-placeholder");
	} else if (this.is_multiple) {
		this.default_text = this.options.placeholder_text_multiple || this.options.placeholder_text || AbstractChosen.default_multiple_text;
	} else {
		this.default_text = this.options.placeholder_text_single || this.options.placeholder_text || AbstractChosen.default_single_text;
	}
	/*<JUI>*/
	/* Original: not exist */
	this.custom_group_text = this.form_field.getAttribute("data-custom_group_text") || this.options.custom_group_text || "Custom Value";
	/*</JUI>*/
	return this.results_none_found = this.form_field.getAttribute("data-no_results_text") || this.options.no_results_text || AbstractChosen.default_no_result_text;
};

AbstractChosen.prototype.container_width = function() {
	if (this.options.width != null) {
		return this.options.width;
	} else {
		/*<JUI>*/
		/* Original:
		 return "" + this.form_field.offsetWidth + "px";
		 */
		return this.form_field_jq.css("width") || "" + this.form_field.offsetWidth + "px";
		/*</JUI>*/
	}
};

Chosen.prototype.setup = function() {
	this.form_field_jq = $(this.form_field);
	this.current_selectedIndex = this.form_field.selectedIndex;
	/*<JUI>*/
	/* Original: not exist */
	this.allow_custom_value = this.form_field_jq.hasClass("chosen-custom-value") || this.options.allow_custom_value;
	/*</JUI>*/
	return this.is_rtl = this.form_field_jq.hasClass("chosen-rtl");
};

Chosen.prototype.result_select = function(evt) {
	/*<JUI>*/
	/* Original:
	 var high, item, selected_index;
	 */
	var group, high, high_id, item, option, position, value;
	/*</JUI>*/

	if (this.result_highlight) {
		high = this.result_highlight;
		this.result_clear_highlight();
		if (this.is_multiple && this.max_selected_options <= this.choices_count()) {
			this.form_field_jq.trigger("chosen:maxselected", {
				chosen: this
			});
			return false;
		}
		if (this.is_multiple) {
			high.removeClass("active-result");
		} else {
			this.reset_single_select_options();
		}
		high.addClass("result-selected");
		item = this.results_data[high[0].getAttribute("data-option-array-index")];
		item.selected = true;
		this.form_field.options[item.options_index].selected = true;
		this.selected_option_count = null;
		if (this.is_multiple) {
			this.choice_build(item);
		} else {
			this.single_set_selected_text(this.choice_label(item));
		}
		if (!((evt.metaKey || evt.ctrlKey) && this.is_multiple)) {
			this.results_hide();
		}
		this.search_field.val("");
		if (this.is_multiple || this.form_field.selectedIndex !== this.current_selectedIndex) {
			this.form_field_jq.trigger("change", {
				'selected': this.form_field.options[item.options_index].value
			});
		}
		this.current_selectedIndex = this.form_field.selectedIndex;
		evt.preventDefault();
		return this.search_field_scale();
	}
	/*<JUI>*/
	/* Original: not exist */
	else if ((!this.is_multiple) && this.allow_custom_value) {
		value = this.search_field.val();
		group = this.add_unique_custom_group();
		option = $('<option value="' + value + '">' + value + '</option>');
		group.append(option);
		this.form_field_jq.append(group);
		this.form_field.options[this.form_field.options.length - 1].selected = true;
		if (!evt.metaKey) {
			this.results_hide();
		}
		return this.results_build();
	}
	/*</JUI>*/
};

/*<JUI>*/
/* Original: not exist */
Chosen.prototype.find_custom_group = function() {
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

Chosen.prototype.add_unique_custom_group = function() {
	var group;
	group = this.find_custom_group();
	if (!group) {
		group = $('<optgroup label="' + this.custom_group_text + '"></optgroup>');
	}
	return $(group);
};
/*</JUI>*/
