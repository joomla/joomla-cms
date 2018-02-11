;customElements.define('joomla-field-tags', class extends HTMLElement {
	constructor() {
		super();

		// Define some things
		this.actualInput = '';
		this.activeInput = '';
		this.tagsContainer = '';
		this.tags = [];
		this.values = [];
		this.dragSrcEl = null;
		this.prefixValue = '#new#';
	}

	connectedCallback() {
		const self = this;

		this.actualInput = this.querySelector('input');

		if (!this.actualInput) {
			throw new Error('`joomla-field-tags` UI component is missing the input element');
		}

		// Bind functions
		this.insert.bind(this);
		this.appendNewTag.bind(this);
		const initialValues = JSON.parse(this.actualInput.value === '' ? {} : this.actualInput.value);

		for (const p in initialValues) {
			this.tags.push(p);
			this.values.push(initialValues[p]);
		}

		// Create the tags area
		this.tagsContainer = document.createElement('div');
		this.tagsContainer.classList.add('inline');
		this.actualInput.insertAdjacentElement('afterend', this.tagsContainer);

		// Create the hidden input
		this.activeInput = this.actualInput.cloneNode();

		this.actualInput.setAttribute('type', 'hidden');
		this.actualInput.removeAttribute('id');

		this.activeInput.setAttribute('type', 'text');
		this.tagsContainer.appendChild(this.activeInput);

		this.activeInput.addEventListener('input', (e) => {
			e.preventDefault();
			e.stopPropagation();
			self.insert(false, e.target, false);
		});

		// Enter
		this.activeInput.addEventListener('keydown', (e) => {
			if (e.keyCode === 13) {
				e.preventDefault();
				e.stopPropagation();
				if (self.activeInput.value !== '') {
					self.insert(true, self.activeInput, true);
					self.activeInput.focus();
				}
			}

			// Backspace
			if (e.keyCode === 8 && self.activeInput.value === '') {
				e.preventDefault();
				e.stopPropagation();
				const foo = self.activeInput.previousElementSibling.querySelector('span');
				console.log(self.activeInput.previousElementSibling)
				foo.click();
				self.activeInput.focus();
			}
		});

		this.render();

		this.activeInput.value = '';
	}

	render() {
		// const childs = [].slice.call(this.tagsContainer.childNodes).filter(e => {e.tagName === 'span'});
		const childs = [].slice.call(this.tagsContainer.querySelectorAll('span.tag'));
		// debugger;
		for (var i = 0; i < this.tags.length; i++) {
			if (childs[i]) {
				childs[i].childNodes[0].nodeValue = this.tags[i];
			} else {
				this.appendNewTag(this.tags[i], this.values[i]);
			}
		}

		for (; i < childs.length; i++) {
			this.tagsContainer.removeChild(childs[i]);
		}


		// Regenerate the hidden input value
		const final = {};
		for (var i = 0; i < this.tags.length; i++) {
			final[this.tags[i]] = this.values[i];
		}

		let tmpJson = JSON.stringify(final);
		tmpJson = tmpJson.replace(/\"/g, '&quot;');

		this.actualInput.setAttribute('value', tmpJson);

		// Move the input to the end
		this.tagsContainer.insertAdjacentElement('beforeend', this.activeInput)
	}

	insert(ignoreComma, context, userInput) {
		if (context.value.indexOf(',') != -1 || ignoreComma) {
			if (context.value.substring(context.value.length - 1) === ',') {
				context.value = context.value.substring(0, context.value.length - 1);
			}

			const newTags = context.value.split(',');
			//   debugger;
			newTags.forEach((tag) => {
				if (this.tags.indexOf(tag) === -1) {
					this.tags.push(tag);
					this.values.push(this.prefixValue + tag);
				}
			});

			context.value = '';

			this.render();
		}
	}

	removeTag(e) {
		if (this.tags.length) {
			let parentEl = '';
			if (e && typeof e === 'object' && e.target) {
				parentEl = e.target.parentNode;
			} else {
				parentEl = e.parentNode;
			}

			this.tags.splice(this.tags.indexOf(parentEl.childNodes[0].nodeValue), 1);
			this.values.splice(this.values.indexOf(parentEl.getAttribute('data-value')), 1);

			this.render();
		}
	}

	handleDragStart(e) {
		e.target.style.opacity = '0.9';
		e.target.classList.remove('delete');
		this.dragSrcEl = e.target;
		e.dataTransfer.effectAllowed = 'move';
	}

	handleDragEnter(e) {
		e.target.style.opacity = '0.6';
		e.target.classList.add('over');
	};

	handleDragLeave(e) {
		e.srcElement.style.opacity = null;
		e.srcElement.classList.remove('over');
	};

	handleDragEnd(e) {
		this.dragSrcEl.style.opacity = null;
		e.target.style.opacity = null;
		e.target.classList.add('delete');
		this.dragSrcEl.classList.add('delete');
	};

	handleDragOver(e) {
		if (e.preventDefault) {
			e.preventDefault();
		}

		e.dataTransfer.dropEffect = 'move';
	};

	handleDrop(e) {
		if (e.stopPropagation) {
			e.stopPropagation();
		}
		e.target.classList.remove('over');

		if (this.dragSrcEl != e.target) {
			const indexTwo = this.tags.indexOf(e.target.childNodes[0].nodeValue);
			this.tags[this.tags.indexOf(this.dragSrcEl.childNodes[0].nodeValue)] = e.target.childNodes[0].nodeValue;
			this.values[this.values.indexOf(this.dragSrcEl.getAttribute('data-value'))] = e.target.getAttribute('data-value');
			this.tags[indexTwo] = this.dragSrcEl.childNodes[0].nodeValue;
			this.values[indexTwo] = this.dragSrcEl.getAttribute('data-value');

			this.render();
		}

		e.target.style.opacity = null;
	}

	appendNewTag(text, value) {
		const self = this;
		const para = document.createElement('span');
		para.className = 'tag delete';
		para.setAttribute('tabindex', 0);
		para.draggable = true;
		para.addEventListener('keyup', (e) => {
			if (e.keyCode === 8 || e.keyCode === 46) {
				if (document.activeElement.previousSibling) {
					para.previousSibling.focus();
				}

				self.removeTag.bind(self)(e.target.querySelector('span.remove'));
			}
		});
		para.addEventListener('dragstart', this.handleDragStart.bind(this));
		para.addEventListener('dragenter', this.handleDragEnter.bind(this));
		para.addEventListener('dragleave', this.handleDragLeave.bind(this));
		para.addEventListener('dragover', this.handleDragOver.bind(this));
		para.addEventListener('dragend', this.handleDragEnd.bind(this));
		para.addEventListener('drop', this.handleDrop.bind(this));

		para.setAttribute('data-value', value);
		para.appendChild(document.createTextNode(text));

		const remove = document.createElement('span');
		remove.appendChild(document.createTextNode('✖'));
		remove.className = 'remove';
		remove.addEventListener('click', this.removeTag.bind(this));
		para.appendChild(remove);

		this.tagsContainer.appendChild(para);
	}
});