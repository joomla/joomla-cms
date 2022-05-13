<?php
/**
 * @package    Joomla.Administrator
 * @subpackage com_users
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Model;

use DateInterval;
use DateTimeZone;
use Exception;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Component\Users\Administrator\Helper\Tfa as TfaHelper;
use Joomla\Database\ParameterType;
use RuntimeException;

/**
 * Two Step Verification Methods list page's model
 *
 * @since __DEPLOY_VERSION__
 */
class MethodsModel extends BaseDatabaseModel
{
	/**
	 * Returns a list of all available and their currently active records for given user.
	 *
	 * @param   User|null  $user  The user object. Skip to use the current user.
	 *
	 * @return  array
	 * @throws  Exception
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function getMethods(?User $user = null): array
	{
		if (is_null($user))
		{
			$user = Factory::getApplication()->getIdentity()
				?: Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);
		}

		if ($user->guest)
		{
			return [];
		}

		// Get an associative array of TFA Methods
		$rawMethods = TfaHelper::getTfaMethods();
		$methods    = [];

		foreach ($rawMethods as $method)
		{
			$method['active']         = [];
			$methods[$method['name']] = $method;
		}

		// Put the user TFA records into the Methods array
		$userTfaRecords = TfaHelper::getUserTfaRecords($user->id);

		if (!empty($userTfaRecords))
		{
			foreach ($userTfaRecords as $record)
			{
				if (!isset($methods[$record->method]))
				{
					continue;
				}

				$methods[$record->method]->addActiveMethod($record);
			}
		}

		return $methods;
	}

	/**
	 * Delete all Two Step Verification Methods for the given user.
	 *
	 * @param   User|null  $user  The user object to reset TSV for. Null to use the current user.
	 *
	 * @return  void
	 * @throws  Exception
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function deleteAll(?User $user = null): void
	{
		// Make sure we have a user object
		if (is_null($user))
		{
			$user = Factory::getApplication()->getIdentity() ?: Factory::getUser();
		}

		// If the user object is a guest (who can't have TSV) we abort with an error
		if ($user->guest)
		{
			throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->delete($db->qn('#__user_tfa'))
			->where($db->qn('user_id') . ' = :user_id')
			->bind(':user_id', $user->id, ParameterType::INTEGER);
		$db->setQuery($query)->execute();
	}

	/**
	 * Format a relative timestamp. It deals with timestamps today and yesterday in a special manner. Example returns:
	 * Yesterday, 13:12
	 * Today, 08:33
	 * January 1, 2015
	 *
	 * @param   string  $dateTimeText  The database time string to use, e.g. "2017-01-13 13:25:36"
	 *
	 * @return  string  The formatted, human-readable date
	 * @throws  Exception
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function formatRelative(?string $dateTimeText): string
	{
		if (empty($dateTimeText))
		{
			return '&ndash;';
		}

		// The timestamp is given in UTC. Make sure Joomla! parses it as such.
		$utcTimeZone = new DateTimeZone('UTC');
		$jDate       = new Date($dateTimeText, $utcTimeZone);
		$unixStamp   = $jDate->toUnix();

		// I'm pretty sure we didn't have TFA in Joomla back in 1970 ;)
		if ($unixStamp < 0)
		{
			return '&ndash;';
		}

		// I need to display the date in the user's local timezone. That's how you do it.
		$user   = Factory::getApplication()->getIdentity()
			?: Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);
		$userTZ = $user->getParam('timezone', 'UTC');
		$tz     = new DateTimeZone($userTZ);
		$jDate->setTimezone($tz);

		// Default format string: way in the past, the time of the day is not important
		$formatString    = Text::_('COM_USERS_TFA_LBL_DATE_FORMAT_PAST');
		$containerString = Text::_('COM_USERS_TFA_LBL_PAST');

		// If the timestamp is within the last 72 hours we may need a special format
		if ($unixStamp > (time() - (72 * 3600)))
		{
			// Is this timestamp today?
			$jNow = new Date;
			$jNow->setTimezone($tz);
			$checkNow  = $jNow->format('Ymd', true);
			$checkDate = $jDate->format('Ymd', true);

			if ($checkDate == $checkNow)
			{
				$formatString    = Text::_('COM_USERS_TFA_LBL_DATE_FORMAT_TODAY');
				$containerString = Text::_('COM_USERS_TFA_LBL_TODAY');
			}
			else
			{
				// Is this timestamp yesterday?
				$jYesterday = clone $jNow;
				$jYesterday->setTime(0, 0, 0);
				$oneSecond = new DateInterval('PT1S');
				$jYesterday->sub($oneSecond);
				$checkYesterday = $jYesterday->format('Ymd', true);

				if ($checkDate == $checkYesterday)
				{
					$formatString    = Text::_('COM_USERS_TFA_LBL_DATE_FORMAT_YESTERDAY');
					$containerString = Text::_('COM_USERS_TFA_LBL_YESTERDAY');
				}
			}
		}

		return sprintf($containerString, $jDate->format($formatString, true));
	}

	/**
	 * Set the user's "don't show this again" flag.
	 *
	 * @param   User  $user  The user to check
	 * @param   bool  $flag  True to set the flag, false to unset it (it will be set to 0, actually)
	 *
	 * @return  void
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function setFlag(User $user, bool $flag = true): void
	{
		$db         = $this->getDbo();
		$profileKey = 'tfa.dontshow';
		$query      = $db->getQuery(true)
			->select($db->qn('profile_value'))
			->from($db->qn('#__user_profiles'))
			->where($db->qn('user_id') . ' = :user_id')
			->where($db->qn('profile_key') . ' = :profileKey')
			->bind(':user_id', $user->id, ParameterType::INTEGER)
			->bind(':profileKey', $profileKey, ParameterType::STRING);

		try
		{
			$result = $db->setQuery($query)->loadResult();
		}
		catch (Exception $e)
		{
			return;
		}

		$exists = !is_null($result);

		$object = (object) [
			'user_id'       => $user->id,
			'profile_key'   => 'tfa.dontshow',
			'profile_value' => ($flag ? 1 : 0),
			'ordering'      => 1,
		];

		if (!$exists)
		{
			$db->insertObject('#__user_profiles', $object);
		}
		else
		{
			$db->updateObject('#__user_profiles', $object, ['user_id', 'profile_key']);
		}
	}
}
