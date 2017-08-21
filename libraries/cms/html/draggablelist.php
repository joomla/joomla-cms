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
	 * Array containing information for loaded files
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $loaded = array();

	/**
	 * Method to load the Dragula script and make table sortable
	 *
	 * @param   string   $tableId          DOM id of the table
	 * @param   string   $formId           DOM id of the form
	 * @param   string   $sortDir          Sort direction
	 * @param   string   $saveOrderingUrl  Save ordering url, ajax-load after an item dropped
	 * @param   string   $redundant        Not used
	 * @param   boolean  $nestedList       Set whether the list is a nested list
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @throws  InvalidArgumentException
	 */
	public static function draggable($tableId = null, $formId = null, $sortDir = 'asc', $saveOrderingUrl = null, $redundant = null, $nestedList = false)
	{
		// Only load once
		if (isset(static::$loaded[__METHOD__]))
		{
			return;
		}

		$doc = JFactory::getDocument();

		// Please consider using data attributes instead of passing arguments here!
		if (!empty($tableId) && !empty($saveOrderingUrl) && !empty($formId) && !empty($sortDir))
		{
			$doc->addScriptOptions(
				'draggable-list',
				array(
					'id'         => '#' . $tableId . ' tbody',
					'formId'     => $formId,
					'direction'  => $sortDir,
					'url'        => $saveOrderingUrl . '&' . JSession::getFormToken() . '=1',
					'nested'     => $nestedList
				)
			);
		}

		// Depends on Joomla.getOptions()
		JHtml::_('behavior.core');

		// Attach draggable to document
		JHtml::_('script', 'vendor/dragula/dragula.min.js', false, true);
		JHtml::_('script', 'system/draggable.min.js', false, true);
		JHtml::_('stylesheet', 'vendor/dragula/dragula.min.css', false, true, false);

		// Set static array
		static::$loaded[__METHOD__] = true;
	}
}
