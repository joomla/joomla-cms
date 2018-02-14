;customElements.define('joomla-core-loader', class extends HTMLElement {
	constructor() {
		super();

		// Define some things
		this.css = `{{CSS_CONTENTS_AUTOMATICALLY_INSERTED_HERE}}`;
		this.styleEl = document.createElement('style');
		this.styleEl.id = 'joomla-loader-css';
		this.styleEl.innerHTML = this.css;

		this.element = document.createElement('div');
		this.element.id = 'joomla-loader';
		this.element.innerHTML = `<div class="box"><span class="yellow"></span><span class="red"></span><span class="blue"></span><span class="green"></span><p>&reg;</p></div>`;

		if (!document.head.querySelector('#joomla-loader-css')) {
			document.head.appendChild(this.styleEl)
		}
	}

	connectedCallback() {
		this.appendChild(this.element);
	}
});