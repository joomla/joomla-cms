<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Privacy.user
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

JLoader::register('PrivacyPlugin', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/plugin.php');
JLoader::register('PrivacyRemovalStatus', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/removal/status.php');

/**
 * Privacy plugin managing Joomla user data
 *
 * @since  3.9.0
 */
class PlgPrivacyUser extends PrivacyPlugin
{
	/**
	 * Performs validation to determine if the data associated with a remove information request can be processed
	 *
	 * This event will not allow a super user account to be removed
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 * @param   JUser                $user     The user account associated with this request if available
	 *
	 * @return  PrivacyRemovalStatus
	 *
	 * @since   3.9.0
	 */
	public function onPrivacyCanRemoveData(PrivacyTableRequest $request, JUser $user = null)
	{
		$status = new PrivacyRemovalStatus;

		if (!$user)
		{
			return $status;
		}

		if ($user->authorise('core.admin'))
		{
			$status->canRemove = false;
			$status->reason    = JText::_('PLG_PRIVACY_USER_ERROR_CANNOT_REMOVE_SUPER_USER');
		}

		return $status;
	}

	/**
	 * Processes an export request for Joomla core user data
	 *
	 * This event will collect data for the following core tables:
	 *
	 * - #__users (excluding the password, otpKey, and otep columns)
	 * - #__user_notes
	 * - #__user_profiles
	 * - User custom fields
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 * @param   JUser                $user     The user account associated with this request if available
	 *
	 * @return  PrivacyExportDomain[]
	 *
	 * @since   3.9.0
	 */
	public function onPrivacyExportRequest(PrivacyTableRequest $request, JUser $user = null)
	{
		if (!$user)
		{
			return array();
		}

		/** @var JTableUser $userTable */
		$userTable = JUser::getTable();
		$userTable->load($user->id);

		$domains = array();
		$domains[] = $this->createUserDomain($userTable);
		$domains[] = $this->createNotesDomain($userTable);
		$domains[] = $this->createProfileDomain($userTable);
		$domains[] = $this->createCustomFieldsDomain('com_users.user', array($userTable));

		return $domains;
	}

	/**
	 * Removes the data associated with a remove information request
	 *
	 * This event will pseudoanonymise the user account
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 * @param   JUser                $user     The user account associated with this request if available
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	public function onPrivacyRemoveData(PrivacyTableRequest $request, JUser $user = null)
	{
		// This plugin only processes data for registered user accounts
		if (!$user)
		{
			return;
		}

		$pseudoanonymisedData = array(
			'name'      => 'User ID ' . $user->id,
			'username'  => bin2hex(random_bytes(12)),
			'email'     => 'UserID' . $user->id . 'removed@email.invalid',
			'block'     => true,
		);

		$user->bind($pseudoanonymisedData);

		$user->save();

		// Destroy all sessions for the user account
		$sessionIds = $this->db->setQuery(
			$this->db->getQuery(true)
				->select($this->db->quoteName('session_id'))
				->from($this->db->quoteName('#__session'))
				->where($this->db->quoteName('userid') . ' = ' . (int) $user->id)
		)->loadColumn();

		// If there aren't any active sessions then there's nothing to do here
		if (empty($sessionIds))
		{
			return;
		}

		$storeName = JFactory::getConfig()->get('session_handler', 'none');
		$store     = JSessionStorage::getInstance($storeName);
		$quotedIds = array();

		// Destroy the sessions and quote the IDs to purge the session table
		foreach ($sessionIds as $sessionId)
		{
			$store->destroy($sessionId);
			$quotedIds[] = $this->db->quote($sessionId);
		}

		$this->db->setQuery(
			$this->db->getQuery(true)
				->delete($this->db->quoteName('#__session'))
				->where($this->db->quoteName('session_id') . ' IN (' . implode(', ', $quotedIds) . ')')
		)->execute();
	}

	/**
	 * Create the domain for the user notes data
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   3.9.0
	 */
	private function createNotesDomain(JTableUser $user)
	{
		$domain = $this->createDomain('user_notes', 'joomla_user_notes_data');

		$query = $this->db->getQuery(true)
			->select('*')
			->from($this->db->quoteName('#__user_notes'))
			->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($user->id));

		$items = $this->db->setQuery($query)->loadAssocList();

		// Remove user ID columns
		foreach (array('user_id', 'created_user_id', 'modified_user_id') as $column)
		{
			$items = ArrayHelper::dropColumn($items, $column);
		}

		foreach ($items as $item)
		{
			$domain->addItem($this->createItemFromArray($item, $item['id']));
		}

		return $domain;
	}

	/**
	 * Create the domain for the user profile data
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   3.9.0
	 */
	private function createProfileDomain(JTableUser $user)
	{
		$domain = $this->createDomain('user_profile', 'joomla_user_profile_data');

		$query = $this->db->getQuery(true)
			->select('*')
			->from($this->db->quoteName('#__user_profiles'))
			->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($user->id))
			->order($this->db->quoteName('ordering') . ' ASC');

		$items = $this->db->setQuery($query)->loadAssocList();

		foreach ($items as $item)
		{
			$domain->addItem($this->createItemFromArray($item));
		}

		return $domain;
	}

	/**
	 * Create the domain for the user record
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   3.9.0
	 */
	private function createUserDomain(JTableUser $user)
	{
		$domain = $this->createDomain('users', 'joomla_users_data');
		$domain->addItem($this->createItemForUserTable($user));

		return $domain;
	}

	/**
	 * Create an item object for a JTableUser object
	 *
	 * @param   JTableUser  $user  The JTableUser object to convert
	 *
	 * @return  PrivacyExportItem
	 *
	 * @since   3.9.0
	 */
	private function createItemForUserTable(JTableUser $user)
	{
		$data    = array();
		$exclude = array('password', 'otpKey', 'otep');

		foreach (array_keys($user->getFields()) as $fieldName)
		{
			if (!in_array($fieldName, $exclude))
			{
				$data[$fieldName] = $user->$fieldName;
			}
		}

		return $this->createItemFromArray($data, $user->id);
	}
}
