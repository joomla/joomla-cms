<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Privacy.message
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('PrivacyPlugin', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/plugin.php');

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

		return array($domain);
	}
}
