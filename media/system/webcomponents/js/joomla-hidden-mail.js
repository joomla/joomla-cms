(() => {
	class JoomlaHiddenMail extends HTMLElement {

		connectedCallback() {
			if (!window.Joomla) {
				throw new Error('Error loading the Joomla API')
			}

			this.id = this.getAttribute('id');

			if (!this.id) {
				throw new Error('Error: the element needs an ID')
			}

			var options = window.Joomla.getOptions('email-cloak');
			this.id = this.id.replace('cloak-', '');

			if (typeof options[this.id] === 'object') {
				var newEl;
				var isSpan = false;

				if (options[this.id].linkable === true) {
					newEl = '<a ' + options[this.id].properties.before + ' href="mailto:' + options[this.id].properties.name + '@' + options[this.id].properties.domain +
						'" ' + options[this.id].properties.after + '>';
				} else {
					isSpan = true;
					newEl = '<span ' + options[this.id].properties.before + options[this.id].properties.after + '>';
				}

				if (options[this.id].isEmail === true) {
					newEl += options[this.id].properties.text !== '' ? options[this.id].properties.text : options[this.id].properties.name + '@' + options[this.id].properties.domain;
				}

				if (isSpan) {
					newEl += '</span>'
				} else {
					newEl += '</a>'
				}

				this.innerHTML = newEl;
			}
		}
	}

	customElements.define('joomla-hidden-mail', JoomlaHiddenMail);
})();
