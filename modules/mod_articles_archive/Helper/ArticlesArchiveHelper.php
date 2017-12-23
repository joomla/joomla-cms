<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_archive
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\ArticlesArchive\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;

/**
 * Helper for mod_articles_archive
 *
 * @since  1.5
 */
class ArticlesArchiveHelper
{
	/**
	 * Retrieve list of archived articles
	 *
	 * @param   \Joomla\Registry\Registry  &$params  module parameters
	 *
	 * @return  array
	 *
	 * @since   1.5
	 */
	public static function getList(&$params)
	{
		// Get database
		$db    = Factory::getDbo();
		$states = (array) $params->get('state', [4]);

		$query = $db->getQuery(true);
		$query->select($query->month($db->quoteName('created')) . ' AS created_month')
			->select('MIN(' . $db->quoteName('created') . ') AS created')
			->select($query->year($db->quoteName('created')) . ' AS created_year')
			->from($db->qn('#__content', 'c'))
			->innerJoin($db->qn('#__workflow_associations', 'wa') . ' ON wa.item_id = c.id')
			->group($query->year($db->quoteName('c.created')) . ', ' . $query->month($db->quoteName('c.created')))
			->order($query->year($db->quoteName('c.created')) . ' DESC, ' . $query->month($db->quoteName('c.created')) . ' DESC');

		if (!empty($states))
		{
			$states = ArrayHelper::toInteger($states);

			$query->where($db->qn('wa.state_id') . ' IN (' . implode(', ', $states) . ')');
		}

		// Filter by language
		if (Factory::getApplication()->getLanguageFilter())
		{
			$query->where('language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		}

		$db->setQuery($query, 0, (int) $params->get('count'));

		try
		{
			$rows = (array) $db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage(\JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			return [];
		}

		$app    = Factory::getApplication();
		$menu   = $app->getMenu();
		$item   = $menu->getItems('link', 'index.php?option=com_content&view=archive', true);
		$itemid = (isset($item) && !empty($item->id)) ? '&Itemid=' . $item->id : '';

		$i     = 0;
		$lists = array();

		$states = array_map(
			function ($el)
			{
				return '&state[]=' . (int) $el;
			},
			$states
		);

		$states = implode($states);

		foreach ($rows as $row)
		{
			$date = Factory::getDate($row->created);

			$created_month = $date->format('n');
			$created_year  = $date->format('Y');

			$created_year_cal = \JHtml::_('date', $row->created, 'Y');
			$month_name_cal   = \JHtml::_('date', $row->created, 'F');

			$lists[$i] = new \stdClass;

			$route = 'index.php?option=com_content&view=archive' . $states . '&year=' . $created_year . '&month=' . $created_month . $itemid;

			$lists[$i]->link = \JRoute::_($route);
			$lists[$i]->text = \JText::sprintf('MOD_ARTICLES_ARCHIVE_DATE', $month_name_cal, $created_year_cal);

			$i++;
		}

		return $lists;
	}
}
