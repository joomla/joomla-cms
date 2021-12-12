<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Webauthn
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Webauthn\PluginTraits;

// Protect from unauthorized access
\defined('_JEXEC') or die();

use Exception;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Joomla\Plugin\System\Webauthn\Helper\Joomla;
use Joomla\Utilities\ArrayHelper;

/**
 * Delete all WebAuthn credentials for a particular user
 *
 * @since   4.0.0
 */
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
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   4.0.0
	 */
	public function onUserAfterDelete(array $user, bool $success, ?string $msg): void
	{
		if (!$success)
		{
			return;
		}

		$userId = ArrayHelper::getValue($user, 'id', 0, 'int');

		if ($userId)
		{
			Joomla::log('system', "Removing WebAuthn Passwordless Login information for deleted user #{$userId}");

			/** @var DatabaseDriver $db */
			$db = Factory::getContainer()->get('DatabaseDriver');

			$query = $db->getQuery(true)
				->delete($db->qn('#__webauthn_credentials'))
				->where($db->qn('user_id') . ' = :userId')
				->bind(':userId', $userId);

			$db->setQuery($query)->execute();
		}
	}
}
