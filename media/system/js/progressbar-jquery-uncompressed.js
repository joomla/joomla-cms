/*
name: Fx.ProgressBar

description: Creates a progressbar with WAI-ARIA and optional HTML5 support.

license: MIT-style

authors:
- Harald Kirschner <mail [at] digitarald [dot] de>
- Rouven Weßling <me [at] rouvenwessling [dot] de>

requires: [Core/Fx, Core/Class, Core/Element]

provides: Fx.ProgressBar
*/

Fx.ProgressBar = new Class({
	Extends: Fx,

	options: {
		//onComplete: function () {},
		text: null,
		html5: true
	},

	initialize: function(element, options) {
		element = document.id(element);
		this.parent(options);
		var url = this.options.url;
		this.useHtml5 = this.options.html5 && this.supportsHtml5();

		var classes = element.className;
		var id = element.id;
		if (this.useHtml5) {
			var classes = element.className;
			this.element = new Element('progress').replaces(element);
			this.element.max = 100;
			this.element.value = 0;
		} else {
			this.element = new Element('div', {'class': 'progress progress-striped'}).replaces(element);
			this.barElement = new Element('div', {
				'class': 'bar'
			}).inject(this.element);

			// WAI-ARIA
			this.element.set('role', 'progressbar');
			this.element.set('aria-valuenow', '0');
			this.element.set('aria-valuemin', '0');
			this.element.set('aria-valuemax', '100');
		}
		this.element.id = id;
		this.element.addClass(classes);

		this.set(0);
	},

	supportsHtml5: function () {
		return 'value' in document.createElement('progress');
	},

	start: function(to, total) {
		return this.parent(this.now, (arguments.length == 1) ? to.limit(0, 100) : to / total * 100);
	},

	setIndeterminate: function() {
		this.indeterminate = true;

		if (this.useHtml5) {
			this.element.removeProperty('value');
		} else {
			this.barElement.setStyle('width', '100%');
			this.barElement.addClass('active');
			this.element.removeProperty('aria-valuenow').title = '';
		}
	},

	set: function(to) {
		if (to >= 100) {
			to = 100;
		}
		this.now = to;

		if (this.useHtml5) {
			this.element.value = to;
		} else {
			this.barElement.setStyle('width', to + '%');
			this.element.set('aria-valuenow', to).title = Math.round(to) + '%';
		}

		var text = document.id(this.options.text);
		if (text) text.set('text', Math.round(to) + '%');

		if (to >= 100) {
			self.fireEvent('complete');
		}

		return this;
	}
});
