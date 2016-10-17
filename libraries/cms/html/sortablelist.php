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
 * @since  3.0
 */
abstract class JHtmlSortablelist
{
	/**
	 * @var    array  Array containing information for loaded files
	 * @since  3.0
	 */
	protected static $loaded = array();

	/**
	 * Method to load the Sortable script and make table sortable
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
	 * @since   3.0
	 *
	 * @throws  InvalidArgumentException
	 */
	public static function sortable($tableId, $formId, $sortDir = 'asc', $saveOrderingUrl = null, $proceedSaveOrderButton = true, $nestedList = false)
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

// Attach sortable to document
JHtml::_('script', 'vendor/dragula/dragula.js', false, true);
JHtml::_('stylesheet', 'vendor/dragula/dragula.min.css', false, true, false);
JFactory::getDocument()->addScriptDeclaration(
<<<JS
document.addEventListener('DOMContentLoaded', function() {

	var container = document.querySelector('.js-draggable');

	if (container) {
		var orderRows = container.querySelectorAll('input[name=\"order[]\"]'),
			orderIds = container.querySelectorAll('input[name=\"cid[]\"]'),
			saveOrderingUrl = "$saveOrderingUrl",
			formId = "$formId";

			var orderIds = Array.prototype.slice.call(orderIds);
			var orderRows = Array.prototype.slice.call(orderRows);
			//var arr = arr.reverse();

			// Store the initial values
			var setInitalValues = function () {
				for (var i = 0; i < orderRows.length; i++) {
					orderRows[i].setAttribute('data-initial', i);
				}
				console.log('darag', orderRows)
			}

			setInitalValues();

			var sortedArray = function () {
				console.log('called')
				var orderRows = container.querySelectorAll('input[name=\"order[]\"]');
				for (var i= 0, l = orderRows.length; l > i; i++) {
					var orderPreviousValue = orderRows[i].getAttribute('data-initial');

					
					if (i === orderPreviousValue) {
						orderRows[i].value = 0;
					} else if ( i > orderPreviousValue) {
						orderRows[i].value = 1;
					} else {
						orderRows[i].value = -1; 
					}
					console.log(i, orderPreviousValue, orderRows[i].value)
				}
			}

		// forEach method from http://toddmotto.com/ditch-the-array-foreach-call-nodelist-hack/
		var nodeListForEach = function (array, callback, scope) {
			for (var i = 0; i < array.length; i++) {
				callback.call(scope, i, array[i]);
			}
		};

		cloneMarkedCheckboxes = function () {
			jQuery('[name="order[]"]', container).attr('name', 'order-tmp');
			jQuery('[type=checkbox]', container).each(function () {
				var _shadow = jQuery(this).clone();
				jQuery(_shadow).attr({'checked':'checked', 'shadow':'shadow', 'id':''});
				jQuery('#' + formId).append(jQuery(_shadow));

				jQuery('[name="order-tmp"]', jQuery(this).parents('tr')).attr('name', 'order[]');
			});
		}

		removeClonedCheckboxes = function () {
			jQuery('[shadow=shadow]').remove();
			jQuery('[name="order-tmp"]', container).attr('name', 'order[]');
		}

		var sortableTable = dragula([container]);
		
		// sortableTable.on('drag', function() {
		// 	setInitalValues(orderRows);
		// });

		sortableTable.on('dragend', function() {

			sortedArray(orderRows);

				if (saveOrderingUrl) {
					//clone and check all the checkboxes in sortable range to post
					cloneMarkedCheckboxes();

					// Detach task field if exists
					var f  = jQuery('#' + formId);
					var ft = jQuery('input[name|="task"]', f);

					if (ft.length) ft.detach();

					//serialize form then post to callback url
					jQuery.post(saveOrderingUrl, f.serialize());

					// Re-Append original task field
					if (ft.length) ft.appendTo(f);

					//remove cloned checkboxes
					removeClonedCheckboxes();
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
