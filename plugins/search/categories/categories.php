<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Search.categories
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once JPATH_SITE . '/components/com_content/helpers/route.php';

/**
 * Categories Search plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Search.categories
 * @since       1.6
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
	 * @return array An array of search areas
	 */
	public function onContentSearchAreas()
	{
		static $areas = array(
			'categories' => 'PLG_SEARCH_CATEGORIES_CATEGORIES'
		);
		return $areas;
	}

	/**
	 * Categories Search method
	 *
	 * The sql must return the following fields that are
	 * used in a common display routine: href, title, section, created, text,
	 * browsernav
	 *
	 * @param string Target search string
	 * @param string mathcing option, exact|any|all
	 * @param string ordering option, newest|oldest|popular|alpha|category
	 * @param mixed  An array if restricted to areas, null if search all
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

		//sqlsrv changes
		$case_when = ' CASE WHEN ';
		$case_when .= $query->charLength('a.alias', '!=', '0');
		$case_when .= ' THEN ';
		$a_id = $query->castAsChar('a.id');
		$case_when .= $query->concatenate(array($a_id, 'a.alias'), ':');
		$case_when .= ' ELSE ';
		$case_when .= $a_id . ' END as slug';
		$query->select('a.title, a.description AS text, \'\' AS created, \'2\' AS browsernav, a.id AS catid, ' . $case_when)
			->from('#__categories AS a')
			->where(
				'(a.title LIKE ' . $text . ' OR a.description LIKE ' . $text . ') AND a.published IN (' . implode(',', $state) . ') AND a.extension = ' . $db->quote('com_content')
					. 'AND a.access IN (' . $groups . ')'
			)
			->group('a.id, a.title, a.description, a.alias')
			->order($order);
		if ($app->isSite() && JLanguageMultilang::isEnabled())
		{
			$query->where('a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		}

		$db->setQuery($query, 0, $limit);
		$rows = $db->loadObjectList();

		$return = array();
		if ($rows)
		{
			$count = count($rows);
			for ($i = 0; $i < $count; $i++)
			{
				$rows[$i]->href = ContentHelperRoute::getCategoryRoute($rows[$i]->slug);
				$rows[$i]->section = JText::_('JCATEGORY');
			}

			foreach ($rows as $category)
			{
				if (searchHelper::checkNoHTML($category, $searchText, array('name', 'title', 'text')))
				{
					$return[] = $category;
				}
			}
		}

		return $return;
	}
}
