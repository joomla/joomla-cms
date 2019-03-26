<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Privacy.user
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('PrivacyPlugin', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/plugin.php');

/**
 * Privacy plugin managing Joomla user data
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgPrivacyUser extends PrivacyPlugin
{
	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * Processes an export request for Joomla core user data
	 *
	 * This event will collect data for the following core tables:
	 *
	 * - #__users (excluding the password, otpKey, and otep columns)
	 * - #__user_notes
	 * - #__user_profiles
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 *
	 * @return  PrivacyExportDomain[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onPrivacyExportRequest(PrivacyTableRequest $request)
	{
		if (!$request->user_id)
		{
			return array();
		}

		/** @var JTableUser $user */
		$user = JUser::getTable();
		$user->load($request->user_id);

		$domains = array();
		$domains[] = $this->createUserDomain($user);
		$domains[] = $this->createNotesDomain($user);
		$domains[] = $this->createProfileDomain($user);

		return $domains;
	}

	/**
	 * Create the domain for the user notes data
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function createNotesDomain(JTableUser $user)
	{
		$domain = $this->createDomain('user notes', 'Joomla! user notes data');

		$query = $this->db->getQuery(true)
			->select('*')
			->from($this->db->quoteName('#__user_notes'))
			->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($user->id));

		$items = $this->db->setQuery($query)->loadAssocList();

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
	 * @since   __DEPLOY_VERSION__
	 */
	private function createProfileDomain(JTableUser $user)
	{
		$domain = $this->createDomain('user profile', 'Joomla! user profile data');

		$query = $this->db->getQuery(true)
			->select('*')
			->from($this->db->quoteName('#__user_profiles'))
			->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($user->id));

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
	 * @since   __DEPLOY_VERSION__
	 */
	private function createUserDomain(JTableUser $user)
	{
		$domain = $this->createDomain('users', 'Joomla! users table data');
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
	 * @since   __DEPLOY_VERSION__
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
