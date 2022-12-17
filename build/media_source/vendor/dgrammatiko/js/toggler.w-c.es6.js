// https://github.com/dgrammatiko/dark-switch/blob/master/src/index.js
export class Switcher extends HTMLElement {
  constructor() {
    super();

    this.onClick = this.onClick.bind(this);
    this.systemQuery = this.systemQuery.bind(this);
  }

  static get observedAttributes() { return ['value', 'text-on', 'text-off', 'text']; }

  get default() { return this.getAttribute('default'); }
  get on() { return this.getAttribute('text-on') || 'on'; }
  set on(value) { this.setAttribute('text-on', value); }
  get off() { return this.getAttribute('text-off') || 'off'; }
  set off(value) { this.setAttribute('text-off', value); }
  get legend() { return this.getAttribute('text-legend') || 'dark theme:'; }
  set legend(value) { this.setAttribute('text-legend', value); }

  attributeChangedCallback(attr, oldValue, newValue) {
    switch (attr) {
      case 'text-on':
        if (this.span && this.state == 'dark') {
          this.span.innerText = newValue;
        }
        break;
      case 'text-off':
        if (this.span && this.state == 'light') {
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
    this.darkModeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    this.supportsMediaColorScheme = window.matchMedia('(prefers-color-scheme)').media !== 'not all' ? true : false;
    this.html = document.documentElement;
    this.state = this.default || 'light';
    this.render();

    if (this.supportsMediaColorScheme) {
      this.darkModeMediaQuery.addListener(this.systemQuery);
    }
  }

  systemQuery(event) {
    this.state = event.matches === true ? 'dark' : 'light';
  }

  disconnectedCallback() {
    if (this.supportsMediaColorScheme) {
      this.darkModeMediaQuery.removeListener(this.systemQuery);
    }
    if (this.button) {
      this.button.removeEventListener('click', this.onClick)
    }
  }

  onClick() {
    const inverted = this.state === 'light' ? 'dark' : 'light';
    this.syncValues(inverted).then(() => {
      this.state = inverted;
      this.button.setAttribute('aria-pressed', this.state == 'dark' ? 'true' : 'false');
      this.html.setAttribute('data-bs-theme', inverted === 'dark' ? 'dark' : 'light');
      window.dispatchEvent(new CustomEvent('joomla:toggle-theme', { detail: { prefersColorScheme: inverted } }));
      if (navigator.cookieEnabled) {
        document.cookie = `atumPrefersColorScheme=${inverted};`;
      }
    }).catch(() => { return; });
  }

  syncValues(value = 'light') {
    const urlBase = Joomla.getOptions('system.paths').baseFull;
    return fetch(new URL(`${urlBase}index.php?option=com_users&task=user.setA11ySettings&format=json`), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': Joomla.getOptions('csrf.token', ''),
      },
      body: JSON.stringify({data: {prefersColorScheme: value}}),
      redirect: 'follow',
    });
  }

  render() {
    if (!this.button) {
      this.button = document.createElement('button');
      this.button.innerText = this.legend;
      this.button.setAttribute('tabindex', 0)
      this.button.setAttribute('aria-pressed', this.state == 'dark' ? 'true' : 'false');
      this.span = document.createElement('span');
      this.span.setAttribute('aria-hidden', 'true');
      this.span.innerText = this.state == 'dark' ? this.on : this.off;
      this.button.appendChild(this.span);

      this.button.addEventListener('click', this.onClick)

      this.appendChild(this.button)
    }
  }
}

if (!customElements.get('joomla-theme-switch')) {
  customElements.define('joomla-theme-switch', Switcher);
}
