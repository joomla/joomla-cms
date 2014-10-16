<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_popular
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once JPATH_SITE . '/components/com_content/helpers/route.php';

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_content/models', 'ContentModel');

/**
 * Helper for mod_articles_popular
 *
 * @package     Joomla.Site
 * @subpackage  mod_articles_popular
 *
 * @since       1.6.0
 */
abstract class ModArticlesPopularHelper
{
	/**
	 * Get a list of popular articles from the articles model
	 *
	 * @param   JRegistry  &$params  object holding the models parameters
	 *
	 * @return mixed
	 */
	public static function getList(&$params)
	{
		// Get a db connection
		$db = JFactory::getDbo();

		// Create new query objects
		$query = $db->getQuery(true);
		$subquery = $db->getQuery(true);
		
		// Get bad categories first
		$subquery
			->select($db->quoteName(array('c.id')))
			->from($db->quoteName('#__categories', 'c'))
			->join('INNER', $db->quoteName('#__categories', 'p') . ' ON (' . $db->quoteName('c.lft') . ' BETWEEN ' . $db->quoteName('p.lft') . ' AND ' . $db->quoteName('p.rgt') . ') ')
			->where($db->quoteName('p.extension') . " IN ('com_content','system') ")
			->where($db->quoteName('p.published') . " != 1 ");

		$query
			// Select only needed fields
			->select($db->quoteName(array('a.id', 'a.title', 'a.alias', 'catid', 'c.alias', 'a.access'), array('id', 'title', 'alias', 'catid', 'category_alias', 'access')))
			->from($db->quoteName('#__content', 'a'))
			->join('INNER', $db->quoteName('#__categories', 'c') . ' ON (' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid') . ') ');

		$catfilter=$params->get('catid', array());
		
		// If the user wants to filter by category, create the subquery
		if (count($catfilter) > 0 && $catfilter[0] != '')
		{
			// Get tree of chosen categories
			$subquery2 = $db->getQuery(true);
			$subquery2
				->select($db->quoteName(array('c.id')))
				->from($db->quoteName('#__categories', 'c'))
				->join('INNER', $db->quoteName('#__categories', 'p') . ' ON (' . $db->quoteName('c.lft') . ' BETWEEN ' . $db->quoteName('p.lft') . ' AND ' . $db->quoteName('p.rgt') . ') ')
				->where($db->quoteName('p.extension') . " IN ('com_content', 'system') ")
				->where($db->quoteName('p.id') . ' IN (' . implode(',', $catfilter) . ') ');
			// Now, show only categories selected by user
			$query->where($db->quoteName('catid') . ' IN (' . $subquery2 . ') ');
		}

		$query
			// Hide bad categories
			->where($db->quoteName('catid') . ' NOT IN (' . $subquery . ') ')
			// Hide unpublished articles
			->where($db->quoteName('a.state') . ' = 1 ')
			->where(' (' . $db->quoteName('a.publish_up') . '  <= NOW() OR ' . $db->quoteName('a.publish_up') . '  = 0 ) ')
			->where(' (' . $db->quoteName('a.publish_down') . '  >= NOW() OR ' . $db->quoteName('a.publish_down') . '  = 0 ) ');

		// Check if this is a multi-language site
		$app = JFactory::getApplication();
		if ($app->getLanguageFilter())
		{
			// Filter language
			$query->where($db->quoteName('a.language') . " IN ('" . $app->getLanguage()->getTag() . "', '*') ");
		}
		
		if ($params->get('show_front', 1) == 0)
		{
			// Show only featured articles
			$query->where($db->quoteName('featured') . ' = 0 ');
		}

		// Access filter
		$access = !JComponentHelper::getParams('com_content')->get('show_noauth');
		$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
		if (!$access)
		{
			$query->where($db->quoteName('access') . ' IN (' . implode(',', $authorised) . ') ');
		}

		// Sort by hits
		$query->order($db->quoteName('a.hits') . ' DESC');

		// Execute query & limit results
		$db->setQuery($query, 0, (int) $params->get('count', 5));

		// Load rows as object array
		$items=$db->loadObjectList();

		foreach ($items as &$item)
		{
			$item->slug = $item->id . ':' . $item->alias;
			$item->catslug = $item->catid . ':' . $item->category_alias;

			if ($access || in_array($item->access, $authorised))
			{
				// We know that user has the privilege to view the article
				$item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
			}
			else
			{
				$item->link = JRoute::_('index.php?option=com_users&view=login');
			}
		}
		
		return $items;
	}
}
