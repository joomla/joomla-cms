<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\User;

use Joomla\Authentication\Password\Argon2idHandler;
use Joomla\Authentication\Password\Argon2iHandler;
use Joomla\Authentication\Password\BCryptHandler;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Authentication\Password\ChainedHandler;
use Joomla\CMS\Authentication\Password\CheckIfRehashNeededHandlerInterface;
use Joomla\CMS\Authentication\Password\MD5Handler;
use Joomla\CMS\Authentication\Password\PHPassHandler;
use Joomla\CMS\Crypt\Crypt;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\SessionManager;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Authorisation helper class, provides static methods to perform various tasks relevant
 * to the Joomla user and authorisation classes
 *
 * This class has influences and some method logic from the Horde Auth package
 *
 * @since  1.7.0
 */
abstract class UserHelper
{
    /**
     * Constant defining the Argon2i password algorithm for use with password hashes
     *
     * Note: PHP's native `PASSWORD_ARGON2I` constant is not used as PHP may be compiled without this constant
     *
     * @var    string
     * @since  4.0.0
     */
    public const HASH_ARGON2I = 'argon2i';

    /**
     * B/C constant `PASSWORD_ARGON2I` for PHP < 7.4 (using integer)
     *
     * Note: PHP's native `PASSWORD_ARGON2I` constant is not used as PHP may be compiled without this constant
     *
     * @var    integer
     * @since  4.0.0
     *
     * @deprecated  4.0 will be removed in 6.0
     *              Use UserHelper::HASH_ARGON2I instead
     */
    public const HASH_ARGON2I_BC = 2;

    /**
     * Constant defining the Argon2id password algorithm for use with password hashes
     *
     * Note: PHP's native `PASSWORD_ARGON2ID` constant is not used as PHP may be compiled without this constant
     *
     * @var    string
     * @since  4.0.0
     */
    public const HASH_ARGON2ID = 'argon2id';

    /**
     * B/C constant `PASSWORD_ARGON2ID` for PHP < 7.4 (using integer)
     *
     * Note: PHP's native `PASSWORD_ARGON2ID` constant is not used as PHP may be compiled without this constant
     *
     * @var    integer
     * @since  4.0.0
     *
     * @deprecated  4.0 will be removed in 6.0
     *              Use UserHelper::HASH_ARGON2ID instead
     */
    public const HASH_ARGON2ID_BC = 3;

    /**
     * Constant defining the BCrypt password algorithm for use with password hashes
     *
     * @var    string
     * @since  4.0.0
     */
    public const HASH_BCRYPT = '2y';

    /**
     * B/C constant `PASSWORD_BCRYPT` for PHP < 7.4 (using integer)
     *
     * @var    integer
     * @since  4.0.0
     *
     * @deprecated  4.0 will be removed in 6.0
     *              Use UserHelper::HASH_BCRYPT instead
     */
    public const HASH_BCRYPT_BC = 1;

    /**
     * Constant defining the MD5 password algorithm for use with password hashes
     *
     * @var    string
     * @since  4.0.0
     *
     * @deprecated  4.0 will be removed in 6.0
     *              Support for MD5 hashed passwords will be removed use any of the other hashing methods
     */
    public const HASH_MD5 = 'md5';

    /**
     * Constant defining the PHPass password algorithm for use with password hashes
     *
     * @var    string
     * @since  4.0.0
     *
     * @deprecated  4.0 will be removed in 6.0
     *              Support for PHPass hashed passwords will be removed use any of the other hashing methods
     */
    public const HASH_PHPASS = 'phpass';

    /**
     * Mapping array for the algorithm handler
     *
     * @var array
     * @since  4.0.0
     */
    public const HASH_ALGORITHMS = [
        self::HASH_ARGON2I     => Argon2iHandler::class,
        self::HASH_ARGON2I_BC  => Argon2iHandler::class,
        self::HASH_ARGON2ID    => Argon2idHandler::class,
        self::HASH_ARGON2ID_BC => Argon2idHandler::class,
        self::HASH_BCRYPT      => BCryptHandler::class,
        self::HASH_BCRYPT_BC   => BCryptHandler::class,
        self::HASH_MD5         => MD5Handler::class,
        self::HASH_PHPASS      => PHPassHandler::class,
    ];

    /**
     * Method to add a user to a group.
     *
     * @param   integer  $userId   The id of the user.
     * @param   integer  $groupId  The id of the group.
     *
     * @return  boolean  True on success
     *
     * @since   1.7.0
     * @throws  \RuntimeException
     */
    public static function addUserToGroup($userId, $groupId)
    {
        // Cast as integer until method is typehinted.
        $userId  = (int) $userId;
        $groupId = (int) $groupId;

        // Get the user object.
        $user = new User($userId);

        // Add the user to the group if necessary.
        if (!\in_array($groupId, $user->groups)) {
            // Check whether the group exists.
            $db    = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__usergroups'))
                ->where($db->quoteName('id') . ' = :groupId')
                ->bind(':groupId', $groupId, ParameterType::INTEGER);
            $db->setQuery($query);

            // If the group does not exist, return an exception.
            if ($db->loadResult() === null) {
                throw new \RuntimeException('Access Usergroup Invalid');
            }

            // Add the group data to the user object.
            $user->groups[$groupId] = $groupId;

            // Reindex the array for prepared statements binding
            $user->groups = array_values($user->groups);

            // Store the user object.
            $user->save();
        }

        // Set the group data for any preloaded user objects.
        $temp         = User::getInstance($userId);
        $temp->groups = $user->groups;

        if (Factory::getSession()->getId()) {
            // Set the group data for the user object in the session.
            $temp = Factory::getUser();

            if ($temp->id == $userId) {
                $temp->groups = $user->groups;
            }
        }

        return true;
    }

    /**
     * Method to get a list of groups a user is in.
     *
     * @param   integer  $userId  The id of the user.
     *
     * @return  array    List of groups
     *
     * @since   1.7.0
     */
    public static function getUserGroups($userId)
    {
        // Get the user object.
        $user = User::getInstance((int) $userId);

        return $user->groups ?? [];
    }

    /**
     * Method to remove a user from a group.
     *
     * @param   integer  $userId   The id of the user.
     * @param   integer  $groupId  The id of the group.
     *
     * @return  boolean  True on success
     *
     * @since   1.7.0
     */
    public static function removeUserFromGroup($userId, $groupId)
    {
        // Get the user object.
        $user = User::getInstance((int) $userId);

        // Remove the user from the group if necessary.
        $key = array_search($groupId, $user->groups);

        if ($key !== false) {
            unset($user->groups[$key]);
            $user->groups = array_values($user->groups);

            // Store the user object.
            $user->save();
        }

        // Set the group data for any preloaded user objects.
        $temp         = Factory::getUser((int) $userId);
        $temp->groups = $user->groups;

        // Set the group data for the user object in the session.
        $temp = Factory::getUser();

        if ($temp->id == $userId) {
            $temp->groups = $user->groups;
        }

        return true;
    }

    /**
     * Method to set the groups for a user.
     *
     * @param   integer  $userId  The id of the user.
     * @param   array    $groups  An array of group ids to put the user in.
     *
     * @return  boolean  True on success
     *
     * @since   1.7.0
     */
    public static function setUserGroups($userId, $groups)
    {
        // Get the user object.
        $user = User::getInstance((int) $userId);

        // Set the group ids.
        $groups       = ArrayHelper::toInteger($groups);
        $user->groups = $groups;

        // Get the titles for the user groups.
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'title']))
            ->from($db->quoteName('#__usergroups'))
            ->whereIn($db->quoteName('id'), $user->groups);
        $db->setQuery($query);
        $results = $db->loadObjectList();

        // Set the titles for the user groups.
        foreach ($results as $result) {
            $user->groups[$result->id] = $result->id;
        }

        // Store the user object.
        $user->save();

        // Set the group data for any preloaded user objects.
        $temp         = Factory::getUser((int) $userId);
        $temp->groups = $user->groups;

        if (Factory::getSession()->getId()) {
            // Set the group data for the user object in the session.
            $temp = Factory::getUser();

            if ($temp->id == $userId) {
                $temp->groups = $user->groups;
            }
        }

        return true;
    }

    /**
     * Gets the user profile information
     *
     * @param   integer  $userId  The id of the user.
     *
     * @return  object
     *
     * @since   1.7.0
     */
    public static function getProfile($userId = 0)
    {
        if ($userId == 0) {
            $user   = Factory::getUser();
            $userId = $user->id;
        }

        // Get the dispatcher and load the user's plugins.
        PluginHelper::importPlugin('user');

        $data     = new CMSObject();
        $data->id = $userId;

        // Trigger the data preparation event.
        Factory::getApplication()->triggerEvent('onContentPrepareData', ['com_users.profile', &$data]);

        return $data;
    }

    /**
     * Method to activate a user
     *
     * @param   string  $activation  Activation string
     *
     * @return  boolean  True on success
     *
     * @since   1.7.0
     */
    public static function activateUser($activation)
    {
        $db       = Factory::getDbo();

        // Let's get the id of the user we want to activate
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('activation') . ' = :activation')
            ->where($db->quoteName('block') . ' = 1')
            ->where($db->quoteName('lastvisitDate') . ' IS NULL')
            ->bind(':activation', $activation);
        $db->setQuery($query);
        $id = (int) $db->loadResult();

        // Is it a valid user to activate?
        if ($id) {
            $user = User::getInstance($id);

            $user->set('block', '0');
            $user->set('activation', '');

            // Time to take care of business.... store the user.
            if (!$user->save()) {
                Log::add($user->getError(), Log::WARNING, 'jerror');

                return false;
            }
        } else {
            Log::add(Text::_('JLIB_USER_ERROR_UNABLE_TO_FIND_USER'), Log::WARNING, 'jerror');

            return false;
        }

        return true;
    }

    /**
     * Returns userid if a user exists
     *
     * @param   string  $username  The username to search on.
     *
     * @return  integer  The user id or 0 if not found.
     *
     * @since   1.7.0
     */
    public static function getUserId($username)
    {
        // Initialise some variables
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('username') . ' = :username')
            ->bind(':username', $username)
            ->setLimit(1);
        $db->setQuery($query);

        return $db->loadResult();
    }

    /**
     * Hashes a password using the current encryption.
     *
     * @param   string          $password   The plaintext password to encrypt.
     * @param   string|integer  $algorithm  The hashing algorithm to use, represented by `HASH_*` class constants, or a container service ID.
     * @param   array           $options    The options for the algorithm to use.
     *
     * @return  string  The encrypted password.
     *
     * @since   3.2.1
     * @throws  \InvalidArgumentException when the algorithm is not supported
     */
    public static function hashPassword($password, $algorithm = self::HASH_BCRYPT, array $options = [])
    {
        $container = Factory::getContainer();

        // If the algorithm is a valid service ID, use that service to generate the hash
        if ($container->has($algorithm)) {
            return $container->get($algorithm)->hashPassword($password, $options);
        }

        // Try to load handler
        if (isset(self::HASH_ALGORITHMS[$algorithm])) {
            return $container->get(self::HASH_ALGORITHMS[$algorithm])->hashPassword($password, $options);
        }

        // Unsupported algorithm, sorry!
        throw new \InvalidArgumentException(\sprintf('The %s algorithm is not supported for hashing passwords.', $algorithm));
    }

    /**
     * Formats a password using the current encryption. If the user ID is given
     * and the hash does not fit the current hashing algorithm, it automatically
     * updates the hash.
     *
     * @param   string   $password  The plaintext password to check.
     * @param   string   $hash      The hash to verify against.
     * @param   integer  $userId    ID of the user if the password hash should be updated
     *
     * @return  boolean  True if the password and hash match, false otherwise
     *
     * @since   3.2.1
     */
    public static function verifyPassword($password, $hash, $userId = 0)
    {
        $passwordAlgorithm = self::HASH_BCRYPT;
        $container         = Factory::getContainer();

        // Cheaply try to determine the algorithm in use otherwise fall back to the chained handler
        if (strpos($hash, '$P$') === 0) {
            /** @var PHPassHandler $handler */
            $handler = $container->get(PHPassHandler::class);
        } elseif (strpos($hash, '$argon2id') === 0) {
            // Check for Argon2id hashes
            /** @var Argon2idHandler $handler */
            $handler = $container->get(Argon2idHandler::class);

            $passwordAlgorithm = self::HASH_ARGON2ID;
        } elseif (strpos($hash, '$argon2i') === 0) {
            // Check for Argon2i hashes
            /** @var Argon2iHandler $handler */
            $handler = $container->get(Argon2iHandler::class);

            $passwordAlgorithm = self::HASH_ARGON2I;
        } elseif (strpos($hash, '$2') === 0) {
            // Check for bcrypt hashes
            /** @var BCryptHandler $handler */
            $handler = $container->get(BCryptHandler::class);
        } else {
            /** @var ChainedHandler $handler */
            $handler = $container->get(ChainedHandler::class);
        }

        $match  = $handler->validatePassword($password, $hash);
        $rehash = $handler instanceof CheckIfRehashNeededHandlerInterface ? $handler->checkIfRehashNeeded($hash) : false;

        // If we have a match and rehash = true, rehash the password with the current algorithm.
        if ((int) $userId > 0 && $match && $rehash) {
            $user           = new User($userId);
            $user->password = static::hashPassword($password, $passwordAlgorithm);
            $user->save();
        }

        return $match;
    }

    /**
     * Generate a random password
     *
     * @param   integer  $length  Length of the password to generate
     *
     * @return  string  Random Password
     *
     * @since   1.7.0
     */
    public static function genRandomPassword($length = 8)
    {
        $salt     = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $base     = \strlen($salt);
        $makepass = '';

        /*
         * Start with a cryptographic strength random string, then convert it to
         * a string with the numeric base of the salt.
         * Shift the base conversion on each character so the character
         * distribution is even, and randomize the start shift so it's not
         * predictable.
         */
        $random = Crypt::genRandomBytes($length + 1);
        $shift  = \ord($random[0]);

        for ($i = 1; $i <= $length; ++$i) {
            $makepass .= $salt[($shift + \ord($random[$i])) % $base];
            $shift += \ord($random[$i]);
        }

        return $makepass;
    }

    /**
     * Method to get a hashed user agent string that does not include browser version.
     * Used when frequent version changes cause problems.
     *
     * @return  string  A hashed user agent string with version replaced by 'abcd'
     *
     * @since   3.2
     */
    public static function getShortHashedUserAgent()
    {
        $ua             = Factory::getApplication()->client;
        $uaString       = $ua->userAgent;
        $browserVersion = $ua->browserVersion;

        if ($browserVersion) {
            $uaShort = str_replace($browserVersion, 'abcd', $uaString);
        } else {
            $uaShort = $uaString;
        }

        return md5(Uri::base() . $uaShort);
    }

    /**
     * Check if there is a super user in the user ids.
     *
     * @param   array  $userIds  An array of user IDs on which to operate
     *
     * @return  boolean  True on success, false on failure
     *
     * @since   3.6.5
     */
    public static function checkSuperUserInUsers(array $userIds)
    {
        foreach ($userIds as $userId) {
            foreach (static::getUserGroups($userId) as $userGroupId) {
                if (Access::checkGroup($userGroupId, 'core.admin')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Destroy all active session for a given user id
     *
     * @param   int      $userId       Id of user
     * @param   boolean  $keepCurrent  Keep the session of the currently acting user
     * @param   int      $clientId     Application client id
     *
     * @return  boolean
     *
     * @since   3.9.28
     */
    public static function destroyUserSessions($userId, $keepCurrent = false, $clientId = null)
    {
        // Destroy all sessions for the user account if able
        if (!Factory::getApplication()->get('session_metadata', true)) {
            return false;
        }

        $db = Factory::getDbo();

        try {
            $userId = (int) $userId;

            $query = $db->getQuery(true)
                ->select($db->quoteName('session_id'))
                ->from($db->quoteName('#__session'))
                ->where($db->quoteName('userid') . ' = :userid')
                ->bind(':userid', $userId, ParameterType::INTEGER);

            if ($clientId !== null) {
                $clientId = (int) $clientId;

                $query->where($db->quoteName('client_id') . ' = :client_id')
                    ->bind(':client_id', $clientId, ParameterType::INTEGER);
            }

            $sessionIds = $db->setQuery($query)->loadColumn();
        } catch (ExecutionFailureException $e) {
            return false;
        }

        // Convert PostgreSQL Session IDs into strings (see GitHub #33822)
        foreach ($sessionIds as &$sessionId) {
            if (\is_resource($sessionId) && get_resource_type($sessionId) === 'stream') {
                $sessionId = stream_get_contents($sessionId);
            }
        }

        // If true, removes the current session id from the purge list
        if ($keepCurrent) {
            $sessionIds = array_diff($sessionIds, [Factory::getSession()->getId()]);
        }

        // If there aren't any active sessions then there's nothing to do here
        if (empty($sessionIds)) {
            return false;
        }

        /** @var SessionManager $sessionManager */
        $sessionManager = Factory::getContainer()->get('session.manager');
        $sessionManager->destroySessions($sessionIds);

        try {
            $db->setQuery(
                $db->getQuery(true)
                    ->delete($db->quoteName('#__session'))
                    ->whereIn($db->quoteName('session_id'), $sessionIds, ParameterType::LARGE_OBJECT)
            )->execute();
        } catch (ExecutionFailureException $e) {
            // No issue, let things go
        }
    }
}
