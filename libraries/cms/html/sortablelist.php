<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * HTML utility class for creating a sortable table list
 *
 * @package     Joomla.Libraries
 * @subpackage  HTML
 * @since       3.0
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
	 */
	public static function sortable($tableId, $formId, $sortDir = 'asc', $saveOrderingUrl, $proceedSaveOrderButton = true, $nestedList = false)
	{
		// Only load once
		if (isset(static::$loaded[__METHOD__]))
		{
			return;
		}

		// Depends on jQuery UI
		JHtml::_('jquery.ui', array('core', 'sortable'));

		JHtml::_('script', 'jui/sortablelist.js', false, true);
		JHtml::_('stylesheet', 'jui/sortablelist.css', false, true, false);

		// Attach sortable to document
		JFactory::getDocument()->addScriptDeclaration("
			(function ($){
				$(document).ready(function (){
					var sortableList = new $.JSortableList('#" . $tableId . " tbody','" . $formId . "','" . $sortDir . "' , '" . $saveOrderingUrl . "','','" . $nestedList . "');
				});
			})(jQuery);
			"
		);

		if ($proceedSaveOrderButton)
		{
			static::_proceedSaveOrderButton();
		}

		// Set static array
		static::$loaded[__METHOD__] = true;
		return;
	}

	/**
	 * Method to inject script for enabled and disable Save order button
	 * when changing value of ordering input boxes
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function _proceedSaveOrderButton()
	{
		JFactory::getDocument()->addScriptDeclaration(
			"(function ($){
				$(document).ready(function (){
					var saveOrderButton = $('.saveorder');
					saveOrderButton.css({'opacity':'0.2', 'cursor':'default'}).attr('onclick','return false;');
					var oldOrderingValue = '';
					$('.text-area-order').focus(function ()
					{
						oldOrderingValue = $(this).attr('value');
					})
					.keyup(function (){
						var newOrderingValue = $(this).attr('value');
						if (oldOrderingValue != newOrderingValue)
						{
							saveOrderButton.css({'opacity':'1', 'cursor':'pointer'}).removeAttr('onclick')
						}
					});
				});
			})(jQuery);"
		);
		return;
	}
}
