"use strict";

/**
 * PasswordStrength script by Thomas Kjaergaard
 * License: MIT
 * Repo: https://github.com/tkjaergaard/Password-Strength
 */
var PasswordStrength;

PasswordStrength = (function() {
	function PasswordStrength(settings) {
		this.lowercase = settings.lowercase || 1;
		this.uppercase = settings.uppercase || 1;
		this.numbers = settings.numbers || 1;
		this.special = settings.special || 1;
		this.length = settings.length || 8;
	}

	PasswordStrength.prototype.getScore = function(value) {
		var score;
		score = 0;
		score += this.calc(value, /[a-z]/g, this.lowercase);
		score += this.calc(value, /[A-Z]/g, this.uppercase);
		score += this.calc(value, /[0-9]/g, this.numbers);
		score += this.calc(value, /[\$\!\#\?\=\;\:\*\-\_\€\%\&\(\)\`\´]/g, this.special);
		score += value.length > this.length ? 20 : 20 / this.length * value.length;
		return score;
	};

	PasswordStrength.prototype.calc = function(value, pattern, length) {
		var count;
		count = value.match(pattern);
		if (count && count.length > length) {
			return 20;
		}
		if (count) {
			return 20 / length * count.length;
		} else {
			return 0;
		}
	};

	return PasswordStrength;

})();

/**
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
document.addEventListener('DOMContentLoaded', function(){
	"use strict";

	/** Method to check the input and set the meter **/
	var getMeter = function(element) {
		var $minLength = element.getAttribute('data-min-length'),
			$minIntegers = element.getAttribute('data-min-integers'),
			$minSymbols = element.getAttribute('data-min-symbols'),
			$minUppercase = element.getAttribute('data-min-uppercase'),
			$minLowercase = element.getAttribute('data-min-lowercase'),
			meter = element.parentNode.querySelector('progress');

		var strength = new PasswordStrength({
			lowercase: $minLowercase ? $minLowercase : 0,
			uppercase: $minUppercase ? $minUppercase : 0,
			numbers: $minIntegers ? $minIntegers : 0,
			special: $minSymbols ? $minSymbols : 0,
			length: $minLength ? $minLength : 4
		});

		var score = strength.getScore(element.value),
			i = meter.getAttribute('aria-describedby').replace( /^\D+/g, ''),
			label = element.parentNode.querySelector('#password-' + i);

		if (score > 79){
			meter.setAttribute('class', 'progress progress-warning');
			label.innerHTML = Joomla.JText._('JFIELD_PASSWORD_INDICATE_VSTRONG');
		}
		if (score > 64 && score < 80){
			meter.setAttribute('class', 'progress progress-warning');
			label.innerHTML = Joomla.JText._('JFIELD_PASSWORD_INDICATE_STRONG');
		}
		if (score > 50 && score < 65){
			meter.setAttribute('class', 'progress progress-warning');
			label.innerHTML = Joomla.JText._('JFIELD_PASSWORD_INDICATE_NORMAL');
		}
		if (score > 40 && score < 51){
			meter.setAttribute('class', 'progress progress-warning');
			label.innerHTML = Joomla.JText._('JFIELD_PASSWORD_INDICATE_MEDIUM');
		}
		if (score < 41){
			meter.setAttribute('class', 'progress progress-danger');
			label.innerHTML = Joomla.JText._('JFIELD_PASSWORD_INDICATE_WEAK');
		}
		if (score === 100){
			meter.setAttribute('class', 'progress progress-success');
		}
		meter.value = score;

		if (!element.value.length) {
			meter.setAttribute('class', 'progress');
			label.innerHTML = '';
			element.setAttribute('required', '');
		}
	};

	var fields = document.querySelectorAll('.js-password-strength');

	/** Loop  through the fields **/
	for(var i = 0, l = fields.length; i<l; i++) {

		var startClass = '',
			initialVal = '';

		if (!fields[i].value.length) {
			startClass =' progress-danger';
			initialVal = 0;
		}

		/** Create a progress meter and the label **/
		var meter = document.createElement('progress');
		meter.setAttribute('class', 'progress' + startClass);
		meter.value = 0 + initialVal;
		meter.max = 100;
		meter.setAttribute('aria-describedby', 'password-' + i);

		var label = document.createElement('div');
		label.setAttribute('class', 'text-xs-center');
		label.setAttribute('id', 'password-' + i);

		fields[i].parentNode.append(label);
		fields[i].parentNode.append(meter);

		/** Add a data attribute for the required **/
		if (fields[i].value.length > 0) {
			fields[i].setAttribute('required', true);
		}

		/** Add a listener for input data change **/
		fields[i].addEventListener('keyup', function(event) {
			getMeter(event.target);
		})
	}

	/** Set a handler for the validation script **/
	if (fields[0]) {
		document.formvalidator.setHandler('password-strength', function(value) {

			var returnedValue = false,
				fields = document.querySelectorAll('.js-password-strength'),
				$minLength = fields[0].getAttribute('data-min-length'),
				$minIntegers = fields[0].getAttribute('data-min-integers'),
				$minSymbols = fields[0].getAttribute('data-min-symbols'),
				$minUppercase = fields[0].getAttribute('data-min-uppercase'),
				$minLowercase = fields[0].getAttribute('data-min-lowercase');

			var strength = new PasswordStrength({
				lowercase: $minLowercase ? $minLowercase : 0,
				uppercase: $minUppercase ? $minUppercase : 0,
				numbers: $minIntegers ? $minIntegers : 0,
				special: $minSymbols ? $minSymbols : 0,
				length: $minLength ? $minLength : 4
			});

			var score = strength.getScore(value);

			if (score === 100 ) returnedValue = true;

			return returnedValue;
		});
	}
});
