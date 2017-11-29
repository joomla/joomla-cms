(() => {
	class JoomlaIframe extends HTMLElement {
		static get observedAttributes() {
			return ['autoHeight', 'name', 'src', 'width', 'height', 'scrolling', 'frameborder', 'useClass', 'noFrameText'];
		}

		get autoHeight() { return (this.getAttribute('auto-height') === 'true'); }
		get name() { this.getAttribute('name'); }
		get src() { return this.getAttribute('src'); }
		get width() { this.getAttribute('width'); }
		get height() { this.getAttribute('height'); }
		get scrolling() { this.getAttribute('scrolling'); }
		get frameborder() { this.getAttribute('frameborder'); }
		get useClass() { this.getAttribute('use-class'); }
		get noFrameText() { this.getAttribute('no-frame-text'); }

		connectedCallback() {
			this.adjustHeight = this.adjustHeight().bind(this);
			const iframe = document.createElement('iframe');

			iframe.setAttribute('name', this.name);
			iframe.setAttribute('src', this.src);
			iframe.setAttribute('width', this.width);
			iframe.setAttribute('height', this.height);
			iframe.setAttribute('scrolling', this.scrolling);
			iframe.setAttribute('frameborder', this.frameborder);
			iframe.setAttribute('class', this.useClass);
			iframe.innerText = this.noFrameText;

			if (this.autoHeight) {
				iframe.addEventListener('load', this.adjustHeight, false);
			}
		}

		adjustHeight() {
			let height = 0;
			const iframe = this.querySelector('iframe');
			const doc    = 'contentDocument' in iframe ? iframe.contentDocument : iframe.contentWindow.document;
			height = doc.body.scrollHeight;
			iframe.style.height = parseInt(height) + 60 + 'px';
		}
	}

	customElements.define('joomla-iframe', JoomlaIframe);
})();
