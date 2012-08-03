/**
 * JavaScript file for Element: Toggler
 * Adds slide in and out functionality to elements based on an elements value
 *
 * @package			NoNumber Framework
 * @version			12.6.4
 *
 * @author			Peter van Westen <peter@nonumber.nl>
 * @link			http://www.nonumber.nl
 * @copyright		Copyright © 2012 NoNumber All Rights Reserved
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

if (typeof( window['nnToggler'] ) == "undefined") {
	window.addEvent('domready', function()
	{
		if (document.getElements('.nntoggler').length) {
			nnToggler = new nnToggler();
		} else {
			// Try again 2 seconds later, because IE sometimes can't see object immediately
			(function()
			{
				if (document.getElements('.nntoggler').length) {
					nnToggler = new nnToggler();
				}
			}).delay(2000);
		}
	});

	var nnToggler = new Class({
		togglers: {}, // holds all the toggle areas
		elements: {}, // holds all the elements and their values that affect toggle areas

		initialize: function()
		{
			var self = this;

			this.togglers = document.getElements('.nntoggler');
			if (!this.togglers.length) {
				return;
			}

			nnScripts.overlay.open(0.2);

			( function()
			{
				self.initTogglers();
			} ).delay(250);
		},

		initTogglers: function()
		{
			var self = this;

			var new_togglers = {};

			$each(this.togglers, function(toggler)
			{
				toggler.setStyle('visibility', 'visible');

				// make parent tds have no padding
				if (toggler.getParent().get('tag') == 'td') {
					toggler.getParent().setStyle('padding', '0');
				}
				// init togglers
				if (toggler.id) {
					toggler.elements = {};
					toggler.fx = {};
					toggler.nofx = toggler.hasClass('nntoggler_nofx');
					toggler.mode = ( toggler.hasClass('nntoggler_horizontal') ) ? 'horizontal' : 'vertical';
					toggler.method = ( toggler.hasClass('nntoggler_and') ) ? 'and' : 'or';
					toggler.ids = toggler.id.split('___');
					for (var i = 1; i < toggler.ids.length; i++) {
						keyval = toggler.ids[i].split('.');

						key = keyval[0];
						val = 1;
						if (keyval.length > 1) {
							val = keyval[1];
						}

						if (typeof( toggler.elements[key] ) == "undefined") {
							toggler.elements[key] = [];
						}
						toggler.elements[key].include(val);

						if (typeof( self.elements[key] ) == "undefined") {
							self.elements[key] = {};
							self.elements[key].elements = [];
							self.elements[key].values = [];
							self.elements[key].togglers = [];
						}
						self.elements[key].togglers.push(toggler.id);
					}

					new_togglers[toggler.id] = toggler;
				}
			});
			this.togglers = new_togglers;
			new_togglers = null;

			// add effects
			$each(this.togglers, function(toggler)
			{
				if (toggler.nofx) {
					toggler.fx.slide = new Fx.Slide(toggler, { 'duration': 1, 'mode': toggler.mode, onComplete: function() { self.completeSlide(toggler); } });
				} else {
					toggler.fx.slide = new Fx.Slide(toggler, { 'duration': 500, 'mode': toggler.mode, onStart: function() { self.startSlide(); }, onComplete: function() { self.completeSlide(toggler); } });
					toggler.fx.fade = new Fx.Morph(toggler, { 'duration': 500 });
				}
			});

			this.setElements();

			// hide togglers that should be
			$each(this.togglers, function(toggler)
			{
				self.toggleByID(toggler.id, 1);
			});

			// set all divs in the form to auto height
			this.autoHeightDivs();

			( function()
			{
				document.body.setStyle('cursor', '');
				nnScripts.overlay.close();
			} ).delay(250);
		},

		startSlide: function()
		{
		},

		completeSlide: function(toggler)
		{
			toggler.getParent().setStyle('height', 'auto');
		},

		autoHeightDivs: function()
		{
			// set all divs in the form to auto height
			$each(document.getElements('div.col div, div.fltrt div'), function(el)
			{
				if (el.getStyle('height') != '0px'
					&& !el.hasClass('input')
					&& !el.hasClass('nn_hr')
					&& !el.hasClass('textarea_handle')
					// GK elements
					&& el.id.indexOf('gk_') === -1
					&& el.className.indexOf('gk_') === -1
					&& el.className.indexOf('switcher-') === -1
					) {
					el.setStyle('height', 'auto');
				}
			});
		},

		toggle: function(el_name)
		{
			this.setValues(el_name);
			for (var i = 0; i < this.elements[el_name].togglers.length; i++) {
				this.toggleByID(this.elements[el_name].togglers[i]);
			}
			this.autoHeightDivs();
		},

		toggleByID: function(id, nofx)
		{
			if (typeof( this.togglers[id] ) == "undefined") {
				return;
			}

			var toggler = this.togglers[id];

			var show = this.isShow(toggler);

			toggler.fx.slide.cancel();
			if (nofx || toggler.nofx) {
				if (show) {
					toggler.fx.slide.show();
					this.completeSlide(toggler);
				} else {
					toggler.fx.slide.hide();
				}
			} else {
				toggler.fx.fade.cancel();
				if (show) {
					toggler.fx.slide.slideIn();
					( function() { toggler.fx.fade.start({ 'opacity': 1 }) } ).delay(250);
				} else {
					toggler.fx.slide.slideOut();
					toggler.fx.fade.start({ 'opacity': 0 });
				}
			}
		},

		isShow: function(toggler)
		{
			var show = ( toggler.method == 'and' );
			for (el_name in toggler.elements) {
				var vals = toggler.elements[el_name];
				var values = this.elements[el_name].values;
				if (values != null && values.length && ( ( vals == '*' && values != '' ) || nnScripts.in_array(vals, values) )) {
					if (toggler.method == 'or') {
						show = 1;
						break;
					}
				} else {
					if (toggler.method == 'and') {
						show = 0;
						break;
					}
				}
			}

			return show;
		},

		setValues: function(el_name)
		{
			var els = this.elements[el_name].elements;

			var values = [];
			// get value
			$each(els, function(el)
			{
				switch (el.type) {
					case 'radio':
					case 'checkbox':
						if (el.checked) {
							values.push(el.value);
						}
						break;
					default:
						if (typeof( el.elements ) != "undefined" && el.elements.length > 1) {
							for (var i = 0; i < el.elements.length; i++) {
								if (el.checked) {
									values.push(el.value);
								}
							}
						} else {
							values.push(el.value);
						}
						break;
				}
			});
			this.elements[el_name].values = values;
		},

		setElements: function()
		{
			var self = this;
			$each(document.getElements('input, select'), function(el)
			{
				el_name = el.name.replace('@', '_').replace('[]', '').replace(/(?:jform\[params\]|jform|params|advancedparams)\[(.*?)\]/g, '\$1').trim();
				if (el_name !== '') {
					if (typeof( self.elements[el_name]) != "undefined") {
						self.elements[el_name].elements.push(el);
						self.setValues(el_name);
						self.setElementEvents(el, el_name);
					}
				}
			});
		},

		setElementEvents: function(el, el_name)
		{
			var self = this;
			var type;
			if (typeof( el.type ) == "undefined") {
				if (el.get('tag') == 'select') {
					type = 'select';
				}
			} else {
				type = el.type;
			}

			var func = function() { self.toggle(el_name); };

			switch (type) {
				case 'radio':
				case 'checkbox':
					el.addEvent('click', func);
					el.addEvent('keyup', func);
					break;
				case 'select':
				case 'select-one':
				case 'text':
					el.addEvent('change', func);
					el.addEvent('keyup', func);
					break;
				default:
					el.addEvent('change', func);
					break;
			}
		}
	});
}