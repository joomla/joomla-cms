Joomla = Joomla || {};

Joomla.installer = {
	handlers: [],
	addHandler: function (type, func) {
		if (typeof func == 'function') {
			this.handlers[type] = func;
		}
	},
	triggerHandler: function (type, form) {
		if (typeof this.handlers[type] == 'function') {
			return this.handlers[type].apply(this, [form]);
		}
		console.log('Unknown install submit button handler: ' + type);
		return false;
	},
	submit: function (type, form) {
		form = form || document.getElementById('adminForm');
		var valid = this.triggerHandler(type, form);

		if (valid) {
			jQuery('#loading').css('display', 'block');
			form.submit();
		}
	}
};
