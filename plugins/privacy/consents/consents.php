<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Privacy.consents
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('PrivacyPlugin', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/plugin.php');

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

		$domain    = $this->createDomain('consents', 'joomla_consent_data');

		$query = $this->db->getQuery(true)
			->select('*')
			->from($this->db->quoteName('#__privacy_consents'))
			->where($this->db->quoteName('user_id') . ' = ' . (int) $user->id)
			->order($this->db->quoteName('created') . ' ASC');

		$items = $this->db->setQuery($query)->loadAssocList();

		foreach ($items as $item)
		{
			$domain->addItem($this->createItemFromArray($item));
		}

		return array($domain);
	}
}
