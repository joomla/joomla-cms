(() => {
	class JoomlaHiddenMail extends HTMLElement {
		connectedCallback() {
			var newEl;
			var isSpan = false;

			if (this.getAttribute('is-link') === '1') {
				newEl = document.createElement('a');
				newEl.setAttribute('href', 'mailto:' + window.atob(this.getAttribute('first')) + '@' + window.atob(this.getAttribute('last')));

				// Get all of the original element attributes, and pass them to the link
				for(var i = 0, l = this.attributes.length; i < l; ++i){
					var nodeName  = this.attributes.item(i).nodeName;

					if (nodeName) {
						// We do care for some attributes
						if (['is-link', 'is-email', 'first', 'last', 'text'].indexOf(nodeName)) {
							continue;
						}

						var nodeValue = this.attributes.item(i).nodeValue;

						newEl.setAttribute(nodeName, nodeValue);
					}
				}
			} else {
				newEl = document.createElement('span');
			}

			if (this.getAttribute('is-email') === '1') {
				newEl.innerText = this.getAttribute('text') !== '' ? window.atob(this.getAttribute('text')) : window.atob(this.getAttribute('first')) + '@' + window.atob(this.getAttribute('last'));
			}

			// Remove the noscript message
			this.innerText = '';

			// Display the new element
			this.appendChild(newEl);
		}
	}

	customElements.define('joomla-hidden-mail', JoomlaHiddenMail);
})();
