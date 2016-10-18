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

document.addEventListener('DOMContentLoaded', function(){
	var fields = document.querySelectorAll('.js-password-strength');

	for(var i = 0, l = fields.length; i<l; i++) {

		var startClass = '',
			initialVal = '';

		if (!fields[i].value.length) {
			startClass =' progress-danger';
			initialVal = 100;
		}

		// Create a progress meter and the label
		var meter = document.createElement('progress');
		meter.setAttribute('class', 'progress' + startClass);
		meter.value = 0 + initialVal;
		meter.max = 100;
		meter.style.height = '2em';
		meter.setAttribute('aria-describedby', 'password-' + i);

		var label = document.createElement('div');
		label.setAttribute('class', 'text-xs-center');
		label.setAttribute('id', 'password-' + i);

		fields[i].parentNode.append(label);
		fields[i].parentNode.append(meter);

		// Add a listener for keyup
		fields[i].addEventListener('keyup', function(event) {
			var $minLength = event.target.getAttribute('data-min-length'),
				$minIntegers = event.target.getAttribute('data-min-integers'),
				$minSymbols = event.target.getAttribute('data-min-symbols'),
				$minUppercase = event.target.getAttribute('data-min-uppercase'),
				$minLowercase = event.target.getAttribute('data-min-lowercase');

			var strength = new PasswordStrength({
				lowercase: $minLowercase ? $minLowercase : 0,
				uppercase: $minUppercase ? $minUppercase : 0,
				numbers: $minIntegers ? $minIntegers : 0,
				special: $minSymbols ? $minSymbols : 0,
				length: $minLength ? $minLength : 4
			});

			var score = strength.getScore(event.target.value),
				meter = event.target.parentNode.querySelector('progress'),
				i = meter.getAttribute('aria-describedby').replace( /^\D+/g, ''),
				label = event.target.parentNode.querySelector('#password-' + i);

			if (score > 79){
				meter.setAttribute('class', 'progress progress-success');
				label.innerHTML = Joomla.JText._('JFIELD_PASSWORD_INDICATE_VSTRONG');
			}
			if (score > 64 && score < 80){
				meter.setAttribute('class', 'progress progress-success');
				label.innerHTML = Joomla.JText._('JFIELD_PASSWORD_INDICATE_STRONG');
			}
			if (score > 50 && score < 65){
				meter.setAttribute('class', 'progress progress-info');
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

			meter.value = score;
		})
	}

	if (fields[0].getAttribute())
	document.formvalidator.setHandler('passwordExtra', function(value) {
console.log('called')
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
console.log(score)
		if (score > 60 ) returnedValue = true;

		return returnedValue;
	});
});