<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Privacy.actionlogs
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

JLoader::register('ActionlogsHelper', JPATH_COMPONENT . '/helpers/actionlogs.php');
JLoader::register('PrivacyPlugin', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/plugin.php');


/**
 * Privacy plugin managing Joomla actionlogs data
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgPrivacyActionlogs extends PrivacyPlugin
{
	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Processes an export request for Joomla core actionlog data
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 * @param   JUser                $user     The user account associated with this request if available
	 *
	 * @return  PrivacyExportDomain[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onPrivacyExportRequest(PrivacyTableRequest $request, JUser $user = null)
	{
		if (!$user)
		{
			return array();
		}

		$domain = $this->createDomain('actionlog', 'Logged actions of the user');

		$query = $this->db->getQuery(true)
			->select('a.*, u.name')
			->from('#__action_logs AS a')
			->innerJoin('#__users AS u ON a.user_id = u.id')
			->where($this->db->quoteName('a.user_id') . ' = ' . $user->id);

		$this->db->setQuery($query);

		$data = $this->db->loadObjectList();

		if (!count($data))
		{
			return array();
		}

		$data = ActionlogsHelper::getCsvData($data);
		array_shift($data);

		foreach ($data as $item)
		{
			$domain->addItem($this->createItemFromArray($item));
		}

		return array($domain);
	}
}
