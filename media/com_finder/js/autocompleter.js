//@deprecated  3.4
var Observer = new Class({
	Implements: [Options, Events],
	options: {
		periodical: false,
		delay: 1000
	},
	initialize: function (el, onFired, options) {
		this.element = document.id(el) || $document.id(el);
		this.addEvent('onFired', onFired);
		this.setOptions(options);
		this.bound = this.changed.bind(this);
		this.resume();
	},
	changed: function () {
		var value = this.element.get('value');
		if ($equals(this.value, value)) return;
		this.clear();
		this.value = value;
		this.timeout = this.onFired.delay(this.options.delay, this);
	},
	setValue: function (value) {
		this.value = value;
		this.element.set('value', value);
		return this.clear();
	},
	onFired: function () {
		this.fireEvent('onFired', [this.value, this.element]);
	},
	clear: function () {
		clearTimeout(this.timeout || null);
		return this;
	},
	pause: function () {
		if (this.timer) clearTimeout(this.timer);
		else this.element.removeEvent('keyup', this.bound);
		return this.clear();
	},
	resume: function () {
		this.value = this.element.get('value');
		if (this.options.periodical) this.timer = this.changed.periodical(this.options.periodical, this);
		else this.element.addEvent('keyup', this.bound);
		return this;
	}
});
var $equals = function (obj1, obj2) {
		return (obj1 == obj2 || JSON.encode(obj1) == JSON.encode(obj2));
	};
var Autocompleter = new Class({
	Implements: [Options, Events],
	options: {
		minLength: 1,
		markQuery: true,
		width: 'inherit',
		maxChoices: 10,
		injectChoice: null,
		customChoices: null,
		emptyChoices: null,
		visibleChoices: true,
		className: 'autocompleter-choices',
		zIndex: 1000,
		delay: 400,
		observerOptions: {},
		fxOptions: {},
		autoSubmit: false,
		overflow: false,
		overflowMargin: 25,
		selectFirst: false,
		filter: null,
		filterCase: false,
		filterSubset: false,
		forceSelect: false,
		selectMode: true,
		choicesMatch: null,
		multiple: false,
		separator: ', ',
		separatorSplit: /\s*[,;]\s*/,
		autoTrim: false,
		allowDupes: false,
		cache: true,
		relative: false
	},
	initialize: function (element, options) {
		this.element = document.id(element);
		this.setOptions(options);
		this.build();
		this.observer = new Observer(this.element, this.prefetch.bind(this), Object.merge({}, {
			'delay': this.options.delay
		}, this.options.observerOptions));
		this.queryValue = null;
		if (this.options.filter) this.filter = this.options.filter.bind(this);
		var mode = this.options.selectMode;
		this.typeAhead = (mode == 'type-ahead');
		this.selectMode = (mode === true) ? 'selection' : mode;
		this.cached = [];
	},
	build: function () {
		if (document.id(this.options.customChoices)) {
			this.choices = this.options.customChoices;
		} else {
			this.choices = new Element('ul', {
				'class': this.options.className,
				'styles': {
					'zIndex': this.options.zIndex
				}
			}).inject(document.body);
			this.relative = false;
			if (this.options.relative) {
				this.choices.inject(this.element, 'after');
				this.relative = this.element.getOffsetParent();
			}
			this.fix = new OverlayFix(this.choices);
		}
		if (!this.options.separator.test(this.options.separatorSplit)) {
			this.options.separatorSplit = this.options.separator;
		}
		this.fx = (!this.options.fxOptions) ? null : new Fx.Tween(this.choices, Object.merge({}, {
			'property': 'opacity',
			'link': 'cancel',
			'duration': 200
		}, this.options.fxOptions)).addEvent('onStart', Chain.prototype.clearChain).set(0);
		this.element.setProperty('autocomplete', 'off').addEvent((Browser.ie || Browser.safari || Browser.chrome) ? 'keydown' : 'keypress', this.onCommand.bind(this)).addEvent('click', this.onCommand.bind(this, [false])).addEvent('focus', this.toggleFocus.pass({
			bind: this,
			arguments: true,
			delay: 100
		})).addEvent('blur', this.toggleFocus.pass({
			bind: this,
			arguments: false,
			delay: 100
		}));
	},
	destroy: function () {
		if (this.fix) this.fix.destroy();
		this.choices = this.selected = this.choices.destroy();
	},
	toggleFocus: function (state) {
		this.focussed = state;
		if (!state) this.hideChoices(true);
		this.fireEvent((state) ? 'onFocus' : 'onBlur', [this.element]);
	},
	onCommand: function (e) {
		if (!e && this.focussed) return this.prefetch();
		if (e && e.key && !e.shift) {
			switch (e.key) {
			case 'enter':
				if (this.element.value != this.opted) return true;
				if (this.selected && this.visible) {
					this.choiceSelect(this.selected);
					return !!(this.options.autoSubmit);
				}
				break;
			case 'up':
			case 'down':
				if (!this.prefetch() && this.queryValue !== null) {
					var up = (e.key == 'up');
					this.choiceOver((this.selected || this.choices)[(this.selected) ? ((up) ? 'getPrevious' : 'getNext') : ((up) ? 'getLast' : 'getFirst')](this.options.choicesMatch), true);
				}
				return false;
			case 'esc':
			case 'tab':
				this.hideChoices(true);
				break;
			}
		}
		return true;
	},
	setSelection: function (finish) {
		var input = this.selected.inputValue,
			value = input;
		var start = this.queryValue.length,
			end = input.length;
		if (input.substr(0, start).toLowerCase() != this.queryValue.toLowerCase()) start = 0;
		if (this.options.multiple) {
			var split = this.options.separatorSplit;
			value = this.element.value;
			start += this.queryIndex;
			end += this.queryIndex;
			var old = value.substr(this.queryIndex).split(split, 1)[0];
			value = value.substr(0, this.queryIndex) + input + value.substr(this.queryIndex + old.length);
			if (finish) {
				var tokens = value.split(this.options.separatorSplit).filter(function (entry) {
					return this.test(entry);
				}, /[^\s,]+/);
				if (!this.options.allowDupes) tokens = [].combine(tokens);
				var sep = this.options.separator;
				value = tokens.join(sep) + sep;
				end = value.length;
			}
		}
		this.observer.setValue(value);
		this.opted = value;
		if (finish || this.selectMode == 'pick') start = end;
		this.element.selectRange(start, end);
		this.fireEvent('onSelection', [this.element, this.selected, value, input]);
	},
	showChoices: function () {
		var match = this.options.choicesMatch,
			first = this.choices.getFirst(match);
		this.selected = this.selectedValue = null;
		if (this.fix) {
			var pos = this.element.getCoordinates(this.relative),
				width = this.options.width || 'auto';
			this.choices.setStyles({
				'left': pos.left,
				'top': pos.bottom,
				'width': (width === true || width == 'inherit') ? pos.width : width
			});
		}
		if (!first) return;
		if (!this.visible) {
			this.visible = true;
			this.choices.setStyle('display', '');
			if (this.fx) this.fx.start(1);
			this.fireEvent('onShow', [this.element, this.choices]);
		}
		if (this.options.selectFirst || this.typeAhead || first.inputValue == this.queryValue) this.choiceOver(first, this.typeAhead);
		var items = this.choices.getChildren(match),
			max = this.options.maxChoices;
		var styles = {
			'overflowY': 'hidden',
			'height': ''
		};
		this.overflown = false;
		if (items.length > max) {
			var item = items[max - 1];
			styles.overflowY = 'scroll';
			styles.height = item.getCoordinates(this.choices).bottom;
			this.overflown = true;
		};
		this.choices.setStyles(styles);
		this.fix.show();
		if (this.options.visibleChoices) {
			var scroll = document.getScroll(),
				size = document.getSize(),
				coords = this.choices.getCoordinates();
			if (coords.right > scroll.x + size.x) scroll.x = coords.right - size.x;
			if (coords.bottom > scroll.y + size.y) scroll.y = coords.bottom - size.y;
			window.scrollTo(Math.min(scroll.x, coords.left), Math.min(scroll.y, coords.top));
		}
	},
	// TODO: No $arguments in MT 1.3
	hideChoices: function (clear) {
		if (clear) {
			var value = this.element.value;
			if (this.options.forceSelect) value = this.opted;
			if (this.options.autoTrim) {
				value = value.split(this.options.separatorSplit).filter($arguments(0)).join(this.options.separator);
			}
			this.observer.setValue(value);
		}
		if (!this.visible) return;
		this.visible = false;
		if (this.selected) this.selected.removeClass('autocompleter-selected');
		this.observer.clear();
		var hide = function () {
				this.choices.setStyle('display', 'none');
				this.fix.hide();
			}.bind(this);
		if (this.fx) this.fx.start(0).chain(hide);
		else hide();
		this.fireEvent('onHide', [this.element, this.choices]);
	},
	prefetch: function () {
		var value = this.element.value,
			query = value;
		if (this.options.multiple) {
			var split = this.options.separatorSplit;
			var values = value.split(split);
			var index = this.element.getSelectedRange().start;
			var toIndex = value.substr(0, index).split(split);
			var last = toIndex.length - 1;
			index -= toIndex[last].length;
			query = values[last];
		}
		if (query.length < this.options.minLength) {
			this.hideChoices();
		} else {
			if (query === this.queryValue || (this.visible && query == this.selectedValue)) {
				if (this.visible) return false;
				this.showChoices();
			} else {
				this.queryValue = query;
				this.queryIndex = index;
				if (!this.fetchCached()) this.query();
			}
		}
		return true;
	},
	fetchCached: function () {
		return false;
		if (!this.options.cache || !this.cached || !this.cached.length || this.cached.length >= this.options.maxChoices || this.queryValue) return false;
		this.update(this.filter(this.cached));
		return true;
	},
	update: function (tokens) {
		this.choices.empty();
		this.cached = tokens;
		var type = tokens && typeOf(tokens);
		if (!type || (type == 'array' && !tokens.length) || (type == 'hash' && !tokens.getLength())) {
			(this.options.emptyChoices || this.hideChoices).call(this);
		} else {
			if (this.options.maxChoices < tokens.length && !this.options.overflow) tokens.length = this.options.maxChoices;
			tokens.each(this.options.injectChoice ||
			function (token) {
				var choice = new Element('li', {
					'html': this.markQueryValue(token)
				});
				choice.inputValue = token;
				this.addChoiceEvents(choice).inject(this.choices);
			}, this);
			this.showChoices();
		}
	},
	choiceOver: function (choice, selection) {
		if (!choice || choice == this.selected) return;
		if (this.selected) this.selected.removeClass('autocompleter-selected');
		this.selected = choice.addClass('autocompleter-selected');
		this.fireEvent('onSelect', [this.element, this.selected, selection]);
		if (!this.selectMode) this.opted = this.element.value;
		if (!selection) return;
		this.selectedValue = this.selected.inputValue;
		if (this.overflown) {
			var coords = this.selected.getCoordinates(this.choices),
				margin = this.options.overflowMargin,
				top = this.choices.scrollTop,
				height = this.choices.offsetHeight,
				bottom = top + height;
			if (coords.top - margin < top && top) this.choices.scrollTop = Math.max(coords.top - margin, 0);
			else if (coords.bottom + margin > bottom) this.choices.scrollTop = Math.min(coords.bottom - height + margin, bottom);
		}
		if (this.selectMode) this.setSelection();
	},
	choiceSelect: function (choice) {
		if (choice) this.choiceOver(choice);
		this.setSelection(true);
		this.queryValue = false;
		this.hideChoices();
	},
	filter: function (tokens) {
		return (tokens || this.tokens).filter(function (token) {
			return this.test(token);
		}, new RegExp(((this.options.filterSubset) ? '' : '^') + this.queryValue.escapeRegExp(), (this.options.filterCase) ? '' : 'i'));
	},
	markQueryValue: function (str) {
		return (!this.options.markQuery || !this.queryValue) ? str : str.replace(new RegExp('(' + ((this.options.filterSubset) ? '' : '^') + this.queryValue.escapeRegExp() + ')', (this.options.filterCase) ? '' : 'i'), '<span class="autocompleter-queried">$1</span>');
	},
	addChoiceEvents: function (el) {
		return el.addEvents({
			'mouseover': this.choiceOver.bind(this, el),
			'click': this.choiceSelect.bind(this, el)
		});
	}
});
var OverlayFix = new Class({
	initialize: function (el) {
		if (Browser.ie) {
			this.element = document.id(el);
			this.relative = this.element.getOffsetParent();
			this.fix = new Element('iframe', {
				'frameborder': '0',
				'scrolling': 'no',
				'src': 'javascript:false;',
				'styles': {
					'position': 'absolute',
					'border': 'none',
					'display': 'none',
					'filter': 'progid:DXImageTransform.Microsoft.Alpha(opacity=0)'
				}
			}).inject(this.element, 'after');
		}
	},
	show: function () {
		if (this.fix) {
			var coords = this.element.getCoordinates(this.relative);
			delete coords.right;
			delete coords.bottom;
			this.fix.setStyles(Object.append(coords, {
				'display': '',
				'zIndex': (this.element.getStyle('zIndex') || 1) - 1
			}));
		}
		return this;
	},
	hide: function () {
		if (this.fix) this.fix.setStyle('display', 'none');
		return this;
	},
	destroy: function () {
		if (this.fix) this.fix = this.fix.destroy();
	}
});
Element.implement({
	getSelectedRange: function () {
		if (!Browser.ie) return {
			start: this.selectionStart,
			end: this.selectionEnd
		};
		var pos = {
			start: 0,
			end: 0
		};
		var range = this.getDocument().selection.createRange();
		if (!range || range.parentElement() != this) return pos;
		var dup = range.duplicate();
		if (this.type == 'text') {
			pos.start = 0 - dup.moveStart('character', -100000);
			pos.end = pos.start + range.text.length;
		} else {
			var value = this.value;
			var offset = value.length - value.match(/[\n\r]*$/)[0].length;
			dup.moveToElementText(this);
			dup.setEndPoint('StartToEnd', range);
			pos.end = offset - dup.text.length;
			dup.setEndPoint('StartToStart', range);
			pos.start = offset - dup.text.length;
		}
		return pos;
	},
	selectRange: function (start, end) {
		if (Browser.ie) {
			var diff = this.value.substr(start, end - start).replace(/\r/g, '').length;
			start = this.value.substr(0, start).replace(/\r/g, '').length;
			var range = this.createTextRange();
			range.collapse(true);
			range.moveEnd('character', start + diff);
			range.moveStart('character', start);
			range.select();
		} else {
			this.focus();
			this.setSelectionRange(start, end);
		}
		return this;
	}
});
Autocompleter.Base = Autocompleter;
Autocompleter.Request = new Class({
	Extends: Autocompleter,
	options: {
		postData: {},
		ajaxOptions: {},
		postVar: 'value'
	},
	query: function () {
		var data = this.options.postData.unlink || {};
		data[this.options.postVar] = this.queryValue;
		var indicator = document.id(this.options.indicator);
		if (indicator) indicator.setStyle('display', '');
		var cls = this.options.indicatorClass;
		if (cls) this.element.addClass(cls);
		this.fireEvent('onRequest', [this.element, this.request, data, this.queryValue]);
		this.request.send({
			'data': data
		});
	},
	queryResponse: function () {
		var indicator = document.id(this.options.indicator);
		if (indicator) indicator.setStyle('display', 'none');
		var cls = this.options.indicatorClass;
		if (cls) this.element.removeClass(cls);
		return this.fireEvent('onComplete', [this.element, this.request]);
	}
});
Autocompleter.Request.JSON = new Class({
	Extends: Autocompleter.Request,
	initialize: function (el, url, options) {
		this.parent(el, options);
		this.request = new Request.JSON(Object.merge({}, {
			'url': url,
			'link': 'cancel'
		}, this.options.ajaxOptions)).addEvent('onComplete', this.queryResponse.bind(this));
	},
	queryResponse: function (response) {
		this.parent();
		this.update(response);
	}
});
Autocompleter.Request.HTML = new Class({
	Extends: Autocompleter.Request,
	initialize: function (el, url, options) {
		this.parent(el, options);
		this.request = new Request.HTML(Object.merge({}, {
			'url': url,
			'link': 'cancel',
			'update': this.choices
		}, this.options.ajaxOptions)).addEvent('onComplete', this.queryResponse.bind(this));
	},
	queryResponse: function (tree, elements) {
		this.parent();
		if (!elements || !elements.length) {
			this.hideChoices();
		} else {
			this.choices.getChildren(this.options.choicesMatch).each(this.options.injectChoice ||
			function (choice) {
				var value = choice.innerHTML;
				choice.inputValue = value;
				this.addChoiceEvents(choice.set('html', this.markQueryValue(value)));
			}, this);
			this.showChoices();
		}
	}
});
Autocompleter.Ajax = {
	Base: Autocompleter.Request,
	Json: Autocompleter.Request.JSON,
	Xhtml: Autocompleter.Request.HTML
};
