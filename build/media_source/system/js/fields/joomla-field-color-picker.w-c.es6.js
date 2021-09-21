/**
 * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Joomla Color Picker
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
const colorNames = ['white', 'black', 'grey', 'red', 'orange', 'brown', 'gold', 'olive', 'yellow', 'lime', 'green', 'teal', 'cyan', 'blue', 'violet', 'magenta', 'pink'];

class ColorPicker extends HTMLElement {
  constructor() {
    super();

    // set initial state
    this.dragElement = null;
    this.isOpen = false;
    this.isDisconnected = false;
    this.isMobile = 'ontouchstart' in document && /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
    this.controlPositions = {
      c1x: 0, c1y: 0, c2y: 0, c3y: 0,
    };

    // bind events
    this.showPicker = this.showPicker.bind(this);
    this.togglePicker = this.togglePicker.bind(this);
    this.toggleMenu = this.toggleMenu.bind(this);
    this.menuClickHandler = this.menuClickHandler.bind(this);
    this.menuKeyHandler = this.menuKeyHandler.bind(this);
    this.pointerDown = this.pointerDown.bind(this);
    this.pointerMove = this.pointerMove.bind(this);
    this.pointerUp = this.pointerUp.bind(this);
    this.handleScroll = this.handleScroll.bind(this);
    this.handleResize = this.handleResize.bind(this);
    this.handleLabelFocus = this.handleLabelFocus.bind(this);
    this.handleFocusOut = this.handleFocusOut.bind(this);
    this.handleSubmit = this.handleSubmit.bind(this);
    this.changeHandler = this.changeHandler.bind(this);
    this.handleDismiss = this.handleDismiss.bind(this);
    this.keyHandler = this.keyHandler.bind(this);
    this.handleKnobs = this.handleKnobs.bind(this);
    this.updateDropdownPosition = this.updateDropdownPosition.bind(this);
    this.updateDropdownWidth = this.updateDropdownWidth.bind(this);
  }

  // getters
  get value() { return this.input.value || this.getAttribute('value'); }

  set value(v) { this.input.value = v; }

  get required() { return this.hasAttribute('required'); }

  get format() { return this.getAttribute('format'); }

  get name() { return this.getAttribute('name'); }

  get type() { return this.localName; }

  get form() { return this.closest('form'); }

  get input() { return this.shadowRoot.querySelector('.color-preview'); }

  get label() { return document.querySelector(`[for="${this.id}"]`); }

  get allowNonColor() { return this.keywords && this.keywords.some((x) => nonColors.includes(x)); }

  get hex() { return this.color.toHex(); }

  get hsv() { return this.color.toHsv(); }

  get hsl() { return this.color.toHsl(); }

  get rgb() { return this.color.toRgb(); }

  get brightness() { return this.color.getBrightness(); }

  get isDark() { const { rgb } = this; return this.brightness < 120 && rgb.a > 0.33; }

  get isValid() { const inputValue = this.input.value; return inputValue !== '' && new TinyColor(inputValue).isValid; }

  disconnectedCallback() {
    this.isDisconnected = true;
    this.toggleEvents();
  }

  connectedCallback() {
    // The element was already initialised
    if (this.color) {
      if (this.isDisconnected) {
        this.toggleEvents(1);
        this.isDisconnected = false;
      }
      return;
    }

    // set new state
    this.isDisconnected = false;

    // get input value, format, direction and labels
    const fieldLabel = this.label.innerText.replace('*', '').trim();
    const inputLabel = this.getAttribute('inputLabel');
    const colorValue = this.getAttribute('value');
    const hint = this.getAttribute('placeholder');
    const placeholder = hint ? ` placeholder="${hint}"` : '';
    const requiredLabel = this.getAttribute('requiredLabel');
    const formatLabel = this.getAttribute('formatLabel');
    const pickerLabel = this.getAttribute('pickerLabel');
    const alphaLabel = this.getAttribute('alphaLabel') || 'alpha';
    const appearanceLabel = this.getAttribute('appearanceLabel');
    const hexLabel = this.getAttribute('hexLabel');
    const hueLabel = this.getAttribute('hueLabel');
    const saturationLabel = this.getAttribute('saturationLabel');
    const lightnessLabel = this.getAttribute('lightnessLabel');
    const redLabel = this.getAttribute('redLabel');
    const greenLabel = this.getAttribute('greenLabel');
    const blueLabel = this.getAttribute('blueLabel');
    const translatedColorLabels = this.getAttribute('colorLabels').split(',');
    const colorLabels = translatedColorLabels.length !== 17 ? colorNames : translatedColorLabels;

    // expose component labels to all methods
    this.componentLabels = {
      appearance: appearanceLabel,
      alpha: alphaLabel,
      hex: hexLabel,
      red: redLabel,
      green: greenLabel,
      blue: blueLabel,
      hue: hueLabel,
      saturation: saturationLabel,
      lightness: lightnessLabel,
      required: this.required ? ` ${requiredLabel}` : '',
    };

    // expose color labels to all methods
    this.colorLabels = {};

    colorNames.forEach((c, i) => { this.colorLabels[c] = colorLabels[i]; });

    // init color
    this.color = new TinyColor((nonColors.includes(colorValue) ? '#fff' : colorValue), { format: this.format });

    // set initial controls dimensions
    // make the controls smaller on mobile
    const cv1w = this.isMobile ? 150 : 230;
    const cvh = this.isMobile ? 150 : 230;
    const cv2w = 21;
    const dropClass = this.isMobile ? ' mobile' : '';
    const ctrl1Labelledby = this.format === 'hsl' ? 'appearance appearance1' : 'appearance1';
    const ctrl2Labelledby = this.format === 'hsl' ? 'appearance2' : 'appearance appearance2';

    const control3Template = this.format === 'hex' ? '' : `<div class="color-control">
    <label id="appearance3" class="color-label visually-hidden" aria-live="polite"></label>
    <canvas class="visual-control3" height="${cvh}" width="${cv2w}" aria-hidden="true"></canvas>
    <div class="color-slider knob" tabindex="0" aria-labelledby="appearance3"></div>
  </div>`;

    const controlsTemplate = `<div class="color-control">
  <label id="appearance1" class="color-label visually-hidden" aria-live="polite"></label>
  <canvas class="visual-control1" height="${cvh}" width="${cv1w}" aria-hidden="true"></canvas>
  <div class="color-pointer knob" tabindex="0" aria-labelledby="${ctrl1Labelledby}"></div>
</div>
<div class="color-control">
  <label id="appearance2" class="color-label visually-hidden" aria-live="polite"></label>
  <canvas class="visual-control2" height="${cvh}" width="${cv2w}" aria-hidden="true"></canvas>
  <div class="color-slider knob" tabindex="0" aria-labelledby="${ctrl2Labelledby}"></div>
</div>
${control3Template}`;

    // set inputs template
    let inputsTemplate;

    if (this.format === 'hex') {
      const hexForm = `<label for="color_hex" class="hex-label"><span aria-hidden="true">#</span><span class="visually-hidden">${hexLabel}</span></label>
<input id="color_hex" name="color_hex" class="color-input color-input-hex" type="text" autocomplete="off" spellcheck="false">`;

      inputsTemplate = hexForm;
    } else if (this.format === 'rgb') {
      const rgbForm = `<label for="rgb_color_red"><span aria-hidden="true">R:</span><span class="visually-hidden">${redLabel}</span></label>
<input id="rgb_color_red" name="rgb_color_red" class="color-input" type="number" min="0" max="255" autocomplete="off" spellcheck="false">
<label for="rgb_color_green"><span aria-hidden="true">G:</span><span class="visually-hidden">${greenLabel}</span></label>
<input id="rgb_color_green" name="rgb_color_green" class="color-input" type="number" min="0" max="255" autocomplete="off" spellcheck="false">
<label for="rgb_color_blue"><span aria-hidden="true">B:</span><span class="visually-hidden">${blueLabel}</span></label>
<input id="rgb_color_blue" name="rgb_color_blue" class="color-input" type="number" min="0" max="255" autocomplete="off" spellcheck="false">
<label for="rgb_color_alpha"><span aria-hidden="true">A:</span><span class="visually-hidden">${alphaLabel}</span></label>
<input id="rgb_color_alpha" name="rgb_color_alpha" class="color-input" type="number" min="0" max="1" step="0.01" autocomplete="off" spellcheck="false">`;

      inputsTemplate = rgbForm;
    } else if (this.format === 'hsl') {
      const hslForm = `<label for="hsl_color_hue"><span aria-hidden="true">H:</span><span class="visually-hidden">${hueLabel}</span></label>
<input id="hsl_color_hue" name="hsl_color_hue" class="color-input" type="number" min="0" max="360" autocomplete="off" spellcheck="false">
<label for="hsl_color_saturation"><span aria-hidden="true">S:</span><span class="visually-hidden">${saturationLabel}></span></label>
<input id="hsl_color_saturation" name="hsl_color_saturation" class="color-input" type="number" min="0" max="100" autocomplete="off" spellcheck="false">
<label for="hsl_color_lightness"><span aria-hidden="true">L:</span><span class="visually-hidden">${lightnessLabel}</span></label>
<input id="hsl_color_lightness" name="hsl_color_lightness" class="color-input" type="number" min="0" max="100" autocomplete="off" spellcheck="false">
<label for="hsl_color_alpha"><span aria-hidden="true">A:</span><span class="visually-hidden">${alphaLabel}</span></label>
<input id="hsl_color_alpha" name="hsl_color_alpha" class="color-input" type="number" min="0" max="1" step="0.01" autocomplete="off" spellcheck="false">`;

      inputsTemplate = hslForm;
    }

    // set color key menu template
    this.keywords = false;
    const colorKeysOption = this.getAttribute('keywords');
    const toggleLabel = this.getAttribute('toggleLabel');
    const menuLabel = this.getAttribute('menuLabel');
    let menuTemplate = '';
    let menuToggle = '';
    if (colorKeysOption !== 'false') {
      const colorKeys = colorKeysOption ? colorKeysOption.split(',') : nonColors;
      this.keywords = colorKeys;
      let colorOpsMarkup = '';
      colorKeys.forEach((x) => {
        const xKey = x.trim();
        const xRealColor = new TinyColor(xKey, { format: this.format }).toString();
        const xClass = xRealColor === this.getAttribute('value') ? ' class="active" aria-selected="true"' : '';
        colorOpsMarkup += `<li role="option" tabindex="0" value="${xKey}"${xClass}>${x}</li>`;
      });
      // the btn toggle
      menuToggle = `<button id="presets-btn" class="menu-toggle button-appearance" aria-expanded="false" aria-haspopup="true">
  <span class="visually-hidden">${toggleLabel}</span>
  <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024">
    <path fill="#fff" d="M777.857 367.557c9.748 -9.605 25.41 -9.605 35.087 0s9.713 25.124 0 34.729L529.521 682.914c-9.677 9.605 -25.338 9.605 -35.087 0L211.011 402.286c-9.677 -9.605 -9.677 -25.123 0 -34.729c9.712 -9.605 25.41 -9.605 35.087 0l265.897 255.934L777.857 367.557z"></path>
  </svg>
</button>`;
      // the menu
      menuTemplate = `<div class="color-dropdown menu">
  <label id="presets-label" class="visually-hidden">${menuLabel}</label>
  <ul aria-labelledby="presets-label" class="color-menu${dropClass}" role="list">${colorOpsMarkup}</ul>
</div>`;
    }

    // set the main template
    this.template = document.createElement('template');

    this.template.innerHTML = `<style>{{CSS_CONTENTS_PLACEHOLDER}}</style>
  <button id="picker-btn" class="picker-toggle button-appearance" aria-expanded="false" aria-haspopup="true" aria-live="polite">
    <span class="visually-hidden"></span>
  </button>
  
  <label for="color-input" class="visually-hidden">${fieldLabel} ${formatLabel}. ${inputLabel}</label>
  <input id="color-input" name="color-input" type="text" class="color-preview button-appearance" autocomplete="off" spellcheck="false"${placeholder}/>
  
<div class="color-dropdown picker${dropClass}" role="group" aria-labelledby="picker-label format-label">
  <label id="picker-label" class="visually-hidden" aria-hidden="true">${pickerLabel}</label>
  <label id="format-label" class="visually-hidden" aria-hidden="true">${formatLabel}</label>
  <label id="appearance" class="color-appearance visually-hidden" aria-hidden="true" aria-live="polite">${appearanceLabel}</label>
  <div class="color-controls">
    ${controlsTemplate}
  </div>
  <div class="color-form">
    ${inputsTemplate}
  </div>
</div>
${menuToggle}
${menuTemplate}`;

    // Patch shadow DOM
    if (window.ShadyCSS) {
      window.ShadyCSS.prepareTemplate(this.template, 'joomla-field-color-picker');
    }

    this.attachShadow({ mode: 'open' });
    this.shadowRoot.appendChild(this.template.content.cloneNode(true));

    // Patch shadow DOM
    if (window.ShadyCSS) {
      window.ShadyCSS.styleElement(this);
    }

    // set main elements
    this.pickerToggle = this.shadowRoot.querySelector('.picker-toggle');
    this.menuToggle = this.shadowRoot.querySelector('.menu-toggle');
    this.colorMenu = this.shadowRoot.querySelector('.color-dropdown.menu');
    this.controls = this.shadowRoot.querySelector('.color-controls');
    this.colorPicker = this.shadowRoot.querySelector('.color-dropdown.picker');
    this.inputs = Array.from(this.shadowRoot.querySelectorAll('.color-input'));
    this.controlKnobs = Array.from(this.shadowRoot.querySelectorAll('.knob'));
    this.visuals = Array.from(this.shadowRoot.querySelectorAll('canvas'));
    this.knobLabels = Array.from(this.shadowRoot.querySelectorAll('.color-label'));
    this.appearance = this.shadowRoot.querySelector('.color-appearance');

    // set dimensions
    this.width1 = this.visuals[0].width;
    this.height1 = this.visuals[0].height;
    this.width2 = this.visuals[1].width;
    this.height2 = this.visuals[1].height;
    // set main controls
    this.ctx1 = this.visuals[0].getContext('2d');
    this.ctx2 = this.visuals[1].getContext('2d');
    this.ctx1.rect(0, 0, this.width1, this.height1);
    this.ctx2.rect(0, 0, this.width2, this.height2);

    // set alpha control except hex
    if (this.format !== 'hex') {
      this.width3 = this.visuals[2].width;
      this.height3 = this.visuals[2].height;
      this.ctx3 = this.visuals[2].getContext('2d');
      this.ctx3.rect(0, 0, this.width3, this.height3);
    }

    // Accessibility
    // set default tabindex for visible elements
    // don't expose control inputs yet
    this.toggleTabIndex();
    // set required
    if (this.required) {
      this.input.setAttribute('required', 'true');
    }

    // update color picker
    this.setControlPositions();
    this.setColorAppearence();
    this.updateInputs(1); // don't trigger change in this context
    this.updateControls();
    this.render();
    // add main events listeners
    this.toggleEvents(1);

    // solve non-colors after settings save
    if (this.keywords && nonColors.includes(colorValue)) {
      this.value = colorValue;
    }
  }

  toggleEvents(action) {
    const fn = action ? 'addEventListener' : 'removeEventListener';

    this.label[fn]('click', this.handleLabelFocus);
    this.input[fn]('focusin', this.showPicker);
    this.pickerToggle[fn]('click', this.togglePicker);
    this.form[fn]('submit', this.handleSubmit);

    this[fn]('keydown', this.keyHandler);

    if (this.menuToggle) {
      this.menuToggle[fn]('click', this.toggleMenu);
    }
  }

  toggleEventsOnShown(action) {
    const fn = action ? 'addEventListener' : 'removeEventListener';
    const pointerEvents = 'ontouchstart' in document
      ? { down: 'touchstart', move: 'touchmove', up: 'touchend' }
      : { down: 'mousedown', move: 'mousemove', up: 'mouseup' };

    this.controls[fn](pointerEvents.down, this.pointerDown);
    // turn off "handleKnobs" for now, screen readers won't work without ALT key
    this.controlKnobs.forEach((x) => x[fn]('keydown', this.handleKnobs));

    window[fn]('scroll', this.handleScroll);
    window[fn]('resize', this.handleResize);

    this.inputs.concat(this.input).forEach((x) => x[fn]('change', this.changeHandler));

    if (this.colorMenu) {
      this.colorMenu[fn]('click', this.menuClickHandler);
      this.colorMenu[fn]('keydown', this.menuKeyHandler);
    }

    document[fn](pointerEvents.move, this.pointerMove);
    document[fn](pointerEvents.up, this.pointerUp);
    window[fn]('keyup', this.handleDismiss);
    this[fn]('focusout', this.handleFocusOut);
  }

  render() {
    const rgb = this.color.toRgb();

    if (this.format !== 'hsl') {
      const hue = Math.round((this.controlPositions.c2y / this.height2) * 360);

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
      const saturation = Math.round((1 - this.controlPositions.c2y / this.height2) * 100);

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

    if (this.format !== 'hex') {
      this.ctx3.clearRect(0, 0, this.width3, this.height3);
      const alphaGrad = this.ctx3.createLinearGradient(0, 0, 0, this.height3);
      alphaGrad.addColorStop(0, `rgba(${rgb.r},${rgb.g},${rgb.b},1)`);
      alphaGrad.addColorStop(1, `rgba(${rgb.r},${rgb.g},${rgb.b},0)`);
      this.ctx3.fillStyle = alphaGrad;
      this.ctx3.fillRect(0, 0, this.width3, this.height3);
    }
  }

  handleSubmit() {
    const hiddenInput = this.querySelector('input');
    hiddenInput.name = this.name;
    hiddenInput.value = this.value;
  }

  handleFocusOut({ relatedTarget }) {
    const activeEl = this.shadowRoot.activeElement;

    if (relatedTarget !== activeEl) {
      this.hide(1);
    }
  }

  handleLabelFocus() {
    this.input.focus();
  }

  handleDismiss(e) {
    const which = e.which || e.keyCode;

    if (this.isOpen && which === 27) {
      this.hide();
    }
  }

  dispatchChange() {
    this.dispatchEvent(new Event('change'));
  }

  handleResize(e) {
    this.updateDropdownWidth(e);
    this.updateDropdownPosition(e);
  }

  handleScroll(e) {
    // prevent scroll
    // * when updating controls on mobile
    // * when control knobs react to keyboard input
    const activeEl = this.shadowRoot.activeElement;

    if ((this.isMobile && this.dragElement) || this.controlKnobs.includes(activeEl)) {
      e.stopPropagation();
      e.preventDefault();
    }

    this.updateDropdownPosition(e);
  }

  menuKeyHandler(e) {
    const which = e.which || e.keyCode;
    const eTarget = e.target;

    if ([38, 40].includes(which)) {
      e.preventDefault();
    } else if ([13, 32].includes(which)) {
      this.menuClickHandler({ target: eTarget });
    }
  }

  menuClickHandler(e) {
    const eTarget = e.target;
    const newOption = eTarget.getAttribute('value').trim();
    const currentActive = this.colorMenu.querySelector('li.active');
    const newColor = nonColors.includes(newOption) ? 'white' : newOption;
    this.color = new TinyColor(newColor, { format: this.format });
    this.setControlPositions();
    this.setColorAppearence();
    this.updateInputs(1);
    this.updateControls();
    this.render();

    if (currentActive) {
      currentActive.classList.remove('active');
      currentActive.removeAttribute('aria-selected');
    }

    if (currentActive !== eTarget) {
      eTarget.classList.add('active');
      eTarget.setAttribute('aria-selected', 'true');

      if (nonColors.includes(newOption)) {
        this.value = newOption;
        this.dispatchChange();
      }
    }
  }

  pointerDown(e) {
    const eTarget = e.target;
    const visual = eTarget.tagName === 'canvas' ? eTarget : eTarget.parentElement.querySelector('canvas');
    const visualRect = visual.getBoundingClientRect();
    const pageX = e.type === 'touchstart' ? e.touches[0].pageX : e.pageX;
    const pageY = e.type === 'touchstart' ? e.touches[0].pageY : e.pageY;
    const offsetX = pageX - window.pageXOffset - visualRect.left;
    const offsetY = pageY - window.pageYOffset - visualRect.top;

    if (eTarget === this.visuals[0] || eTarget === this.controlKnobs[0]) {
      this.dragElement = visual;
      this.changeControl1({ offsetX, offsetY });
    } else if (eTarget === this.visuals[1] || eTarget === this.controlKnobs[1]) {
      this.dragElement = visual;
      this.changeControl2({ offsetY });
    } else if (this.format !== 'hex' && (eTarget === this.visuals[2] || eTarget === this.controlKnobs[2])) {
      this.dragElement = visual;
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

    if (controlInFocus === this.visuals[0]) {
      this.changeControl1({ offsetX, offsetY });
    }

    if (controlInFocus === this.visuals[1]) {
      this.changeControl2({ offsetY });
    }

    if (controlInFocus === this.visuals[2] && this.format !== 'hex') {
      this.changeAlpha({ offsetY });
    }
  }

  handleKnobs(e) {
    const eTarget = e.target;
    const which = e.which || e.keyCode;

    // only react to arrow buttons
    if (![37, 38, 39, 40].includes(which)) return;
    e.preventDefault();

    const activeEl = this.shadowRoot.activeElement;
    const currentKnob = this.controlKnobs.find((x) => x === activeEl);

    if (currentKnob) {
      let offsetX = 0;
      let offsetY = 0;

      if (eTarget === this.controlKnobs[0]) {
        if ([37, 39].includes(which)) {
          this.controlPositions.c1x += which === 39 ? +1 : -1;
        } else if ([38, 40].includes(which)) {
          this.controlPositions.c1y += which === 40 ? +1 : -1;
        }

        offsetX = this.controlPositions.c1x;
        offsetY = this.controlPositions.c1y;
        this.changeControl1({ offsetX, offsetY });
      } else if (eTarget === this.controlKnobs[1]) {
        this.controlPositions.c2y += which === 40 ? +1 : -1;
        offsetY = this.controlPositions.c2y;
        this.changeControl2({ offsetY });
      } else if (eTarget === this.controlKnobs[2]) {
        this.controlPositions.c3y += which === 40 ? +1 : -1;
        offsetY = this.controlPositions.c3y;
        this.changeAlpha({ offsetY });
      }

      this.setColorAppearence();
      this.updateInputs();
      this.updateControls();
      this.render();
      // stop scrolling when changing controls
      this.handleScroll(e);
    }
  }

  changeHandler() {
    let colorSource;
    const activeEl = this.shadowRoot.activeElement;
    const { inputs } = this;
    const currentValue = this.value;
    const isNonColorValue = this.allowNonColor && nonColors.includes(currentValue);

    if (activeEl === this.input || inputs.includes(activeEl)) {
      if (activeEl === this.input) {
        if (isNonColorValue) {
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
      this.setColorAppearence();
      this.updateInputs();
      this.updateControls();
      this.render();

      // set non-color keyword
      if (activeEl === this.input && isNonColorValue) {
        this.value = currentValue;
      }
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
      ? Math.round((this.controlPositions.c2y / this.height2) * 360)
      : Math.round((offsetX / this.width1) * 360);

    const saturation = this.format !== 'hsl'
      ? Math.round((offsetX / this.width1) * 100)
      : Math.round((1 - this.controlPositions.c2y / this.height2) * 100);

    const lightness = Math.round((1 - offsetY / this.height1) * 100);
    const alpha = this.format !== 'hex' ? Math.round((1 - this.controlPositions.c3y / this.height3) * 100) / 100 : 1;
    const format = this.format !== 'hsl' ? 'hsva' : 'hsla';

    // new color
    this.color = new TinyColor(`${format}(${hue},${saturation}%,${lightness}%,${alpha})`, { format: this.format });
    // new positions
    this.controlPositions.c1x = offsetX;
    this.controlPositions.c1y = offsetY;

    // update color picker
    this.setColorAppearence();
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

    const hue = this.format !== 'hsl' ? Math.round((offsetY / this.height2) * 360) : Math.round((this.controlPositions.c1x / this.width1) * 360);
    const saturation = this.format !== 'hsl' ? Math.round((this.controlPositions.c1x / this.width1) * 100) : Math.round((1 - offsetY / this.height2) * 100);
    const lightness = Math.round((1 - this.controlPositions.c1y / this.height1) * 100);
    const alpha = this.format !== 'hex' ? Math.round((1 - this.controlPositions.c3y / this.height3) * 100) / 100 : 1;
    const colorFormat = this.format !== 'hsl' ? 'hsva' : 'hsla';

    // new color
    this.color = new TinyColor(`${colorFormat}(${hue},${saturation}%,${lightness}%,${alpha})`, { format: this.format });
    // new position
    this.controlPositions.c2y = offsetY;
    // update color picker
    this.setColorAppearence();
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
    const alpha = Math.round((1 - offsetY / this.height3) * 100);
    this.color.setAlpha(alpha / 100);
    // update position
    this.controlPositions.c3y = offsetY;
    // update color picker
    this.updateInputs();
    this.updateControls();
  }

  updateDropdownWidth() {
    const dropPad = parseInt(getComputedStyle(this.colorPicker).paddingLeft, 10);
    this.width1 = this.offsetWidth - Math.round((this.width2 + dropPad) * (this.format !== 'hex' ? 2.4 : 1.5));
    this.visuals[0].setAttribute('width', this.width1);
    this.setControlPositions();
    this.updateControls();
    this.render();
  }

  updateDropdownPosition() {
    const elRect = this.input.getBoundingClientRect();
    const elHeight = this.input.offsetHeight;
    const windowHeight = document.documentElement.clientHeight;
    const isPicker = this.classToggle(this.colorPicker, 1);
    const dropdown = isPicker ? this.colorPicker : this.colorMenu;
    const dropHeight = dropdown.offsetHeight;
    const distanceBottom = windowHeight - elRect.bottom;
    const distanceTop = elRect.top;
    const bottomExceed = elRect.top + dropHeight + elHeight > windowHeight; // show
    const topExceed = elRect.top - dropHeight < 0; // show-top

    if (dropdown.classList.contains('show') && distanceBottom < distanceTop && bottomExceed) {
      dropdown.classList.remove('show');
      dropdown.classList.add('show-top');
    }
    if (dropdown.classList.contains('show-top') && distanceBottom > distanceTop && topExceed) {
      dropdown.classList.remove('show-top');
      dropdown.classList.add('show');
    }
  }

  setControlPositions() {
    const { hsv, hsl } = this;
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

  setColorAppearence() {
    const labels = this.componentLabels;
    const {
      colorLabels, hsl, hsv, hex,
    } = this;
    const knob1Lbl = this.knobLabels[0];
    const knob2Lbl = this.knobLabels[1];
    const hue = Math.round(hsl.h);
    const alpha = hsv.a;
    const saturationSource = this.format === 'hsl' ? hsl.s : hsv.s;
    const saturation = Math.round(saturationSource * 100);
    const lightness = Math.round(hsl.l * 100);
    const hsvl = hsv.v * 100;
    let colorName;

    // determine color appearance
    if (lightness === 100 && saturation === 0) {
      colorName = colorLabels.white;
    } else if (lightness === 0) {
      colorName = colorLabels.black;
    } else if (saturation === 0) {
      colorName = colorLabels.grey;
    } else if (hue < 15 || hue >= 345) {
      colorName = colorLabels.red;
    } else if (hue >= 15 && hue < 45) {
      colorName = hsvl > 80 && saturation > 80 ? colorLabels.orange : colorLabels.brown;
    } else if (hue >= 45 && hue < 75) {
      const isGold = hue > 46 && hue < 54 && hsvl < 80 && saturation > 90;
      const isOlive = hue >= 54 && hue < 75 && hsvl < 80;
      colorName = isGold ? colorLabels.gold : colorLabels.yellow;
      colorName = isOlive ? colorLabels.olive : colorName;
    } else if (hue >= 75 && hue < 155) {
      colorName = hsvl < 68 ? colorLabels.green : colorLabels.lime;
    } else if (hue >= 155 && hue < 175) {
      colorName = colorLabels.teal;
    } else if (hue >= 175 && hue < 195) {
      colorName = colorLabels.cyan;
    } else if (hue >= 195 && hue < 255) {
      colorName = colorLabels.blue;
    } else if (hue >= 255 && hue < 270) {
      colorName = colorLabels.violet;
    } else if (hue >= 270 && hue < 295) {
      colorName = colorLabels.magenta;
    } else if (hue >= 295 && hue < 345) {
      colorName = colorLabels.pink;
    }

    if (this.format === 'hsl') {
      knob1Lbl.innerText = `${labels.hue}: ${hue}°. ${labels.lightness}: ${lightness}%`;
      knob2Lbl.innerText = `${labels.saturation}: ${saturation}%`;
    } else {
      knob1Lbl.innerText = `${labels.lightness}: ${lightness}%. ${labels.saturation}: ${saturation}%`;
      knob2Lbl.innerText = `${labels.hue}: ${hue}°`;
    }

    if (this.format !== 'hex') {
      const alphaValue = Math.round(alpha * 100);
      const knob3Lbl = this.knobLabels[2];
      knob3Lbl.innerText = `${labels.alpha}: ${alphaValue}%`;
    }

    // update color labels
    this.appearance.innerText = `${labels.appearance}: ${colorName}.`;
    const colorLabel = this.format === 'hex' ? `${labels.hex} ${hex.split('').join(' ')}.` : this.value.toUpperCase();
    const fieldLabel = this.label.innerText.replace('*', '').trim();
    const pickerBtnSpan = this.pickerToggle.children[0];
    pickerBtnSpan.innerText = `${fieldLabel}: ${colorLabel}${labels.required}`;
  }

  updateControls() {
    const control1 = this.controlKnobs[0];
    const control2 = this.controlKnobs[1];
    control1.style.transform = `translate3d(${this.controlPositions.c1x - 3}px,${this.controlPositions.c1y - 3}px,0)`;
    control2.style.transform = `translate3d(0,${this.controlPositions.c2y - 3}px,0)`;

    if (this.format !== 'hex') {
      const control3 = this.controlKnobs[2];
      control3.style.transform = `translate3d(0,${this.controlPositions.c3y - 3}px,0)`;
    }
  }

  updateInputs(isPrevented) {
    const oldColor = this.value;
    const { rgb, hsl, hsv } = this;
    const alpha = hsl.a;
    const hue = Math.round(hsl.h);
    const saturation = Math.round(hsl.s * 100);
    const lightSource = this.format === 'hsl' ? hsl.l : hsv.v;
    const lightness = Math.round(lightSource * 100);

    let newColor;

    if (this.format === 'hex') {
      newColor = this.color.toHexString();
      this.inputs[0].value = this.hex;
    } else if (this.format === 'hsl') {
      newColor = this.color.toHslString();
      this.inputs[0].value = hue;
      this.inputs[1].value = saturation;
      this.inputs[2].value = lightness;
      this.inputs[3].value = alpha;
    } else if (this.format === 'rgb') {
      newColor = this.color.toRgbString();
      this.inputs[0].value = rgb.r;
      this.inputs[1].value = rgb.g;
      this.inputs[2].value = rgb.b;
      this.inputs[3].value = alpha;
    }

    // update this instance
    this.value = newColor;

    // update the visible input
    this.input.style.backgroundColor = newColor;

    // toggle dark/light classes will also style the placeholder
    // dark sets color white, light sets color black
    // isDark ? '#000' : '#fff'
    if (!this.isDark) {
      if (this.classList.contains('dark')) this.classList.remove('dark');
      if (!this.classList.contains('light')) this.classList.add('light');
    } else {
      if (this.classList.contains('light')) this.classList.remove('light');
      if (!this.classList.contains('dark')) this.classList.add('dark');
    }

    // don't trigger the custom event unless it's really changed
    if (!isPrevented && newColor !== oldColor) {
      this.dispatchChange();
    }
  }

  keyHandler(e) {
    const activeEl = this.shadowRoot.activeElement;
    const which = e.which || e.keyCode;

    if ([13, 32].includes(which)) {
      if ((this.menuToggle && activeEl === this.menuToggle) || !activeEl) {
        e.preventDefault();
        if (!activeEl) {
          this.togglePicker();
        } else {
          this.toggleMenu();
        }
      }
    }
  }

  toggleTabIndex(enabled) {
    const tabIndexValue = enabled ? 0 : -1;

    this.input.setAttribute('tabindex', tabIndexValue);
    if (this.menuToggle) {
      this.menuToggle.setAttribute('tabindex', tabIndexValue);
    }
  }

  showPicker() {
    this.classToggle(this.colorMenu);
    this.colorPicker.classList.add('show');
    this.input.focus();
    this.show();
    this.pickerToggle.setAttribute('aria-expanded', 'true');
  }

  togglePicker(e) {
    e.preventDefault();
    const pickerIsOpen = this.classToggle(this.colorPicker, 1);

    if (this.isOpen && pickerIsOpen) {
      this.hide(1);
    } else {
      this.showPicker();
    }
  }

  showMenu() {
    this.classToggle(this.colorPicker);
    this.colorMenu.classList.add('show');
    this.show();
    this.menuToggle.setAttribute('aria-expanded', 'true');
  }

  toggleMenu() {
    const menuIsOpen = this.classToggle(this.colorMenu, 1);

    if (this.isOpen && menuIsOpen) {
      this.hide(1);
    } else {
      this.showMenu();
    }
  }

  classToggle(element, check) {
    const fn1 = !check ? 'forEach' : 'some';
    const fn2 = !check ? 'remove' : 'contains';
    this._ = 0;

    if (element) {
      return ['show', 'show-top'][fn1]((x) => element.classList[fn2](x));
    }

    return false;
  }

  show() {
    if (!this.isOpen) {
      const current = document.querySelector('joomla-field-color-picker.open');
      if (current) current.hide(1);

      this.toggleTabIndex(1);

      this.classList.add('open');
      this.toggleEventsOnShown(1);
      this.updateDropdownPosition();
      this.updateDropdownWidth();
      this.isOpen = true;
    }
  }

  hide(focusPrevented) {
    if (this.isOpen) {
      this.toggleEventsOnShown();

      this.toggleTabIndex();

      this.classList.remove('open');

      this.classToggle(this.colorPicker);
      this.pickerToggle.setAttribute('aria-expanded', 'false');

      if (this.colorMenu) {
        this.classToggle(this.colorMenu);
        this.menuToggle.setAttribute('aria-expanded', 'false');
      }

      if (!this.isValid) {
        this.value = this.color.toString();
      }

      this.isOpen = false;

      if (!focusPrevented) {
        this.pickerToggle.focus();
      }
    }
  }
}

customElements.define('joomla-field-color-picker', ColorPicker);