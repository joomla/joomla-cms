/**
  * Joomla Color Picker Web Component
  * Implements TinyColor https://github.com/scttcper/tinycolor
  * Copyright Dimitris Grammatikogiannis & Dan Partac
  * License MIT
  */

import TinyColor from '@ctrl/tinycolor';

((customElements, Joomla) => {
  'use strict';

  if (!Joomla) {
    throw new Error('Joomla API is not properly initiated');
  }

  class ColorPicker extends HTMLElement {
    constructor() {
      super();

      // set essentials
      this.value = this.getAttribute('value') || 'rgba(0,0,0,1)';
      this.format = this.getAttribute('format');
      this.placeholder = this.getAttribute('placeholder');
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
      this.show = this.show.bind(this);
      this.pointerDown = this.pointerDown.bind(this);
      this.pointerMove = this.pointerMove.bind(this);
      this.pointerUp = this.pointerUp.bind(this);
      this.handleScroll = this.handleScroll.bind(this);
      this.changeHandler = this.changeHandler.bind(this);
      this.keyHandler = this.keyHandler.bind(this);
      this.updateDropdown = this.updateDropdown.bind(this);
    }

    connectedCallback() {
      // Make sure TinyColor is loaded
      if (window.TinyColor || document.readyState === 'complete') {
        this.doConnect();
      } else {
        const callback = () => {
          this.doConnect();
          window.removeEventListener('load', callback);
        };
        window.addEventListener('load', callback);
      }
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

      // get template part(s)
      this.inputsTemplate = this.querySelector(`[name="${this.format}-form"]`);

      if (!this.inputsTemplate) {
        throw new Error(`ColorPicker requires a <template name="${this.format}-form"> fragment`);
      }

      // The element was already initialised previously and perhaps was detached from DOM
      if (this.color) {
        if (this.isDisconnected) {
          // re-attach events
          this.toggleEvents(1);
          this.isDisconnected = false;
        }
        return;
      }

      this.isDisconnected = false;

      // init color
      this.color = new TinyColor(this.value, { format: this.format });

      // give the <color-picker> a unique id
      const pickerID = `color-picker-${Math.round(Math.random() * 999)}`;

      // make the controls smaller on mobile
      const cv1w = this.isMobile ? 150 : 230;
      const cvh = this.isMobile ? 150 : 230;
      const dropClass = this.isMobile ? ' mobile' : '';
      const alphaControlViz = this.format === 'hex' ? ' visually-hidden' : '';

      this.controlsTemplate = `<div class="color-control">
  <canvas class="color-control1" height="${cvh}" width="${cv1w}"></canvas>
  <div class="color-pointer"></div>
  </div>
  <div class="color-control">
  <canvas class="color-control2" height="${cvh}" width="21" ></canvas>
  <div class="color-slider"></div>
  </div>
  <div class="color-control${alphaControlViz}">
  <canvas class="color-control3" height="${cvh}" width="21"></canvas>
  <div class="color-slider"></div>
  </div>`;

      // set the main template
      this.template = document.createElement('template');

      this.template.innerHTML = `<style>
.picker-box {
  position: relative;
  display: flex
}

.color-dropdown {
  width: 280px;
  background: rgba(0,0,0,0.75);
  color: rgba(255,255,255,0.8);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
  position: absolute;
  padding: 0.5rem;
  border-radius: 0.5rem;
  display: none;
  left:0;
  flex-wrap: wrap;
  justify-content: space-between;
  z-index: 50
}

[format="hex"] + .color-dropdown {
  width: 255px
}

.color-dropdown.mobile {
  width: 210px
}

[format="hex"] + .color-dropdown.mobile {
  width: 180px
}

.color-dropdown.show {
  top: calc(100% + 5px);
  display: flex
}
.color-dropdown.show-top {
  bottom: calc(100% + 5px);
  display: flex;
  top: auto
}

.color-preview {
  border: 0;
  outline: none;
  box-shadow: 0 0 1px 1px rgba(120,120,120,0.33) inset;
  height: 1.5rem;
  line-height: 1.5;
  font-size: 1rem;
  border-radius: 0.25rem;
  padding: 0.6rem 1rem;
  appearance: none;
  width: 100%
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

.color-form * {
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
  border: 1px solid rgba(255,255,255,0.8);
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
</style>

<div class="picker-box">
  <label for="${pickerID}" class="visually-hidden">${Joomla.Text._('JFIELD_COLOR_SELECT', 'Select a colour')}</label>
  <input id="${pickerID}" name="${pickerID}" value="${this.value}" format="${this.format}" placeholder="${this.placeholder}" type="text" class="color-preview" autocomplete="off" spellcheck="false" />
  <div class="color-dropdown${dropClass}">
    ${this.controlsTemplate}
    <div class="color-form">
      ${this.inputsTemplate.innerHTML}
    </div>
  </div>
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
    }

    toggleEvents(action) {
      const fn = action ? 'addEventListener' : 'removeEventListener';
      this.preview[fn]('focusin', this.show);
    }

    toggleEventsOnShown(action) {
      const fn = action ? 'addEventListener' : 'removeEventListener';
      const pointerEvents = 'ontouchstart' in document
        ? { down: 'touchstart', move: 'touchmove', up: 'touchend' }
        : { down: 'mousedown', move: 'mousemove', up: 'mouseup' };

      this.controls.map((x) => x[fn](pointerEvents.down, this.pointerDown));

      window[fn]('scroll', this.handleScroll);

      Array.from(this.inputs).concat(this.preview)
        .map((x) => x[fn]('change', this.changeHandler));

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
        this.ctx2.fill();
      } else {
        const hueGrad = this.ctx1.createLinearGradient(0, 0, this.width1, 0);
        const saturation = Math.round((1 - this.controlPositions.c2y / this.height2) * 100);

        hueGrad.addColorStop(0, new TinyColor('rgb(255, 0, 0)').desaturate(100 - saturation).toRgbString());
        hueGrad.addColorStop(0.17, new TinyColor('rgb(255, 255, 0)').desaturate(100 - saturation).toRgbString());
        hueGrad.addColorStop(0.34, new TinyColor('rgb(0, 255, 0)').desaturate(100 - saturation).toRgbString());
        hueGrad.addColorStop(0.51, new TinyColor('rgb(0, 255, 255)').desaturate(100 - saturation).toRgbString());
        hueGrad.addColorStop(0.68, new TinyColor('rgb(0, 0, 255)').desaturate(100 - saturation).toRgbString());
        hueGrad.addColorStop(0.85, new TinyColor('rgb(255, 0, 255)').desaturate(100 - saturation).toRgbString());
        hueGrad.addColorStop(1, new TinyColor('rgb(255, 0, 0)').desaturate(100 - saturation).toRgbString());
        this.ctx1.fillStyle = hueGrad;
        this.ctx1.fill();

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

    handleScroll(e) {
      // prevent scroll when updating controls on mobile
      if (this.isMobile && this.dragElement) {
        e.preventDefault();
        e.stopPropagation();
      }
      // update color-dropdown position
      this.updateDropdown(e);
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
      const activeEl = this.shadowRoot.activeElement;
      const inputs = Array.from(this.inputs);
      let colorSource;

      if (activeEl === this.preview || (this.isOpen && inputs.includes(activeEl))) {
        if (activeEl === this.preview) {
          colorSource = this.preview.value;
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

    updateDropdown() {
      const elRect = this.preview.parentElement.getBoundingClientRect();
      const elHeight = this.preview.parentElement.offsetHeight;
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

    dispatchCustomEvent(eventName) {
      const OriginalCustomEvent = new CustomEvent(eventName);
      OriginalCustomEvent.relatedTarget = this;
      this.dispatchEvent(OriginalCustomEvent);
      this.removeEventListener(eventName, this);
    }

    updateInputs(isInit) {
      const oldColor = this.preview.value;

      let newColor = ''; let hsl; let
        rgb;

      if (this.format === 'hex') {
        newColor = this.color.toHexString();
        this.inputs[0].value = newColor;
      } else if (this.format === 'hsl') {
        newColor = this.color.toHslString();
        hsl = this.color.toHsl();
        this.inputs[0].value = Math.floor(hsl.h);
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
      // update the main input
      this.value = newColor;

      [this.input, this.preview].forEach((x) => {
        if (x) {
          x.value = newColor;
          if (x === this.preview) {
            x.style.background = newColor;
            x.style.color = !this.color.isDark() || this.color.getAlpha() < 0.45 ? '#000' : '#fff';
          }
        }
      });

      // don't trigger the custom event unless it's really changed
      if (!isInit && newColor !== oldColor) {
        this.dispatchCustomEvent('joomla.colorpicker.change');
      }
    }

    show() {
      const current = document.querySelector('color-picker.open');
      if (current) current.hide();

      if (!this.isOpen) {
        this.dropdown.classList.add('show');
        this.updateDropdown();
        this.classList.add('open');
        this.toggleEventsOnShown(1);
        this.isOpen = true;
      }
    }

    hide() {
      if (this.isOpen) {
        this.toggleEventsOnShown();
        this.isOpen = false;

        this.classList.remove('open');
        ['show', 'show-top'].map((x) => this.dropdown.classList.remove(x));
      }
    }
  }
  customElements.define('color-picker', ColorPicker);
})(customElements, Joomla);
