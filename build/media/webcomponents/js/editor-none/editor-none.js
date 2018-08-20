customElements.define('joomla-editor-none', class extends HTMLElement {
	connectedCallback() {
		this.insertAtCursor = this.insertAtCursor.bind(this);

		const that = this;
		/** Register Editor */
		Joomla.editors.instances[that.childNodes[0].id] = {
			'id': that.childNodes[0].id,
			'element':  that,
			'getValue': () => { return that.childNodes[0].value; },
			'setValue': (text) => { return that.childNodes[0].value = text; },
			'replaceSelection': (text) => { return that.insertAtCursor(text); },
			'onSave': () => {}
		};
	}

	disconnectedCallback() {
		/** Remove from the Joomla API */
		delete Joomla.editors.instances[this.childNodes[0].id];
	}

	insertAtCursor(myValue) {
		if (this.childNodes[0].selectionStart || this.childNodes[0].selectionStart === 0) {
			const startPos = this.childNodes[0].selectionStart;
			const endPos = this.childNodes[0].selectionEnd;
			this.childNodes[0].value = this.childNodes[0].value.substring(0, startPos)
				+ myValue
				+ this.childNodes[0].value.substring(endPos, this.childNodes[0].value.length);
		} else {
			this.childNodes[0].value += myValue;
		}
	}
});