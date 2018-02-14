;customElements.define('joomla-field-category', class extends HTMLElement {
	constructor () {
		super();

		this.element = '';
		Joomla.loadingLayer('load', document.body);
	}

	connectedCallback() {
		// Check if custom fields are enabled
		if (this.getAttribute('custom-fields-enabled') !== 'true') {
			return;
		}

		this.element = this.querySelector('select');
		this.categoryHasChanged = this.categoryHasChanged.bind(this);

		if (!this.element.value !== this.getAttribute('custom-fields-cat-id')) {
			this.element.value = this.getAttribute('custom-fields-cat-id');
		}

		this.element.addEventListener('change', this.categoryHasChanged);
	}

	categoryHasChanged() {
		if (this.element.value === parseInt(this.element.parentNode.getAttribute('custom-fields-cat-id'))) {
			return;
		}

		Joomla.loadingLayer('show', document.body);

		document.querySelector('input[name=task]').value = this.element.parentNode.getAttribute('custom-fields-section') + '.reload';
		this.element.form.submit();
	}
});
