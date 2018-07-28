/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function() {
	"use strict";
	Joomla.submitbutton = function (task, type) {
		if (task == 'item.setType' || task == 'item.setMenuType') {
			if (task == 'item.setType') {
				var list = document.querySelectorAll('#item-form input[name="jform[type]"]');
				list.forEach(function (item) {
					item.value = type;
				});

				document.getElementById('fieldtype').value = 'type';
			} else {
				var list = document.querySelectorAll('#item-form input[name="jform[menutype]"]');
				list.forEach(function (item) {
					item.value = type;
				});
			}
			Joomla.submitform('item.setType', document.getElementById('item-form'));
		} else if (task == 'item.cancel' || document.formvalidator.isValid(document.getElementById('item-form'))) {
			Joomla.submitform(task, document.getElementById('item-form'));
		} else {
			// special case for modal popups validation response
			var list = document.querySelectorAll('#item-form .modal-value.invalid');

			list.forEach(function (field) {
				var idReversed        = field.getAttribute('id').split('').reverse().join(''),
				    separatorLocation = idReversed.indexOf('_'),
				    nameId            = idReversed.substr(separatorLocation).split('').reverse().join('') + 'name';
				document.getElementById(nameId).classList.add('invalid');
			});
		}
	};

	document.addEventListener('DOMContentLoaded', function () {
		document.getElementById('jform_menutype').addEventListener('change', function (event) {
			var menutype = event.target.value;

			Joomla.request({
				url    : 'index.php?option=com_menus&task=item.getParentItem&menutype=' + menutype,
				headers: {'Content-Type': 'application/json'},

				onSuccess: function (response, xhr) {
					var data = JSON.parse(response);
					var list = document.querySelectorAll('#jform_parent_id option');
					list.forEach(function (item) {
						if (item != '1') {
							item.remove();
						}
					});

					data.forEach(function (val) {
						var option       = document.createElement('option');
						option.innerText = value;
						option.id        = val.id;
						document.getElementById('jform_parent_id').appendChild(option);
					});
					document.getElementById('jform_parent_id').trigger('change');
				},
				onError  : function (xhr) {
					Joomla.renderMessages(Joomla.ajaxErrorsMessages(xhr));
				}
			});
		});
	});
})();
