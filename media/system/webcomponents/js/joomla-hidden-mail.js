(() => {
	class JoomlaHiddenMail extends HTMLElement {

		connectedCallback() {
			var newEl;
			var isSpan = false;

			if (this.getAttribute('is-link') === '1') {
				newEl = '<a href="mailto:' + window.atob(this.getAttribute('first')) + '@' + window.atob(this.getAttribute('last')) +
					'">';
			} else {
				isSpan = true;
				newEl = '<span>';
			}

			if (this.getAttribute('is-email') === '1') {
				newEl += this.getAttribute('text') !== '' ? window.atob(this.getAttribute('text')) : window.atob(this.getAttribute('first')) + '@' + window.atob(this.getAttribute('last'));
			}

			if (isSpan) {
				newEl += '</span>'
			} else {
				newEl += '</a>'
			}

			this.innerHTML = newEl;
		}
	}

	customElements.define('joomla-hidden-mail', JoomlaHiddenMail);
})();
