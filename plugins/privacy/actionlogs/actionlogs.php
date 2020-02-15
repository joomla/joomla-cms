<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Privacy.actionlogs
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('ActionlogsHelper', JPATH_ADMINISTRATOR . '/components/com_actionlogs/helpers/actionlogs.php');
JLoader::register('PrivacyPlugin', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/plugin.php');

/**
 * Privacy plugin managing Joomla actionlogs data
 *
 * @since  3.9.0
 */
class PlgPrivacyActionlogs extends PrivacyPlugin
{
	/**
	 * Processes an export request for Joomla core actionlog data
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

		$domain = $this->createDomain('user_action_logs', 'joomla_user_action_logs_data');

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

		$data    = ActionlogsHelper::getCsvData($data);
		$isFirst = true;

		foreach ($data as $item)
		{
			if ($isFirst)
			{
				$isFirst = false;

				continue;
			}

			$domain->addItem($this->createItemFromArray($item));
		}

		return array($domain);
	}
}
