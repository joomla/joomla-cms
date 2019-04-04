<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tjnotification
 *
 * @copyright   Copyright (C) 2005 -  2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * TJNotification model.
 *
 * @since  1.6
 */
class TJNotificationsModelPreferences extends JModelAdmin
{
	/**
	 * Method to getClient the form data.
	 *
	 * @return clients
	 *
	 * @throws Exception
	 * @since 1.6
	 */
	public function getClient()
	{
		// Initialize variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('DISTINCT(client)');
		$query->from($db->quoteName('#__tj_notification_templates'));
		$db->setQuery($query);
		$clients = $db->loadObjectList();

		return $clients;
	}

	/**
	 * Method to get keys the form data.
	 *
	 * @param   array  $client  The form data
	 *
	 * @return $keys
	 *
	 * @throws Exception
	 * @since 1.6
	 */
	public function Keys($client)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('DISTINCT(`key`)');
		$query->from($db->quoteName('#__tj_notification_templates'));
		$query->where($db->quoteName('client') . ' = ' . $db->quote($client));
		$query->where($db->quoteName('user_control') . ' = 1');
		$db->setQuery($query);
		$keys = $db->loadObjectList();

		return $keys;
	}

	/**
	 * Method to getState the form data.
	 *
	 * @return preferences
	 *
	 * @throws Exception
	 * @since 1.6
	 */
	public function getStates()
	{
		// Initialize variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$uid   = JFactory::getUser()->id;
			$query->select('client,`key`,provider');
			$query->from($db->quoteName('#__tj_notification_user_exclusions'));

			if ($uid)
			{
			$query->where($db->quoteName('user_id') . ' = ' . $db->quote($uid));
			}

			$db->setQuery($query);
			$preferences = $db->loadObjectList();

			if ($preferences)
			{
				return $preferences;
			}

			return false;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data
	 *
	 * @return bool
	 *
	 * @throws Exception
	 * @since 1.6
	 */
	public function save($data)
	{
		if ($data)
		{
			parent::save($data);
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('tjnotification');
			$dispatcher->trigger('tjnOnAfterUnsubscribeNotification', array($data));

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to delete data
	 *
	 * @param   array  &$data  Data to be deleted
	 *
	 * @return bool|int If success returns the id of the deleted item, if not false
	 *
	 * @throws Exception
	 */
	public function deletePreference(&$data)
	{
		if ($data)
		{
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('tjnotification');
			$dispatcher->trigger('tjnOnAfterResubscribeNotification', array($data));
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$conditions = array(
				$db->quoteName('user_id') . ' = ' . $db->quote($data['user_id']),
				$db->quoteName('client') . ' = ' . $db->quote($data['client']),
				$db->quoteName('key') . ' = ' . $db->quote($data['key']),
				$db->quoteName('provider') . ' = ' . $db->quote($data['provider'])
			);
			$query->delete($db->quoteName('#__tj_notification_user_exclusions'));
			$query->where($conditions);

			$db->setQuery($query);
			$result = $db->execute();

			return $result;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to get the profile form.
	 *
	 * The base form is loaded from XML
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return    JForm    A JForm object on success, false on failure
	 *
	 * @since    1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			'com_tjnotifications.preferences',
			'preferences',
			array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
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

	public function getTable($type ='Preferences', $prefix = 'TJNotificationTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to count the form data.
	 *
	 * @return preferences
	 *
	 * @throws Exception
	 * @since 1.6
	 */
	public function count()
	{
			// Initialize variables.
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('COUNT(*) as name');
			$query->from($db->quoteName('#__tj_notification_user_exclusions'));
			$query->where($db->quoteName('provider') . ' = ' . $db->quote('email'));

			$db->setQuery($query);

			$count = $db->loadObject();

			return $count;
	}

	/**
	 * Method to get keys the form data.
	 *
	 * @param   array  $provider  The form data
	 *
	 * @return $adminPreferences
	 *
	 * @throws Exception
	 * @since 1.6
	 */
	public function adminPreferences($provider)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$provider = strtolower($provider);
		$query->select('client,`key`');
		$query->from($db->quoteName('#__tj_notification_templates'));
		$query->where($db->quoteName($provider . '_status') . '=' . $db->quote('1'));

		$db->setQuery($query);
		$adminPreferences = $db->loadObjectList();

		return $adminPreferences;
	}

	/**
	 * Method to get the profile form.
	 *
	 * The base form is loaded from XML
	 *
	 * @param   array  $client  An optional array of data for the form to interogate.
	 * @param   array  $key     True if the form is to load its own data (default case), false if not.
	 *
	 * @return    JForm    A JForm object on success, false on failure
	 *
	 * @since    1.6
	 */
	public function getUnsubscribedUsers($client,$key)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('user_id');
		$query->from($db->quoteName('#__tj_notification_user_exclusions'));
		$query->where(
					array(
							$db->quoteName('client') . ' = ' . $db->quote($client),
							$db->quoteName('key') . ' = ' . $db->quote($key)
							)
						);
		$db->setQuery($query);
		$userIds = $db->loadObjectList();
		$unsubscribed_users = array();

		foreach ($userIds as $userId)
		{
			$unsubscribed_users[] = $userId->user_id;
		}

		return $unsubscribed_users;
	}
}
