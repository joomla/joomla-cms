var DisableSearch = new Class({
	options: {
		inputField: null,
		submitButton: null,
		minLength: 1
	},
	initialize: function (options) {
		this.setOptions(options);
	},
	checkInput: function () {
		var lenEl, lenM = this.options.minLength, self = this;

		if (this.options.inputField.value.length < lenM) {
			self.options.submitButton.disabled = true;
		}

		this.options.inputField.addEvent('keydown', function (event) {
			lenEl = self.options.inputField.value.length;

			switch (event.key) {
			case 'enter':
				if (lenEl < lenM) {
					return false;
				}
				break;
			case 'backspace':
				if (lenEl - 1 < lenM && self.options.submitButton.disabled !== true) {
					self.options.submitButton.disabled = true;
				}
				break;
			default:
				if (lenEl + 1 >= lenM && self.options.submitButton.disabled !== false) {
					self.options.submitButton.disabled = false;
				}
				break;
			}
		});
	}
});
DisableSearch.implement(new Options);