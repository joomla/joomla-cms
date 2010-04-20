/**
 * Fx.ProgressBar
 *
 * @version		1.1
 *
 * @license		MIT License
 *
 * @author		Harald Kirschner <mail [at] digitarald [dot] de>
 * @copyright	Authors
 */

Fx.ProgressBar = new Class({

	Extends: Fx,

	options: {
		text: null,
		url: null,
		transition: Fx.Transitions.Circ.easeOut,
		fit: true,
		link: 'cancel'
	},

	initialize: function(element, options) {
		this.element = $(element);
		this.parent(options);

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

		var text = $(this.options.text);
		if (text) text.set('text', Math.round(to) + '%');

		return this;
	}

});