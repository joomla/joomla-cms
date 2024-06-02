/**
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
window.customElements.define('joomla-field-permissions', class extends HTMLElement {
  constructor() {
    super();

    if (!Joomla) {
      throw new Error('Joomla API is not properly initiated');
    }

    if (!this.getAttribute('data-uri')) {
      throw new Error('No valid url for validation');
    }

    this.query = window.location.search.substring(1);
    this.buttons = '';
    this.buttonDataSelector = 'data-onchange-task';
    this.onDropdownChange = this.onDropdownChange.bind(this);
    this.getUrlParam = this.getUrlParam.bind(this);

    this.component = this.getUrlParam('component');
    this.extension = this.getUrlParam('extension');
    this.option = this.getUrlParam('option');
    this.view = this.getUrlParam('view');
    this.asset = 'not';
    this.context = '';
  }

  /**
   * Lifecycle
   */
  connectedCallback() {
    this.buttons = document.querySelectorAll(`[${this.buttonDataSelector}]`);
    if (this.buttons) {
      this.buttons.forEach((button) => {
        button.addEventListener('change', this.onDropdownChange);
      });
    }
  }

  /**
   * Lifecycle
   */
  disconnectedCallback() {
    if (this.buttons) {
      this.buttons.forEach((button) => {
        button.removeEventListener('change', this.onDropdownChange);
      });
    }
  }

  /**
   * Lifecycle
   */
  onDropdownChange(event) {
    event.preventDefault();
    const task = event.target.getAttribute(this.buttonDataSelector);
    if (task === 'permissions.apply') {
      this.sendPermissions(event);
    }
  }

  sendPermissions(event) {
    const { target } = event;

    // Set the icon while storing the values
    const icon = document.getElementById(`icon_${target.id}`);
    icon.removeAttribute('class');
    icon.setAttribute('class', 'joomla-icon joomla-field-permissions__spinner');

    // Get values add prepare GET-Parameter
    const { value } = target;

    if (document.getElementById('jform_context')) {
      this.context = document.getElementById('jform_context').value;
      [this.context] = this.context.split('.');
    }

    if (this.option === 'com_config' && !this.component && !this.extension) {
      this.asset = 'root.1';
    } else if (!this.extension && this.view === 'component') {
      this.asset = this.component;
    } else if (this.context) {
      if (this.view === 'group') {
        this.asset = `${this.context}.fieldgroup.${this.getUrlParam('id')}`;
      } else {
        this.asset = `${this.context}.field.{this.getUrlParam('id')}`;
      }
      this.title = document.getElementById('jform_title').value;
    } else if (this.extension && this.view) {
      this.asset = `${this.extension}.${this.view}.${this.getUrlParam('id')}`;
      this.title = document.getElementById('jform_title').value;
    } else if (!this.extension && this.view) {
      this.asset = `${this.option}.${this.view}.${this.getUrlParam('id')}`;
      this.title = document.getElementById('jform_title').value;
    }

    const id = target.id.replace('jform_rules_', '');
    const lastUnderscoreIndex = id.lastIndexOf('_');

    const permissionData = {
      comp: this.asset,
      action: id.substring(0, lastUnderscoreIndex),
      rule: id.substring(lastUnderscoreIndex + 1),
      value,
      title: this.title,
    };

    // Remove JS messages, if they exist.
    Joomla.removeMessages();

    // Ajax request
    Joomla.request({
      url: this.getAttribute('data-uri'),
      method: 'POST',
      data: JSON.stringify(permissionData),
      perform: true,
      headers: { 'Content-Type': 'application/json' },
      onSuccess: (data) => {
        let response;

        try {
          response = JSON.parse(data);
        } catch (e) {
          // eslint-disable-next-line no-console
          console.error(e);
        }

        icon.removeAttribute('class');

        // Check if everything is OK
        if (response.data && response.data.result) {
          icon.setAttribute('class', 'joomla-icon joomla-field-permissions__allowed');

          const badgeSpan = target.parentNode.parentNode.nextElementSibling.querySelector('span');
          badgeSpan.removeAttribute('class');
          badgeSpan.setAttribute('class', response.data.class);
          badgeSpan.innerHTML = Joomla.sanitizeHtml(response.data.text);
        }

        // Render messages, if any. There are only message in case of errors.
        if (typeof response.messages === 'object' && response.messages !== null) {
          Joomla.renderMessages(response.messages);

          if (response.data && response.data.result) {
            icon.setAttribute('class', 'joomla-icon joomla-field-permissions__allowed');
          } else {
            icon.setAttribute('class', 'joomla-icon joomla-field-permissions__denied');
          }
        }
      },
      onError: (xhr) => {
        // Remove the spinning icon.
        icon.removeAttribute('style');

        Joomla.renderMessages(Joomla.ajaxErrorsMessages(xhr, xhr.statusText));
        icon.setAttribute('class', 'joomla-icon joomla-field-permissions__denied');
      },
    });
  }

  getUrlParam(variable) {
    const vars = this.query.split('&');
    let i = 0;

    for (i; i < vars.length; i += 1) {
      const pair = vars[i].split('=');
      if (pair[0] === variable) {
        return pair[1];
      }
    }
    return false;
  }
});
