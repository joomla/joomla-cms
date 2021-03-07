/**
* BASED ON: https://codepen.io/dgrammatiko/pen/zLvXwR
* BASED ON: https://codepen.io/thednp/pen/yLVzZzW
*
* Example
* <joomla-field-color-picker>
*   <input type="hidden">
* </joomla-field-color-picker>
*/

'use strict';

import TinyColor from '@ctrl/tinycolor';

const nonColors = ['transparent', 'currentColor', 'inherit', 'initial'];

class ColorPicker extends HTMLElement {
  constructor() {
    super();

    // set essentials
    this.dragElement = null;
    this.isOpen = false;
    this.isDisconnected = false;
    this.isMobile = 'ontouchstart' in document && /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
    this.keyTimer = null;
    // control positions
    this.controlPositions = {
      c1x: 0, c1y: 0, c2y: 0, c3y: 0,
    };

    // bind events
    this.showPicker = this.showPicker.bind(this);
    this.showMenu = this.showMenu.bind(this);
    this.menuHandler = this.menuHandler.bind(this);
    this.pointerDown = this.pointerDown.bind(this);
    this.pointerMove = this.pointerMove.bind(this);
    this.pointerUp = this.pointerUp.bind(this);
    this.handleScroll = this.handleScroll.bind(this);
    this.handleResize = this.handleResize.bind(this);
    this.changeHandler = this.changeHandler.bind(this);
    this.keyHandler = this.keyHandler.bind(this);
    this.updateDropdownPosition = this.updateDropdownPosition.bind(this);
    this.updateDropdownWidth = this.updateDropdownWidth.bind(this);
  }

  connectedCallback() {
    this.doConnect();
  }

  disconnectedCallback() {
    this.isDisconnected = true;
    this.toggleEvents();
  }

  doConnect() {
    // get the main input
    this.input = this.querySelector('input[type="hidden"]');

    if (!this.input) {
      throw new Error('ColorPicker requires a child <input type="hidden"> form element');
    }

    // give the <color-picker> a unique id
    const pickerID = `color-picker-${Math.round(Math.random() * 999)}`;

    // The element was already initialised previously and perhaps was detached from DOM
    if (this.color) {
      if (this.isDisconnected) {
        // re-attach events
        this.toggleEvents(1);
        this.isDisconnected = false;
      }
      return;
    }

    // set new state
    this.isDisconnected = false;

    // get input value, format, direction and labels
    const colorValue = this.input.getAttribute('value') || 'rgb(0,0,0)';
    this.format = this.getAttribute('format');

    const placeholder = this.input.getAttribute('placeholder');
    const inputLabel = this.input.getAttribute('inputLabel');

    // init color
    this.color = new TinyColor((nonColors.includes(colorValue) ? '#fff' : colorValue), { format: this.format });

    // set initial controls dimensions
    // make the controls smaller on mobile
    const cv1w = this.isMobile ? 150 : 230;
    const cvh = this.isMobile ? 150 : 230;
    const cv2w = 21;
    const dropClass = this.isMobile ? ' mobile' : '';
    const alphaControlViz = this.format === 'hex' ? ' visually-hidden' : '';

    const controlsTemplate = `<div class="color-control">
  <canvas class="color-control1" height="${cvh}" width="${cv1w}"></canvas>
  <div class="color-pointer"></div>
</div>
<div class="color-control">
  <canvas class="color-control2" height="${cvh}" width="${cv2w}" ></canvas>
  <div class="color-slider"></div>
</div>
<div class="color-control${alphaControlViz}">
  <canvas class="color-control3" height="${cvh}" width="${cv2w}"></canvas>
  <div class="color-slider"></div>
</div>`;

    // set inputs template
    const inputLabels = this.input.getAttribute('inputLabels').split(',');

    const hexForm = `<label for="${pickerID}_hex" class="hex-label">HEX: <span class="visually-hidden">${inputLabels[0]}</span></label>
<input id="${pickerID}_hex" name="${pickerID}_hex" value="#000" placeholder="${placeholder}" class="color-input color-input-hex" type="text" autocomplete="off" spellcheck="false">`;

    const rgbForm = `<label for="${pickerID}_red">R: <span class="visually-hidden">${inputLabels[0]}</span></label>
<input id="${pickerID}_red" name="${pickerID}_red" value="0" class="color-input" type="number" placeholder="[0-255]" min="0" max="255" autocomplete="off" spellcheck="false">

<label for="${pickerID}_green">G: <span class="visually-hidden">${inputLabels[1]}</span></label>
<input id="${pickerID}_green" name="${pickerID}_green" value="0" class="color-input" type="number" placeholder="[0-255]" min="0" max="255" autocomplete="off" spellcheck="false">

<label for="${pickerID}_blue">B: <span class="visually-hidden">${inputLabels[2]}</span></label>
<input id="${pickerID}_blue" name="${pickerID}_blue" value="0" class="color-input" type="number" placeholder="[0-255]" min="0" max="255" autocomplete="off" spellcheck="false">

<label for="${pickerID}_alpha">A: <span class="visually-hidden">${inputLabels[3]}</span></label>
<input id="${pickerID}_alpha" name="${pickerID}_alpha" value="1" class="color-input" type="number" placeholder="[0-1]" min="0" max="1" step="0.01" autocomplete="off" spellcheck="false">`;

    const hslForm = `<label for="${pickerID}_hue">H: <span class="visually-hidden">${inputLabels[0]}</span></label>
<input id="${pickerID}_hue" name="${pickerID}_hue" value="0" class="color-input" type="number" placeholder="[0-360]" min="0" max="360" autocomplete="off" spellcheck="false">

<label for="${pickerID}_saturation">S: <span class="visually-hidden">${inputLabels[1]}></span></label>
<input id="${pickerID}_saturation" name="${pickerID}_saturation" value="0" class="color-input" type="number" placeholder="[0-100]" min="0" max="100" autocomplete="off" spellcheck="false">

<label for="${pickerID}_lightness">L: <span class="visually-hidden">${inputLabels[2]}</span></label>
<input id="${pickerID}_lightness" name="${pickerID}_lightness" value="0" class="color-input" type="number" placeholder="[0-100]" min="0" max="100" autocomplete="off" spellcheck="false">

<label for="${pickerID}_alpha">A: <span class="visually-hidden">${inputLabels[3]}</span></label>
<input id="${pickerID}_alpha" name="${pickerID}_alpha" value="1" class="color-input" type="number" placeholder="[0-1]" min="0" max="1" step="0.01" autocomplete="off" spellcheck="false">`;

    // set inputs template
    let inputsTemplate = hexForm;

    if (this.format === 'rgb') {
      inputsTemplate = rgbForm;
    } else if (this.format === 'hsl') {
      inputsTemplate = hslForm;
    }

    // set color key menu template
    this.keywords = false;
    const colorKeysOption = this.input.getAttribute('keywords');
    let menuTemplate = '';
    let menuToggle = '';

    if (colorKeysOption !== 'false') {
      const colorKeys = colorKeysOption ? colorKeysOption.split(',') : nonColors;
      this.keywords = colorKeys;
      let colorOpsMarkup = '';
      colorKeys.forEach((x) => { colorOpsMarkup += `<li value="${x.trim()}">${x}</li>`; });
      menuTemplate = `<ul class="color-menu">${colorOpsMarkup}</ul>`;
      menuToggle = `<button class="menu-toggle">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024">
    <rect fill="rgba(0,0,0,.3)" width="100%" height="100%" x="0" y="0"></rect>
    <path fill="#fff" d="M777.857 367.557c9.748 -9.605 25.41 -9.605 35.087 0s9.713 25.124 0 34.729L529.521 682.914c-9.677 9.605 -25.338 9.605 -35.087 0L211.011 402.286c-9.677 -9.605 -9.677 -25.123 0 -34.729c9.712 -9.605 25.41 -9.605 35.087 0l265.897 255.934L777.857 367.557z"></path>
  </svg>
</button>`;
    }

    // set the main template
    this.template = document.createElement('template');

    this.template.innerHTML = `<style>
:host {
  position: relative;
  display: flex
}
.color-dropdown {
  width: 100%;
  max-width: calc(100% - 1rem);
  background: rgba(0,0,0,0.75);
  color: rgba(255,255,255,0.8);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
  position: absolute;
  padding: 0.5rem;
  border-radius: 0.5rem;
  display: none;
  left:0;
  z-index: 50
}
.color-dropdown.show {
  top: calc(100% + 5px);
  display: block
}
.color-dropdown.show-top {
  bottom: calc(100% + 5px);
  display: block;
  top: auto
}
.color-controls {
  display: none;
  flex-wrap: wrap;
  justify-content: space-between;  
}
.color-preview {
  border: 0;
  outline: none;
  box-shadow: 0 0 1px 1px rgba(120,120,120,0.33) inset;
  line-height: 1.5;
  font-size: 1rem;
  border-radius: 0.25rem;
  appearance: none;
  width: 100%;
  height: 1.5rem;
  padding: 0.6rem 1rem;
  direction: ltr; /* color value can never be rtl */
}
.color-preview.dark {
  color: rgba(255,255,255,0.8);
}
.color-preview.dark::placeholder {
  color: rgba(255,255,255,0.6);
}
.color-preview.light {
  color: rgba(0,0,0,0.8);
}
.color-preview.light::placeholder {
  color: rgba(0,0,0,0.6);
}
.menu-toggle {
  position: absolute;
  height: 100%; width: 3rem;
  top: 0; right: 0;
  background: none;
  border: 0;
  outline: none;
  border-radius: 0 .25rem .25rem 0;
  background: rgba(0,0,0,0.2);
  cursor: pointer
}
.menu-toggle svg {
  width: auto;
  height: 100%
}
.color-menu {
  list-style: none;
  padding-inline: 0;
  margin: 0;
  flex-wrap: wrap;
  flex-flow: column;
  display: none;
  max-height: 160px;
  overflow-y: auto
}
.color-menu::-webkit-scrollbar {
  width: 10px;
}
.color-menu::-webkit-scrollbar-track {
  background-color: transparent;
}
.color-menu::-webkit-scrollbar-thumb {
  background-color: transparent;
  border-radius: 10px;
  border: 0;
  background-clip: content-box;
}
.color-menu:hover::-webkit-scrollbar-thumb {
  background-color: rgba(255,255,255,0.2);
}
.color-menu::-webkit-scrollbar-thumb:hover {
  background-color: #fff;
}
.color-dropdown.menu .color-menu,
.color-dropdown.picker .color-controls {
  display: flex
}
li {
  padding: 0.25rem 0.5rem;
  cursor: pointer
}
li:hover {
  background: #fff;
  color: #000
}
.color-form {
  font: 12px Arial;
  display: flex;
  flex-wrap: wrap;
  flex-direction: inherit;
  width: 100%;
  align-items: center;
  padding: 0.25rem 0
}
.color-form > * {
  flex: 1 0 0%;
  max-width: 17.5%;
  width: 17.5%
}
.color-form label {
  text-align: center;
  max-width: 7.5%;
  width: 7.5%
}
label.hex-label {
  max-width: 12.5%;
  width: 12.5%
}
input.color-input-hex {
  max-width: 87.5%;
  width: 87.5%
}
.color-input {
  background: transparent;
  border: 1px solid rgba(255,255,255,0.15);
  text-align: right;
  outline: none;
  color: inherit
}
.color-input:active,
.color-input:focus {
  background: rgba(0,0,0,0.25);
  border: 1px solid rgba(255,255,255,0.33);
}
.color-control1 {
  cursor: crosshair;
}
.color-control2,
.color-control3 {
  cursor: ns-resize;
}
.color-control {
  position:relative;
  display: inline-block
}
.color-control:focus canvas:active {
  cursor: none;
}
.color-pointer,
.color-slider {
  position:absolute;
  background: #000;
  border: 1px solid #fff;
  height: 5px;
  cursor: inherit;
  user-select: none;
  pointer-events: none
}
.color-pointer {
  width: 5px;
  border-radius: 5px
}
.color-slider {
  left: 0;
  width: calc(100% - 2px);
}
.visually-hidden { display: none }

:host([dir="rtl"]) .color-preview { text-align: right }
:host([dir="rtl"]) .menu-toggle {
  right: auto;
  left: 0
}
:host([dir="rtl"]) .menu-toggle {
  border-radius: .25rem 0 0 .25rem;
}
</style>

<label for="${pickerID}" class="visually-hidden">${inputLabel}</label>
<input id="${pickerID}" name="${pickerID}" value="${this.value}" format="${this.format}" placeholder="${placeholder}" type="text" class="color-preview" autocomplete="off" spellcheck="false" />
${menuToggle}
<div class="color-dropdown${dropClass}">
  <div class="color-controls">
    ${controlsTemplate}
    <div class="color-form">
      ${inputsTemplate}
    </div>
  </div>
  ${menuTemplate}
</div>`;

    // Patch shadow DOM
    if (window.ShadyCSS) {
      window.ShadyCSS.prepareTemplate(this.template, 'color-picker');
    }

    this.attachShadow({ mode: 'open' });
    this.shadowRoot.appendChild(this.template.content.cloneNode(true));

    // Patch shadow DOM
    if (window.ShadyCSS) {
      window.ShadyCSS.styleElement(this);
    }

    // set main elements
    this.preview = this.shadowRoot.querySelector('.color-preview');
    this.menuToggle = this.shadowRoot.querySelector('.menu-toggle');
    this.colorMenu = this.shadowRoot.querySelector('.color-menu');
    this.inputs = this.shadowRoot.querySelectorAll('.color-input');
    this.dropdown = this.shadowRoot.querySelector('.color-dropdown');
    this.control1 = this.shadowRoot.querySelector('.color-pointer');
    this.control2 = this.shadowRoot.querySelector('.color-control2 + .color-slider');
    this.controls = Array.from(this.shadowRoot.querySelectorAll('canvas'));
    // set dimensions
    this.width1 = this.controls[0].width;
    this.height1 = this.controls[0].height;
    this.width2 = this.controls[1].width;
    this.height2 = this.controls[1].height;
    // set main controls
    this.ctx1 = this.controls[0].getContext('2d');
    this.ctx2 = this.controls[1].getContext('2d');
    this.ctx1.rect(0, 0, this.width1, this.height1);
    this.ctx2.rect(0, 0, this.width2, this.height2);

    // set alpha control except hex
    if (this.format !== 'hex') {
      this.control3 = this.shadowRoot.querySelector('.color-control3 + .color-slider');
      this.width3 = this.controls[2].width;
      this.height3 = this.controls[2].height;
      this.ctx3 = this.controls[2].getContext('2d');
      this.ctx3.rect(0, 0, this.width3, this.height3);
    }

    // update color picker
    this.setControlPositions();
    this.updateInputs(1); // don't trigger change in this context
    this.updateControls();
    this.render();
    // attach main event
    this.toggleEvents(1);

    // solve non-colors after settings save
    if (this.keywords && nonColors.includes(colorValue)) {
      this.value = colorValue;
      this.input.value = colorValue;
      this.preview.value = colorValue;
    }
  }

  toggleEvents(action) {
    const fn = action ? 'addEventListener' : 'removeEventListener';
    this.preview[fn]('focusin', this.showPicker);

    if (this.menuToggle) {
      this.menuToggle[fn]('click', this.showMenu);
    }
  }

  toggleEventsOnShown(action) {
    const fn = action ? 'addEventListener' : 'removeEventListener';
    const pointerEvents = 'ontouchstart' in document
      ? { down: 'touchstart', move: 'touchmove', up: 'touchend' }
      : { down: 'mousedown', move: 'mousemove', up: 'mouseup' };

    this.controls.map((x) => x[fn](pointerEvents.down, this.pointerDown));

    window[fn]('scroll', this.handleScroll);
    window[fn]('resize', this.handleResize);

    Array.from(this.inputs).concat(this.preview)
      .map((x) => x[fn]('change', this.changeHandler));

    if (this.colorMenu) {
      this.colorMenu[fn]('click', this.menuHandler);
    }

    document[fn](pointerEvents.move, this.pointerMove);
    document[fn](pointerEvents.up, this.pointerUp);
    window[fn]('keyup', this.keyHandler);
  }

  render() {
    const rgb = this.color.toRgb();

    if (this.format !== 'hsl') {
      const hue = Math.floor((this.controlPositions.c2y / this.height2) * 360);

      this.ctx1.fillStyle = new TinyColor(`hsl(${hue},100%,50%)`).toRgbString();
      this.ctx1.fillRect(0, 0, this.width1, this.height1);

      const whiteGrad = this.ctx2.createLinearGradient(0, 0, this.width1, 0);
      whiteGrad.addColorStop(0, 'rgba(255,255,255,1)');
      whiteGrad.addColorStop(1, 'rgba(255,255,255,0)');
      this.ctx1.fillStyle = whiteGrad;
      this.ctx1.fillRect(0, 0, this.width1, this.height1);

      const blackGrad = this.ctx2.createLinearGradient(0, 0, 0, this.height1);
      blackGrad.addColorStop(0, 'rgba(0,0,0,0)');
      blackGrad.addColorStop(1, 'rgba(0,0,0,1)');
      this.ctx1.fillStyle = blackGrad;
      this.ctx1.fillRect(0, 0, this.width1, this.height1);

      const hueGrad = this.ctx2.createLinearGradient(0, 0, 0, this.height1);
      hueGrad.addColorStop(0, 'rgba(255, 0, 0, 1)');
      hueGrad.addColorStop(0.17, 'rgba(255, 255, 0, 1)');
      hueGrad.addColorStop(0.34, 'rgba(0, 255, 0, 1)');
      hueGrad.addColorStop(0.51, 'rgba(0, 255, 255, 1)');
      hueGrad.addColorStop(0.68, 'rgba(0, 0, 255, 1)');
      hueGrad.addColorStop(0.85, 'rgba(255, 0, 255, 1)');
      hueGrad.addColorStop(1, 'rgba(255, 0, 0, 1)');
      this.ctx2.fillStyle = hueGrad;
      this.ctx2.fillRect(0, 0, this.width2, this.height2);
    } else {
      const hueGrad = this.ctx1.createLinearGradient(0, 0, this.width1, 0);
      const saturation = Math.floor((1 - this.controlPositions.c2y / this.height2) * 100);

      hueGrad.addColorStop(0, new TinyColor('rgb(255, 0, 0)').desaturate(100 - saturation).toRgbString());
      hueGrad.addColorStop(0.17, new TinyColor('rgb(255, 255, 0)').desaturate(100 - saturation).toRgbString());
      hueGrad.addColorStop(0.34, new TinyColor('rgb(0, 255, 0)').desaturate(100 - saturation).toRgbString());
      hueGrad.addColorStop(0.51, new TinyColor('rgb(0, 255, 255)').desaturate(100 - saturation).toRgbString());
      hueGrad.addColorStop(0.68, new TinyColor('rgb(0, 0, 255)').desaturate(100 - saturation).toRgbString());
      hueGrad.addColorStop(0.85, new TinyColor('rgb(255, 0, 255)').desaturate(100 - saturation).toRgbString());
      hueGrad.addColorStop(1, new TinyColor('rgb(255, 0, 0)').desaturate(100 - saturation).toRgbString());

      this.ctx1.fillStyle = hueGrad;
      this.ctx1.fillRect(0, 0, this.width1, this.height1);

      const whiteGrad = this.ctx1.createLinearGradient(0, 0, 0, this.height1);
      whiteGrad.addColorStop(0, 'rgba(255,255,255,1)');
      whiteGrad.addColorStop(0.5, 'rgba(255,255,255,0)');
      this.ctx1.fillStyle = whiteGrad;
      this.ctx1.fillRect(0, 0, this.width1, this.height1);

      const blackGrad = this.ctx1.createLinearGradient(0, 0, 0, this.height1);
      blackGrad.addColorStop(0.5, 'rgba(0,0,0,0)');
      blackGrad.addColorStop(1, 'rgba(0,0,0,1)');
      this.ctx1.fillStyle = blackGrad;
      this.ctx1.fillRect(0, 0, this.width1, this.height1);

      const saturationGrad = this.ctx2.createLinearGradient(0, 0, 0, this.height2);
      const incolor = this.color.clone().greyscale().toRgb();

      saturationGrad.addColorStop(0, `rgb(${rgb.r},${rgb.g},${rgb.b})`);
      saturationGrad.addColorStop(1, `rgb(${incolor.r},${incolor.g},${incolor.b})`);

      this.ctx2.fillStyle = saturationGrad;
      this.ctx2.fillRect(0, 0, this.width3, this.height3);
    }

    // alpha
    if (this.format !== 'hex') {
      this.ctx3.clearRect(0, 0, this.width3, this.height3);
      const alphaGrad = this.ctx3.createLinearGradient(0, 0, 0, this.height3);
      alphaGrad.addColorStop(0, `rgba(${rgb.r},${rgb.g},${rgb.b},1)`);
      alphaGrad.addColorStop(1, `rgba(${rgb.r},${rgb.g},${rgb.b},0)`);
      this.ctx3.fillStyle = alphaGrad;
      this.ctx3.fillRect(0, 0, this.width3, this.height3);
    }
  }

  menuHandler(e) {
    const newOption = e.target.getAttribute('value').trim();
    const newColor = nonColors.includes(newOption) ? 'white' : newOption;
    this.color = new TinyColor(newColor, { format: this.format });
    this.setControlPositions();
    this.updateInputs(1);
    this.updateControls();
    this.render();
    if (nonColors.includes(newOption)) {
      this.value = newOption;
      this.input.value = newOption;
      this.preview.value = newOption;
      this.dispatchChange();
    }
  }

  handleScroll(e) {
    // prevent scroll when updating controls on mobile
    if (this.isMobile && this.dragElement) {
      e.preventDefault();
      e.stopPropagation();
    }
    // update color-dropdown position
    this.updateDropdownPosition(e);
  }

  handleResize(e) {
    // update color-dropdown position
    this.updateDropdownWidth(e);
  }

  pointerDown(e) {
    const eTarget = e.target;
    const controlRect = eTarget.getBoundingClientRect();
    const pageX = e.type === 'touchstart' ? e.touches[0].pageX : e.pageX;
    const pageY = e.type === 'touchstart' ? e.touches[0].pageY : e.pageY;
    const offsetX = pageX - window.pageXOffset - controlRect.left;
    const offsetY = pageY - window.pageYOffset - controlRect.top;

    if (eTarget === this.controls[0] || eTarget === this.control1) {
      const control1 = this.controls[0];
      this.dragElement = control1;
      this.changeControl1({ offsetX, offsetY });
    } else if (eTarget === this.controls[1] || eTarget === this.control2) {
      const control2 = this.controls[1];
      this.dragElement = control2;
      this.changeControl2({ offsetY });
    } else if (this.format !== 'hex' && (eTarget === this.controls[2] || eTarget === this.control3)) {
      const control3 = this.controls[2];
      this.dragElement = control3;
      this.changeAlpha({ offsetY });
    }
    e.preventDefault();
  }

  pointerUp(e) {
    if (!this.dragElement && !document.getSelection().toString().length
      && !this.contains(e.target)) {
      this.hide();
    }

    this.dragElement = null;
  }

  pointerMove(e) {
    const controlInFocus = this.dragElement;

    if (!controlInFocus) return;

    const controlRect = controlInFocus.getBoundingClientRect();
    const pageX = e.type === 'touchmove' ? e.touches[0].pageX : e.pageX;
    const pageY = e.type === 'touchmove' ? e.touches[0].pageY : e.pageY;
    const offsetX = pageX - window.pageXOffset - controlRect.left;
    const offsetY = pageY - window.pageYOffset - controlRect.top;

    if (controlInFocus === this.controls[0]) {
      this.changeControl1({ offsetX, offsetY });
    }

    if (controlInFocus === this.controls[1]) {
      this.changeControl2({ offsetY });
    }

    if (controlInFocus === this.controls[2] && this.format !== 'hex') {
      this.changeAlpha({ offsetY });
    }
  }

  changeHandler() {
    let colorSource;
    const activeEl = this.shadowRoot.activeElement;
    const inputs = Array.from(this.inputs);
    const currentValue = this.preview.value;
    const allowNonColor = this.keywords
      && this.keywords.some((x) => nonColors.includes(x));

    if (activeEl === this.preview || inputs.includes(activeEl)) {
      if (activeEl === this.preview) {
        if (allowNonColor && nonColors.includes(currentValue)) {
          colorSource = 'white';
        } else {
          colorSource = currentValue;
        }
      } else if (this.format === 'hex') {
        colorSource = inputs[0].value;
      } else if (this.format === 'hsl') {
        colorSource = `hsla(${inputs[0].value},${inputs[1].value}%,${inputs[2].value}%,${inputs[3].value})`;
      } else {
        colorSource = `rgba(${inputs.map((x) => x.value).join(',')})`;
      }

      this.color = new TinyColor(colorSource, { format: this.format });
      this.setControlPositions();
      this.updateInputs();
      this.updateControls();
      this.render();

      // set nonColor keyword (inherit/transparent/currentColor)
      if (activeEl === this.preview && allowNonColor && nonColors.includes(currentValue)) {
        this.input.value = currentValue;
        this.preview.value = currentValue;
      }
    }
  }

  keyHandler(e) {
    if (this.isOpen) {
      if (e.which === 27) {
        this.hide();
        return;
      }

      clearTimeout(this.keyTimer);
      this.keyTimer = setTimeout(() => {
        const focusedInput = Array.from(this.inputs)
          .concat(this.preview)
          .find((x) => x === this.shadowRoot.activeElement);

        if (focusedInput && focusedInput.value && focusedInput.value !== this.value) {
          focusedInput.dispatchEvent(new Event('change'));
        }
      }, 700);
    }
  }

  changeControl1(e) {
    let offsetX = 0;
    let offsetY = 0;

    if (e.offsetX > this.width1) {
      offsetX = this.width1;
    } else if (e.offsetX >= 0) {
      offsetX = e.offsetX;
    }

    if (e.offsetY > this.height1) {
      offsetY = this.height1;
    } else if (e.offsetY >= 0) {
      offsetY = e.offsetY;
    }

    const hue = this.format !== 'hsl'
      ? Math.floor((this.controlPositions.c2y / this.height2) * 360)
      : Math.floor((offsetX / this.width1) * 360);

    const saturation = this.format !== 'hsl'
      ? Math.floor((offsetX / this.width1) * 100)
      : Math.floor((1 - this.controlPositions.c2y / this.height2) * 100);

    const lightness = Math.floor((1 - offsetY / this.height1) * 100);
    const alpha = this.format !== 'hex' ? Math.floor((1 - this.controlPositions.c3y / this.height3) * 100) / 100 : 1;
    const colorFormat = this.format !== 'hsl' ? 'hsva' : 'hsla';

    // new color
    this.color = new TinyColor(`${colorFormat}(${hue},${saturation}%,${lightness}%,${alpha})`, { format: this.format });
    // new positions
    this.controlPositions.c1x = offsetX;
    this.controlPositions.c1y = offsetY;
    // update color picker
    this.updateInputs();
    this.updateControls();
    this.render();
  }

  changeControl2(e) {
    let offsetY = 0;

    if (e.offsetY > this.height2) {
      offsetY = this.height2;
    } else if (e.offsetY >= 0) {
      offsetY = e.offsetY;
    }

    const hue = this.format !== 'hsl' ? Math.floor((offsetY / this.height2) * 360) : Math.floor((this.controlPositions.c1x / this.width1) * 360);
    const saturation = this.format !== 'hsl' ? Math.floor((this.controlPositions.c1x / this.width1) * 100) : Math.floor((1 - offsetY / this.height2) * 100);
    const lightness = Math.floor((1 - this.controlPositions.c1y / this.height1) * 100);
    const alpha = this.format !== 'hex' ? Math.floor((1 - this.controlPositions.c3y / this.height3) * 100) / 100 : 1;
    const colorFormat = this.format !== 'hsl' ? 'hsva' : 'hsla';

    // new color
    this.color = new TinyColor(`${colorFormat}(${hue},${saturation}%,${lightness}%,${alpha})`, { format: this.format });
    // new position
    this.controlPositions.c2y = offsetY;
    // update color picker
    this.updateInputs();
    this.updateControls();
    this.render();
  }

  changeAlpha(e) {
    let offsetY = 0;

    if (e.offsetY > this.height3) {
      offsetY = this.height3;
    } else if (e.offsetY >= 0) {
      offsetY = e.offsetY;
    }

    // update color alpha
    this.color.setAlpha(Math.floor((1 - offsetY / this.height3) * 100) / 100);
    // update position
    this.controlPositions.c3y = offsetY;
    // update color picker
    this.updateInputs();
    this.updateControls();
  }

  updateDropdownWidth() {
    const dropPad = parseInt(getComputedStyle(this.dropdown).paddingLeft, 10);
    this.width1 = this.offsetWidth - Math.floor((this.width2 + dropPad) * (this.format !== 'hex' ? 2.4 : 1.5));
    this.controls[0].setAttribute('width', this.width1);
    this.setControlPositions();
    this.updateControls();
    this.render();
  }

  updateDropdownPosition() {
    const elRect = this.preview.getBoundingClientRect();
    const elHeight = this.preview.offsetHeight;
    const windowHeight = document.documentElement.clientHeight;
    const dropHeight = this.dropdown.offsetHeight;
    const distanceBottom = windowHeight - elRect.bottom;
    const distanceTop = elRect.top;
    const bottomExceed = elRect.top + dropHeight + elHeight > windowHeight; // show
    const topExceed = elRect.top - dropHeight < 0; // show-top

    if (this.dropdown.classList.contains('show') && distanceBottom < distanceTop && bottomExceed) {
      this.dropdown.classList.remove('show');
      this.dropdown.classList.add('show-top');
    }
    if (this.dropdown.classList.contains('show-top') && distanceBottom > distanceTop && topExceed) {
      this.dropdown.classList.remove('show-top');
      this.dropdown.classList.add('show');
    }
  }

  setControlPositions() {
    const hsv = this.color.toHsv();
    const hsl = this.color.toHsl();
    const hue = hsl.h;
    const saturation = this.format !== 'hsl' ? hsv.s : hsl.s;
    const lightness = this.format !== 'hsl' ? hsv.v : hsl.l;
    const alpha = hsv.a;

    this.controlPositions.c1x = this.format !== 'hsl' ? saturation * this.width1 : (hue / 360) * this.width1;

    this.controlPositions.c1y = (1 - lightness) * this.height1;

    this.controlPositions.c2y = this.format !== 'hsl' ? (hue / 360) * this.height2 : (1 - saturation) * this.height2;

    if (this.format !== 'hex') {
      this.controlPositions.c3y = (1 - alpha) * this.height3;
    }
  }

  updateControls() {
    this.control1.style.left = `${this.controlPositions.c1x - 3}px`;
    this.control1.style.top = `${this.controlPositions.c1y - 3}px`;
    this.control2.style.top = `${this.controlPositions.c2y - 3}px`;

    if (this.format !== 'hex') {
      this.control3.style.top = `${this.controlPositions.c3y - 3}px`;
    }
  }

  updateInputs(isPrevented) {
    const oldColor = this.value;
    let newColor;
    let hsl;
    let rgb;

    if (this.format === 'hex') {
      newColor = this.color.toHexString();
      this.inputs[0].value = newColor;
    } else if (this.format === 'hsl') {
      newColor = this.color.toHslString();
      hsl = this.color.toHsl();
      this.inputs[0].value = Math.round(hsl.h);
      this.inputs[1].value = Math.round(hsl.s * 100);
      this.inputs[2].value = Math.round(hsl.l * 100);
      this.inputs[3].value = hsl.a;
    } else if (this.format === 'rgb') {
      newColor = this.color.toRgbString();
      rgb = this.color.toRgb();
      this.inputs[0].value = rgb.r;
      this.inputs[1].value = rgb.g;
      this.inputs[2].value = rgb.b;
      this.inputs[3].value = rgb.a;
    }

    // update this instance
    this.value = newColor;

    // update the main inputs
    [this.input, this.preview].forEach((x) => {
      if (x) {
        x.value = newColor;
        if (x === this.preview) {
          x.style.background = newColor;
          const isDark = this.color.isDark() || this.color.getAlpha() < 0.45;

          // toggle dark/light classes will also style the placeholder
          // dark sets color white, light sets color black
          // isDark ? '#000' : '#fff'
          if (!isDark) {
            if (x.classList.contains('dark')) x.classList.remove('dark');
            if (!x.classList.contains('light')) x.classList.add('light');
          } else {
            if (x.classList.contains('light')) x.classList.remove('light');
            if (!x.classList.contains('dark')) x.classList.add('dark');
          }
        }
      }
    });

    // don't trigger the custom event unless it's really changed
    if (!isPrevented && newColor !== oldColor) {
      this.dispatchChange();
    }
  }

  dispatchChange() {
    const changeEvent = new Event('change');
    changeEvent.relatedTarget = this;
    this.input.dispatchEvent(changeEvent);
  }

  showPicker() {
    this.dropdown.classList.add('picker');
    if (this.colorMenu) {
      this.dropdown.classList.remove('menu');
    }
    this.show();
  }

  showMenu() {
    this.dropdown.classList.remove('picker');
    this.dropdown.classList.add('menu');
    this.show();
  }

  show() {
    const current = document.querySelector('color-picker.open');
    if (current) current.hide();

    if (!this.isOpen) {
      this.dropdown.classList.add('show');
      this.classList.add('open');
      this.updateDropdownPosition();
      this.updateDropdownWidth();
      this.toggleEventsOnShown(1);
      this.isOpen = true;
    }
  }

  hide() {
    if (this.isOpen) {
      this.toggleEventsOnShown();

      this.classList.remove('open');
      ['show', 'show-top'].map((x) => this.dropdown.classList.remove(x));

      if (!this.preview.value) {
        this.preview.value = this.value;
      }
      this.isOpen = false;
    }
  }
}

customElements.define('joomla-field-color-picker', ColorPicker);
