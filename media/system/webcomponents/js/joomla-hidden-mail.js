;customElements.define('joomla-hidden-mail', class extends HTMLElement {
	connectedCallback() {
		let newEl;

		if (this.getAttribute('is-link') === '1') {
			newEl = document.createElement('a');
			newEl.setAttribute('href', 'mailto:' + window.atob(this.getAttribute('first')) + '@' + window.atob(this.getAttribute('last')));

			// Get all of the original element attributes, and pass them to the link
			for(let i = 0, l = this.attributes.length; i < l; ++i){
				const nodeName  = this.attributes.item(i).nodeName;

				if (nodeName) {
					// We do care for some attributes
					if (['is-link', 'is-email', 'first', 'last', 'text'].indexOf(nodeName) > -1) {
						continue;
					}

					const nodeValue = this.attributes.item(i).nodeValue;

					newEl.setAttribute(nodeName, nodeValue);
				}
			}
		} else {
			newEl = document.createElement('span');
		}

		if (this.getAttribute('text')) {
			let innerStr = decodeURIComponent(escape(window.atob(this.getAttribute('text'))));
			innerStr = innerStr.replace('src="images/', 'src="/images/');
			newEl.innerHTML = innerStr;
		} else {
			newEl.innerText = window.atob(this.getAttribute('first')) + '@' + window.atob(this.getAttribute('last'));
		}

		// Remove the noscript message
		this.innerText = '';

		// Display the new element
		this.appendChild(newEl);
	}
});
