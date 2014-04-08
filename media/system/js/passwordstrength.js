/*
---
description: Form.PasswordStrength class, and basic dom methods

license: MIT-style

authors:
 - Al Kent

requires:
 - core/1.3.1: '*'

provides:
 - Form.PasswordStrength
 - Element.Events.keyupandchange
 - String.strength
...
*/

if (!this.Form) this.Form = {};

Form.PasswordStrength = new Class({
	
	Implements: [Options, Events],
	
	options: {
		//onUpdate: $empty,
		threshold: 66,
		primer: '',
		height: 5,
		opacity: 1,
		bgcolor: 'transparent'
	},
	
	element: null,
	fx: null,
	
	initialize: function(el, options){
		this.element = document.id(el);
		this.setOptions(options);
		if (this.options.primer) this.options.threshold = this.options.primer.strength();
		var coord = this.element.getCoordinates();
		var bar = new Element('div', {
			styles: {
				'width': coord.width,
				'height': this.options.height,
				'opacity': this.options.opacity,
				'background-color': this.options.bgcolor
			}
		}).inject(this.element, 'after');
		var meter = new Element('div', {
			styles: {
				'width': 0,
				'height': '100%'
			}
		}).inject(bar);
		this.fx = new Fx.Morph(meter, {
			duration: 'short',
			link: 'cancel',
			unit: '%'
		});
		this.element.addEvent('keyupandchange', this.animate.bind(this));
		if (this.element.get('value')) this.animate();
	},
	
	animate: function(){
		var value = this.element.get('value');
		var color, strength = value.strength(), ratio = (strength / this.options.threshold).round(2).limit(0, 1);
		if (ratio < 0.5) color = ('rgb(255, ' + (255 * ratio * 2).round() + ', 0)').rgbToHex();
		else color = ('rgb(' + (255 * (1 - ratio) * 2).round() + ', 255, 0)').rgbToHex();
		this.fx.start({
			'width': 100 * ratio,
			'background-color': color
		});
		this.fireEvent('update', [this.element, strength, this.options.threshold]);
	}
});

Element.Events.keyupandchange = {
	base: 'keyup',
	condition: function(event){
		var prev = this.retrieve('prev', null);
		var cur = this.get('value');
		if (typeOf(prev) != 'null' && prev == cur) return false;
		this.store('prev', cur);
		return true;
	}
};

String.implement({
	strength: function(){
		var n = 0;
		if (this.match(/\d/)) n += 10;
		if (this.match(/[a-z]+/)) n += 26;
		if (this.match(/[A-Z]+/)) n += 26;
		if (this.match(/[^\da-zA-Z]/)) n += 33;
		return (n == 0) ? 0 : (this.length * n.log() / (2).log()).round();
	}
});
