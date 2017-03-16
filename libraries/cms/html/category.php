<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Utility class for categories
 *
 * @since  1.5
 */
abstract class JHtmlCategory
{
	/**
	 * Cached array of the category items.
	 *
	 * @var    array
	 * @since  1.5
	 */
	protected static $items = array();

	/**
	 * Returns an array of categories for the given extension.
	 *
	 * @param   string  $extension  The extension option e.g. com_something.
	 * @param   array   $config     An array of configuration options. By default, only
	 *                              published and unpublished categories are returned.
	 *
	 * @return  array
	 *
	 * @since   1.5
	 */
	public static function options($extension, $config = array('filter.published' => array(0, 1)))
	{
		$hash = md5($extension . '.' . serialize($config));

		if (!isset(static::$items[$hash]))
		{
			$config = (array) $config;
			$db     = JFactory::getDbo();
			$user   = JFactory::getUser();
			$groups = implode(',', $user->getAuthorisedViewLevels());

			$query = $db->getQuery(true)
				->select('a.id, a.title, a.level, a.language')
				->from('#__categories AS a')
				->where('a.parent_id > 0');

			// Filter on extension.
			$query->where('extension = ' . $db->quote($extension));
			
			// Filter on user access level
			$query->where('a.access IN (' . $groups . ')');

			// Filter on the published state
			if (isset($config['filter.published']))
			{
				if (is_numeric($config['filter.published']))
				{
					$query->where('a.published = ' . (int) $config['filter.published']);
				}
				elseif (is_array($config['filter.published']))
				{
					$config['filter.published'] = ArrayHelper::toInteger($config['filter.published']);
					$query->where('a.published IN (' . implode(',', $config['filter.published']) . ')');
				}
			}

			// Filter on the language
			if (isset($config['filter.language']))
			{
				if (is_string($config['filter.language']))
				{
					$query->where('a.language = ' . $db->quote($config['filter.language']));
				}
				elseif (is_array($config['filter.language']))
				{
					foreach ($config['filter.language'] as &$language)
					{
						$language = $db->quote($language);
					}

					$query->where('a.language IN (' . implode(',', $config['filter.language']) . ')');
				}
			}

			// Filter on the access
			if (isset($config['filter.access']))
			{
				if (is_string($config['filter.access']))
				{
					$query->where('a.access = ' . $db->quote($config['filter.access']));
				}
				elseif (is_array($config['filter.access']))
				{
					foreach ($config['filter.access'] as &$access)
					{
						$access = $db->quote($access);
					}

					$query->where('a.access IN (' . implode(',', $config['filter.access']) . ')');
				}
			}

			$query->order('a.lft');

			$db->setQuery($query);
			$items = $db->loadObjectList();

			// Assemble the list options.
			static::$items[$hash] = array();

			foreach ($items as &$item)
			{
				$repeat = ($item->level - 1 >= 0) ? $item->level - 1 : 0;
				$item->title = str_repeat('- ', $repeat) . $item->title;

				if ($item->language !== '*')
				{
					$item->title .= ' (' . $language . ')';
				}

				static::$items[$hash][] = JHtml::_('select.option', $item->id, $item->title);
			}
		}

		return static::$items[$hash];
	}

	/**
	 * Returns an array of categories for the given extension.
	 *
	 * @param   string  $extension  The extension option.
	 * @param   array   $config     An array of configuration options. By default, only published and unpublished categories are returned.
	 *
	 * @return  array   Categories for the extension
	 *
	 * @since   1.6
	 */
	public static function categories($extension, $config = array('filter.published' => array(0, 1)))
	{
		$hash = md5($extension . '.' . serialize($config));

		if (!isset(static::$items[$hash]))
		{
			$config = (array) $config;
			$user = JFactory::getUser();
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('a.id, a.title, a.level, a.parent_id')
				->from('#__categories AS a')
				->where('a.parent_id > 0');

			// Filter on extension.
			$query->where('extension = ' . $db->quote($extension));
			
			// Filter on user level.
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');

			// Filter on the published state
			if (isset($config['filter.published']))
			{
				if (is_numeric($config['filter.published']))
				{
					$query->where('a.published = ' . (int) $config['filter.published']);
				}
				elseif (is_array($config['filter.published']))
				{
					$config['filter.published'] = ArrayHelper::toInteger($config['filter.published']);
					$query->where('a.published IN (' . implode(',', $config['filter.published']) . ')');
				}
			}

			$query->order('a.lft');

			$db->setQuery($query);
			$items = $db->loadObjectList();

			// Assemble the list options.
			static::$items[$hash] = array();

			foreach ($items as &$item)
			{
				$repeat = ($item->level - 1 >= 0) ? $item->level - 1 : 0;
				$item->title = str_repeat('- ', $repeat) . $item->title;
				static::$items[$hash][] = JHtml::_('select.option', $item->id, $item->title);
			}
			// Special "Add to root" option:
			static::$items[$hash][] = JHtml::_('select.option', '1', JText::_('JLIB_HTML_ADD_TO_ROOT'));
		}

		return static::$items[$hash];
	}
}
