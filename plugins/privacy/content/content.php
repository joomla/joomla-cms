<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Privacy.content
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\User\User;
use Joomla\Component\Privacy\Administrator\Plugin\PrivacyPlugin;
use Joomla\Component\Privacy\Administrator\Table\RequestTable;

/**
 * Privacy plugin managing Joomla user content data
 *
 * @since  3.9.0
 */
class PlgPrivacyContent extends PrivacyPlugin
{
	/**
	 * Processes an export request for Joomla core user content data
	 *
	 * This event will collect data for the content core table
	 *
	 * - Content custom fields
	 *
	 * @param   RequestTable  $request  The request record being processed
	 * @param   User          $user     The user account associated with this request if available
	 *
	 * @return  \Joomla\Component\Privacy\Administrator\Export\Domain[]
	 *
	 * @since   3.9.0
	 */
	public function onPrivacyExportRequest(RequestTable $request, User $user = null)
	{
		if (!$user)
		{
			return array();
		}

		$domains   = array();
		$domain    = $this->createDomain('user_content', 'joomla_user_content_data');
		$domains[] = $domain;

		$query = $this->db->getQuery(true)
			->select('*')
			->from($this->db->quoteName('#__content'))
			->where($this->db->quoteName('created_by') . ' = ' . (int) $user->id)
			->order($this->db->quoteName('ordering') . ' ASC');

		$items = $this->db->setQuery($query)->loadObjectList();

		foreach ($items as $item)
		{
			$domain->addItem($this->createItemFromArray((array) $item));
		}

		$domains[] = $this->createCustomFieldsDomain('com_content.article', $items);

		return $domains;
	}
}
