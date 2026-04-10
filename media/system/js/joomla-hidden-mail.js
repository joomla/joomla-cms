/**
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
window.customElements.define('joomla-hidden-mail', class extends HTMLElement {
  constructor() {
    super();
    this.newElement = '';
    this.base = '';
  }

  /**
   * Lifecycle
   */
  disconnectedCallback() {
    this.innerHTML = '';
  }

  /**
   * Lifecycle
   */
  connectedCallback() {
    this.base = `${this.getAttribute('base')}/`;
    if (this.getAttribute('is-link') === '1') {
      this.newElement = document.createElement('a');
      this.newElement.setAttribute('href', `mailto:${this.constructor.b64DecodeUnicode(this.getAttribute('first'))}@${this.constructor.b64DecodeUnicode(this.getAttribute('last'))}`);

      // Get all of the original element attributes, and pass them to the link
      [].slice.call(this.attributes).forEach((attribute, index) => {
        const {
          nodeName
        } = this.attributes.item(index);
        if (nodeName) {
          // We do care for some attributes
          if (['is-link', 'is-email', 'first', 'last', 'text'].indexOf(nodeName) === -1) {
            const {
              nodeValue
            } = this.attributes.item(index);
            this.newElement.setAttribute(nodeName, nodeValue);
          }
        }
      });
    } else {
      this.newElement = document.createElement('span');
    }
    if (this.getAttribute('text')) {
      let innerStr = this.constructor.b64DecodeUnicode(this.getAttribute('text'));
      innerStr = innerStr.replace('src="images/', `src="${this.base}images/`).replace('src="media/', `src="${this.base}media/`);
      this.newElement.innerHTML = Joomla.sanitizeHtml(innerStr);
    } else {
      this.newElement.innerText = `${window.atob(this.getAttribute('first'))}@${window.atob(this.getAttribute('last'))}`;
    }

    // Remove class and style Attributes
    this.removeAttribute('class');
    this.removeAttribute('style');

    // Remove the noscript message
    this.innerText = '';

    // Display the new element
    this.appendChild(this.newElement);
  }
  static b64DecodeUnicode(str) {
    return decodeURIComponent(Array.prototype.map.call(atob(str), c => `%${`00${c.charCodeAt(0).toString(16)}`.slice(-2)}`).join(''));
  }
});
