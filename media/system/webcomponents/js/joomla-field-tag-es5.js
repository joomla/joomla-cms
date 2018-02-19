(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

;customElements.define('joomla-field-tags', function (_HTMLElement) {
	_inherits(_class, _HTMLElement);

	function _class() {
		_classCallCheck(this, _class);

		// Define some things
		var _this = _possibleConstructorReturn(this, (_class.__proto__ || Object.getPrototypeOf(_class)).call(this));

		_this.actualInput = '';
		_this.activeInput = '';
		_this.tagsContainer = '';
		_this.tags = [];
		_this.values = [];
		_this.dragSrcEl = null;
		_this.prefixValue = '#new#';
		return _this;
	}

	_createClass(_class, [{
		key: 'connectedCallback',
		value: function connectedCallback() {
			var self = this;

			this.actualInput = this.querySelector('input');

			if (!this.actualInput) {
				throw new Error('`joomla-field-tags` UI component is missing the input element');
			}

			// Bind functions
			this.insert.bind(this);
			this.appendNewTag.bind(this);
			var initialValues = JSON.parse(this.actualInput.value === '' ? {} : this.actualInput.value);

			for (var p in initialValues) {
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

			this.activeInput.addEventListener('input', function (e) {
				e.preventDefault();
				e.stopPropagation();
				self.insert(false, e.target, false);
			});

			// Enter
			this.activeInput.addEventListener('keydown', function (e) {
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
					var foo = self.activeInput.previousElementSibling.querySelector('span');
					console.log(self.activeInput.previousElementSibling);
					foo.click();
					self.activeInput.focus();
				}
			});

			this.render();

			this.activeInput.value = '';
		}
	}, {
		key: 'render',
		value: function render() {
			// const childs = [].slice.call(this.tagsContainer.childNodes).filter(e => {e.tagName === 'span'});
			var childs = [].slice.call(this.tagsContainer.querySelectorAll('span.tag'));
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
			var final = {};
			for (var i = 0; i < this.tags.length; i++) {
				final[this.tags[i]] = this.values[i];
			}

			var tmpJson = JSON.stringify(final);
			tmpJson = tmpJson.replace(/\"/g, '&quot;');

			this.actualInput.setAttribute('value', tmpJson);

			// Move the input to the end
			this.tagsContainer.insertAdjacentElement('beforeend', this.activeInput);
		}
	}, {
		key: 'insert',
		value: function insert(ignoreComma, context, userInput) {
			var _this2 = this;

			if (context.value.indexOf(',') != -1 || ignoreComma) {
				if (context.value.substring(context.value.length - 1) === ',') {
					context.value = context.value.substring(0, context.value.length - 1);
				}

				var newTags = context.value.split(',');
				//   debugger;
				newTags.forEach(function (tag) {
					if (_this2.tags.indexOf(tag) === -1) {
						_this2.tags.push(tag);
						_this2.values.push(_this2.prefixValue + tag);
					}
				});

				context.value = '';

				this.render();
			}
		}
	}, {
		key: 'removeTag',
		value: function removeTag(e) {
			if (this.tags.length) {
				var parentEl = '';
				if (e && (typeof e === 'undefined' ? 'undefined' : _typeof(e)) === 'object' && e.target) {
					parentEl = e.target.parentNode;
				} else {
					parentEl = e.parentNode;
				}

				this.tags.splice(this.tags.indexOf(parentEl.childNodes[0].nodeValue), 1);
				this.values.splice(this.values.indexOf(parentEl.getAttribute('data-value')), 1);

				this.render();
			}
		}
	}, {
		key: 'handleDragStart',
		value: function handleDragStart(e) {
			e.target.style.opacity = '0.9';
			e.target.classList.remove('delete');
			this.dragSrcEl = e.target;
			e.dataTransfer.effectAllowed = 'move';
		}
	}, {
		key: 'handleDragEnter',
		value: function handleDragEnter(e) {
			e.target.style.opacity = '0.6';
			e.target.classList.add('over');
		}
	}, {
		key: 'handleDragLeave',
		value: function handleDragLeave(e) {
			e.srcElement.style.opacity = null;
			e.srcElement.classList.remove('over');
		}
	}, {
		key: 'handleDragEnd',
		value: function handleDragEnd(e) {
			this.dragSrcEl.style.opacity = null;
			e.target.style.opacity = null;
			e.target.classList.add('delete');
			this.dragSrcEl.classList.add('delete');
		}
	}, {
		key: 'handleDragOver',
		value: function handleDragOver(e) {
			if (e.preventDefault) {
				e.preventDefault();
			}

			e.dataTransfer.dropEffect = 'move';
		}
	}, {
		key: 'handleDrop',
		value: function handleDrop(e) {
			if (e.stopPropagation) {
				e.stopPropagation();
			}
			e.target.classList.remove('over');

			if (this.dragSrcEl != e.target) {
				var indexTwo = this.tags.indexOf(e.target.childNodes[0].nodeValue);
				this.tags[this.tags.indexOf(this.dragSrcEl.childNodes[0].nodeValue)] = e.target.childNodes[0].nodeValue;
				this.values[this.values.indexOf(this.dragSrcEl.getAttribute('data-value'))] = e.target.getAttribute('data-value');
				this.tags[indexTwo] = this.dragSrcEl.childNodes[0].nodeValue;
				this.values[indexTwo] = this.dragSrcEl.getAttribute('data-value');

				this.render();
			}

			e.target.style.opacity = null;
		}
	}, {
		key: 'appendNewTag',
		value: function appendNewTag(text, value) {
			var self = this;
			var para = document.createElement('span');
			para.className = 'tag delete';
			para.setAttribute('tabindex', 0);
			para.draggable = true;
			para.addEventListener('keyup', function (e) {
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

			var remove = document.createElement('span');
			remove.appendChild(document.createTextNode('✖'));
			remove.className = 'remove';
			remove.addEventListener('click', this.removeTag.bind(this));
			para.appendChild(remove);

			this.tagsContainer.appendChild(para);
		}
	}]);

	return _class;
}(HTMLElement));

},{}]},{},[1]);
