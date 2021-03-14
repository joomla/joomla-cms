((customElements) => {
  'strict';

  /**
   * Creates a custom element with the default spinner of the Joomla logo
   */
  class JoomlaCoreLoader extends HTMLElement {
    constructor() {
      super();

      const template = document.createElement('template');
      template.innerHTML = `<style>{{CSS_CONTENTS_PLACEHOLDER}}</style>
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
})(customElements);
