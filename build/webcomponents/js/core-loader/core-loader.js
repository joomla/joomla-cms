;customElements.define('joomla-core-loader', class extends HTMLElement {
	constructor() {
		super();

		// Define some things
		this.css = `joomla-core-loader {
  display: block;
  top: 0;
  left: 0;
  position: fixed;
  width: 100%;
  height: 100%;
  opacity: .8;
  overflow: hidden;
  z-index: 10000;
  background-color: #fff;
}
joomla-core-loader .jbox {
  width: 300px;
  height: 300px;
  margin: 0 auto;
  transform: rotate(45deg);
}
joomla-core-loader .j1, joomla-core-loader .j2, joomla-core-loader .j3, joomla-core-loader .j4 {
  content: "";
  position: absolute;
  width: 90px;
  height: 90px;
  border-radius: 90px;
}
joomla-core-loader .j1::before, joomla-core-loader .j2::before, joomla-core-loader .j3::before, joomla-core-loader .j4::before {
  box-sizing: content-box;
  -webkit-box-sizing: content-box;
  content: "";
  position: absolute;
  width: 50px;
  height: 35px;
  margin: 66px 0 0 -30px;
  background: transparent;
  border-radius: 75px 75px 0 0;
}
joomla-core-loader .j1::after, joomla-core-loader .j2::after, joomla-core-loader .j3::after, joomla-core-loader .j4::after {
  box-sizing: content-box;
  -webkit-box-sizing: content-box;
  content: "";
  position: absolute;
  width: 50px;
  height: 101px;
  margin: 150px 0 0 -30px;
  background: transparent;
}
joomla-core-loader .j1 {
  margin: 0 0 0 182px;
  background: orange;
  transform: rotate(0deg);
}
joomla-core-loader .j1::before, joomla-core-loader .j1::after {
  border: 50px solid orange;
}
joomla-core-loader .j1::before {
  border-width: 50px 50px 0 50px;
}
joomla-core-loader .j1::after {
  border-width: 0 0 0 50px;
}
joomla-core-loader .j2 {
  margin: 182px 0 0 364px;
  background: red;
  transform: rotate(90deg);
}
joomla-core-loader .j2::before, joomla-core-loader .j2::after {
  border: 50px solid red;
}
joomla-core-loader .j2::before {
  border-width: 50px 50px 0 50px;
}
joomla-core-loader .j2::after {
  border-width: 0 0 0 50px;
}
joomla-core-loader .j3 {
  margin: 364px 0 0 182px;
  background: blue;
  transform: rotate(180deg);
}
joomla-core-loader .j3::before, joomla-core-loader .j3::after {
  border: 50px solid blue;
}
joomla-core-loader .j3::before {
  border-width: 50px 50px 0 50px;
}
joomla-core-loader .j3::after {
  border-width: 0 0 0 50px;
}
joomla-core-loader .j4 {
  margin: 182px 0 0 0;
  background: green;
  transform: rotate(270deg);
}
joomla-core-loader .j4::before, joomla-core-loader .j4::after {
  border: 50px solid green;
}
joomla-core-loader .j4::before {
  border-width: 50px 50px 0 50px;
}
joomla-core-loader .j4::after {
  border-width: 0 0 0 50px;
}
joomla-core-loader .jbox p {
  float: left;
  margin: -20px 0 0 252px;
  font: normal 1.25em/1em Helvetica, Arial, sans-serif;
  color: #999;
  transform: rotate(-45deg);
}
joomla-core-loader .jbox > span {
  animation: jspinner 1s infinite;
}
joomla-core-loader .jbox .j2 {
  animation-delay: -1.8s;
}
joomla-core-loader .jbox .j3 {
  animation-delay: -1.6s;
}
joomla-core-loader .jbox .j4 {
  animation-delay: -1.4s;
}

@keyframes jspinner {
  0%, 40%, 100% {
    opacity: 0.1;
  }
  50% {
    opacity: 1;
  }
}`;
		this.styleEl = document.createElement('style');
		this.styleEl.id = 'joomla-loader-css';
		this.styleEl.innerHTML = this.css;

		this.element = document.createElement('div');
		this.element.id = 'joomla-loader';
		this.element.innerHTML = `<div class="jbox"><span class="j1"></span><span class="j2"></span><span class="j3"></span><span class="j4"></span><p>&reg;</p></div>`;

		if (!document.head.querySelector('#joomla-loader-css')) {
			document.head.appendChild(this.styleEl)
		}
	}

	connectedCallback() {
		this.appendChild(this.element);
	}
});