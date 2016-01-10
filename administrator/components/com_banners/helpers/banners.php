<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Banners component helper.
 *
 * @since  1.6
 */
class BannersHelper extends JHelperContent
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_BANNERS_SUBMENU_BANNERS'),
			'index.php?option=com_banners&view=banners',
			$vName == 'banners'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_BANNERS_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_banners',
			$vName == 'categories'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_BANNERS_SUBMENU_CLIENTS'),
			'index.php?option=com_banners&view=clients',
			$vName == 'clients'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_BANNERS_SUBMENU_TRACKS'),
			'index.php?option=com_banners&view=tracks',
			$vName == 'tracks'
		);
	}

	/**
	 * Update / reset the banners
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public static function updateReset()
	{
		$db       = JFactory::getDbo();
		$nullDate = $db->getNullDate();
		$query    = $db->getQuery(true)
			->select('*')
			->from('#__banners')
			->where($db->quote(JFactory::getDate()) . ' >= ' . $db->quote('reset'))
			->where($db->quoteName('reset') . ' != ' . $db->quote($nullDate) . ' AND ' . $db->quoteName('reset') . '!= NULL')
			->where(
				'(' . $db->quoteName('checked_out') . ' = 0 OR ' . $db->quoteName('checked_out') . ' = '
				. (int) $db->quote(JFactory::getUser()->id) . ')'
			);
		$db->setQuery($query);

		try
		{
			$rows = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());

			return false;
		}

		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');

		foreach ($rows as $row)
		{
			$purchase_type = $row->purchase_type;

			if ($purchase_type < 0 && $row->cid)
			{
				/** @var BannersTableClient $client */
				$client = JTable::getInstance('Client', 'BannersTable');
				$client->load($row->cid);
				$purchase_type = $client->purchase_type;
			}

			if ($purchase_type < 0)
			{
				$params = JComponentHelper::getParams('com_banners');
				$purchase_type = $params->get('purchase_type');
			}

			switch ($purchase_type)
			{
				case 1:
					$reset = $nullDate;
					break;
				case 2:
					$date = JFactory::getDate('+1 year ' . date('Y-m-d', strtotime('now')));
					$reset = $db->quote($date->toSql());
					break;
				case 3:
					$date = JFactory::getDate('+1 month ' . date('Y-m-d', strtotime('now')));
					$reset = $db->quote($date->toSql());
					break;
				case 4:
					$date = JFactory::getDate('+7 day ' . date('Y-m-d', strtotime('now')));
					$reset = $db->quote($date->toSql());
					break;
				case 5:
					$date = JFactory::getDate('+1 day ' . date('Y-m-d', strtotime('now')));
					$reset = $db->quote($date->toSql());
					break;
			}

			// Update the row ordering field.
			$query->clear()
				->update($db->quoteName('#__banners'))
				->set($db->quoteName('reset') . ' = ' . $db->quote($reset))
				->set($db->quoteName('impmade') . ' = ' . $db->quote(0))
				->set($db->quoteName('clicks') . ' = ' . $db->quote(0))
				->where($db->quoteName('id') . ' = ' . $db->quote($row->id));
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				JError::raiseWarning(500, $db->getMessage());

				return false;
			}
		}

		return true;
	}

	/**
	 * Get client list in text/value format for a select field
	 *
	 * @return  array
	 */
	public static function getClientOptions()
	{
		$options = array();

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('id AS value, name AS text')
			->from('#__banner_clients AS a')
			->where('a.state = 1')
			->order('a.name');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		array_unshift($options, JHtml::_('select.option', '0', JText::_('COM_BANNERS_NO_CLIENT')));

		return $options;
	}

	/**
	 * Adds Count Items for Category Manager.
	 *
	 * @param   JDatabaseQuery  $query  The query object of com_categories
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   3.4
	 */
	public static function countItems($query)
	{
		// Join articles to categories and
		// Count published items
		$query->select('COUNT(DISTINCT cp.id) AS count_published');
		$query->join('LEFT', '#__banners AS cp ON cp.catid = a.id AND cp.state = 1');

		// Count unpublished items
		$query->select('COUNT(DISTINCT cu.id) AS count_unpublished');
		$query->join('LEFT', '#__banners AS cu ON cu.catid = a.id AND cu.state = 0');

		// Count archived items
		$query->select('COUNT(DISTINCT ca.id) AS count_archived');
		$query->join('LEFT', '#__banners AS ca ON ca.catid = a.id AND ca.state = 2');

		// Count trashed items
		$query->select('COUNT(DISTINCT ct.id) AS count_trashed');
		$query->join('LEFT', '#__banners AS ct ON ct.catid = a.id AND ct.state = -2');

		return $query;
	}

}
