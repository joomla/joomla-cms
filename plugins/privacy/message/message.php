<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Privacy.message
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Database\ParameterType;

/**
 * Privacy plugin managing Joomla user messages
 *
 * @since  3.9.0
 */
class PlgPrivacyMessage extends PrivacyPlugin
{
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

		$domain = $this->createDomain('user_messages', 'joomla_user_messages_data');
		$db     = $this->db;

		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__messages'))
			->where($db->quoteName('user_id_from') . ' = :useridfrom')
			->extendWhere('OR', $db->quoteName('user_id_to') . ' = :useridto')
			->order($db->quoteName('date_time') . ' ASC')
			->bind([':useridfrom', ':useridto'], $user->id, ParameterType::INTEGER);

		$items = $db->setQuery($query)->loadAssocList();

		foreach ($items as $item)
		{
			$domain->addItem($this->createItemFromArray($item));
		}

		return array($domain);
	}
}
