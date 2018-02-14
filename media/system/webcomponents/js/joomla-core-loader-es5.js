(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

;customElements.define('joomla-core-loader', function (_HTMLElement) {
  _inherits(_class, _HTMLElement);

  function _class() {
    _classCallCheck(this, _class);

    // Define some things
    var _this = _possibleConstructorReturn(this, (_class.__proto__ || Object.getPrototypeOf(_class)).call(this));

    _this.css = 'joomla-core-loader {\n  display: block;\n  top: 0;\n  left: 0;\n  position: fixed;\n  width: 100%;\n  height: 100%;\n  opacity: .8;\n  overflow: hidden;\n  z-index: 10000;\n  background-color: #fff;\n}\njoomla-core-loader .jbox {\n  width: 300px;\n  height: 300px;\n  margin: 0 auto;\n  transform: rotate(45deg);\n}\njoomla-core-loader .j1, joomla-core-loader .j2, joomla-core-loader .j3, joomla-core-loader .j4 {\n  content: "";\n  position: absolute;\n  width: 90px;\n  height: 90px;\n  border-radius: 90px;\n}\njoomla-core-loader .j1::before, joomla-core-loader .j2::before, joomla-core-loader .j3::before, joomla-core-loader .j4::before {\n  box-sizing: content-box;\n  -webkit-box-sizing: content-box;\n  content: "";\n  position: absolute;\n  width: 50px;\n  height: 35px;\n  margin: 66px 0 0 -30px;\n  background: transparent;\n  border-radius: 75px 75px 0 0;\n}\njoomla-core-loader .j1::after, joomla-core-loader .j2::after, joomla-core-loader .j3::after, joomla-core-loader .j4::after {\n  box-sizing: content-box;\n  -webkit-box-sizing: content-box;\n  content: "";\n  position: absolute;\n  width: 50px;\n  height: 101px;\n  margin: 150px 0 0 -30px;\n  background: transparent;\n}\njoomla-core-loader .j1 {\n  margin: 0 0 0 182px;\n  background: orange;\n  transform: rotate(0deg);\n}\njoomla-core-loader .j1::before, joomla-core-loader .j1::after {\n  border: 50px solid orange;\n}\njoomla-core-loader .j1::before {\n  border-width: 50px 50px 0 50px;\n}\njoomla-core-loader .j1::after {\n  border-width: 0 0 0 50px;\n}\njoomla-core-loader .j2 {\n  margin: 182px 0 0 364px;\n  background: red;\n  transform: rotate(90deg);\n}\njoomla-core-loader .j2::before, joomla-core-loader .j2::after {\n  border: 50px solid red;\n}\njoomla-core-loader .j2::before {\n  border-width: 50px 50px 0 50px;\n}\njoomla-core-loader .j2::after {\n  border-width: 0 0 0 50px;\n}\njoomla-core-loader .j3 {\n  margin: 364px 0 0 182px;\n  background: blue;\n  transform: rotate(180deg);\n}\njoomla-core-loader .j3::before, joomla-core-loader .j3::after {\n  border: 50px solid blue;\n}\njoomla-core-loader .j3::before {\n  border-width: 50px 50px 0 50px;\n}\njoomla-core-loader .j3::after {\n  border-width: 0 0 0 50px;\n}\njoomla-core-loader .j4 {\n  margin: 182px 0 0 0;\n  background: green;\n  transform: rotate(270deg);\n}\njoomla-core-loader .j4::before, joomla-core-loader .j4::after {\n  border: 50px solid green;\n}\njoomla-core-loader .j4::before {\n  border-width: 50px 50px 0 50px;\n}\njoomla-core-loader .j4::after {\n  border-width: 0 0 0 50px;\n}\njoomla-core-loader .jbox p {\n  float: left;\n  margin: -20px 0 0 252px;\n  font: normal 1.25em/1em Helvetica, Arial, sans-serif;\n  color: #999;\n  transform: rotate(-45deg);\n}\njoomla-core-loader .jbox > span {\n  animation: jspinner 1s infinite;\n}\njoomla-core-loader .jbox .j2 {\n  animation-delay: -1.8s;\n}\njoomla-core-loader .jbox .j3 {\n  animation-delay: -1.6s;\n}\njoomla-core-loader .jbox .j4 {\n  animation-delay: -1.4s;\n}\n\n@keyframes jspinner {\n  0%, 40%, 100% {\n    opacity: 0.1;\n  }\n  50% {\n    opacity: 1;\n  }\n}';
    _this.styleEl = document.createElement('style');
    _this.styleEl.id = 'joomla-loader-css';
    _this.styleEl.innerHTML = _this.css;

    _this.element = document.createElement('div');
    _this.element.id = 'joomla-loader';
    _this.element.innerHTML = '<div class="jbox"><span class="j1"></span><span class="j2"></span><span class="j3"></span><span class="j4"></span><p>&reg;</p></div>';

    if (!document.head.querySelector('#joomla-loader-css')) {
      document.head.appendChild(_this.styleEl);
    }
    return _this;
  }

  _createClass(_class, [{
    key: 'connectedCallback',
    value: function connectedCallback() {
      this.appendChild(this.element);
    }
  }]);

  return _class;
}(HTMLElement));

},{}]},{},[1]);
