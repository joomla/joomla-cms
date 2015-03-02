<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_archive
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
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
		$db    = JFactory::getDbo();

		$orderDate = $params->get('list_show_date', 'publish_up');

		$query = $db->getQuery(true);
		$query->select(
				array(
					$db->qn($orderDate, 'order_date'),
					$db->qn('id'),
					$db->qn('title'),
					$query->month($db->qn($orderDate)) . ' AS ' . $db->qn('order_month'),
					$query->year($db->qn($orderDate)) . ' AS ' . $db->qn('order_year'),
				)
			)
			->from('#__content')
			->where('state = 2 AND checked_out = 0')
			->group('order_year, order_month')
			->order('order_year DESC, order_month DESC');

		// Filter by language
		if (JFactory::getApplication()->getLanguageFilter())
		{
			$query->where('language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		}

		$db->setQuery($query, 0, (int) $params->get('count'));
		$rows = (array) $db->loadObjectList();

		$app    = JFactory::getApplication();
		$menu   = $app->getMenu();
		$item   = $menu->getItems('link', 'index.php?option=com_content&view=archive', true);
		$itemid = (isset($item) && !empty($item->id)) ? '&Itemid=' . $item->id : '';

		$i     = 0;
		$lists = array();

		foreach ($rows as $row)
		{
			$date = JFactory::getDate($row->order_date);

			$order_month = $date->format('n');
			$order_year  = $date->format('Y');

			$order_year_cal = JHTML::_('date', $row->order_date, 'Y');
			$order_month_cal   = JHTML::_('date', $row->order_date, 'F');

			$lists[$i] = new stdClass;

			$lists[$i]->link = JRoute::_('index.php?option=com_content&view=archive&year=' . $order_year . '&month=' . $order_month . $itemid);
			$lists[$i]->text = JText::sprintf('MOD_ARTICLES_ARCHIVE_DATE', $order_month_cal, $order_year_cal);

			$i++;
		}

		return $lists;
	}
}
