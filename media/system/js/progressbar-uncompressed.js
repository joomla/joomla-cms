/**
 * Fx.ProgressBar
 *
 * @version		1.2
 *
 * @license		MIT License
 *
 * @author		Harald Kirschner <mail [at] digitarald [dot] de>
 * @author		Rouven Weßling <me [at] rouvenwessling [dot] de>
 * @copyright	Authors
 */

Fx.ProgressBar = new Class({

	Extends: Fx,

	options: {
		text: null,
		url: null,
		transition: Fx.Transitions.Circ.easeOut,
		fit: true,
		link: 'cancel',
		html5: true
	},

	initialize: function(element, options) {
		this.element = $(element);
		this.parent(options);

		//WAI-ARIA
		this.element.set('role', 'progressbar');
		this.element.set('aria-valuenow', '0');
		this.element.set('aria-valuemin', '0');
		this.element.set('aria-valuemax', '100');

		if (this.options.html5) {
			this.progressElement = new Element('progress').wraps(this.element);
			this.progressElement.max = 100;
			this.progressElement.value = 0;
		}

		var url = this.options.url;
		if (url) {
			this.element.setStyles({
				'background-image': 'url(' + url + ')',
				'background-repeat': 'no-repeat'
			});
		}

		if (this.options.fit) {
			url = url || this.element.getStyle('background-image').replace(/^url\(["']?|["']?\)$/g, '');
			if (url) {
				var fill = new Image();
				fill.onload = function() {
					this.fill = fill.width;
					fill = fill.onload = null;
					this.set(this.now || 0);
				}.bind(this);
				fill.src = url;
				if (!this.fill && fill.width) fill.onload();
			}
		} else {
			this.set(0);
		}
	},

	start: function(to, total) {
		return this.parent(this.now, (arguments.length == 1) ? to.limit(0, 100) : to / total * 100);
	},

	set: function(to) {
		this.now = to;
		var css = (this.fill)
			? (((this.fill / -2) + (to / 100) * (this.element.width || 1) || 0).round() + 'px')
			: ((100 - to) + '%');
		
		this.element.setStyle('backgroundPosition', css + ' 0px').title = Math.round(to) + '%';
		this.element.set('aria-valuenow', to);

		if (this.options.html5) {
			this.progressElement.value = to;
		}

		var text = $(this.options.text);
		if (text) text.set('text', Math.round(to) + '%');

		return this;
	}
});
