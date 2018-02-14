(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

;customElements.define('joomla-core-loader', function (_HTMLElement) {
	_inherits(_class, _HTMLElement);

	function _class() {
		_classCallCheck(this, _class);

		var _this = _possibleConstructorReturn(this, (_class.__proto__ || Object.getPrototypeOf(_class)).call(this));

		var template = document.createElement('template');
		template.innerHTML = '<style>{{CSS_CONTENTS_AUTOMATICALLY_INSERTED_HERE}}</style>\n<div class="box"><span class="yellow"></span><span class="red"></span><span class="blue"></span><span class="green"></span><p>&reg;</p></div>';

		// Patch shadow DOM
		if (ShadyCSS) {
			ShadyCSS.prepareTemplate(template, 'joomla-core-loader');
		}

		_this.attachShadow({ mode: 'open' });
		_this.shadowRoot.appendChild(template.content.cloneNode(true));

		// Patch shadow DOM
		if (ShadyCSS) {
			ShadyCSS.styleElement(_this);
		}
		return _this;
	}

	return _class;
}(HTMLElement));

},{}]},{},[1]);
