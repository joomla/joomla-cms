const darkModeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
const notForced = () => !('colorSchemeOs' in document.documentElement.dataset);
const lightColor = 'rgba(255, 255, 255, 0.8)';
const darkColor = 'rgba(0, 0, 0, 0.8)';
const getColorScheme = () => {
  if (notForced()) {
    return darkModeMediaQuery.matches ? darkColor : lightColor;
  }

  if ('colorScheme' in document.documentElement.dataset) {
    return document.documentElement.dataset.colorScheme === 'dark' ? darkColor : lightColor;
  }

  return darkModeMediaQuery.matches ? darkColor : lightColor;
};

/**
 * Creates a custom element with the default spinner of the Joomla logo
 */
class JoomlaCoreLoader extends HTMLElement {
  get inline() { return this.hasAttribute('inline'); }

  set inline(value) {
    if (value !== null) {
      this.setAttribute('inline', '');
    } else {
      this.removeAttribute('inline');
    }
  }

  get size() { return this.getAttribute('size') || '345'; }

  set size(value) { this.setAttribute('size', value); }

  get color() { return this.getAttribute('color'); }

  set color(value) { this.setAttribute('color', value); }

  static get observedAttributes() {
    return ['color', 'size', 'inline'];
  }

  constructor() {
    super();
    this.attachShadow({ mode: 'open' });

    const template = document.createElement('template');
    template.innerHTML = `
    <style>{{CSS_CONTENTS_PLACEHOLDER}}</style>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 150 150" width="${this.size}" height="${this.size}">
      <style>@keyframes joomla-spinner{0%,28%,to{opacity:.30}20%{opacity:1}}.joomla-spinner{animation:joomla-spinner 1.6s infinite cubic-bezier(0,.15,1,.75)}
      </style>
      <path d="m27 75.5-2.9-2.9c-8.9-8.9-11.7-21.7-8.3-33C6.9 37.6.2 29.6.2 20.1c0-11.1 9-20 20-20 10 0 18.2 7.3 19.8 16.8 10.8-2.5 22.6.4 31.1 8.8l1.2 1.2-14.9 14.7-1.1-1.2c-4.8-4.8-12.6-4.8-17.4 0-4.8 4.8-4.8 12.6 0 17.4l2.9 2.9 14.8 14.8 15.6 15.6-14.8 14.8-15.6-15.7L27 75.5z" class="joomla-spinner" style="animation-delay:-1.2s" fill="#7ac143" />
      <path d="m43.5 58.9 15.6-15.6 14.8-14.8 2.9-2.9c8.9-8.9 21.6-11.7 32.8-8.4C111 7.5 119.4 0 129.5 0c11.1 0 20 9 20 20 0 10.2-7.6 18.6-17.4 19.9 3.2 11.2.4 23.8-8.4 32.7l-1.2 1.2L107.7 59l1.1-1.1c4.8-4.8 4.8-12.6 0-17.4-4.8-4.8-12.5-4.8-17.4 0l-2.9 2.9-14.6 14.7-15.6 15.6-14.8-14.8z" class="joomla-spinner" style="animation-delay:-.8s" fill="#f9a541" />
      <path d="M110.1 133.5c-11.4 3.5-24.2.7-33.2-8.3l-1.1-1.1 14.8-14.8 1.1 1.1c4.8 4.8 12.6 4.8 17.4 0 4.8-4.8 4.8-12.5 0-17.4l-2.9-2.9-14.9-14.6-15.6-15.7L90.5 45l15.6 15.6 14.8 14.8 2.9 2.9c8.5 8.5 11.4 20.5 8.8 31.3 9.7 1.4 17.2 9.7 17.2 19.8 0 11.1-9 20-20 20-9.8.2-17.9-6.7-19.7-15.9z" class="joomla-spinner" style="animation-delay:-.4s" fill="#f44321" />
      <path d="m104.3 92-15.6 15.6-14.8 14.8-2.9 2.9c-8.5 8.5-20.6 11.4-31.5 8.7-2 8.9-10 15.5-19.5 15.5-11.1 0-20-9-20-20 0-9.5 6.6-17.4 15.4-19.5-2.8-11 .1-23.1 8.7-31.7l1.1-1.1L40 92l-1.1 1.1c-4.8 4.8-4.8 12.6 0 17.4 4.8 4.8 12.6 4.8 17.4 0l2.9-2.9L74 92.8l15.6-15.6L104.3 92z" class="joomla-spinner" fill="#5091cd" />
    </svg>`;

    this.shadowRoot.appendChild(template.content.cloneNode(true));
  }

  connectedCallback() {
    this.style.backgroundColor = this.color ? this.color : getColorScheme();

    darkModeMediaQuery.addEventListener('change', this.systemQuery);

    if (!this.inline) {
      this.classList.add('fullscreen');
    }
  }

  disconnectedCallback() {
    darkModeMediaQuery.removeEventListener('change', this.systemQuery);
  }

  attributeChangedCallback(attr, oldValue, newValue) {
    switch (attr) {
      case 'color':
        if (newValue && newValue !== oldValue) {
          this.style.backgroundColor = newValue;
        }
        break;
      case 'size':
        if (newValue && newValue !== oldValue) {
          const svg = this.shadowRoot.querySelector('svg');
          svg.setAttribute('width', newValue);
          svg.setAttribute('height', newValue);
        }
        break;
      case 'inline':
        if (this.hasAttribute('inline')) {
          this.classList.remove('fullscreen');
        } else {
          this.classList.add('fullscreen');
        }
        break;
      default:
        break;
    }
  }

  systemQuery(event) {
    if (!notForced() || this.color) return;
    const color = event.matches === true ? darkColor : lightColor;
    if (this.style.backgroundColor !== color) {
      this.style.backgroundColor = color;
    }
  }
}

window.customElements.define('joomla-core-loader', JoomlaCoreLoader);
