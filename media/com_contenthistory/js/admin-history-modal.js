/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (){
	document.addEventListener('DOMContentLoaded', function () {
		document.getElementById('toolbar-load').addEventListener('click', function() {
			var ids = document.querySelectorAll('input[id*="cb"]:checked');
			if (ids.length == 1) {
				// Add version item id to URL
				var url = document.getElementById('toolbar-load').getAttribute('data-url') + '&version_id=' + ids[0].value;
				document.getElementById('content-url').setAttribute('data-url', url);
				if (window.parent) {
					window.parent.location = url;
				}
			} else {
				alert(Joomla.JText._('COM_CONTENTHISTORY_BUTTON_SELECT_ONE'));
			}
		});

		document.getElementById('toolbar-preview').addEventListener('click', function() {
			var windowSizeArray = ['width=800, height=600, resizable=yes, scrollbars=yes'],
			    ids = document.querySelectorAll('input[id*="cb"]:checked');
			if (ids.length == 1) {
				// Add version item id to URL
				var url = document.getElementById('toolbar-preview').getAttribute('data-url') + '&version_id=' + ids[0].value;
				document.getElementById('content-url').setAttribute('data-url', url);
				if (window.parent) {
					window.open(url, '', windowSizeArray);
					return false;
				}
			} else {
				alert(Joomla.JText._('COM_CONTENTHISTORY_BUTTON_SELECT_ONE'));
			}
		});

		document.getElementById('toolbar-compare').addEventListener('click', function() {
			var windowSizeArray = ['width=1000, height=600, resizable=yes, scrollbars=yes'],
			    ids = document.querySelectorAll('input[id*="cb"]:checked');
			if (ids.length == 2) {
				// Add version item ids to URL
				var url = document.getElementById('toolbar-compare').getAttribute('data-url') + '&id1=' + ids[0].value + '&id2=' + ids[1].value;
				document.getElementById('content-url').setAttribute('data-url', url);
				if (window.parent) {
					window.open(url, '', windowSizeArray);
					return false;
				}
			} else {
				alert(Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));
			}
		});
	});
})();