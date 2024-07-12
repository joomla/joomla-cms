const style = `
:host {
  position: fixed;
  top: 0;
  left: 0;
  z-index: 10000;
  display: flex;
  align-items: center;
  width: 100%;
  height: 100%;
  overflow: hidden;
  opacity: 0.8;
}
.box {
  position: relative;
  width: 345px;
  height: 345px;
  margin: 0 auto;
}
.box p {
  float: right;
  margin: 95px 0 0;
  font: normal 1.25em/1em sans-serif;
  color: #999;
}
.box > span {
  animation: jspinner 2s infinite ease-in-out;
}
.box .red {
  animation-delay: -1.5s;
}
.box .blue {
  animation-delay: -1s;
}
.box .green {
  animation-delay: -0.5s;
}
.yellow {
  position: absolute;
  top: 0;
  left: 0;
  width: 90px;
  height: 90px;
  content: "";
  background: #f9a541;
  border-radius: 90px;
}
.yellow::before, .yellow::after {
  position: absolute;
  top: 0;
  left: 0;
  box-sizing: content-box;
  width: 50px;
  content: "";
  background: transparent;
  border: 50px solid #f9a541;
}
.yellow::before {
  height: 35px;
  margin: 60px 0 0 -30px;
  border-width: 50px 50px 0;
  border-radius: 75px 75px 0 0;
}
.yellow::after {
  height: 105px;
  margin: 140px 0 0 -30px;
  border-width: 0 0 0 50px;
}
.red {
  position: absolute;
  top: 0;
  left: 0;
  width: 90px;
  height: 90px;
  content: "";
  background: #f44321;
  border-radius: 90px;
}
.red::before, .red::after {
  position: absolute;
  top: 0;
  left: 0;
  box-sizing: content-box;
  width: 50px;
  content: "";
  background: transparent;
  border: 50px solid #f44321;
}
.red::before {
  height: 35px;
  margin: 60px 0 0 -30px;
  border-width: 50px 50px 0;
  border-radius: 75px 75px 0 0;
}
.red::after {
  height: 105px;
  margin: 140px 0 0 -30px;
  border-width: 0 0 0 50px;
}
.blue {
  position: absolute;
  top: 0;
  left: 0;
  width: 90px;
  height: 90px;
  content: "";
  background: #5091cd;
  border-radius: 90px;
}
.blue::before, .blue::after {
  position: absolute;
  top: 0;
  left: 0;
  box-sizing: content-box;
  width: 50px;
  content: "";
  background: transparent;
  border: 50px solid #5091cd;
}
.blue::before {
  height: 35px;
  margin: 60px 0 0 -30px;
  border-width: 50px 50px 0;
  border-radius: 75px 75px 0 0;
}
.blue::after {
  height: 105px;
  margin: 140px 0 0 -30px;
  border-width: 0 0 0 50px;
}
.green {
  position: absolute;
  top: 0;
  left: 0;
  width: 90px;
  height: 90px;
  content: "";
  background: #7ac143;
  border-radius: 90px;
}
.green::before, .green::after {
  position: absolute;
  top: 0;
  left: 0;
  box-sizing: content-box;
  width: 50px;
  content: "";
  background: transparent;
  border: 50px solid #7ac143;
}
.green::before {
  height: 35px;
  margin: 60px 0 0 -30px;
  border-width: 50px 50px 0;
  border-radius: 75px 75px 0 0;
}
.green::after {
  height: 105px;
  margin: 140px 0 0 -30px;
  border-width: 0 0 0 50px;
}
.yellow {
  margin: 0 0 0 255px;
  transform: rotate(45deg);
}
.red {
  margin: 255px 0 0 255px;
  transform: rotate(135deg);
}
.blue {
  margin: 255px 0 0;
  transform: rotate(225deg);
}
.green {
  transform: rotate(315deg);
}
@keyframes jspinner {
  0%, 40%, 100% {
    opacity: 0.3;
  }
  20% {
    opacity: 1;
  }
}
@media (prefers-reduced-motion: reduce) {
  .box > span {
    animation: none;
  }
}`;

/**
 * Creates a custom element with the default spinner of the Joomla logo
 */
class JoomlaCoreLoader extends HTMLElement {
  constructor() {
    super();

    const template = document.createElement('template');
    template.innerHTML = `<style>${style}</style>
<div>
  <span class="yellow"></span>
  <span class="red"></span>
  <span class="blue"></span>
  <span class="green"></span>
  <p>&trade;</p>
</div>`;

    // Patch the shadow DOM
    if (window.ShadyCSS) {
      window.ShadyCSS.prepareTemplate(template, 'joomla-core-loader');
    }

    this.attachShadow({ mode: 'open' });
    this.shadowRoot.appendChild(template.content.cloneNode(true));

    // Patch the shadow DOM
    if (window.ShadyCSS) {
      window.ShadyCSS.styleElement(this);
    }
  }

  connectedCallback() {
    this.style.backgroundColor = this.color;
    this.style.opacity = 0.8;
    this.shadowRoot.querySelector('div').classList.add('box');
  }

  static get observedAttributes() {
    return ['color'];
  }

  get color() { return this.getAttribute('color') || '#fff'; }

  set color(value) { this.setAttribute('color', value); }

  attributeChangedCallback(attr, oldValue, newValue) {
    switch (attr) {
      case 'color':
        if (newValue && newValue !== oldValue) {
          this.style.backgroundColor = this.color;
        }
        break;
      default:
      // Do nothing
    }
  }
}

if (!customElements.get('joomla-core-loader')) {
  customElements.define('joomla-core-loader', JoomlaCoreLoader);
}
