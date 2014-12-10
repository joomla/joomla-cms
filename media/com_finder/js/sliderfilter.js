FinderFilter = new Class({

	Implements: [Options, Events],
	Extends: Fx,

	options: {
		onActive: Class.empty,
		onBackground: Class.empty,
		height: false,
		width: true,
		opacity: true,
		fixedHeight: false,
		fixedWidth: 220,
		wait: true
	},

	initialize: function (togglers, elements, container, frame) {
		this.togglers = togglers || [];
		this.elements = elements || [];
		this.container = document.id(container);
		this.frame = document.id(frame);

		this.effects = {};

		this.addEvent('onBackground', function () {
			el = this.elements[this.active];
			el.getElements('input').each(function (n) {
				n.removeProperty('checked');
			});
		});
		this.addEvent('onComplete', function () {
			el = this.elements[this.active];
			if (!el.getStyle('width').toInt()) {
				this.container.set('styles', {
					'width': this.container.getStyle('width').toInt() - el.fullWidth
				});
			}
			this.active = null;
		});
		for (var i = 0, l = this.togglers.length; i < l; i++) this.addSection(this.togglers[i], this.elements[i]);
		this.elements.each(function (el, i) {
			var cbs = el.getElements('input.selector').length;
			var cba = 0;
			el.getElements('input.selector').each(function (n) {
				if (n.getProperty('checked')) {
					this.togglers[i].setProperty('checked', 'checked');
					cba += 1;
				}
			}, this);
			if (cbs > 0 && cbs === cba && el.getElement('input.branch-selector') != null) {
				el.getElement('input.branch-selector').setProperty('checked', 'checked');
			}
			if (cba) {
				this.fireEvent('onActive', [this.togglers[i], el]);
			} else {
				for (var fx in this.effects) el.setStyle(fx, 0);
			}
			el.getElement('dt').getElement('input').addEvent('change', function (e) {
				if (e.target.getProperty('checked')) {
					el.getElements('div').each(function (div) {
						div.getElements('input').setProperty('checked', 'checked');
					});
				} else {
					el.getElements('div').each(function (div) {
						div.getElements('input').removeProperty('checked');
					});
				}
			});
		}, this);
	},

	addSection: function (toggler, element, pos) {
		toggler = document.id(toggler);
		element = document.id(element);
		var test = this.togglers.contains(toggler);
		var len = this.togglers.length;
		this.togglers.include(toggler);
		this.elements.include(element);
		if (len && (!test || pos)) {
			pos = Array.pick(pos, len - 1);
			toggler.inject(this.togglers[pos], 'before');
			element.inject(toggler, 'after');
		} else if (this.container && !test) {
			toggler.inject(this.container);
			element.inject(this.container);
		}
		var idx = this.togglers.indexOf(toggler);
		toggler.addEvent('click', this.toggle.bind(this, idx));
		if (this.options.fixedWidth) element.fullWidth = this.options.fixedWidth;
		if (this.options.fixedHeight) element.fullHeight = this.options.fixedHeight;
		return this;
	},

	toggle: function (index) {
		index = (typeOf(index) == 'element') ? this.elements.indexOf(index) : index;
		if (this.timer && this.options.wait) return this;
		this.active = index;
		var obj = {};
		obj[index] = {};
		var el = this.elements[index];
		if (this.togglers[index].getProperty('checked')) {
			for (var fx in this.effects) obj[index][fx] = el[this.effects[fx]];
			this.start(obj);
			this.fireEvent('onActive', [this.togglers[index], el]);
		} else {
			for (var fx in this.effects) obj[index][fx] = 0;
			this.start(obj);
			this.fireEvent('onBackground', [this.togglers[index], el]);
		}
		return this;
	}
});

window.addEvent('domready', function () {
	Filter = new FinderFilter(document.getElements('input.toggler'), document.getElements('dl.checklist'), document.id('finder-filter-container'), document.id('finder-filter-window'));
});
