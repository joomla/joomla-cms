<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Search.categories
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');

/**
 * Categories search plugin.
 *
 * @since  1.6
 */
class PlgSearchCategories extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Determine areas searchable by this plugin.
	 *
	 * @return  array  An array of search areas.
	 *
	 * @since   1.6
	 */
	public function onContentSearchAreas()
	{
		static $areas = array(
			'categories' => 'PLG_SEARCH_CATEGORIES_CATEGORIES'
		);

		return $areas;
	}

	/**
	 * Search content (categories).
	 *
	 * The SQL must return the following fields that are used in a common display
	 * routine: href, title, section, created, text, browsernav.
	 *
	 * @param   string  $text      Target search string.
	 * @param   string  $phrase    Matching option (possible values: exact|any|all).  Default is "any".
	 * @param   string  $ordering  Ordering option (possible values: newest|oldest|popular|alpha|category).  Default is "newest".
	 * @param   mixed   $areas     An array if the search is to be restricted to areas or null to search all areas.
	 *
	 * @return  array  Search results.
	 *
	 * @since   1.6
	 */
	public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
	{
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$searchText = $text;

		if (is_array($areas))
		{
			if (!array_intersect($areas, array_keys($this->onContentSearchAreas())))
			{
				return array();
			}
		}

		$sContent = $this->params->get('search_content', 1);
		$sArchived = $this->params->get('search_archived', 1);
		$limit = $this->params->def('search_limit', 50);
		$state = array();

		if ($sContent)
		{
			$state[] = 1;
		}

		if ($sArchived)
		{
			$state[] = 2;
		}

		if (empty($state))
		{
			return array();
		}

		$text = trim($text);

		if ($text == '')
		{
			return array();
		}

		/* TODO: The $where variable does not seem to be used at all
		switch ($phrase)
		{
			case 'exact':
				$text = $db->quote('%' . $db->escape($text, true) . '%', false);
				$wheres2 = array();
				$wheres2[] = 'a.title LIKE ' . $text;
				$wheres2[] = 'a.description LIKE ' . $text;
				$where = '(' . implode(') OR (', $wheres2) . ')';
				break;

			case 'any':
			case 'all';
			default:
				$words = explode(' ', $text);
				$wheres = array();
				foreach ($words as $word)
				{
					$word = $db->quote('%' . $db->escape($word, true) . '%', false);
					$wheres2 = array();
					$wheres2[] = 'a.title LIKE ' . $word;
					$wheres2[] = 'a.description LIKE ' . $word;
					$wheres[] = implode(' OR ', $wheres2);
				}
				$where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
				break;
		}
		*/

		switch ($ordering)
		{
			case 'alpha':
				$order = 'a.title ASC';
				break;

			case 'category':
			case 'popular':
			case 'newest':
			case 'oldest':
			default:
				$order = 'a.title DESC';
		}

		$text = $db->quote('%' . $db->escape($text, true) . '%', false);
		$query = $db->getQuery(true);

		$case_when = ' CASE WHEN ' . $query->charLength('a.alias', '!=', '0')
			. ' THEN ' . $query->concatenate(array($query->castAsChar('a.id'), 'a.alias'), ':')
			. ' ELSE a.id END AS slug';

		$query->select('a.title, a.description AS text, a.created_time AS created')
			->select($db->quote('2') . ' AS browsernav')
			->select('a.id AS catid')
			->select($case_when)
			->from($db->quoteName('#__categories', 'a'))
			->where(
				'(a.title LIKE ' . $text . ' OR a.description LIKE ' . $text . ') AND a.published IN (' . implode(',', $state) . ') AND a.extension = '
				. $db->quote('com_content') . 'AND a.access IN (' . $groups . ')'
			)
			->group('a.id, a.title, a.description, a.alias, a.created_time')
			->order($order);

		if ($app->isClient('site') && JLanguageMultilang::isEnabled())
		{
			$query->where('a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		}

		$db->setQuery($query, 0, $limit);

		try
		{
			$rows = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$rows = array();
			JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}

		$return = array();

		if ($rows)
		{
			foreach ($rows as $i => $row)
			{

				if (searchHelper::checkNoHtml($row, $searchText, array('name', 'title', 'text')))
				{
					$row->href = ContentHelperRoute::getCategoryRoute($row->slug);
					$row->section = JText::_('JCATEGORY');

					$return[] = $row;
				}
			}
		}

		return $return;
	}
}
