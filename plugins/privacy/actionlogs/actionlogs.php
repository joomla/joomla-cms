<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Privacy.actionlogs
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\User\User;
use Joomla\Component\Actionlogs\Administrator\Helper\ActionlogsHelper;
use Joomla\Component\Privacy\Administrator\Plugin\PrivacyPlugin;
use Joomla\Component\Privacy\Administrator\Table\RequestTable;
use Joomla\Database\ParameterType;

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

		$domain = $this->createDomain('user_action_logs', 'joomla_user_action_logs_data');
		$db     = $this->db;
		$userId = (int) $user->id;

		$query = $db->getQuery(true)
			->select(['a.*', $db->quoteName('u.name')])
			->from($db->quoteName('#__action_logs', 'a'))
			->join('INNER', $db->quoteName('#__users', 'u'), $db->quoteName('a.user_id') . ' = ' . $db->quoteName('u.id'))
			->where($db->quoteName('a.user_id') . ' = :id')
			->bind(':id', $userId, ParameterType::INTEGER);

		$db->setQuery($query);

		$data = $db->loadObjectList();

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
