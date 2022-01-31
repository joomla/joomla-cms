/**
 * @package         Joomla.JavaScript
 * @copyright       (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */
customElements.define('joomla-field-module-order', class extends HTMLElement {
  constructor() {
    super();

    this.linkedFieldSelector = '';
    this.linkedFieldElement = '';
    this.originalPosition = '';

    this.writeDynaList.bind(this);
    this.getNewOrder.bind(this);
  }

  connectedCallback() {
    this.linkedFieldSelector = this.getAttribute('data-linked-field') || 'jform_position';

    if (!this.linkedFieldSelector) {
      throw new Error('No linked field defined!');
    }

    this.linkedFieldElement = document.getElementById(this.linkedFieldSelector);

    if (!this.linkedFieldElement) {
      throw new Error('No linked field defined!');
    }

    const that = this;
    this.originalPosition = this.linkedFieldElement.value;

    /** Initialize the field * */
    this.getNewOrder(this.originalPosition);

    /** Watch for changes on the linked field * */
    this.linkedFieldElement.addEventListener('change', () => {
      that.originalPosition = that.linkedFieldElement.value;
      that.getNewOrder(that.linkedFieldElement.value);
    });
  }

  writeDynaList(selectProperties, source, originalPositionName, originalPositionValue) {
    let i = 0;
    const selectNode = document.createElement('select');
    if (this.hasAttribute('disabled')) {
      selectNode.setAttribute('disabled', '');
    }

    if (this.getAttribute('onchange')) {
      selectNode.setAttribute('onchange', this.getAttribute('onchange'));
    }

    if (this.getAttribute('size')) {
      selectNode.setAttribute('size', this.getAttribute('size'));
    }

    selectNode.classList.add(selectProperties.itemClass);
    selectNode.setAttribute('name', selectProperties.name);
    selectNode.id = selectProperties.id;

    // eslint-disable-next-line no-restricted-syntax
    for (const x in source) {
      // eslint-disable-next-line no-prototype-builtins
      if (!source.hasOwnProperty(x)) {
        // eslint-disable-next-line no-continue
        continue;
      }

      const node = document.createElement('option');
      const item = source[x];

      // eslint-disable-next-line prefer-destructuring
      node.value = item[1];
      // eslint-disable-next-line prefer-destructuring
      node.innerHTML = Joomla.sanitizeHtml(item[2]);

      if ((originalPositionName && originalPositionValue === item[1])
        || (!originalPositionName && i === 0)) {
        node.setAttribute('selected', 'selected');
      }

      selectNode.appendChild(node);
      i += 1;
    }

    this.innerHTML = '';
    this.appendChild(selectNode);
  }

  getNewOrder(originalPosition) {
    const url = this.getAttribute('data-url');
    const clientId = this.getAttribute('data-client-id');
    const originalOrder = this.getAttribute('data-ordering');
    const name = this.getAttribute('data-name');
    const attr = this.getAttribute('data-client-attr') ? this.getAttribute('data-client-attr') : 'form-select';
    const id = `${this.getAttribute('data-id')}`;
    const moduleId = `${this.getAttribute('data-module-id')}`;
    const orders = [];
    const that = this;

    Joomla.request({
      url: `${url}&client_id=${clientId}&position=${originalPosition}&module_id=${moduleId}`,
      method: 'GET',
      perform: true,
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      onSuccess(resp) {
        if (resp) {
          let response;
          try {
            response = JSON.parse(resp);
          } catch (e) {
            // eslint-disable-next-line no-console
            console.error(e);
          }

          /** Check if everything is OK * */
          if (response.data.length > 0) {
            for (let i = 0; i < response.data.length; i += 1) {
              orders[i] = response.data[i].split(',');
            }

            that.writeDynaList(
              {
                name,
                id,
                itemClass: attr,
              },
              orders,
              that.originalPosition,
              originalOrder,
            );
          }
        }

        /** Render messages, if any. There are only message in case of errors. * */
        if (typeof resp.messages === 'object' && resp.messages !== null) {
          Joomla.renderMessages(resp.messages);
        }
      },
    });
  }
});
