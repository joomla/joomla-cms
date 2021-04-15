<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Privacy.contact
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('PrivacyPlugin', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/plugin.php');

/**
 * Privacy plugin managing Joomla user contact data
 *
 * @since  3.9.0
 */
class PlgPrivacyContact extends PrivacyPlugin
{
	/**
	 * Processes an export request for Joomla core user contact data
	 *
	 * This event will collect data for the contact core tables:
	 *
	 * - Contact custom fields
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
		if (!$user && !$request->email)
		{
			return array();
		}

		$domains   = array();
		$domain    = $this->createDomain('user_contact', 'joomla_user_contact_data');
		$domains[] = $domain;

		$query = $this->db->getQuery(true)
			->select('*')
			->from($this->db->quoteName('#__contact_details'))
			->order($this->db->quoteName('ordering') . ' ASC');

		if ($user)
		{
			$query->where($this->db->quoteName('user_id') . ' = ' . (int) $user->id);
		}
		else
		{
			$query->where($this->db->quoteName('email_to') . ' = ' . $this->db->quote($request->email));
		}

		$items = $this->db->setQuery($query)->loadObjectList();

		foreach ($items as $item)
		{
			$domain->addItem($this->createItemFromArray((array) $item));
		}

		$domains[] = $this->createCustomFieldsDomain('com_contact.contact', $items);

		return $domains;
	}
}
