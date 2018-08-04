class LanguageFlag extends HTMLElement {
  constructor() {
    super();
  }
  connectedCallback() {
    const style = this.style;
    style.display = 'inline-block';
    style.position = 'relative';
    style.backgroundSize = 'contain';
    style.backgroundPosition = '50%';
    style.backgroundRepeat = 'no-repeat';
    style.height = '1em';

    this.render()
  }

  // @todo use images from: https://github.com/lipis/flag-icon-css
  get path() { return 'https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.1.0/flags'}
  set path(value) {
    this.path = value;
    this.render()
  }

  get country() { return this.getAttribute('country') }
  set country(value) {
    this.setAttribute('country', value);
    this.render()
  }

  render() {
    this.style.backgroundImage = `url(${this.path}/1x1/${this.country.toLowerCase()}.svg)`;
    this.style.width = '1em';
  }
}
customElements.define('joomla-language-flag', LanguageFlag);
