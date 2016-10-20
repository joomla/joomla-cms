<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * HTML utility class for creating a sortable table list
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class JHtmlDragablelist
{
	/**
	 * @var    array  Array containing information for loaded files
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $loaded = array();

	/**
	 * Method to load the Dragula script and make table sortable
	 *
	 * @param   string   $tableId                 DOM id of the table
	 * @param   string   $formId                  DOM id of the form
	 * @param   string   $sortDir                 Sort direction
	 * @param   string   $saveOrderingUrl         Save ordering url, ajax-load after an item dropped
	 * @param   boolean  $proceedSaveOrderButton  Set whether a save order button is displayed
	 * @param   boolean  $nestedList              Set whether the list is a nested list
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @throws  InvalidArgumentException
	 */
	public static function dragable($tableId, $formId, $sortDir = 'asc', $saveOrderingUrl = null, $proceedSaveOrderButton = true, $nestedList = false)
	{
		// Only load once
		if (isset(static::$loaded[__METHOD__]))
		{
			return;
		}

		// Note: $i is required but has to be an optional argument in the function call due to argument order
		if (null === $saveOrderingUrl)
		{
			throw new InvalidArgumentException('$saveOrderingUrl is a required argument in JHtmlSortablelist::sortable');
		}

		$saveOrderingUrl = JUri::root(true) . $saveOrderingUrl;

		// Depends on Joomla.getOptions()
		JHtml::_('behavior.core');

		// Attach dragable to document
		JHtml::_('script', 'vendor/dragula/dragula.js', false, true);
		JHtml::_('stylesheet', 'vendor/dragula/dragula.min.css', false, true, false);
		JFactory::getDocument()->addScriptDeclaration(
<<<JS
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
			var container = document.querySelector('.js-draggable');

	if (container) {
		var saveOrderingUrl = "$saveOrderingUrl",
			formId = "$formId"
			sortableTable = dragula([container]);

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
		}

		removeIds = function (form) {
			var i, l, inputs = form.querySelectorAll('[shadow="shadow"]');

			for(i = 0, l = inputs.length; l>i; i++) {
				inputs[i].parentNode.removeChild(inputs[i]);
			}
		}

		sortableTable.on('dragend', function() {
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
});
JS
		);

		// Set static array
		static::$loaded[__METHOD__] = true;
	}
}
