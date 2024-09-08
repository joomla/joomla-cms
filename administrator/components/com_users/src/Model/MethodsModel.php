<?php

/**
 * @package    Joomla.Administrator
 * @subpackage com_users
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Model;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\User\User;
use Joomla\Component\Users\Administrator\Helper\Mfa as MfaHelper;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Multi-factor Authentication Methods list page's model
 *
 * @since 4.2.0
 */
class MethodsModel extends BaseDatabaseModel
{
    /**
     * Returns a list of all available MFA methods and their currently active records for a given user.
     *
     * @param   User|null  $user  The user object. Skip to use the current user.
     *
     * @return  array
     * @throws  \Exception
     *
     * @since 4.2.0
     */
    public function getMethods(?User $user = null): array
    {
        if (\is_null($user)) {
            $user = $this->getCurrentUser();
        }

        if ($user->guest) {
            return [];
        }

        // Get an associative array of MFA Methods
        $rawMethods = MfaHelper::getMfaMethods();
        $methods    = [];

        foreach ($rawMethods as $method) {
            $method['active']         = [];
            $methods[$method['name']] = $method;
        }

        // Put the user MFA records into the Methods array
        $userMfaRecords = MfaHelper::getUserMfaRecords($user->id);

        if (!empty($userMfaRecords)) {
            foreach ($userMfaRecords as $record) {
                if (!isset($methods[$record->method])) {
                    continue;
                }

                $methods[$record->method]->addActiveMethod($record);
            }
        }

        return $methods;
    }

    /**
     * Delete all Multi-factor Authentication Methods for the given user.
     *
     * @param   User|null  $user  The user object to reset MFA for. Null to use the current user.
     *
     * @return  void
     * @throws  \Exception
     *
     * @since 4.2.0
     */
    public function deleteAll(?User $user = null): void
    {
        // Make sure we have a user object
        if (\is_null($user)) {
            $user = $this->getCurrentUser() ?: Factory::getApplication()->getIdentity();
        }

        // If the user object is a guest (who can't have MFA) we stop with an error
        if ($user->guest) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__user_mfa'))
            ->where($db->quoteName('user_id') . ' = :user_id')
            ->bind(':user_id', $user->id, ParameterType::INTEGER);
        $db->setQuery($query)->execute();
    }

    /**
     * Format a relative timestamp. It deals with timestamps today and yesterday in a special manner. Example returns:
     * Yesterday, 13:12
     * Today, 08:33
     * January 1, 2015
     *
     * @param   ?string  $dateTimeText  The database time string to use, e.g. "2017-01-13 13:25:36"
     *
     * @return  string  The formatted, human-readable date
     * @throws  \Exception
     *
     * @since 4.2.0
     */
    public function formatRelative(?string $dateTimeText): string
    {
        if (empty($dateTimeText)) {
            return Text::_('JNEVER');
        }

        // The timestamp is given in UTC. Make sure Joomla! parses it as such.
        $utcTimeZone = new \DateTimeZone('UTC');
        $jDate       = new Date($dateTimeText, $utcTimeZone);
        $unixStamp   = $jDate->toUnix();
        $app         = Factory::getApplication();

        // I'm pretty sure we didn't have MFA in Joomla back in 1970 ;)
        if ($unixStamp < 0) {
            return Text::_('JNEVER');
        }

        // I need to display the date in the user's local timezone. That's how you do it.
        $user   = $this->getCurrentUser();
        $userTZ = $user->getParam('timezone', $app->get('offset', 'UTC'));
        $tz     = new \DateTimeZone($userTZ);
        $jDate->setTimezone($tz);

        // Default format string: way in the past, the time of the day is not important
        $formatString    = Text::_('COM_USERS_MFA_LBL_DATE_FORMAT_PAST');
        $containerString = Text::_('COM_USERS_MFA_LBL_PAST');

        // If the timestamp is within the last 72 hours we may need a special format
        if ($unixStamp > (time() - (72 * 3600))) {
            // Is this timestamp today?
            $jNow = new Date();
            $jNow->setTimezone($tz);
            $checkNow  = $jNow->format('Ymd', true);
            $checkDate = $jDate->format('Ymd', true);

            if ($checkDate == $checkNow) {
                $formatString    = Text::_('COM_USERS_MFA_LBL_DATE_FORMAT_TODAY');
                $containerString = Text::_('COM_USERS_MFA_LBL_TODAY');
            } else {
                // Is this timestamp yesterday?
                $jYesterday = clone $jNow;
                $jYesterday->setTime(0, 0, 0);
                $oneSecond = new \DateInterval('PT1S');
                $jYesterday->sub($oneSecond);
                $checkYesterday = $jYesterday->format('Ymd', true);

                if ($checkDate == $checkYesterday) {
                    $formatString    = Text::_('COM_USERS_MFA_LBL_DATE_FORMAT_YESTERDAY');
                    $containerString = Text::_('COM_USERS_MFA_LBL_YESTERDAY');
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
     * @since 4.2.0
     */
    public function setFlag(User $user, bool $flag = true): void
    {
        $db         = $this->getDatabase();
        $profileKey = 'mfa.dontshow';
        $query      = $db->getQuery(true)
            ->select($db->quoteName('profile_value'))
            ->from($db->quoteName('#__user_profiles'))
            ->where($db->quoteName('user_id') . ' = :user_id')
            ->where($db->quoteName('profile_key') . ' = :profileKey')
            ->bind(':user_id', $user->id, ParameterType::INTEGER)
            ->bind(':profileKey', $profileKey, ParameterType::STRING);

        try {
            $result = $db->setQuery($query)->loadResult();
        } catch (\Exception $e) {
            return;
        }

        $exists = !\is_null($result);

        $object = (object) [
            'user_id'       => $user->id,
            'profile_key'   => 'mfa.dontshow',
            'profile_value' => ($flag ? 1 : 0),
            'ordering'      => 1,
        ];

        if (!$exists) {
            $db->insertObject('#__user_profiles', $object);
        } else {
            $db->updateObject('#__user_profiles', $object, ['user_id', 'profile_key']);
        }
    }
}
