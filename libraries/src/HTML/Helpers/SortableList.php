<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\HTML\HTMLHelper;

/**
 * HTML utility class for creating a sortable table list
 *
 * @since  3.0
 */
abstract class SortableList
{
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
	public static function sortable($tableId, $formId, $sortDir = 'asc', $saveOrderingUrl = null, $proceedSaveOrderButton = true, $nestedList = false)
	{
		HtmlHelper::_('dragablelist.dragable', $tableId, $formId, $sortDir, $saveOrderingUrl, $proceedSaveOrderButton, $nestedList);
	}
}
