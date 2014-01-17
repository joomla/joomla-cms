<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class working with content language select lists
 *
 * @package     Joomla.Libraries
 * @subpackage  HTML
 * @since       1.6
 */
abstract class JHtmlContentLanguage
{
	/**
	 * Cached array of the content language items.
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected static $items = null;

	/**
	 * Get a list of the available content language items.
	 *
	 * @param   boolean  $all        True to include All (*)
	 * @param   boolean  $translate  True to translate All
	 *
	 * @return  string
	 *
	 * @see     JFormFieldContentLanguage
	 * @since   1.6
	 */
	public static function existing($all = false, $translate = false)
	{
		if (empty(static::$items))
		{
			// Get the database object and a new query object.
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Build the query.
			$query->select('a.lang_code AS value, a.title AS text, a.title_native')
				->from('#__languages AS a')
				->where('a.published >= 0')
				->order('a.title');

			// Set the query and load the options.
			$db->setQuery($query);
			static::$items = $db->loadObjectList();
		}

		if ($all)
		{
			$all_option = array(new JObject(array('value' => '*', 'text' => $translate ? JText::alt('JALL', 'language') : 'JALL_LANGUAGE')));

			return array_merge($all_option, static::$items);
		}
		else
		{
			return static::$items;
		}
	}
}
