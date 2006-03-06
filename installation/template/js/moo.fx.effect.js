fx.Slide = Class.create();
fx.Slide.prototype = {
	setOptions: function(options) {
		this.options = {
			delay: 50,
			opacity: false
		}
		Object.extend(this.options, options || {});
	},

	initialize: function(togglers, sliders, options) {
		this.sliders = sliders;
		this.setOptions(options);
		sliders.each(function(el, i){
			options.onComplete = function(){
				if (el.offsetHeight > 0) el.style.height = '1%';
			}
			el.fx = new fx.Combo(el, options);
			el.fx.hide();
		});

		togglers.each(function(toggler, i){
			toggler.onclick = function(){
				this.toggle(sliders[i], toggler);
			}.bind(this);
		}.bind(this));
	},

	toggle: function(slider, toggler){
		if (slider.offsetHeight == 0) {
			setTimeout(function(){this.clear(slider);}.bind(this), this.options.delay);
			Element.addClassName(toggler, 'moofx-toggler-down');
		}
		
		if (slider.offsetHeight > 0) {
			setTimeout(function(){this.clear(slider);}.bind(this), this.options.delay);
				Element.removeClassName(toggler, 'moofx-toggler-down');
		}
	},

	clear: function(slider){
		slider.fx.clearTimer();
		slider.fx.toggle();
	}
}

function init_moofx()
{
	var sliders  = document.getElementsByClassName('moofx-slider'); 	//div that stretches
	var togglers = document.getElementsByClassName('moofx-toggler'); 	//h3s where I click on

	var slide = new fx.Slide(togglers, sliders, {opacity: true, duration: 400});

	//hash functions
	var found = false;
	togglers.each(function(h3, i)
	{
			var div = Element.find(h3, 'nextSibling'); //element.find is located in prototype.lite
			if (window.location.href.indexOf(h3.title) > 0) 
			{
					slide.toggle(div);
					found = true;
			}
	});
	if (!found) slide.toggle(stretchers[0]);
}