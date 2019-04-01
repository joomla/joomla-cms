<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Privacy.message
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');
JLoader::register('PrivacyPlugin', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/plugin.php');

/**
 * Privacy plugin managing Joomla user messages
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgPrivacyMessage extends PrivacyPlugin
{
	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * Affects constructor behaviour. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Processes an export request for Joomla core user message
	 *
	 * This event will collect data for the message table
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

		$domains   = array();
		$domains[] = $this->createMessageDomain($user);
	
		return $domains;
	}

	/**
	 * Create the domain for the user message data
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function createMessageDomain(JTableUser $user)
	{
		$domain = $this->createDomain('user message', 'Joomla! user message data');

		$query = $this->db->getQuery(true)
			->select('*')
			->from($this->db->quoteName('#__messages'))
			->where($this->db->quoteName('user_id_from') . ' = ' . $this->db->quote($user->id))
			->orWhere($this->db->quoteName('user_id_to') . ' = ' . $this->db->quote($user->id))
			->order($this->db->quoteName('date_time') . ' ASC');

		$items = $this->db->setQuery($query)->loadAssocList();

		foreach ($items as $item)
		{
			$domain->addItem($this->createItemFromArray($item));
		}

		return $domain;
	}
}
