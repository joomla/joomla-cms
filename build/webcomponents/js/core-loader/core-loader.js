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
joomla-core-loader .box {
  width: 300px;
  height: 300px;
  margin: 0 auto;
  transform: rotate(45deg);
}
joomla-core-loader .box p {
  float: left;
  margin: -20px 0 0 252px;
  font: normal 1.25em/1em Helvetica, Arial, sans-serif;
  color: #999;
  transform: rotate(-45deg);
}
joomla-core-loader .box > span {
  animation: jspinner 2s infinite ease-in-out;
}
joomla-core-loader .box .red {
  animation-delay: -1.5s;
}
joomla-core-loader .box .blue {
  animation-delay: -1s;
}
joomla-core-loader .box .green {
  animation-delay: -.5s;
}
joomla-core-loader .yellow {
  content: "";
  position: absolute;
  width: 90px;
  height: 90px;
  border-radius: 90px;
  background: #F9A541;
}
joomla-core-loader .yellow::before, joomla-core-loader .yellow::after {
  box-sizing: content-box;
  -webkit-box-sizing: content-box;
  border: 50px solid #F9A541;
}
joomla-core-loader .yellow::before {
  content: "";
  position: absolute;
  width: 50px;
  height: 35px;
  margin: 60px 0 0 -30px;
  background: transparent;
  border-radius: 75px 75px 0 0;
  border-width: 50px 50px 0 50px;
}
joomla-core-loader .yellow::after {
  content: "";
  position: absolute;
  width: 50px;
  height: 101px;
  margin: 145px 0 0 -30px;
  background: transparent;
  border-width: 0 0 0 50px;
}
joomla-core-loader .red {
  content: "";
  position: absolute;
  width: 90px;
  height: 90px;
  border-radius: 90px;
  background: #F44321;
}
joomla-core-loader .red::before, joomla-core-loader .red::after {
  box-sizing: content-box;
  -webkit-box-sizing: content-box;
  border: 50px solid #F44321;
}
joomla-core-loader .red::before {
  content: "";
  position: absolute;
  width: 50px;
  height: 35px;
  margin: 60px 0 0 -30px;
  background: transparent;
  border-radius: 75px 75px 0 0;
  border-width: 50px 50px 0 50px;
}
joomla-core-loader .red::after {
  content: "";
  position: absolute;
  width: 50px;
  height: 101px;
  margin: 145px 0 0 -30px;
  background: transparent;
  border-width: 0 0 0 50px;
}
joomla-core-loader .blue {
  content: "";
  position: absolute;
  width: 90px;
  height: 90px;
  border-radius: 90px;
  background: #5091CD;
}
joomla-core-loader .blue::before, joomla-core-loader .blue::after {
  box-sizing: content-box;
  -webkit-box-sizing: content-box;
  border: 50px solid #5091CD;
}
joomla-core-loader .blue::before {
  content: "";
  position: absolute;
  width: 50px;
  height: 35px;
  margin: 60px 0 0 -30px;
  background: transparent;
  border-radius: 75px 75px 0 0;
  border-width: 50px 50px 0 50px;
}
joomla-core-loader .blue::after {
  content: "";
  position: absolute;
  width: 50px;
  height: 101px;
  margin: 145px 0 0 -30px;
  background: transparent;
  border-width: 0 0 0 50px;
}
joomla-core-loader .green {
  content: "";
  position: absolute;
  width: 90px;
  height: 90px;
  border-radius: 90px;
  background: #7AC143;
}
joomla-core-loader .green::before, joomla-core-loader .green::after {
  box-sizing: content-box;
  -webkit-box-sizing: content-box;
  border: 50px solid #7AC143;
}
joomla-core-loader .green::before {
  content: "";
  position: absolute;
  width: 50px;
  height: 35px;
  margin: 60px 0 0 -30px;
  background: transparent;
  border-radius: 75px 75px 0 0;
  border-width: 50px 50px 0 50px;
}
joomla-core-loader .green::after {
  content: "";
  position: absolute;
  width: 50px;
  height: 101px;
  margin: 145px 0 0 -30px;
  background: transparent;
  border-width: 0 0 0 50px;
}
joomla-core-loader .yellow {
  margin: 0 0 0 182px;
  transform: rotate(0deg);
}
joomla-core-loader .red {
  margin: 182px 0 0 364px;
  transform: rotate(90deg);
}
joomla-core-loader .blue {
  margin: 364px 0 0 182px;
  transform: rotate(180deg);
}
joomla-core-loader .green {
  margin: 182px 0 0 0;
  transform: rotate(270deg);
}

@keyframes jspinner {
  0%, 40%, 100% {
    opacity: 0.3;
  }
  20% {
    opacity: 1;
  }
}`;
		this.styleEl = document.createElement('style');
		this.styleEl.id = 'joomla-loader-css';
		this.styleEl.innerHTML = this.css;

		this.element = document.createElement('div');
		this.element.id = 'joomla-loader';
		this.element.innerHTML = `<div class="box"><span class="yellow"></span><span class="red"></span><span class="blue"></span><span class="green"></span><p>&reg;</p></div>`;

		if (!document.head.querySelector('#joomla-loader-css')) {
			document.head.appendChild(this.styleEl)
		}
	}

	connectedCallback() {
		this.appendChild(this.element);
	}
});