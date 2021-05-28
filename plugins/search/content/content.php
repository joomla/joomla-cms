<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Search.content
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Content search plugin.
 *
 * @since  1.6
 */
class PlgSearchContent extends JPlugin
{
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
			'content' => 'JGLOBAL_ARTICLES'
		);

		return $areas;
	}

	/**
	 * Search content (articles).
	 * The SQL must return the following fields that are used in a common display
	 * routine: href, title, section, created, text, browsernav.
	 *
	 * @param   string  $text      Target search string.
	 * @param   string  $phrase    Matching option (possible values: exact|any|all).  Default is "any".
	 * @param   string  $ordering  Ordering option (possible values: newest|oldest|popular|alpha|category).  Default is "newest".
	 * @param   mixed   $areas     An array if the search it to be restricted to areas or null to search all areas.
	 *
	 * @return  array  Search results.
	 *
	 * @since   1.6
	 */
	public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
	{
		$db         = JFactory::getDbo();
		$serverType = $db->getServerType();
		$app        = JFactory::getApplication();
		$user       = JFactory::getUser();
		$groups     = implode(',', $user->getAuthorisedViewLevels());
		$tag        = JFactory::getLanguage()->getTag();

		JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');
		JLoader::register('SearchHelper', JPATH_ADMINISTRATOR . '/components/com_search/helpers/search.php');

		$searchText = $text;

		if (is_array($areas) && !array_intersect($areas, array_keys($this->onContentSearchAreas())))
		{
			return array();
		}

		$sContent  = $this->params->get('search_content', 1);
		$sArchived = $this->params->get('search_archived', 1);
		$limit     = $this->params->def('search_limit', 50);

		$nullDate  = $db->getNullDate();
		$date      = JFactory::getDate();
		$now       = $date->toSql();

		$text = trim($text);

		if ($text === '')
		{
			return array();
		}

		switch ($phrase)
		{
			case 'exact':
				$text      = $db->quote('%' . $db->escape($text, true) . '%', false);
				$wheres2   = array();
				$wheres2[] = 'a.title LIKE ' . $text;
				$wheres2[] = 'a.introtext LIKE ' . $text;
				$wheres2[] = 'a.fulltext LIKE ' . $text;
				$wheres2[] = 'a.metakey LIKE ' . $text;
				$wheres2[] = 'a.metadesc LIKE ' . $text;

				$relevance[] = ' CASE WHEN ' . $wheres2[0] . ' THEN 5 ELSE 0 END ';

				// Join over Fields.
				$subQuery = $db->getQuery(true);
				$subQuery->select("cfv.item_id")
					->from("#__fields_values AS cfv")
					->join('LEFT', '#__fields AS f ON f.id = cfv.field_id')
					->where('(f.context IS NULL OR f.context = ' . $db->q('com_content.article') . ')')
					->where('(f.state IS NULL OR f.state = 1)')
					->where('(f.access IS NULL OR f.access IN (' . $groups . '))')
					->where('cfv.value LIKE ' . $text);

				// Filter by language.
				if ($app->isClient('site') && JLanguageMultilang::isEnabled())
				{
					$subQuery->where('(f.language IS NULL OR f.language in (' . $db->quote($tag) . ',' . $db->quote('*') . '))');
				}

				if ($serverType == "mysql")
				{
					/* This generates a dependent sub-query so do no use in MySQL prior to version 6.0 !
					* $wheres2[] = 'a.id IN( '. (string) $subQuery.')';
					*/

					$db->setQuery($subQuery);
					$fieldids = $db->loadColumn();

					if (count($fieldids))
					{
						$wheres2[] = 'a.id IN(' . implode(",", $fieldids) . ')';
					}
				}
				else
				{
					$wheres2[] = $subQuery->castAsChar('a.id') . ' IN( ' . (string) $subQuery . ')';
				}

				$where = '(' . implode(') OR (', $wheres2) . ')';
				break;

			case 'all':
			case 'any':
			default:
				$words = explode(' ', $text);
				$wheres = array();
				$cfwhere = array();

				foreach ($words as $word)
				{
					$word      = $db->quote('%' . $db->escape($word, true) . '%', false);
					$wheres2   = array();
					$wheres2[] = 'LOWER(a.title) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'LOWER(a.introtext) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'LOWER(a.fulltext) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'LOWER(a.metakey) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'LOWER(a.metadesc) LIKE LOWER(' . $word . ')';

					$relevance[] = ' CASE WHEN ' . $wheres2[0] . ' THEN 5 ELSE 0 END ';

					if ($phrase === 'all')
					{
						// Join over Fields.
						$subQuery = $db->getQuery(true);
						$subQuery->select("cfv.item_id")
							->from("#__fields_values AS cfv")
							->join('LEFT', '#__fields AS f ON f.id = cfv.field_id')
							->where('(f.context IS NULL OR f.context = ' . $db->q('com_content.article') . ')')
							->where('(f.state IS NULL OR f.state = 1)')
							->where('(f.access IS NULL OR f.access IN (' . $groups . '))')
							->where('LOWER(cfv.value) LIKE LOWER(' . $word . ')');

						// Filter by language.
						if ($app->isClient('site') && JLanguageMultilang::isEnabled())
						{
							$subQuery->where('(f.language IS NULL OR f.language in (' . $db->quote($tag) . ',' . $db->quote('*') . '))');
						}

						if ($serverType == "mysql")
						{
							$db->setQuery($subQuery);
							$fieldids = $db->loadColumn();

							if (count($fieldids))
							{
								$wheres2[] = 'a.id IN(' . implode(",", $fieldids) . ')';
							}
						}
						else
						{
							$wheres2[] = $subQuery->castAsChar('a.id') . ' IN( ' . (string) $subQuery . ')';
						}
					}
					else
					{
						$cfwhere[] = 'LOWER(cfv.value) LIKE LOWER(' . $word . ')';
					}

					$wheres[] = implode(' OR ', $wheres2);
				}

				if ($phrase === 'any')
				{
					// Join over Fields.
					$subQuery = $db->getQuery(true);
					$subQuery->select("cfv.item_id")
						->from("#__fields_values AS cfv")
						->join('LEFT', '#__fields AS f ON f.id = cfv.field_id')
						->where('(f.context IS NULL OR f.context = ' . $db->q('com_content.article') . ')')
						->where('(f.state IS NULL OR f.state = 1)')
						->where('(f.access IS NULL OR f.access IN (' . $groups . '))')
						->where('(' . implode(($phrase === 'all' ? ') AND (' : ') OR ('), $cfwhere) . ')');

					// Filter by language.
					if ($app->isClient('site') && JLanguageMultilang::isEnabled())
					{
						$subQuery->where('(f.language IS NULL OR f.language in (' . $db->quote($tag) . ',' . $db->quote('*') . '))');
					}

					if ($serverType == "mysql")
					{
						$db->setQuery($subQuery);
						$fieldids = $db->loadColumn();

						if (count($fieldids))
						{
							$wheres[] = 'a.id IN(' . implode(",", $fieldids) . ')';
						}
					}
					else
					{
						$wheres[] = $subQuery->castAsChar('a.id') . ' IN( ' . (string) $subQuery . ')';
					}
				}

				$where = '(' . implode(($phrase === 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
				break;
		}

		switch ($ordering)
		{
			case 'oldest':
				$order = 'a.created ASC';
				break;

			case 'popular':
				$order = 'a.hits DESC';
				break;

			case 'alpha':
				$order = 'a.title ASC';
				break;

			case 'category':
				$order = 'c.title ASC, a.title ASC';
				break;

			case 'newest':
			default:
				$order = 'a.created DESC';
				break;
		}

		$rows = array();
		$query = $db->getQuery(true);

		// Search articles.
		if ($sContent && $limit > 0)
		{
			$query->clear();

			// SQLSRV changes.
			$case_when  = ' CASE WHEN ';
			$case_when .= $query->charLength('a.alias', '!=', '0');
			$case_when .= ' THEN ';
			$a_id       = $query->castAsChar('a.id');
			$case_when .= $query->concatenate(array($a_id, 'a.alias'), ':');
			$case_when .= ' ELSE ';
			$case_when .= $a_id . ' END as slug';

			$case_when1  = ' CASE WHEN ';
			$case_when1 .= $query->charLength('c.alias', '!=', '0');
			$case_when1 .= ' THEN ';
			$c_id        = $query->castAsChar('c.id');
			$case_when1 .= $query->concatenate(array($c_id, 'c.alias'), ':');
			$case_when1 .= ' ELSE ';
			$case_when1 .= $c_id . ' END as catslug';

			if (!empty($relevance))
			{
				$query->select(implode(' + ', $relevance) . ' AS relevance');
				$order = ' relevance DESC, ' . $order;
			}

			$query->select('a.title AS title, a.metadesc, a.metakey, a.created AS created, a.language, a.catid')
				->select($query->concatenate(array('a.introtext', 'a.fulltext')) . ' AS text')
				->select('c.title AS section, ' . $case_when . ',' . $case_when1 . ', ' . '\'2\' AS browsernav')
				->from('#__content AS a')
				->join('INNER', '#__categories AS c ON c.id=a.catid')
				->where(
					'(' . $where . ') AND a.state=1 AND c.published = 1 AND a.access IN (' . $groups . ') '
						. 'AND c.access IN (' . $groups . ')'
						. 'AND (a.publish_up = ' . $db->quote($nullDate) . ' OR a.publish_up <= ' . $db->quote($now) . ') '
						. 'AND (a.publish_down = ' . $db->quote($nullDate) . ' OR a.publish_down >= ' . $db->quote($now) . ')'
				)
				->group('a.id, a.title, a.metadesc, a.metakey, a.created, a.language, a.catid, a.introtext, a.fulltext, c.title, a.alias, c.alias, c.id')
				->order($order);

			// Filter by language.
			if ($app->isClient('site') && JLanguageMultilang::isEnabled())
			{
				$query->where('a.language in (' . $db->quote($tag) . ',' . $db->quote('*') . ')')
					->where('c.language in (' . $db->quote($tag) . ',' . $db->quote('*') . ')');
			}

			$db->setQuery($query, 0, $limit);

			try
			{
				$list = $db->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				$list = array();
				JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			}

			$limit -= count($list);

			if (isset($list))
			{
				foreach ($list as $key => $item)
				{
					$list[$key]->href = ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language);
				}
			}

			$rows[] = $list;
		}

		// Search archived content.
		if ($sArchived && $limit > 0)
		{
			$query->clear();

			// SQLSRV changes.
			$case_when  = ' CASE WHEN ';
			$case_when .= $query->charLength('a.alias', '!=', '0');
			$case_when .= ' THEN ';
			$a_id       = $query->castAsChar('a.id');
			$case_when .= $query->concatenate(array($a_id, 'a.alias'), ':');
			$case_when .= ' ELSE ';
			$case_when .= $a_id . ' END as slug';

			$case_when1  = ' CASE WHEN ';
			$case_when1 .= $query->charLength('c.alias', '!=', '0');
			$case_when1 .= ' THEN ';
			$c_id        = $query->castAsChar('c.id');
			$case_when1 .= $query->concatenate(array($c_id, 'c.alias'), ':');
			$case_when1 .= ' ELSE ';
			$case_when1 .= $c_id . ' END as catslug';

			if (!empty($relevance))
			{
				$query->select(implode(' + ', $relevance) . ' AS relevance');
				$order = ' relevance DESC, ' . $order;
			}

			$query->select('a.title AS title, a.metadesc, a.metakey, a.created AS created, a.language, a.catid')
				->select($query->concatenate(array('a.introtext', 'a.fulltext')) . ' AS text')
				->select('c.title AS section, ' . $case_when . ',' . $case_when1 . ', ' . '\'2\' AS browsernav')
				->from('#__content AS a')
				->join('INNER', '#__categories AS c ON c.id=a.catid AND c.access IN (' . $groups . ')')
				->where(
					'(' . $where . ') AND a.state = 2 AND c.published = 1 AND a.access IN (' . $groups
						. ') AND c.access IN (' . $groups . ') '
						. 'AND (a.publish_up = ' . $db->quote($nullDate) . ' OR a.publish_up <= ' . $db->quote($now) . ') '
						. 'AND (a.publish_down = ' . $db->quote($nullDate) . ' OR a.publish_down >= ' . $db->quote($now) . ')'
				)
				->order($order);

			// Join over Fields is no longer needed

			// Filter by language.
			if ($app->isClient('site') && JLanguageMultilang::isEnabled())
			{
				$query->where('a.language in (' . $db->quote($tag) . ',' . $db->quote('*') . ')')
					->where('c.language in (' . $db->quote($tag) . ',' . $db->quote('*') . ')');
			}

			$db->setQuery($query, 0, $limit);

			try
			{
				$list3 = $db->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				$list3 = array();
				JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			}

			if (isset($list3))
			{
				foreach ($list3 as $key => $item)
				{
					$list3[$key]->href = ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language);
				}
			}

			$rows[] = $list3;
		}

		$results = array();

		if (count($rows))
		{
			foreach ($rows as $row)
			{
				$new_row = array();

				foreach ($row as $article)
				{
					// Not efficient to get these ONE article at a TIME
					// Lookup field values so they can be checked, GROUP_CONCAT would work in above queries, but isn't supported by non-MySQL DBs.
					$query = $db->getQuery(true);
					$query->select('fv.value')
						->from('#__fields_values as fv')
						->join('left', '#__fields as f on fv.field_id = f.id')
						->where('f.context = ' . $db->quote('com_content.article'))
						->where('fv.item_id = ' . $db->quote((int) $article->slug));
					$db->setQuery($query);
					$article->jcfields = implode(',', $db->loadColumn());

					if (SearchHelper::checkNoHtml($article, $searchText, array('text', 'title', 'jcfields', 'metadesc', 'metakey')))
					{
						$new_row[] = $article;
					}
				}

				$results = array_merge($results, (array) $new_row);
			}
		}

		return $results;
	}

}
