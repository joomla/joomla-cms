

/**
 * @package         Joomla.JavaScript
 * @copyright       Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

customElements.define('joomla-field-module-order', class extends HTMLElement {
	constructor() {
		super();

		if (!window.Joomla) {
			throw new Error('Joomla API is not properly initialised');
		}

		this.linkedField = this.getAttribute('data-linked-field') || 'jform_position';
		this.linkedFieldEl = '';
		this.originalPos = '';

		this.writeDynaList.bind(this);
		this.getNewOrder.bind(this);
	}

	connectedCallback() {
		this.originalPos = this.linkedFieldEl.value;
		this.linkedFieldEl = document.getElementById(this.linkedField);

		/** Initialize the field on document ready **/
		this.getNewOrder(this.originalPos);

		this.linkedFieldEl.addEventListener('change', () => {
			this.originalPos = this.linkedFieldEl.value;
			this.getNewOrder(this.originalPos);
		});
	}

	disconnectedCallback() {

	}

	writeDynaList (selectParams, source, key, orig_val) {
		let node = '';
		const selectNode = document.createElement('select');

		selectNode.classList.add(selectParams.itemClass);
		selectNode.setAttribute('name', selectParams.name);
		selectNode.id = selectParams.id;

		this.innerHTML = '';
		this.appendChild(selectNode);

		var hasSelection = key,
		    i = 0,
		    selected, x, item;

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
			selectNode.parentNode.innerHtml = '';
			selectNode.parentNode.appendChild(selectNode);

			i++;
		}
	}

	getNewOrder (originalPos) {
		console.log('dfgd')
		var url = this.getAttribute('data-url'),
		    clientId = this.getAttribute('data-client-id'),
		    element = document.getElementById(this.getAttribute('data-element')),
		    originalOrder = this.getAttribute('data-ordering'),
		    name = this.getAttribute('data-name'),
		    attr = this.getAttribute('data-client-attr') ? this.getAttribute('data-client-attr') : 'custom-select',
		    id = this.getAttribute('id') + '_1',
		    orders = [],
		    that = this;

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
							for (var i = 0; i < response.data.length; ++i) {
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
