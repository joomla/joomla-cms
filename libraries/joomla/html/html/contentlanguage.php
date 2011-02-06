<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  HTML
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class working with content language select lists
 *
 * @static
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
abstract class JHtmlContentLanguage
{
	/**
	 * @var	array	Cached array of the content language items.
	 */
	protected static $items = null;

	/**
	 * Get a list of the available content language items.
	 *
	 * @return	string
	 * @since	1.6
	 */
	public static function existing($all = false, $translate=false)
	{
		if (empty(self::$items)) {
			// Get the database object and a new query object.
			$db		= JFactory::getDBO();
			$query	= $db->getQuery(true);

			// Build the query.
			$query->select('a.lang_code AS value, a.title AS text, a.title_native');
			$query->from('#__languages AS a');
			$query->where('a.published >= 0');
			$query->order('a.title');

			// Set the query and load the options.
			$db->setQuery($query);
			self::$items = $db->loadObjectList();
			if ($all) {
				array_unshift(self::$items, new JObject(array('value'=>'*','text'=>$translate ? JText::alt('JALL','language') : 'JALL_LANGUAGE')));
			}

			// Detect errors
			if ($db->getErrorNum()) {
				JError::raiseWarning(500, $db->getErrorMsg());
			}
		}
		return self::$items;
	}
}
