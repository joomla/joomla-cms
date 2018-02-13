;customElements.define('joomla-field-category', class extends HTMLElement {
	constructor () {
		super();

		this.element = '';
		this.categoryHasChanged = this.categoryHasChanged.bind(this);
	}

	connectedCallback() {
		this.element = this.querySelector('select');

		Joomla.loadingLayer('load');

		if (!jQuery(this.element).val() !== this.getAttribute('custom-fields-cat-id')) {
			jQuery(this.element).val(this.getAttribute('custom-fields-cat-id'));
		}

		this.element.addEventListener('change', this.categoryHasChanged);
	}

	categoryHasChanged() {
		if (this.element.value === this.element.parentNode.getAttribute('custom-fields-cat-id')) {
			return;
		}

		Joomla.loadingLayer('show');
		document.querySelector('input[name=task]').value = this.element.parentNode.getAttribute('custom-fields-section') + '.reload';
		this.element.form.submit();
	}
});
