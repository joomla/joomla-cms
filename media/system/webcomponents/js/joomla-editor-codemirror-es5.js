(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _asyncToGenerator(fn) { return function () { var gen = fn.apply(this, arguments); return new Promise(function (resolve, reject) { function step(key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { return Promise.resolve(value).then(function (value) { step("next", value); }, function (err) { step("throw", err); }); } } return step("next"); }); }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

customElements.define('joomla-editor-codemirror', function (_HTMLElement) {
	_inherits(_class, _HTMLElement);

	_createClass(_class, [{
		key: 'attributeChangedCallback',
		value: function attributeChangedCallback(attr, oldValue, newValue) {
			switch (attr) {
				case 'options':
					if (oldValue && newValue !== oldValue) {
						this.refresh(this.element);
					}

					break;
			}
		}
	}, {
		key: 'options',
		get: function get() {
			return JSON.parse(this.getAttribute('options'));
		},
		set: function set(value) {
			this.setAttribute('options', value);
		}
	}], [{
		key: 'observedAttributes',
		get: function get() {
			return ['options'];
		}
	}]);

	function _class() {
		_classCallCheck(this, _class);

		var _this = _possibleConstructorReturn(this, (_class.__proto__ || Object.getPrototypeOf(_class)).call(this));

		_this.instance = '';
		_this.cm = '';
		_this.file = document.currentScript;
		_this.element = _this.querySelector('textarea');

		// Append the editor script
		if (!document.head.querySelector('#cm-editor')) {
			var cmPath = _this.getAttribute('editor');
			var script1 = document.createElement('script');

			script1.src = Joomla.getOptions('system.paths').rootFull + cmPath;
			script1.id = 'cm-editor';
			script1.setAttribute('async', false);
			document.head.insertBefore(script1, _this.file);
		}

		_this.toggleFullScreen = _this.toggleFullScreen.bind(_this);
		_this.closeFullScreen = _this.closeFullScreen.bind(_this);
		return _this;
	}

	_createClass(_class, [{
		key: 'connectedCallback',
		value: function connectedCallback() {
			var _this2 = this;

			var buttons = [].slice.call(this.querySelectorAll('.editor-codemirror-xtd-buttons .xtd-button'));
			this.checkElement('CodeMirror').then(function () {
				// Append the addons script
				if (!document.head.querySelector('#cm-addons')) {
					var addonsPath = _this2.getAttribute('addons');
					var script2 = document.createElement('script');

					script2.src = Joomla.getOptions('system.paths').rootFull + addonsPath;
					script2.id = 'cm-addons';
					script2.setAttribute('async', false);
					document.head.insertBefore(script2, _this2.file);
				}

				_this2.checkElement('CodeMirror', 'findModeByName').then(function () {
					window.CodeMirror.keyMap.default["Ctrl-Q"] = _this2.toggleFullScreen;
					window.CodeMirror.keyMap.default[_this2.getAttribute('fs-combo')] = _this2.toggleFullScreen;
					window.CodeMirror.keyMap.default["Esc"] = _this2.closeFullScreen;

					// For mode autoloading.
					window.CodeMirror.modeURL = _this2.getAttribute('mod-path');

					window.CodeMirror.defineOption("buttons", [], function (cm) {
						var panelNode = document.createElement('div');
						panelNode.className = 'CodeMirror-buttonsPanel';
						buttons.forEach(function (el) {
							panelNode.appendChild(el);
						});

						cm.addPanel(panelNode);
					});

					// Fire this function any time an editor is created.
					window.CodeMirror.defineInitHook(function (editor) {
						// Try to set up the mode
						var mode = window.CodeMirror.findModeByName(editor.options.mode || '');

						if (mode) {
							window.CodeMirror.autoLoadMode(editor, mode.mode);
							editor.setOption('mode', mode.mime);
						} else {
							window.CodeMirror.autoLoadMode(editor, editor.options.mode);
						}

						// Handle gutter clicks (place or remove a marker).
						editor.on("gutterClick", function (ed, n, gutter) {
							if (gutter !== "CodeMirror-markergutter") {
								return;
							}

							var info = ed.lineInfo(n);
							var hasMarker = !!info.gutterMarkers && !!info.gutterMarkers["CodeMirror-markergutter"];
							ed.setGutterMarker(n, "CodeMirror-markergutter", hasMarker ? null : this.makeMarker());
						});

						// Some browsers do something weird with the fieldset which doesn't work well with CodeMirror. Fix it.
						if (_this2.parentNode.tagName === 'fieldset') {
							_this2.parentNode.style.minWidth = 0;
						}
					});

					/** Register Editor */
					_this2.instance = window.CodeMirror.fromTextArea(_this2.element, _this2.options);
					Joomla.editors.instances[_this2.element.id] = _this2.instance;
				});
			});
		}
	}, {
		key: 'disconnectedCallback',
		value: function disconnectedCallback() {
			// Remove from the Joomla API
			delete Joomla.editors.instances[this.element.id];
		}
	}, {
		key: 'refresh',
		value: function refresh(element) {
			this.instance = window.CodeMirror.fromTextArea(element, this.options);
		}
	}, {
		key: 'rafAsync',
		value: function rafAsync() {
			return new Promise(function (resolve) {
				requestAnimationFrame(resolve);
			});
		}
	}, {
		key: 'checkElement',
		value: function () {
			var _ref = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee(string1, string2) {
				return regeneratorRuntime.wrap(function _callee$(_context) {
					while (1) {
						switch (_context.prev = _context.next) {
							case 0:
								if (!string2) {
									_context.next = 8;
									break;
								}

							case 1:
								if (!(typeof window[string1][string2] !== 'function')) {
									_context.next = 6;
									break;
								}

								_context.next = 4;
								return this.rafAsync();

							case 4:
								_context.next = 1;
								break;

							case 6:
								_context.next = 13;
								break;

							case 8:
								if (!(typeof window[string1] !== 'function')) {
									_context.next = 13;
									break;
								}

								_context.next = 11;
								return this.rafAsync();

							case 11:
								_context.next = 8;
								break;

							case 13:
								return _context.abrupt('return', true);

							case 14:
							case 'end':
								return _context.stop();
						}
					}
				}, _callee, this);
			}));

			function checkElement(_x, _x2) {
				return _ref.apply(this, arguments);
			}

			return checkElement;
		}()
	}, {
		key: 'toggleFullScreen',
		value: function toggleFullScreen() {
			this.instance.setOption("fullScreen", !this.instance.getOption("fullScreen"));
		}
	}, {
		key: 'closeFullScreen',
		value: function closeFullScreen() {
			this.instance.getOption("fullScreen") && this.instance.setOption("fullScreen", false);
		}
	}, {
		key: 'makeMarker',
		value: function makeMarker() {
			var marker = document.createElement("div");
			marker.className = "CodeMirror-markergutter-mark";
			return marker;
		}
	}]);

	return _class;
}(HTMLElement));

},{}]},{},[1]);
