

/**
 * @package         Joomla.JavaScript
 * @copyright       Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

customElements.define('joomla-field-module-order', class extends HTMLElement {
	constructor() {
		super();

		this.linkedField = this.getAttribute('data-linked-field') || 'jform_position';
		this.linkedFieldEl = '';
		this.originalPos = '';

		this.writeDynaList.bind(this);
		this.getNewOrder.bind(this);
	}

	connectedCallback() {
		this.linkedFieldEl = document.getElementById(this.linkedField);

		if (!this.linkedFieldEl) {
			throw new Error('No linked field defined!')
		}

		const that = this;
		this.originalPos = this.linkedFieldEl.value;

		/** Initialize the field **/
		this.getNewOrder(this.originalPos);

		/** Watch for changes on the linked field **/
		this.linkedFieldEl.addEventListener('change', () => {
			that.originalPos = that.linkedFieldEl.value;
			that.getNewOrder(that.linkedFieldEl.value);
		});
	}

	writeDynaList (selectParams, source, key, orig_val) {
		const selectNode = document.createElement('select');

		selectNode.classList.add(selectParams.itemClass);
		selectNode.setAttribute('name', selectParams.name);
		selectNode.id = selectParams.id;

		let hasSelection = key;
		let i = 0;
		let x;
		let item;

		for ( x in source ) {
			if (!source.hasOwnProperty(x)) { continue; }

			item = source[ x ];

			node = document.createElement('option');
			node.value = item[1];

			node.innerHTML = item[ 2 ];

			if ( ( hasSelection && orig_val == item[ 1 ] ) || ( !hasSelection && i === 0 ) ) {
				node.setAttribute('selected', 'selected');
			}

			selectNode.appendChild(node);
			i++;
		}

		this.innerHTML = '';
		this.appendChild(selectNode);
	}

	getNewOrder (originalPos) {
		const url = this.getAttribute('data-url');
		const clientId = this.getAttribute('data-client-id');
		const originalOrder = this.getAttribute('data-ordering');
		const name = this.getAttribute('data-name');
		const attr = this.getAttribute('data-client-attr') ? this.getAttribute('data-client-attr') : 'custom-select';
		const id = this.getAttribute('id') + '_1';
		const orders = [];
		const that = this;

		Joomla.request(
			{
				url: url,
				method: 'GET',
				data: 'client_id=' + clientId + '&position=' + originalPos,
				perform: true,
				headers: {'Content-Type': 'application/x-www-form-urlencoded'},
				onSuccess: function(response, xhr)
				{
					if (response)
					{
						response = JSON.parse(response);

						/** Check if everything is OK **/
						if (response.data.length > 0)
						{
							for (let i = 0; i < response.data.length; ++i) {
								orders[i] = response.data[i].split(',');
							}

							that.writeDynaList(
								{
									name: name,
									id: id,
									itemClass: attr
								},
								orders,
								that.originalPos,
								originalOrder,
							);
						}
					}

					/** Render messages, if any. There are only message in case of errors. **/
					if (typeof response.messages == 'object' && response.messages !== null)
					{
						Joomla.renderMessages(response.messages);
						window.scrollTo(0, 0);
					}
				}
			}
		);
	}
});
