(() => {
	class JoomlaIframe extends HTMLElement {
		static get observedAttributes() {
			return ['autoHeight', 'name', 'src', 'width', 'height', 'scrolling', 'frameborder', 'useClass', 'noFrameText'];
		}

		get autoHeight() { return (this.getAttribute('auto-height') === '1'); }
		get name() { return this.getAttribute('name'); }
		get src() { return this.getAttribute('src'); }
		get width() { return this.getAttribute('width'); }
		get height() { return this.getAttribute('height'); }
		get scrolling() { return (this.getAttribute('scrolling') === '1'); }
		get frameborder() { return (this.getAttribute('frameborder') === '1'); }
		get useClass() { return this.getAttribute('use-class'); }
		get noFrameText() { return (this.getAttribute('no-frame-text') === '1'); }

		connectedCallback() {
			// this.adjustHeight = this.adjustHeight().bind(this);
			this.iframe = document.createElement('iframe');

			this.iframe.setAttribute('name', this.name);
			this.iframe.setAttribute('src', this.src);
			this.iframe.setAttribute('width', this.width);
			this.iframe.setAttribute('height', this.height);
			this.iframe.setAttribute('scrolling', this.scrolling);
			this.iframe.setAttribute('frameborder', this.frameborder);
			this.iframe.setAttribute('class', this.useClass);
			this.iframe.innerText = this.noFrameText;



			if (this.autoHeight) {
				this.iframe.addEventListener('load', this.adjustHeight.bind(this), false);
			}

			this.appendChild(this.iframe);

		}

		adjustHeight() {
			let height = 0;
			const doc    = this.iframe.contentWindow.document;
			height = doc.body.scrollHeight;
			this.iframe.style.height = parseInt(height) + 60 + 'px';
		}
	}

	customElements.define('joomla-iframe', JoomlaIframe);
})();
