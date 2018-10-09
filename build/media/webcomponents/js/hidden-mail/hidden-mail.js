;customElements.define('joomla-hidden-mail', class extends HTMLElement {
	connectedCallback() {
		let newEl;
		let base = this.getAttribute('base') + '/';

		if (this.getAttribute('is-link') === '1') {
			newEl = document.createElement('a');
			newEl.setAttribute('href', 'mailto:' + this.b64DecodeUnicode(this.getAttribute('first')) + '@' + this.b64DecodeUnicode(this.getAttribute('last')));

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
			let innerStr = this.b64DecodeUnicode(this.getAttribute('text'));

			innerStr = innerStr.replace('src="images/', `src="${base}images/`).replace('src="media/', `src="${base}media/`);
			newEl.innerHTML = innerStr;
		} else {
			newEl.innerText = window.atob(this.getAttribute('first')) + '@' + window.atob(this.getAttribute('last'));
		}

		// Remove the noscript message
		this.innerText = '';

		// Display the new element
		this.appendChild(newEl);
	}

	b64DecodeUnicode(str) {
		return decodeURIComponent(Array.prototype.map.call(atob(str), (c) => {
			return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)
		}).join(''))
	}
});
