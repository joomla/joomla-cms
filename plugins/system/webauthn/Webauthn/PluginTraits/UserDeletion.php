<?php
/**
 * @package   AkeebaPasswordlessLogin
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Passwordless\Webauthn\PluginTraits;

use Akeeba\Passwordless\Webauthn\Helper\Joomla;
use Exception;
use Joomla\Utilities\ArrayHelper;

// Protect from unauthorized access
defined('_JEXEC') or die();

trait UserDeletion
{
	/**
	 * Remove all passwordless credential information for the given user ID.
	 *
	 * This method is called after user data is deleted from the database.
	 *
	 * @param   array   $user     Holds the user data
	 * @param   bool    $success  True if user was successfully stored in the database
	 * @param   string  $msg      Message
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 */
	public function onUserAfterDelete(array $user, bool $success, ?string $msg): bool
	{
		if (!$success)
		{
			return false;
		}

		$userId = ArrayHelper::getValue($user, 'id', 0, 'int');

		if ($userId)
		{
			Joomla::log('system', "Removing Akeeba Passwordless Login information for deleted user #{$userId}");

			$db = Joomla::getDbo();

			$query = $db->getQuery(true)
				->delete($db->qn('#__webauthn_credentials'))
				->where($db->qn('user_id').' = '.$db->q($userId));

			$db->setQuery($query)->execute();
		}

		return true;
	}
}