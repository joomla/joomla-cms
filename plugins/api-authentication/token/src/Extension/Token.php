<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Apiauthentication.token
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\ApiAuthentication\Token\Extension;

use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Crypt\Crypt;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\Component\Plugins\Administrator\Model\PluginModel;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\DispatcherInterface;
use Joomla\Filter\InputFilter;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla Token Authentication plugin
 *
 * @since  4.0.0
 */
final class Token extends CMSPlugin
{
    use DatabaseAwareTrait;
    use UserFactoryAwareTrait;

    /**
     * The prefix of the user profile keys, without the dot.
     *
     * @var    string
     * @since  4.0.0
     */
    private $profileKeyPrefix = 'joomlatoken';

    /**
     * Allowed HMAC algorithms for the token
     *
     * @var    string[]
     * @since  4.0.0
     */
    private $allowedAlgos = ['sha256', 'sha512'];

    /**
     * The input filter
     *
     * @var    InputFilter
     * @since  4.2.0
     */
    private $filter;

    /**
     * Constructor.
     *
     * @param   DispatcherInterface   $dispatcher   The dispatcher
     * @param   array                 $config       An optional associative array of configuration settings
     * @param   InputFilter           $filter       The input filter
     *
     * @since   4.2.0
     */
    public function __construct(DispatcherInterface $dispatcher, array $config, InputFilter $filter)
    {
        parent::__construct($dispatcher, $config);

        $this->filter = $filter;
    }

    /**
     * This method should handle any authentication and report back to the subject
     *
     * @param   array   $credentials  Array holding the user credentials
     * @param   array   $options      Array of extra options
     * @param   object  $response     Authentication response object
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onUserAuthenticate($credentials, $options, &$response): void
    {
        // Default response is authentication failure.
        $response->type          = 'Token';
        $response->status        = Authentication::STATUS_FAILURE;
        $response->error_message = $this->getApplication()->getLanguage()->_('JGLOBAL_AUTH_FAIL');

        /**
         * First look for an HTTP Authorization header with the following format:
         * Authorization: Bearer <token>
         * Do keep in mind that Bearer is **case-sensitive**. Whitespace between Bearer and the
         * token, as well as any whitespace following the token is discarded.
         */
        $authHeader  = $this->getApplication()->getInput()->server->get('HTTP_AUTHORIZATION', '', 'string');
        $tokenString = '';

        // Apache specific fixes. See https://github.com/symfony/symfony/issues/19693
        if (
            empty($authHeader) && \PHP_SAPI === 'apache2handler'
            && function_exists('apache_request_headers') && apache_request_headers() !== false
        ) {
            $apacheHeaders = array_change_key_case(apache_request_headers(), CASE_LOWER);

            if (array_key_exists('authorization', $apacheHeaders)) {
                $authHeader = $this->filter->clean($apacheHeaders['authorization'], 'STRING');
            }
        }

        if (substr($authHeader, 0, 7) == 'Bearer ') {
            $parts       = explode(' ', $authHeader, 2);
            $tokenString = trim($parts[1]);
            $tokenString = $this->filter->clean($tokenString, 'BASE64');
        }

        if (empty($tokenString)) {
            $tokenString = $this->getApplication()->getInput()->server->get('HTTP_X_JOOMLA_TOKEN', '', 'string');
        }

        // No token: authentication failure
        if (empty($tokenString)) {
            return;
        }

        // The token is a base64 encoded string. Make sure we can decode it.
        $authString = @base64_decode($tokenString);

        if (empty($authString) || (strpos($authString, ':') === false)) {
            return;
        }

        /**
         * Deconstruct the decoded token string to its three discrete parts: algorithm, user ID and
         * HMAC of the token string saved in the database.
         */
        $parts = explode(':', $authString, 3);

        if (count($parts) != 3) {
            return;
        }

        list($algo, $userId, $tokenHMAC) = $parts;

        /**
         * Verify the HMAC algorithm requested in the token string is allowed
         */
        $allowedAlgo = in_array($algo, $this->allowedAlgos);

        /**
         * Make sure the user ID is an integer
         */
        $userId = (int) $userId;

        /**
         * Calculate the reference token data HMAC
         */
        try {
            $siteSecret = $this->getApplication()->get('secret');
        } catch (\Exception $e) {
            return;
        }

        // An empty secret! What kind of monster are you?!
        if (empty($siteSecret)) {
            return;
        }

        $referenceTokenData = $this->getTokenSeedForUser($userId);
        $referenceTokenData = empty($referenceTokenData) ? '' : $referenceTokenData;
        $referenceTokenData = base64_decode($referenceTokenData);
        $referenceHMAC      = hash_hmac($algo, $referenceTokenData, $siteSecret);

        // Is the token enabled?
        $enabled = $this->isTokenEnabledForUser($userId);

        // Do the tokens match? Use a timing safe string comparison to prevent timing attacks.
        $hashesMatch = Crypt::timingSafeCompare($referenceHMAC, $tokenHMAC);

        // Is the user in the allowed user groups?
        $inAllowedUserGroups = $this->isInAllowedUserGroup($userId);

        /**
         * Can we log in?
         *
         * DO NOT concatenate in a single line. Due to boolean short-circuit evaluation it might
         * make timing attacks possible. Using separate lines of code with the previously calculated
         * boolean value to the right hand side forces PHP to evaluate the conditions in
         * approximately constant time.
         */

        // We need non-empty reference token data (the user must have configured a token)
        $canLogin = !empty($referenceTokenData);

        // The token must be enabled
        $canLogin = $enabled && $canLogin;

        // The token hash must be calculated with an allowed algorithm
        $canLogin = $allowedAlgo && $canLogin;

        // The token HMAC hash coming into the request and our reference must match.
        $canLogin = $hashesMatch && $canLogin;

        // The user must belong in the allowed user groups
        $canLogin = $inAllowedUserGroups && $canLogin;

        /**
         * DO NOT try to be smart and do an early return when either of the individual conditions
         * are not met. There's a reason we only return after checking all three conditions: it
         * prevents timing attacks.
         */
        if (!$canLogin) {
            return;
        }

        // Get the actual user record
        $user = $this->getUserFactory()->loadUserById($userId);

        // Disallow login for blocked, inactive or password reset required users
        if ($user->block || !empty(trim($user->activation)) || $user->requireReset) {
            $response->status = Authentication::STATUS_DENIED;

            return;
        }

        // Update the response to indicate successful login
        $response->status        = Authentication::STATUS_SUCCESS;
        $response->error_message = '';
        $response->username      = $user->username;
        $response->email         = $user->email;
        $response->fullname      = $user->name;
        $response->timezone      = $user->get('timezone');
        $response->language      = $user->get('language');
    }

    /**
     * Retrieve the token seed string for the given user ID.
     *
     * @param   int  $userId  The numeric user ID to return the token seed string for.
     *
     * @return  string|null  Null if there is no token configured or the user doesn't exist.
     * @since   4.0.0
     */
    private function getTokenSeedForUser(int $userId): ?string
    {
        try {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select($db->quoteName('profile_value'))
                ->from($db->quoteName('#__user_profiles'))
                ->where($db->quoteName('profile_key') . ' = :profileKey')
                ->where($db->quoteName('user_id') . ' = :userId');

            $profileKey = $this->profileKeyPrefix . '.token';
            $query->bind(':profileKey', $profileKey, ParameterType::STRING);
            $query->bind(':userId', $userId, ParameterType::INTEGER);

            return $db->setQuery($query)->loadResult();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Is the token enabled for a given user ID? If the user does not exist or has no token it
     * returns false.
     *
     * @param   int  $userId  The User ID to check whether the token is enabled on their account.
     *
     * @return  boolean
     * @since   4.0.0
     */
    private function isTokenEnabledForUser(int $userId): bool
    {
        try {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select($db->quoteName('profile_value'))
                ->from($db->quoteName('#__user_profiles'))
                ->where($db->quoteName('profile_key') . ' = :profileKey')
                ->where($db->quoteName('user_id') . ' = :userId');

            $profileKey = $this->profileKeyPrefix . '.enabled';
            $query->bind(':profileKey', $profileKey, ParameterType::STRING);
            $query->bind(':userId', $userId, ParameterType::INTEGER);

            $value = $db->setQuery($query)->loadResult();

            return $value == 1;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Retrieves a configuration parameter of a different plugin than the current one.
     *
     * @param   string  $folder  Plugin folder
     * @param   string  $plugin  Plugin name
     * @param   string  $param   Parameter name
     * @param   null    $default Default value, in case the parameter is missing
     *
     * @return  mixed
     * @since   4.0.0
     */
    private function getPluginParameter(string $folder, string $plugin, string $param, $default = null)
    {
        /** @var PluginModel $model */
        $model = $this->getApplication()->bootComponent('plugins')
            ->getMVCFactory()->createModel('Plugin', 'Administrator', ['ignore_request' => true]);

        $pluginObject = $model->getItem(['folder' => $folder, 'element' => $plugin]);

        if (!\is_object($pluginObject) || !$pluginObject->enabled || !\array_key_exists($param, $pluginObject->params)) {
            return $default;
        }

        return $pluginObject->params[$param];
    }

    /**
     * Get the configured user groups which are allowed to have access to tokens.
     *
     * @return  int[]
     * @since   4.0.0
     */
    private function getAllowedUserGroups(): array
    {
        $userGroups = $this->getPluginParameter('user', 'token', 'allowedUserGroups', [8]);

        if (empty($userGroups)) {
            return [];
        }

        if (!is_array($userGroups)) {
            $userGroups = [$userGroups];
        }

        return $userGroups;
    }

    /**
     * Is the user with the given ID in the allowed User Groups with access to tokens?
     *
     * @param   int  $userId  The user ID to check
     *
     * @return  boolean  False when doesn't belong to allowed user groups, user not found, or guest
     * @since   4.0.0
     */
    private function isInAllowedUserGroup($userId)
    {
        $allowedUserGroups = $this->getAllowedUserGroups();

        $user = $this->getUserFactory()->loadUserById($userId);

        if ($user->id != $userId) {
            return false;
        }

        if ($user->guest) {
            return false;
        }

        // No specifically allowed user groups: allow ALL user groups.
        if (empty($allowedUserGroups)) {
            return true;
        }

        $groups       = $user->getAuthorisedGroups();
        $intersection = array_intersect($groups, $allowedUserGroups);

        return !empty($intersection);
    }
}
