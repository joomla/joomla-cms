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
abstract class JHtmlDraggablelist
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
	public static function draggable($tableId, $formId, $sortDir = 'asc', $saveOrderingUrl = null, $proceedSaveOrderButton = true, $nestedList = false)
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

		// Attach draggable to document
		JHtml::_('script', 'vendor/dragula/dragula.min.js', false, true);
		JHtml::_('script', 'system/draggable.js', false, true);
		JHtml::_('stylesheet', 'vendor/dragula/dragula.min.css', false, true, false);

		JFactory::getDocument()->addScriptOptions(
			'draggable-list',
			array(
				'id'         => '#' . $tableId . ' tbody',
				'formId'     => $formId,
				'direction'  => $sortDir,
				'url'        => $saveOrderingUrl,
				'options'    => '',
				'nestedList' => $nestedList
			)
		);

		// Set static array
		static::$loaded[__METHOD__] = true;
	}
}
