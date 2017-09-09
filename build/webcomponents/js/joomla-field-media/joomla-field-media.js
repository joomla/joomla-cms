/** Include the relative styles */
// if (!document.head.querySelector('#joomla-modal-style')) {
//   const style = document.createElement('style');
//   style.id = 'joomla-modal-style';
//   style.innerHTML = ``;
//   document.head.appendChild(style);
// }

class JoomlaFieldMedia extends HTMLElement {
	static get observedAttributes() {
		return ['basepath', 'rootfolder', 'url', 'modalcont', 'modalwidth', 'modalheight', 'input', 'buttonselect', 'buttonclear', 'buttonsaveselected', 'preview', 'previewwidth', 'previewheight'];
	}

	get basepath() { return this.getAttribute('basepath'); }
	set basepath(value) { this.setAttribute('basepath', value); }
	get rootfolder() { return this.getAttribute('rootfolder'); }
	set rootfolder(value) { this.setAttribute('rootfolder', value); }
	get url() { return this.getAttribute('url'); }
	set url(value) { this.setAttribute('url', value); }
	get modalcont() { return this.getAttribute('modalcont'); }
	set modalcont(value) { this.setAttribute('modalcont', value); }
	get input() { return this.getAttribute('input'); }
	set input(value) { this.setAttribute('input', value); }
	get buttonselect() { return this.getAttribute('buttonselect'); }
	set buttonselect(value) { this.setAttribute('buttonselect', value); }
	get buttonclear() { return this.getAttribute('buttonclear'); }
	set buttonclear(value) { this.setAttribute('buttonclear', value); }
	get buttonsaveselected() { return this.getAttribute('buttonsaveselected'); }
	set buttonsaveselected(value) { this.setAttribute('buttonsaveselected', value); }
	get modalwidth() { return this.getAttribute(parseInt('modalwidth', 10)); }
	set modalwidth(value) { this.setAttribute('modalwidth', value); }
	get modalheight() { return this.getAttribute(parseInt('modalheight', 10)); }
	set modalheight(value) { this.setAttribute('modalheight', value); }
	get previewwidth() { return this.getAttribute('previewwidth'); }
	set previewwidth(value) { this.setAttribute('previewwidth', value); }
	get previewheight() { return this.getAttribute('previewheight'); }
	set previewheight(value) { this.setAttribute('previewheight', value); }
	get preview() { return this.getAttribute('preview'); }
	set preview(value) { this.setAttribute('preview', value); }
	get previewcontainer() { return this.getAttribute('previewcontainer'); }

	attributeChangedCallback(attr, oldValue, newValue) {
		switch (attr) {
			case 'basepath':
			case 'rootfolder':
			case 'url':
			case 'modalcont':
			case 'input':
			case 'buttonselect':
			case 'buttonclear':
			case 'buttonsaveselected':
			case 'previewContainer':
				// string
				break;
			case 'modalwidth':
			case 'modalheight':
			case 'previewwidth':
			case 'previewheight':
				// int
				// const value = parseInt(newValue, 10);
				// if (value !== parseInt(oldValue, 10)) {
				//  this.setAttribute(attr, value);
				// }
				break;
			case 'preview':
				// bool|string
				if (['true', 'false', 'tooltip', 'static'].indexOf(newValue) > -1 && oldValue !== newValue) {
					this.preview = newValue;
				} else {
					// if (oldValue )
					//   this.preview = oldValue;
				}
				break;
			default:
				break;
		}
	}

	connectedCallback() {
		const self = this;
		const button = this.querySelector(this.buttonselect);
		const buttonClear = this.querySelector(this.buttonclear);

		button.addEventListener('click', () => { self.show(self); });

		if (buttonClear) {
			buttonClear.addEventListener('click', () => { self.clearValue(); });
		}

		this.updatePreview();
	}

	disconnectedCallback() {
		const button = this.querySelector(this.buttonselect);
		button.removeEventListener('click', self);
	}

	show(self) {
		window.jQuery(this.querySelector('[role="dialog"]')).modal('show');

		window.jQuery(this.querySelector(this.buttonsaveselected)).on('click', (e) => {
			e.preventDefault();
			e.stopPropagation();
			if (self.selectedPath) {
				self.setValue(self.rootfolder + self.selectedPath);
			} else {
				self.setValue('');
			}

			self.modalClose(self);
			return false;
		});

		window.document.addEventListener('onMediaFileSelected', (e) => {
			const path = e.item.path;

			if (path.match(/.jpg|.jpeg|.gif|.png/)) {
				self.selectedPath = e.item.path;
			} else {
				self.selectedPath = '';
			}
		});
	}

	modalClose(self) {
		window.jQuery(self.querySelector('[role="dialog"]')).modal('hide');
	}

	setValue(value) {
		const input = window.jQuery(this.querySelector(this.input));
		input.val(value).trigger('change');
		this.updatePreview();
	}

	clearValue() {
		this.setValue('');
	}

	updatePreview() {
		if (['true', 'tooltip', 'static'].indexOf(this.preview) === -1 || this.preview === 'false') {
			return;
		}

		// Reset tooltip and preview
		if (this.preview) {
			const containerPreview = window.jQuery(this.querySelector(this.previewcontainer));
			const input = window.jQuery(this.querySelector(this.input));
			const value = input.val();

			containerPreview.popover('dispose');
			input.tooltip('dispose');

			if (!value) {
				containerPreview.popover({ content: Joomla.JText._('JLIB_FORM_MEDIA_PREVIEW_EMPTY'), html: true });
			} else {
				const imgPreview = new Image(this.previewwidth, this.previewheight);
				imgPreview.src = this.basepath + value;

				containerPreview.popover({
					content: imgPreview,
					html: true,
				});
				input.tooltip({ placement: 'top', title: value });
			}
		}
	}
}
customElements.define('joomla-field-media', JoomlaFieldMedia);