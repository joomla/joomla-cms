<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_archive
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_articles_archive
 *
 * @since  1.5
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
		$query = $db->getQuery(true);
		$query->select($query->month($db->quoteName('created')) . ' AS created_month')
			->select('MIN(' . $db->quoteName('created') . ') AS created')
			->select($query->year($db->quoteName('created')) . ' AS created_year')
			->from('#__content')
			->where('state = 2')
			->group($query->year($db->quoteName('created')) . ', ' . $query->month($db->quoteName('created')))
			->order($query->year($db->quoteName('created')) . ' DESC, ' . $query->month($db->quoteName('created')) . ' DESC');

		// Filter by language
		if (JFactory::getApplication()->getLanguageFilter())
		{
			$query->where('language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		}

		$db->setQuery($query, 0, (int) $params->get('count'));

		try
		{
			$rows = (array) $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

			return array();
		}

		$app    = JFactory::getApplication();
		$menu   = $app->getMenu();
		$item   = $menu->getItems('link', 'index.php?option=com_content&view=archive', true);
		$itemid = (isset($item) && !empty($item->id)) ? '&Itemid=' . $item->id : '';

		$i     = 0;
		$lists = array();

		foreach ($rows as $row)
		{
			$date = JFactory::getDate($row->created);

			$createdMonth = $date->format('n');
			$createdYear  = $date->format('Y');

			$createdYearCal = JHtml::_('date', $row->created, 'Y');
			$monthNameCal   = JHtml::_('date', $row->created, 'F');

			$lists[$i] = new stdClass;

			$lists[$i]->link = JRoute::_('index.php?option=com_content&view=archive&year=' . $createdYear . '&month=' . $createdMonth . $itemid);
			$lists[$i]->text = JText::sprintf('MOD_ARTICLES_ARCHIVE_DATE', $monthNameCal, $createdYearCal);

			$i++;
		}

		return $lists;
	}
}
