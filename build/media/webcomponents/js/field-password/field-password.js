/**
 * PasswordStrength script by Thomas Kjaergaard
 * License: MIT
 * Repo: https://github.com/tkjaergaard/Password-Strength
 *
 * Adapted by Dimitris Grammatikogiannis
 */
class PasswordStrength {
  constructor(settings) {
    // Properties
    this.lowercase = settings.lowercase || 0;
    this.uppercase = settings.uppercase || 0;
    this.numbers = settings.numbers || 0;
    this.special = settings.special || 0;
    this.length = settings.length || 4;

    // Bindings
    this.getScore = this.getScore.bind(this);
    this.calc = this.calc.bind(this);
  }

  getScore(value) {
    let score = 0;
    let mods = 0;

    ['lowercase', 'uppercase', 'numbers', 'special', 'length'].forEach((val) => {
      if (this.hasOwnProperty(val) && this[val] > 0) {
        mods = mods + 1;
      }
    });

    score += this.calc(value, /[a-z]/g, this.lowercase, mods);
    score += this.calc(value, /[A-Z]/g, this.uppercase, mods);
    score += this.calc(value, /[0-9]/g, this.numbers, mods);
    score += this.calc(value, /[\$\!\#\?\=\;\:\*\-\_\€\%\&\(\)\`\´]/g, this.special, mods);

    if (mods === 1) {
      score += value.length > this.length ? 100 : 100 / this.length * value.length;
    } else {
      score += value.length > this.length ? (100 / mods) : (100 / mods) / this.length * value.length;
    }

    return score;
  };

  calc(value, pattern, length, mods) {
    const count = value.match(pattern);

    if (count && count.length > length && length !== 0) {
      return 100 / mods;
    }

    if (count && length > 0) {
      return (100 / mods) / length * count.length;
    } else {
      return 0;
    }
  }
}

class JoomlaFieldPassword extends HTMLElement {
  static get observedAttributes() {
    return ['min-length', 'min-integers', 'min-symbols', 'min-uppercase', 'min-lowercase', 'reveal', 'text-show', 'text-hide', 'text-complete', 'text-incomplete'];
  }

  get minLength() { return parseInt(this.getAttribute('min-length') || 0); }
  get minIntegers() { return parseInt(this.getAttribute('min-integers') || 0); }
  get minSymbols() { return parseInt(this.getAttribute('min-symbols') || 0); }
  get minUppercase() { return parseInt(this.getAttribute('min-uppercase') || 0); }
  get minLowercase() { return parseInt(this.getAttribute('min-lowercase') || 0); }
  get reveal() { return this.getAttribute('reveal') || false; }
  get showText() { return this.getAttribute('text-show') || 'Show'; }
  get hideText() { return this.getAttribute('text-hide') || 'Hide'; }
  get completeText() { return this.getAttribute('text-complete') || 'Password meets site\'s requirements'; }
  get incompleteText() { return this.getAttribute('text-incomplete') || 'Password does not meet site\'s requirements'; }

  attributeChangedCallback(attr, oldValue, newValue) {}

  constructor() {
    super();

    // Properties
    this.button = '';
    this.meterLabel = '';
    this.meter = '';
    this.isVisible = false;

    // Bindings
    this.childrenChange = this.childrenChange.bind(this);
    this.buildElements = this.buildElements.bind(this);
    this.handler = this.handler.bind(this);
    this.getMeter = this.getMeter.bind(this);
    this.debounce = this.debounce.bind(this);


    // Watch for children changes.
    // eslint-disable-next-line no-return-assign
    new MutationObserver((mutations) => this.childrenChange(mutations))
      .observe(this, { childList: true });
  }

  connectedCallback() {
    this.buildElements()
  }

  disconnectedCallback() {
    if (this.input) {
      this.input.removeEventListener(this.getMeter, this);
    }

    if (this.reveal === 'true' && this.button) {
      this.button.parentNode.removeChild(this.button);
    }

    if ((this.minLength && this.minLength > 0)
      || (this.minIntegers && this.minIntegers > 0)
      || (this.minSymbols && this.minSymbols > 0)
      || (this.minUppercase && this.minUppercase > 0)
      || (this.minLowercase && this.minLowercase > 0)) {
      if (this.meterLabel) {
        this.meterLabel.parentNode.removeChild(this.meterLabel);
      }

      if (this.meter !== '') {
        this.meter.parentNode.removeChild(this.meter);
      }
    }
  }

  buildElements() {
    // Ensure the first child is an input with a password type.
    if (this.firstElementChild
      && this.firstElementChild.tagName
      && this.firstElementChild.tagName.toLowerCase() === 'input'
      && this.firstElementChild.getAttribute('type')
      && this.firstElementChild.getAttribute('type') === 'password'
      && this.firstElementChild.getAttribute('id')) {
      this.input = this.firstElementChild;
      this.input.classList.add('joomla-field-password__flex-item');
    }

    if (this.reveal === 'true') {
      this.button = document.createElement('button');
      this.button.classList.add('joomla-field-password__hide');
      this.button.classList.add('joomla-field-password__flex-item');
      this.button.setAttribute('type', 'button');
      this.appendChild(this.button);

      this.button.addEventListener('click', () => {
        if (this.input.type === 'password'){
          this.isVisible = true;
          this.input.type = 'text';
          this.button.classList.remove('joomla-field-password__hide');
          this.button.classList.add('joomla-field-password__show');
        } else {
          this.isVisible = false;
          this.button.classList.remove('joomla-field-password__show');
          this.button.classList.add('joomla-field-password__hide');
          this.input.type = 'password';
        }
        this.button.innerHTML = `<span class="joomla-field-password__sr-only">${this.isVisible ? this.showText : this.hideText}</span>`;
      });
    }

    // Meter is enabled
    if (this.minLength && this.minLength > 0
      ||this.minIntegers && this.minIntegers > 0
      ||this.minSymbols && this.minSymbols > 0
      ||this.minUppercase && this.minUppercase > 0
      ||this.minLowercase && this.minLowercase > 0
    ) {

      const i = Math.random().toString(36).substr(2, 9);

      /** Create a progress meter **/
      this.meter = document.createElement('progress');
      this.meter.setAttribute('class', 'joomla-field-password__flex-item');
      this.meter.setAttribute('max', 100);
      this.meter.setAttribute('min', 0);
      this.meter.setAttribute('value', 0);

      /** Create the label for A11Y **/
      this.meterLabel = document.createElement('div');
      this.meterLabel.setAttribute('class', 'joomla-field-password__flex-item joomla-field-password__text-xs-center');
      this.meterLabel.setAttribute('id', 'password-' + i);

      this.insertAdjacentElement('beforeend', this.meterLabel);
      this.insertAdjacentElement('beforeend', this.meter);

      /** Add a listener for input data change **/
      this.input.addEventListener('keyup', this.getMeter);

      this.getMeter();

      // Set the validation handler
      // @TODO refactor the validation.js to reflect the changes here!
      this.setAttribute('validation-handler', 'password-strength' + '_' + Math.random().toString(36).substr(2, 9));

      if (document.formvalidator) {
        document.formvalidator.setHandler(this.getAttribute('validation-handler'), this.handler);
      }
    }
  }
  childrenChange(mutations) {
    mutations.forEach(function(mutation){
      if (mutation.addedNodes.length
        && mutation.addedNodes[0].tagName
        && mutation.addedNodes[0].tagName.toLowerCase() === 'input') {
        this.buildElements();
      }
    });
  }

  /** Method to check the input and set the meter **/
  getMeter() {
    if (!this.meter || !this.meterLabel) {
      return;
    }

    const strength = new PasswordStrength({
      lowercase: this.minLowercase ? this.minLowercase : 0,
      uppercase: this.minUppercase ? this.minUppercase : 0,
      numbers: this.minIntegers ? this.minIntegers : 0,
      special: this.minSymbols ? this.minSymbols : 0,
      length: this.minLength ? this.minLength : 4
    });

    const score = strength.getScore(this.input.value);

    if (score > 79) {
      this.meter.setAttribute('value', score);
      this.meterLabel.innerHTML = this.completeText;
    }
    if (score > 64 && score < 80) {
      this.meter.setAttribute('value', score);
      this.meterLabel.innerHTML = this.incompleteText;
    }
    if (score > 50 && score < 65) {
      this.meter.setAttribute('value', score);
      this.meterLabel.innerHTML = this.incompleteText;
    }
    if (score > 40 && score < 51) {
      this.meter.setAttribute('value', score);
      this.meterLabel.innerHTML = this.incompleteText;
    }
    if (score < 41) {
      this.meter.setAttribute('value', score);
      this.meterLabel.innerHTML = this.incompleteText;
    }

    if (!this.input.value.length) {
      this.meterLabel.innerHTML = '';
      this.input.setAttribute('required', '');
    }
  }

  handler(value) {
    const strength = new PasswordStrength({
      lowercase: this.minLowercase ? this.minLowercase : 0,
      uppercase: this.minUppercase ? this.minUppercase : 0,
      numbers  : this.minIntegers ? this.minIntegers : 0,
      special  : this.minSymbols ? this.minSymbols : 0,
      length   : this.minLength ? this.minLength : 4
    });

    if (strength.getScore(value) === 100) {
      return true;
    }

    return false;
  }

  /**
   * debounce function
   * use inDebounce to maintain internal reference of timeout to clear
   */
  debounce(func, delay) {
    let inDebounce;
    return function() {
      const context = this;
      const args = arguments;
      clearTimeout(inDebounce);
      inDebounce = setTimeout(() => func.apply(context, args), delay)
    }
  }
}

customElements.define('joomla-field-password', JoomlaFieldPassword);
