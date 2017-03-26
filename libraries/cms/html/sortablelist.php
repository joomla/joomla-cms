<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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

		// Depends on Joomla.getOptions()
		JHtml::_('behavior.core');

		// Depends on jQuery UI
		JHtml::_('jquery.ui', array('core', 'sortable'));

		JHtml::_('script', 'system/legacy/sortablelist.min.js', false, true);
		JHtml::_('stylesheet', 'system/sortablelist.css', false, true, false);

		// Attach sortable to document
		JFactory::getDocument()->addScriptOptions(
			'sortable-list',
			array(
				'id'         => '#' . $tableId . ' tbody',
				'formId'     => $formId,
				'direction'  => $sortDir,
				'url'        => $saveOrderingUrl,
				'options'    => '',
				'nestedList' => $nestedList,
				'button'     => $proceedSaveOrderButton
			)
		);

		// Set static array
		static::$loaded[__METHOD__] = true;
	}
}
