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
 * @since  3.9.0
 */
class PlgPrivacyMessage extends PrivacyPlugin
{
	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  3.9.0
	 */
	protected $db;

	/**
	 * Affects constructor behaviour. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.9.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Processes an export request for Joomla core user message
	 *
	 * This event will collect data for the message table
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

		$domains   = array();
		$domains[] = $this->createMessageDomain($user);

		return $domains;
	}

	/**
	 * Create the domain for the user message data
	 *
	 * @param   JUser  $user  The user account associated with this request
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   3.9.0
	 */
	private function createMessageDomain(JUser $user)
	{
		$lang = JFactory::getLanguage();

		$receiver = JUser::getInstance($user->id);

		/*
		 * We don't know if the user has admin access, so we will check if they have an admin language in their parameters,
		 * falling back to the site language, falling back to the site default language.
		 */

		$langCode = $receiver->getParam('admin_language', '');

		if (!$langCode)
		{
			$langCode = $receiver->getParam('language', JComponentHelper::getParams('com_languages')->get('site'));
		}

		$lang = JLanguage::getInstance($langCode, $lang->getDebug());

		// Ensure the right language files have been loaded.
		$lang->load('plg_privacy_message', JPATH_ADMINISTRATOR, null, false, true)
			|| $lang->load('plg_privacy_message', JPATH_SITE . '/plugins/privacy/message', null, false, true);

		$domain = $this->createDomain(
			$lang->_('PLG_PRIVACY_MESSAGE_DOMAIN_LABEL'),
			$lang->_('PLG_PRIVACY_MESSAGE_DOMAIN_DESC')
		);

		$query = $this->db->getQuery(true)
			->select('*')
			->from($this->db->quoteName('#__messages'))
			->where($this->db->quoteName('user_id_from') . ' = ' . (int) $user->id)
			->orWhere($this->db->quoteName('user_id_to') . ' = ' . (int) $user->id)
			->order($this->db->quoteName('date_time') . ' ASC');

		$items = $this->db->setQuery($query)->loadAssocList();

		foreach ($items as $item)
		{
			$domain->addItem($this->createItemFromArray($item));
		}

		return $domain;
	}
}

