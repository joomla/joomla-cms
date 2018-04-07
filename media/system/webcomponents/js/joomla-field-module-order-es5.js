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
;customElements.define('joomla-field-module-order', function (_HTMLElement) {
	_inherits(_class, _HTMLElement);

	function _class() {
		_classCallCheck(this, _class);

		var _this = _possibleConstructorReturn(this, (_class.__proto__ || Object.getPrototypeOf(_class)).call(this));

		_this.linkedFieldSelector = '';
		_this.linkedFieldElement = '';
		_this.originalPosition = '';

		_this.writeDynaList.bind(_this);
		_this.getNewOrder.bind(_this);
		return _this;
	}

	_createClass(_class, [{
		key: 'connectedCallback',
		value: function connectedCallback() {
			this.linkedFieldSelector = this.getAttribute('data-linked-field') || 'jform_position';

			if (!this.linkedFieldSelector) {
				throw new Error('No linked field defined!');
			}

			this.linkedFieldElement = document.getElementById(this.linkedFieldSelector);

			if (!this.linkedFieldElement) {
				throw new Error('No linked field defined!');
			}

			var that = this;
			this.originalPosition = this.linkedFieldElement.value;

			/** Initialize the field **/
			this.getNewOrder(this.originalPosition);

			/** Watch for changes on the linked field **/
			this.linkedFieldElement.addEventListener('change', function () {
				that.originalPosition = that.linkedFieldElement.value;
				that.getNewOrder(that.linkedFieldElement.value);
			});
		}
	}, {
		key: 'writeDynaList',
		value: function writeDynaList(selectProperties, source, originalPositionName, originalPositionValue) {
			var i = 0;
			var selectNode = document.createElement('select');
			if (this.hasOwnProperty('disabled')) {
				selectNode.setAttribute('disabled', '');
			}

			if (this.getAttribute('onchange')) {
				selectNode.setAttribute('onchange', this.getAttribute('onchange'));
			}

			if (this.getAttribute('size')) {
				selectNode.setAttribute('size', this.getAttribute('size'));
			}

			selectNode.classList.add(selectProperties.itemClass);
			selectNode.setAttribute('name', selectProperties.name);
			selectNode.id = selectProperties.id;

			for (var x in source) {
				if (!source.hasOwnProperty(x)) {
					continue;
				}

				var node = document.createElement('option');
				var item = source[x];

				node.value = item[1];
				node.innerHTML = item[2];

				if (originalPositionName && originalPositionValue === item[1] || !originalPositionName && i === 0) {
					node.setAttribute('selected', 'selected');
				}

				selectNode.appendChild(node);
				i++;
			}

			this.innerHTML = '';
			this.appendChild(selectNode);
		}
	}, {
		key: 'getNewOrder',
		value: function getNewOrder(originalPosition) {
			var url = this.getAttribute('data-url');
			var clientId = this.getAttribute('data-client-id');
			var originalOrder = this.getAttribute('data-ordering');
			var name = this.getAttribute('data-name');
			var attr = this.getAttribute('data-client-attr') ? this.getAttribute('data-client-attr') : 'custom-select';
			var id = this.getAttribute('id') + '_1';
			var orders = [];
			var that = this;

			Joomla.request({
				url: url + 'client_id=' + clientId + '&position=' + originalPosition,
				method: 'GET',
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
							}, orders, that.originalPosition, originalOrder);
						}
					}

					/** Render messages, if any. There are only message in case of errors. **/
					if (_typeof(response.messages) == 'object' && response.messages !== null) {
						Joomla.renderMessages(response.messages);
					}
				}
			});
		}
	}]);

	return _class;
}(HTMLElement));

},{}]},{},[1]);
