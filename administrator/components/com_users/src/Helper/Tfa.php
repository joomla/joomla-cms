<?php
/**
 * @package    Joomla.Administrator
 * @subpackage com_users
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Helper;

use Exception;
use Joomla\CMS\Event\GenericEvent;
use Joomla\Component\Users\Administrator\DataShape\MethodDescriptor;
use Joomla\Component\Users\Administrator\Table\TfaTable;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\Event\Event;

/**
 * Helper functions for captive TFA handling
 *
 * @since __DEPLOY_VERSION__
 */
abstract class Tfa
{
	/**
	 * Cache of all currently active TFAs
	 *
	 * @var   array|null
	 * @since __DEPLOY_VERSION__
	 */
	protected static $allTFAs = null;

	/**
	 * Are we inside the administrator application
	 *
	 * @var   boolean
	 * @since __DEPLOY_VERSION__
	 */
	protected static $isAdmin = null;

	/**
	 * Get a list of all of the TFA Methods
	 *
	 * @return  MethodDescriptor[]
	 * @since __DEPLOY_VERSION__
	 */
	public static function getTfaMethods(): array
	{
		PluginHelper::importPlugin('twofactorauth');

		if (is_null(self::$allTFAs))
		{
			$event = new GenericEvent('onUserTwofactorGetMethod', []);

			// Get all the plugin results
			$temp = self::triggerEvent($event);

			// Normalize the results
			self::$allTFAs = [];

			foreach ($temp as $method)
			{
				if (!is_array($method) && !($method instanceof MethodDescriptor))
				{
					continue;
				}

				$method = new MethodDescriptor($method);

				if (empty($method['name']))
				{
					continue;
				}

				self::$allTFAs[$method['name']] = $method;
			}
		}

		return self::$allTFAs;
	}

	/**
	 * Trigger a global Event and return the results (if it implements the ResultAwareInterface)
	 *
	 * @param   Event  $event  The event to trigger
	 *
	 * @return  array
	 * @since __DEPLOY_VERSION__
	 */
	public static function triggerEvent(Event $event): array
	{
		try
		{
			$dispatcher = Factory::getApplication()->getDispatcher();
		}
		catch (Exception $exception)
		{
			return [];
		}

		$result = $dispatcher->dispatch($event->getName(), $event);

		return $result->getArgument('result', []) ?: [];
	}

	/**
	 * Is the current user allowed to edit the TFA configuration of $user? To do so I must either be editing my own
	 * account OR I have to be a Super User editing a non-superuser's account. Important to note: nobody can edit the
	 * accounts of Super Users except themselves. Therefore make damn sure you keep those backup codes safe!
	 *
	 * @param   User|null  $user  The user you want to know if we're allowed to edit
	 *
	 * @return  boolean
	 * @throws  Exception
	 * @since __DEPLOY_VERSION__
	 */
	public static function canEditUser(?User $user = null): bool
	{
		// I can edit myself
		if (is_null($user))
		{
			return true;
		}

		// Guests can't have TFA
		if ($user->guest)
		{
			return false;
		}

		// Get the currently logged in user
		$myUser = Factory::getApplication()->getIdentity()
			?: Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);

		// Same user? I can edit myself
		if ($myUser->id === $user->id)
		{
			return true;
		}

		// To edit a different user I must be a Super User myself. If I'm not, I can't edit another user!
		if (!$myUser->authorise('core.admin'))
		{
			return false;
		}

		// Even if I am a Super User I must not be able to edit another Super User.
		if ($user->authorise('core.admin'))
		{
			return false;
		}

		// I am a Super User trying to edit a non-superuser. That's allowed.
		return true;
	}

	/**
	 * Return all TFA records for a specific user
	 *
	 * @param   int|null  $userId  User ID. NULL for currently logged in user.
	 *
	 * @return  TfaTable[]
	 * @throws  Exception
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function getUserTfaRecords(?int $userId): array
	{
		if (empty($userId))
		{
			$user   = Factory::getApplication()->getIdentity() ?: Factory::getUser();
			$userId = $user->id ?: 0;
		}

		/** @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__user_tfa'))
			->where($db->quoteName('user_id') . ' = :user_id')
			->bind(':user_id', $userId, ParameterType::INTEGER);

		try
		{
			$ids = $db->setQuery($query)->loadColumn() ?: [];
		}
		catch (Exception $e)
		{
			$ids = [];
		}

		if (empty($ids))
		{
			return [];
		}

		/** @var MVCFactoryInterface $factory */
		$factory = Factory::getApplication()->bootComponent('com_users')->getMVCFactory();

		// Map all results to Tfa table objects
		$records = array_map(
			function ($id) use ($factory) {
				/** @var TfaTable $record */
				$record = $factory->createTable('Tfa', 'Administrator');
				$loaded = $record->load($id);

				return $loaded ? $record : null;
			},
			$ids
		);

		// Let's remove Methods we couldn't decrypt when reading from the database.
		$hasBackupCodes = false;

		$records = array_filter(
			$records,
			function ($record) use (&$hasBackupCodes) {
				$isValid = !is_null($record) && (!empty($record->options));

				if ($isValid && ($record->method === 'backupcodes'))
				{
					$hasBackupCodes = true;
				}

				return $isValid;
			}
		);

		// If the only Method is backup codes it's as good as having no records
		if ((count($records) === 1) && $hasBackupCodes)
		{
			return [];
		}

		return $records;
	}
}
