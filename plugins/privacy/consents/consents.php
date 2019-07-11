<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Privacy.consents
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Database\ParameterType;

/**
 * Privacy plugin managing Joomla user consent data
 *
 * @since  3.9.0
 */
class PlgPrivacyConsents extends PrivacyPlugin
{
	/**
	 * Processes an export request for Joomla core user consent data
	 *
	 * This event will collect data for the core `#__privacy_consents` table
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

		$domain = $this->createDomain('consents', 'joomla_consent_data');
		$db     = $this->db;

		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__privacy_consents'))
			->where($db->quoteName('user_id') . ' = :id')
			->order($db->quoteName('created') . ' ASC')
			->bind(':id', $user->id, ParameterType::INTEGER);

		$items = $db->setQuery($query)->loadAssocList();

		foreach ($items as $item)
		{
			$domain->addItem($this->createItemFromArray($item));
		}

		return array($domain);
	}
}
