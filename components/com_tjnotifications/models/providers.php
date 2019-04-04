<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tjnotification
 *
 * @copyright   Copyright (C) 2005 - 2016 Open  Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');
/**
 * TJNotification model.
 *
 * @since  1.6
 */
class TJNotificationsModelProviders extends JModelList
{
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('DISTINCT(provider)');
		$query->from($db->quoteName('#__tj_notification_providers'));
		$query->where($db->quoteName('state') . '=' . $db->quote('1'));
		$db->setQuery($query);
		$query = $db->loadObjectList();

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Method to get an array of data items
	 *
	 * @return  mixed An array of data on success, false on failure.
	 */

	public function getItems()
	{
		$items = parent::getItems();

		return $items;
	}

	/**
	 * Method to get an array of getProvider
	 *
	 * @return  mixed An array of data on success, false on failure.
	 */
	public function getProvider()
	{
		// Initialize variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('DISTINCT(provider)');
		$query->from($db->quoteName('#__tj_notification_providers'));
		$query->where($db->quoteName('state') . '=' . $db->quote('1'));
		$db->setQuery($query);
		$providers = $db->loadObjectList();

		return $providers;
	}

	/**
	 * Method to get the table
	 *
	 * @param   string  $type    Name of the JTable class
	 * @param   string  $prefix  Optional prefix for the table class name
	 * @param   array   $config  Optional configuration array for JTable object
	 *
	 * @return  JTable|boolean JTable if found, boolean false on failure
	 */

	public function getTable($type ='Provider', $prefix = 'TJNotificationsTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
}
