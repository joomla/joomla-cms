(() => {
	class JoomlaIframe extends HTMLElement {
		static get observedAttributes() {
			return ['iframe-auto-height', 'iframe-name', 'iframe-src', 'iframe-width', 'iframe-height', 'iframe-scrolling', 'iframe-border', 'iframe-class', 'iframe-title'];
		}

		get iframeAutoHeight() { return (this.getAttribute('iframe-auto-height') === '1'); }
		get iframeName() { return this.getAttribute('iframe-name'); }
		get iframeSrc() { return this.getAttribute('iframe-src'); }
		get iframeWidth() { return this.getAttribute('iframe-width'); }
		get iframeHeight() { return this.getAttribute('iframe-height'); }
		get iframeScrolling() { return (this.getAttribute('iframe-scrolling') === '1'); }
		get iframeBorder() { return (this.getAttribute('iframe-border') === '1'); }
		get iframeClass() { return this.getAttribute('iframe-class'); }
		get iframeTitle() { return this.getAttribute('iframe-title'); }


		connectedCallback() {
			this.iframe = document.createElement('iframe');

			this.iframe.setAttribute('name', this.iframeName);
			this.iframe.setAttribute('src', this.iframeSrc);
			this.iframe.setAttribute('width', this.iframeWidth);
			this.iframe.setAttribute('height', this.iframeHeight);
			this.iframe.setAttribute('scrolling', this.iframeScrolling);
			this.iframe.setAttribute('frameborder', this.iframeBorder);
			this.iframe.setAttribute('class', this.iframeClass);
			this.iframe.setAttribute('title', this.iframeTitle);

			// Generate a random unique ID
			this.iframe.setAttribute('id', 'iframe-' + Date.now().toString(36) + Math.random().toString(36).substr(2, 5));

			if (this.iframeAutoHeight) {
				this.iframe.addEventListener('load', this.adjustHeight.bind(this), false);
			}

			this.appendChild(this.iframe);
		}

		adjustHeight() {
			const doc    = this.iframe.contentWindow.document;
			const height = doc.body.scrollHeight || 0;
			this.iframe.setAttribute('height', (height + 60) + 'px');
		}
	}

	customElements.define('joomla-iframe', JoomlaIframe);
})();
