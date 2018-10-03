;((customElements, Joomla) => {

	class JoomlaFieldPermissions extends HTMLElement {

	constructor() {
		super();

		if (!Joomla) {
			throw new Error('Joomla API is not properly initiated');
		}

		if (!this.getAttribute('data-uri')) {
			throw new Error('No valid url for validation')
		}
	}

	connectedCallback() {
		const buttonDataSelector = 'data-onchange-task';
		const buttons=[].slice.call(document.querySelectorAll('[' + buttonDataSelector + ']'));

		if(buttons) {
			buttons.forEach((button) => {
				button.addEventListener('change', (e) => {
				e.preventDefault();
				const task = e.target.getAttribute(buttonDataSelector);
					if(task == 'permissions.apply') {
						this.sendPermissions(e);
					}
				});
			});
		}
	}

	sendPermissions(event) {
		const target = event.target;
		//set the icon while storing the values
		const icon = document.getElementById('icon_' + target.id);
		icon.removeAttribute('class');
		icon.setAttribute('class','fa fa-spinner fa-spin');

		//get values add prepare GET-Parameter
		let asset		= 'not';
		let component = this.getUrlParam('component');
		let extension = this.getUrlParam('extension');
		let option    = this.getUrlParam('option');
		let view      = this.getUrlParam('view');
		let title     = component;
		let value     = target.value;
		let context   = '';

		if (document.getElementById('jform_context')) {
			context = document.getElementById('jform_context').value;
			context = context.split('.')[0];
		}

		if(option == 'com_config' && component == false && extension == false) {
			asset = 'root.1';
		} else if (extension == false && view == 'component') {
			asset = component;
		} else if (context) {
			if (view == 'group') {
				asset = context + '.fieldgroup.' + this.getUrlParam('id');
			} else {
				asset = context + '.field.' + this.getUrlParam('id');
			}
			title = document.getElementById('jform_title').value;
		} else if (extension != false && view != false) {
			asset = extension + '.' + view + '.' + this.getUrlParam('id');
			title = document.getElementById('jform_title').value;
		} else if (extension == false && view != false) {
			asset = option + '.' + view + '.' + this.getUrlParam('id');
			title = document.getElementById('jform_title').value;
		}

		const id                  = target.id.replace('jform_rules_', '');
		const lastUnderscoreIndex = id.lastIndexOf('_');

		const permissionData = {
			  comp   : asset,
			  action : id.substring(0, lastUnderscoreIndex),
			  rule   : id.substring(lastUnderscoreIndex + 1),
			  value  : value,
			  title  : title,
		};

		// Remove JS messages, if they exist.
		Joomla.removeMessages();

		// Ajax request
		Joomla.request({
			url: this.getAttribute('data-uri'),
			method: 'POST',
			data: JSON.stringify(permissionData),
			perform: true,
			headers: {'Content-Type': 'application/json'},
			onSuccess: (response, xhr) => {
				try {
					response = JSON.parse(response);
				} catch(e) {
					console.log(e)
				}

				icon.removeAttribute('class');

				// Check if everything is OK
				if (response.data && response.data.result === true) {
					icon.setAttribute('class', 'fa fa-check');

					const badgeSpan = target.parentNode.parentNode.nextElementSibling.querySelector('span');
					badgeSpan.removeAttribute('class');
					badgeSpan.setAttribute('class', response.data['class']);
					badgeSpan.innerHTML = response.data.text;
				}

				// Render messages, if any. There are only message in case of errors.
				if (typeof response.messages === 'object' && response.messages !== null) {
					Joomla.renderMessages(response.messages);

					if (response.data && response.data.result === true) {
						icon.setAttribute('class', 'fa fa-check');
					} else {
						icon.setAttribute('class', 'fa fa-times');
					}
				}
			},
			onError: (xhr) => {
				// Remove the spinning icon.
				icon.removeAttribute('style');

				Joomla.renderMessages(Joomla.ajaxErrorsMessages(jqXHR, textStatus, error));
				icon.setAttribute('class', 'fa fa-times');
			}
		});
	}

	getUrlParam(variable) {
		const query = window.location.search.substring(1);
		const vars = query.split('&');

		for(let i = 0; i < vars.length; i += 1) {
			let pair =vars[i].split('=');
			if(pair[0] == variable) {
				return pair[1];
			}
		}
		return false;
	}
}

customElements.define('joomla-field-permissions', JoomlaFieldPermissions);

})(customElements, Joomla);