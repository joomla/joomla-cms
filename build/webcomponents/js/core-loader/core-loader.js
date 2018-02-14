;customElements.define('joomla-core-loader', class extends HTMLElement {
	constructor() {
		super();

		const template = document.createElement('template');
		template.innerHTML = `<style>{{CSS_CONTENTS_AUTOMATICALLY_INSERTED_HERE}}</style>
<div class="box"><span class="yellow"></span><span class="red"></span><span class="blue"></span><span class="green"></span><p>&reg;</p></div>`;

		// Patch shadow DOM
		if (ShadyCSS) {
			ShadyCSS.prepareTemplate(template, 'joomla-core-loader');
		}

		this.attachShadow({mode: 'open'});
		this.shadowRoot.appendChild(template.content.cloneNode(true));

		// Patch shadow DOM
		if (ShadyCSS) {
			ShadyCSS.styleElement(this)
		}
	}
});