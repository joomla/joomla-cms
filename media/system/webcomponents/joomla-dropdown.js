/** Include the relative styles */
if (!document.head.querySelector('#joomla-dropdown-style')) {
  const style = document.createElement('style');
  style.id = 'joomla-dropdown-style';
  style.innerHTML = `joomla-dropdown{display:none}joomla-dropdown[expanded]{position:absolute;top:100%;left:-240px;z-index:1000;display:block;width:20rem;min-width:10rem;padding:.5rem 0;margin:.125rem 0 0;font-size:1rem;color:#292b2c;text-align:left;list-style:none;background-color:#fff;background-clip:padding-box;border:1px solid rgba(0,0,0,.15);border-radius:.25rem}`;
  document.head.appendChild(style);
}

class JoomlaDropdownElement extends HTMLElement {
  static get observedAttributes() {
    return ['for'];
  }

  get for() { return this.getAttribute('for'); }
  set for(value) { return this.setAttribute('for', value); }

  connectedCallback() {
    this.setAttribute('aria-labelledby', this.for.substring(1));
    const button = document.querySelector(this.for);
    const self = this;
    const innerLinks = [].slice.call(this.childNodes)
    if (!button.id) return;

    button.setAttribute('aria-haspopup', 'true');
    button.setAttribute('aria-expanded', 'false');

    button.addEventListener('click', (ev) => {
      let el = ev.target;
      if (ev.target.tagName.toLowerCase() === 'span') {
        el = ev.target.parentNode;
      }

      if (self.hasAttribute('expanded')) {
        self.removeAttribute('expanded');
        el.setAttribute('aria-expanded', 'false');
      } else {
        self.setAttribute('expanded', '');
        el.setAttribute('aria-expanded', 'true');
      }

      innerLinks.forEach((link) => {
        if (link.tagName && link.tagName.toLowerCase !== 'a') {
          return;
        }
        link.addEventListener('click', self.close.bind(self))
       });

      document.addEventListener('click', (evt) => {
        if (evt.target === button) {
          return;
        }
        if (button.childNodes.length && [].slice.call(button.childNodes).indexOf(evt.target) > -1) {
          return;
        }

        self.close();
      });

      innerLinks.forEach((innerLink) => {
        innerLink.addEventListener('click', () => {
          self.close();
        });
      });
    });
  }

  /*eslint-disable */
  disconnectedCallback() { }

  adoptedCallback(oldDocument, newDocument) { }


  attributeChangedCallback(attr, oldValue, newValue) {
    switch (attr) {
      // case 'name':
      // console.log(newValue);
      // break;
    }
  }
  /*eslint-enable */

  close() {
    const button = document.querySelector(`#${this.getAttribute('aria-labelledby')}`);
    this.removeAttribute('expanded');
    button.setAttribute('aria-expanded', 'false');
  }
}

customElements.define('joomla-dropdown', JoomlaDropdownElement);
