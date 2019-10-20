<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;

/**
 * HTML utility class for creating a sortable table list
 *
 * @since  4.0.0
 */
abstract class JHtmlDraggablelist
{
	/**
	 * Array containing information for loaded files
	 *
	 * @var    array
	 * @since  4.0.0
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
	 * @since   4.0.0
	 *
	 * @throws  InvalidArgumentException
	 */
	public static function draggable(string $tableId = '', string $formId = '', string $sortDir = 'asc', string $saveOrderingUrl = '',
		$redundant = null, bool $nestedList = false
	)
	{
		// Only load once
		if (isset(static::$loaded[__METHOD__]))
		{
			return;
		}

		$doc = Factory::getDocument();

		// Please consider using data attributes instead of passing arguments here!
		if (!empty($tableId) && !empty($saveOrderingUrl) && !empty($formId) && !empty($sortDir))
		{
			$doc->addScriptOptions(
				'draggable-list',
				[
					'id'        => '#' . $tableId . ' tbody',
					'formId'    => $formId,
					'direction' => $sortDir,
					'url'       => $saveOrderingUrl . '&' . Session::getFormToken() . '=1',
					'nested'    => $nestedList,
				]
			);
		}

		// Depends on Joomla.getOptions()
		HTMLHelper::_('behavior.core');

		// Attach draggable to document
		HTMLHelper::_('script', 'vendor/dragula/dragula.min.js', ['framework' => false, 'relative' => true]);
		HTMLHelper::_('script', 'system/draggable.min.js', ['framework' => false, 'relative' => true]);
		HTMLHelper::_('stylesheet', 'vendor/dragula/dragula.min.css', ['framework' => false, 'relative' => true, 'pathOnly' => false]);

		// Set static array
		static::$loaded[__METHOD__] = true;
	}
}
