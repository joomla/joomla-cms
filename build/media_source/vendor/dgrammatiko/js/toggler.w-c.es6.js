// https://github.com/dgrammatiko/dark-switch/blob/master/src/index.js
export class Switcher extends HTMLElement {
  constructor() {
    super();

    this.darkModeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    this.supportsMediaColorScheme = window.matchMedia('(prefers-color-scheme)').media !== 'not all' ? true : false;
    this.onClick = this.onClick.bind(this);
    this.systemQuery = this.systemQuery.bind(this);
  }

  static get observedAttributes() { return ['value', 'text-on', 'text-off', 'text']; }

  get value() { return this.getAttribute('value'); }
  set value(value) { this.setAttribute('value', value); }
  get default() { return this.getAttribute('default'); }
  get on() { return this.getAttribute('text-on') || 'on'; }
  set on(value) { this.setAttribute('text-on', value); }
  get off() { return this.getAttribute('text-off') || 'off'; }
  set off(value) { this.setAttribute('text-off', value); }
  get legend() { return this.getAttribute('text-legend') || 'dark theme:'; }
  set legend(value) { this.setAttribute('text-legend', value); }

  attributeChangedCallback(attr, oldValue, newValue) {
    switch (attr) {
      case 'value':
        if (['true', 'false'].indexOf(newValue) < 0) {
          this.setAttribute('value', oldValue);
          break;
        }
        if (newValue === 'true') {
          localStorage.setItem('darkthemeswitcher', 'true');
          this.state = 'true'
          if (this.button) {
            this.button.setAttribute('aria-pressed', 'true');
            this.span.innerText = this.on;
            this.html.setAttribute('data-bs-theme', 'dark');
          }
          break;
        }
        if (newValue === 'false') {
          localStorage.setItem('darkthemeswitcher', 'false');
          this.state = 'false'
          if (this.button) {
            this.button.setAttribute('aria-pressed', 'false');
            this.html.setAttribute('data-bs-theme', 'light');
            this.span.innerText = this.off
          }
          break;
        }
        break;
      case 'text-on':
        if (this.span && this.state == 'true') {
          this.span.innerText = newValue;
        }
        break;
      case 'text-off':
        if (this.span && this.state == 'false') {
          this.span.innerText = newValue
        }
        break;
      case 'text-legend':
        if (this.button) {
          this.button.innerText = newValue
        }
        break;
    }
  }

  connectedCallback() {
    this.html = document.documentElement;
    this.calcInitialState()
    this.render();
    this.value = this.state;

    if (this.supportsMediaColorScheme) {
      this.darkModeMediaQuery.addListener(this.systemQuery);
    }
  }

  systemQuery(event) {
    this.value = event.matches === true ? 'true' : 'false';
  }
  disconnectedCallback() {
    if (this.supportsMediaColorScheme) {
      this.darkModeMediaQuery.removeListener(this.systemQuery);
    }
    if (this.button) {
      this.button.removeEventListener('click', this.onClick)
    }
  }

  calcInitialState() {
    this.state = localStorage.getItem('darkthemeswitcher');
    if (!this.default && !this.state) {
      if (this.supportsMediaColorScheme) {
        this.state = matchMedia("('prefers-color-scheme': 'light'),('prefers-color-scheme':'no-preference')").matches ? 'false' : 'true';
        this.value = this.state;
      } else {
        this.state = 'false';
        this.value = 'false';
      }
      return;
    }
    if (this.default && !this.state) {
      this.value = this.default;
      return;
    }
    if (this.state) {
      return;
    }
  }

  onClick() {
    const inverted = this.value === 'false' ? 'true' : 'false';
    this.value = inverted;
    this.html.setAttribute('data-bs-theme', inverted === 'true' ? 'dark' : 'light')
    this.dispatchEvent(new Event('joomla:toggle-theme'));
  }

  render() {
    if (!this.button) {
      this.button = document.createElement('button');
      this.button.innerText = this.legend;
      this.button.setAttribute('tabindex', 0)
      this.button.setAttribute('aria-pressed', this.state == 'true' ? 'true' : 'false')
      this.span = document.createElement('span');
      this.span.setAttribute('aria-hidden', 'true');
      this.span.innerText = this.state == 'true' ? this.on : this.off;
      this.button.appendChild(this.span);

      this.button.addEventListener('click', this.onClick)

      this.appendChild(this.button)
    }
  }
}

if (!customElements.get('joomla-theme-switch')) {
  customElements.define('joomla-theme-switch', Switcher);
}
