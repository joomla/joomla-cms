<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
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
		if ($saveOrderingUrl === null)
		{
			throw new InvalidArgumentException(sprintf('$saveOrderingUrl is a required argument in %s()', __METHOD__));
		}

		$displayData = array(
			'tableId'                => $tableId,
			'formId'                 => $formId,
			'sortDir'                => $sortDir,
			'saveOrderingUrl'        => $saveOrderingUrl,
			'nestedList'             => $nestedList,
			'proceedSaveOrderButton' => $proceedSaveOrderButton,
		);

		JLayoutHelper::render('joomla.html.sortablelist', $displayData);

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
	 *
	 * @deprecated 4.0 The logic is merged in the JLayout file
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
