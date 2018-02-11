(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * @package         Joomla.JavaScript
 * @copyright       Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

customElements.define('joomla-field-module-order', function (_HTMLElement) {
	_inherits(_class, _HTMLElement);

	function _class() {
		_classCallCheck(this, _class);

		var _this = _possibleConstructorReturn(this, (_class.__proto__ || Object.getPrototypeOf(_class)).call(this));

		if (!window.Joomla) {
			throw new Error('Joomla API is not properly initialised');
		}

		_this.linkedField = _this.getAttribute('data-linked-field') || 'jform_position';
		_this.linkedFieldEl = '';
		_this.originalPos = '';

		_this.writeDynaList.bind(_this);
		_this.getNewOrder.bind(_this);
		return _this;
	}

	_createClass(_class, [{
		key: 'connectedCallback',
		value: function connectedCallback() {
			var _this2 = this;

			this.originalPos = this.linkedFieldEl.value;
			this.linkedFieldEl = document.getElementById(this.linkedField);

			/** Initialize the field on document ready **/
			this.getNewOrder(this.originalPos);

			this.linkedFieldEl.addEventListener('change', function () {
				_this2.originalPos = _this2.linkedFieldEl.value;
				_this2.getNewOrder(_this2.originalPos);
			});
		}
	}, {
		key: 'disconnectedCallback',
		value: function disconnectedCallback() {}
	}, {
		key: 'writeDynaList',
		value: function writeDynaList(selectParams, source, key, orig_val) {
			var node = '';
			var selectNode = document.createElement('select');

			selectNode.classList.add(selectParams.itemClass);
			selectNode.setAttribute('name', selectParams.name);
			selectNode.id = selectParams.id;

			this.innerHTML = '';
			this.appendChild(selectNode);

			var hasSelection = key,
			    i = 0,
			    selected,
			    x,
			    item;

			for (x in source) {
				if (!source.hasOwnProperty(x)) {
					continue;
				}

				item = source[x];

				node = document.createElement('option');
				node.value = item[1];

				node.innerHTML = item[2];

				if (hasSelection && orig_val == item[1] || !hasSelection && i === 0) {
					node.setAttribute('selected', 'selected');
				}

				selectNode.appendChild(node);
				selectNode.parentNode.innerHtml = '';
				selectNode.parentNode.appendChild(selectNode);

				i++;
			}
		}
	}, {
		key: 'getNewOrder',
		value: function getNewOrder(originalPos) {
			console.log('dfgd');
			var url = this.getAttribute('data-url'),
			    clientId = this.getAttribute('data-client-id'),
			    element = document.getElementById(this.getAttribute('data-element')),
			    originalOrder = this.getAttribute('data-ordering'),
			    name = this.getAttribute('data-name'),
			    attr = this.getAttribute('data-client-attr') ? this.getAttribute('data-client-attr') : 'custom-select',
			    id = this.getAttribute('id') + '_1',
			    orders = [],
			    that = this;

			Joomla.request({
				url: url,
				method: 'GET',
				data: 'client_id=' + clientId + '&position=' + originalPos,
				perform: true,
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				onSuccess: function onSuccess(response, xhr) {
					if (response) {
						response = JSON.parse(response);

						/** Check if everything is OK **/
						if (response.data.length > 0) {
							for (var i = 0; i < response.data.length; ++i) {
								orders[i] = response.data[i].split(',');
							}

							that.writeDynaList({
								name: name,
								id: id,
								itemClass: attr
							}, orders, that.originalPos, originalOrder);
						}
					}

					/** Render messages, if any. There are only message in case of errors. **/
					if (_typeof(response.messages) == 'object' && response.messages !== null) {
						Joomla.renderMessages(response.messages);
						window.scrollTo(0, 0);
					}
				}
			});
		}
	}]);

	return _class;
}(HTMLElement));

},{}]},{},[1]);
