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

    _this.css = 'joomla-core-loader {\n  display: block;\n  top: 0;\n  left: 0;\n  position: fixed;\n  width: 100%;\n  height: 100%;\n  opacity: .8;\n  overflow: hidden;\n  z-index: 10000;\n  background-color: #fff;\n}\njoomla-core-loader .box {\n  width: 300px;\n  height: 300px;\n  margin: 0 auto;\n  transform: rotate(45deg);\n}\njoomla-core-loader .box p {\n  float: left;\n  margin: -20px 0 0 252px;\n  font: normal 1.25em/1em Helvetica, Arial, sans-serif;\n  color: #999;\n  transform: rotate(-45deg);\n}\njoomla-core-loader .box > span {\n  animation: jspinner 2s infinite ease-in-out;\n}\njoomla-core-loader .box .red {\n  animation-delay: -1.5s;\n}\njoomla-core-loader .box .blue {\n  animation-delay: -1s;\n}\njoomla-core-loader .box .green {\n  animation-delay: -.5s;\n}\njoomla-core-loader .yellow {\n  content: "";\n  position: absolute;\n  width: 90px;\n  height: 90px;\n  border-radius: 90px;\n  background: #F9A541;\n}\njoomla-core-loader .yellow::before, joomla-core-loader .yellow::after {\n  box-sizing: content-box;\n  -webkit-box-sizing: content-box;\n  border: 50px solid #F9A541;\n}\njoomla-core-loader .yellow::before {\n  content: "";\n  position: absolute;\n  width: 50px;\n  height: 35px;\n  margin: 60px 0 0 -30px;\n  background: transparent;\n  border-radius: 75px 75px 0 0;\n  border-width: 50px 50px 0 50px;\n}\njoomla-core-loader .yellow::after {\n  content: "";\n  position: absolute;\n  width: 50px;\n  height: 101px;\n  margin: 145px 0 0 -30px;\n  background: transparent;\n  border-width: 0 0 0 50px;\n}\njoomla-core-loader .red {\n  content: "";\n  position: absolute;\n  width: 90px;\n  height: 90px;\n  border-radius: 90px;\n  background: #F44321;\n}\njoomla-core-loader .red::before, joomla-core-loader .red::after {\n  box-sizing: content-box;\n  -webkit-box-sizing: content-box;\n  border: 50px solid #F44321;\n}\njoomla-core-loader .red::before {\n  content: "";\n  position: absolute;\n  width: 50px;\n  height: 35px;\n  margin: 60px 0 0 -30px;\n  background: transparent;\n  border-radius: 75px 75px 0 0;\n  border-width: 50px 50px 0 50px;\n}\njoomla-core-loader .red::after {\n  content: "";\n  position: absolute;\n  width: 50px;\n  height: 101px;\n  margin: 145px 0 0 -30px;\n  background: transparent;\n  border-width: 0 0 0 50px;\n}\njoomla-core-loader .blue {\n  content: "";\n  position: absolute;\n  width: 90px;\n  height: 90px;\n  border-radius: 90px;\n  background: #5091CD;\n}\njoomla-core-loader .blue::before, joomla-core-loader .blue::after {\n  box-sizing: content-box;\n  -webkit-box-sizing: content-box;\n  border: 50px solid #5091CD;\n}\njoomla-core-loader .blue::before {\n  content: "";\n  position: absolute;\n  width: 50px;\n  height: 35px;\n  margin: 60px 0 0 -30px;\n  background: transparent;\n  border-radius: 75px 75px 0 0;\n  border-width: 50px 50px 0 50px;\n}\njoomla-core-loader .blue::after {\n  content: "";\n  position: absolute;\n  width: 50px;\n  height: 101px;\n  margin: 145px 0 0 -30px;\n  background: transparent;\n  border-width: 0 0 0 50px;\n}\njoomla-core-loader .green {\n  content: "";\n  position: absolute;\n  width: 90px;\n  height: 90px;\n  border-radius: 90px;\n  background: #7AC143;\n}\njoomla-core-loader .green::before, joomla-core-loader .green::after {\n  box-sizing: content-box;\n  -webkit-box-sizing: content-box;\n  border: 50px solid #7AC143;\n}\njoomla-core-loader .green::before {\n  content: "";\n  position: absolute;\n  width: 50px;\n  height: 35px;\n  margin: 60px 0 0 -30px;\n  background: transparent;\n  border-radius: 75px 75px 0 0;\n  border-width: 50px 50px 0 50px;\n}\njoomla-core-loader .green::after {\n  content: "";\n  position: absolute;\n  width: 50px;\n  height: 101px;\n  margin: 145px 0 0 -30px;\n  background: transparent;\n  border-width: 0 0 0 50px;\n}\njoomla-core-loader .yellow {\n  margin: 0 0 0 182px;\n  transform: rotate(0deg);\n}\njoomla-core-loader .red {\n  margin: 182px 0 0 364px;\n  transform: rotate(90deg);\n}\njoomla-core-loader .blue {\n  margin: 364px 0 0 182px;\n  transform: rotate(180deg);\n}\njoomla-core-loader .green {\n  margin: 182px 0 0 0;\n  transform: rotate(270deg);\n}\n\n@keyframes jspinner {\n  0%, 40%, 100% {\n    opacity: 0.3;\n  }\n  20% {\n    opacity: 1;\n  }\n}\n@media (prefers-reduced-motion: reduce) {\n  joomla-core-loader {\n    .box {\n      > span {\n        animation: none;\n      }\n    }\n  }\n}';
    _this.styleEl = document.createElement('style');
    _this.styleEl.id = 'joomla-loader-css';
    _this.styleEl.innerHTML = _this.css;

    _this.element = document.createElement('div');
    _this.element.id = 'joomla-loader';
    _this.element.innerHTML = '<div class="box"><span class="yellow"></span><span class="red"></span><span class="blue"></span><span class="green"></span><p>&reg;</p></div>';

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
