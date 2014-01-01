<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_archive
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_articles_archive
 *
 * @package     Joomla.Site
 * @subpackage  mod_articles_archive
 * @since       1.5
 */
class ModArchiveHelper
{
	/*
	 * @since  1.5
	 */
	public static function getList(&$params)
	{
		//get database
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($query->month($db->quoteName('created')) . ' AS created_month')
			->select('created, id, title')
			->select($query->year($db->quoteName('created')) . ' AS created_year')
			->from('#__content')
			->where('state = 2 AND checked_out = 0')
			->group('created_year DESC, created_month DESC');

		// Filter by language
		if (JFactory::getApplication()->getLanguageFilter())
		{
			$query->where('language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		}

		$db->setQuery($query, 0, (int) $params->get('count'));
		$rows = (array) $db->loadObjectList();

		$app = JFactory::getApplication();
		$menu = $app->getMenu();
		$item = $menu->getItems('link', 'index.php?option=com_content&view=archive', true);
		$itemid = (isset($item) && !empty($item->id)) ? '&Itemid=' . $item->id : '';

		$i = 0;
		$lists = array();
		foreach ($rows as $row)
		{
			$date = JFactory::getDate($row->created);

			$created_month = $date->format('n');
			$created_year = $date->format('Y');

			$created_year_cal = JHTML::_('date', $row->created, 'Y');
			$month_name_cal = JHTML::_('date', $row->created, 'F');

			$lists[$i] = new stdClass;

			$lists[$i]->link = JRoute::_('index.php?option=com_content&view=archive&year=' . $created_year . '&month=' . $created_month . $itemid);
			$lists[$i]->text = JText::sprintf('MOD_ARTICLES_ARCHIVE_DATE', $month_name_cal, $created_year_cal);

			$i++;
		}
		return $lists;
	}
}
