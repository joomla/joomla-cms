function serialize(form) {
	if (!form || form.nodeName !== "FORM") {
		return;
	}
	var i, j, q = [];
	for (i = form.elements.length - 1; i >= 0; i = i - 1) {
		if (form.elements[i].name === "") {
			continue;
		}
		switch (form.elements[i].nodeName) {
			case 'INPUT':
				switch (form.elements[i].type) {
					case 'text':
					case 'hidden':
					case 'password':
					case 'button':
					case 'reset':
					case 'submit':
						q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
						break;
					case 'checkbox':
					case 'radio':
						if (form.elements[i].checked) {
							q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
						}
						break;
					case 'file':
						break;
				}
				break;
			case 'TEXTAREA':
				q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
				break;
			case 'SELECT':
				switch (form.elements[i].type) {
					case 'select-one':
						q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
						break;
					case 'select-multiple':
						for (j = form.elements[i].options.length - 1; j >= 0; j = j - 1) {
							if (form.elements[i].options[j].selected) {
								q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].options[j].value));
							}
						}
						break;
				}
				break;
			case 'BUTTON':
				switch (form.elements[i].type) {
					case 'reset':
					case 'submit':
					case 'button':
						q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
						break;
				}
				break;
		}
	}
	return q.join("&");
}

document.addEventListener('DOMContentLoaded', function() {
	if (Joomla.getOptions('draggable-list')) {

		var options = Joomla.getOptions('draggable-list');

		// IOS 10 BUG
		document.addEventListener("touchstart", function() {},false);

		// This is here to make the transition to new forms easier.
		// Should be removed when everybody' using the default class!
		if (!document.querySelector(options.id).classList.contains('js-draggable')) {
			document.querySelector(options.id).classList.add('js-draggable');
		}

		// The container where the draggable will be enabled
		var container = document.querySelector('.js-draggable');

		if (container) {
			var saveOrderingUrl = options.url,
				formId = options.formId,
				sortableTable = dragula(
					[container],
					{
						direction: 'vertical',               // Y axis is considered when determining where an element would be dropped
						copy: false,                         // elements are moved by default, not copied
						copySortSource: false,               // elements in copy-source containers can be reordered
						revertOnSpill: true,                 // spilling will put the element back where it was dragged from, if this is true
						removeOnSpill: false                 // spilling will `.remove` the element, if this is true
						// mirrorContainer: document.body,    // set the element that gets mirror elements appended
						// ignoreInputTextSelection: true     // allows users to select input text, see details below
					}
				);

			var sortedArray = function () {
				var orderRows = container.querySelectorAll('input[name="order[]"]');

				for (var i= 0, l = orderRows.length; l > i; i++) {
					orderRows[i].value = i;
				}
			};

			cloneIds = function (form) {
				var i, l, _shadow, inputs = form.querySelectorAll('[name="cid[]"]');

				for(i = 0, l = inputs.length; l>i; i++) {
					_shadow = inputs[i].cloneNode();
					_shadow.setAttribute('checked', 'checked');
					_shadow.setAttribute('shadow', 'shadow');
					_shadow.setAttribute('id', '');
					form.append(_shadow);
				}
			};

			removeIds = function (form) {
				var i, l, inputs = form.querySelectorAll('[shadow="shadow"]');

				for(i = 0, l = inputs.length; l>i; i++) {
					inputs[i].parentNode.removeChild(inputs[i]);
				}
			};

			sortableTable.on('drop', function(event) {
				console.log(event);

				sortedArray();

				if (saveOrderingUrl) {
					// Set the form
					var form  = document.querySelector('#' + formId);

					// Detach task field if exists
					var task = document.querySelector('[name="task"]');

					//clone and check all the checkboxes in sortable range to post
					cloneIds(form);

					// Detach task field if exists
					if (task) {
						task.setAttribute('name', 'some__Temporary__Name__');
					}

					// Prepare the options
					var ajaxOptions = {
						url:    saveOrderingUrl,
						method: 'POST',
						data:    serialize(form),
						perform: true
					};

					Joomla.request(ajaxOptions);

					// Re-Append original task field
					if (task) {
						task.setAttribute('name', 'task');
					}

					//remove cloned checkboxes
					removeIds(form);
				}
			});
		}
	}
});
