<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Application;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Encrypt\Aes;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\User as UserTable;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\Component\Users\Administrator\Helper\Mfa as MfaHelper;
use Joomla\Component\Users\Administrator\Table\MfaTable;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use RuntimeException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Implements the code required for integrating with Joomla's Multi-factor Authentication.
 *
 * Please keep in mind that Joomla's MFA, like any MFA method, is designed to be user-interactive.
 * Moreover, it's meant to be used in an HTML- and JavaScript-aware execution environment i.e. a web
 * browser, web view or similar.
 *
 * If your application is designed to work non-interactively (e.g. a JSON API application) or
 * outside and HTML- and JavaScript-aware execution environments (e.g. CLI) you MUST NOT use this
 * trait. Authentication should be either implicit (e.g. CLI) or using sufficiently secure non-
 * interactive methods (tokens, certificates, ...).
 *
 * Regarding the Joomla CMS itself, only the SiteApplication (frontend) and AdministratorApplication
 * (backend) applications use this trait because of this reason. The CLI application is implicitly
 * authorised at the highest level, whereas the ApiApplication encourages the use of tokens for
 * authentication.
 *
 * @since 4.2.0
 */
trait MultiFactorAuthenticationHandler
{
    /**
     * Handle the redirection to the Multi-factor Authentication captive login or setup page.
     *
     * @return  boolean  True if we are currently handling a Multi-factor Authentication captive page.
     * @throws  Exception
     * @since   4.2.0
     */
    protected function isHandlingMultiFactorAuthentication(): bool
    {
        // Multi-factor Authentication checks take place only for logged in users.
        try {
            $user = $this->getIdentity() ?? null;
        } catch (Exception $e) {
            return false;
        }

        if (!($user instanceof User) || $user->guest) {
            return false;
        }

        // If there is no need for a redirection I must not proceed
        if (!$this->needsMultiFactorAuthenticationRedirection()) {
            return false;
        }

        /**
         * Automatically migrate from legacy MFA, if needed.
         *
         * We prefer to do a user-by-user migration instead of migrating everybody on Joomla update
         * for practical reasons. On a site with hundreds or thousands of users the migration could
         * take several minutes, causing Joomla Update to time out.
         *
         * Instead, every time we are in a captive Multi-factor Authentication page (captive MFA login
         * or captive forced MFA setup) we spend a few milliseconds to check if a migration is
         * necessary. If it's necessary, we perform it.
         *
         * The captive pages don't load any content or modules, therefore the few extra milliseconds
         * we spend here are not a big deal. A failed all-users migration which would stop Joomla
         * Update dead in its tracks would, however, be a big deal (broken sites). Moreover, a
         * migration that has to be initiated by the site owner would also be a big deal — if they
         * did not know they need to do it none of their users who had previously enabled MFA would
         * now have it enabled!
         *
         * To paraphrase Otto von Bismarck: programming, like politics, is the art of the possible,
         * the attainable -- the art of the next best.
         */
        $this->migrateFromLegacyMFA();

        // We only kick in when the user has actually set up MFA or must definitely enable MFA.
        $userOptions        = ComponentHelper::getParams('com_users');
        $neverMFAUserGroups = $userOptions->get('neverMFAUserGroups', []);
        $forceMFAUserGroups = $userOptions->get('forceMFAUserGroups', []);
        $isMFADisallowed    = count(
            array_intersect(
                is_array($neverMFAUserGroups) ? $neverMFAUserGroups : [],
                $user->getAuthorisedGroups()
            )
        ) >= 1;
        $isMFAMandatory     = count(
            array_intersect(
                is_array($forceMFAUserGroups) ? $forceMFAUserGroups : [],
                $user->getAuthorisedGroups()
            )
        ) >= 1;
        $isMFADisallowed = $isMFADisallowed && !$isMFAMandatory;
        $isMFAPending    = $this->isMultiFactorAuthenticationPending();
        $session         = $this->getSession();
        $isNonHtml       = $this->input->getCmd('format', 'html') !== 'html';

        // Prevent non-interactive (non-HTML) content from being loaded until MFA is validated.
        if ($isMFAPending && $isNonHtml) {
            throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        if ($isMFAPending && !$isMFADisallowed) {
            /**
             * Saves the current URL as the return URL if all of the following conditions apply
             * - It is not a URL to com_users' MFA feature itself
             * - A return URL does not already exist, is imperfect or external to the site
             *
             * If no return URL has been set up and the current URL is com_users' MFA feature
             * we will save the home page as the redirect target.
             */
            $returnUrl       = $session->get('com_users.return_url', '');

            if (empty($returnUrl) || !Uri::isInternal($returnUrl)) {
                $returnUrl = $this->isMultiFactorAuthenticationPage()
                    ? Uri::base()
                    : Uri::getInstance()->toString(['scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment']);
                $session->set('com_users.return_url', $returnUrl);
            }

            // Redirect
            $this->redirect(Route::_('index.php?option=com_users&view=captive', false), 307);
        }

        // If we're here someone just logged in but does not have MFA set up. Just flag him as logged in and continue.
        $session->set('com_users.mfa_checked', 1);

        // If the user is in a group that requires MFA we will redirect them to the setup page.
        if (!$isMFAPending && $isMFAMandatory) {
            // First unset the flag to make sure the redirection will apply until they conform to the mandatory MFA
            $session->set('com_users.mfa_checked', 0);

            // Now set a flag which forces rechecking MFA for this user
            $session->set('com_users.mandatory_mfa_setup', 1);

            // Then redirect them to the setup page
            if (!$this->isMultiFactorAuthenticationPage()) {
                $url = Route::_('index.php?option=com_users&view=methods', false);
                $this->redirect($url, 307);
            }
        }

        // Do I need to redirect the user to the MFA setup page after they have fully logged in?
        $hasRejectedMultiFactorAuthenticationSetup = $this->hasRejectedMultiFactorAuthenticationSetup() && !$isMFAMandatory;

        if (
            !$isMFAPending && !$isMFADisallowed && ($userOptions->get('mfaredirectonlogin', 0) == 1)
            && !$user->guest && !$hasRejectedMultiFactorAuthenticationSetup && !empty(MfaHelper::getMfaMethods())
        ) {
            $this->redirect(
                $userOptions->get('mfaredirecturl', '') ?:
                    Route::_('index.php?option=com_users&view=methods&layout=firsttime', false)
            );
        }

        return true;
    }

    /**
     * Does the current user need to complete MFA authentication before being allowed to access the site?
     *
     * @return  boolean
     * @throws  Exception
     * @since   4.2.0
     */
    private function isMultiFactorAuthenticationPending(): bool
    {
        $user = $this->getIdentity();

        if (empty($user) || $user->guest) {
            return false;
        }

        // Get the user's MFA records
        $records = MfaHelper::getUserMfaRecords($user->id);

        // No MFA Methods? Then we obviously don't need to display a Captive login page.
        if (count($records) < 1) {
            return false;
        }

        // Let's get a list of all currently active MFA Methods
        $mfaMethods = MfaHelper::getMfaMethods();

        // If no MFA Method is active we can't really display a Captive login page.
        if (empty($mfaMethods)) {
            return false;
        }

        // Get a list of just the Method names
        $methodNames = [];

        foreach ($mfaMethods as $mfaMethod) {
            $methodNames[] = $mfaMethod['name'];
        }

        // Filter the records based on currently active MFA Methods
        foreach ($records as $record) {
            if (in_array($record->method, $methodNames)) {
                // We found an active Method. Show the Captive page.
                return true;
            }
        }

        // No viable MFA Method found. We won't show the Captive page.
        return false;
    }

    /**
     * Check whether we'll need to do a redirection to the Multi-factor Authentication captive page.
     *
     * @return  boolean
     * @since 4.2.0
     */
    private function needsMultiFactorAuthenticationRedirection(): bool
    {
        $isAdmin = $this->isClient('administrator');

        /**
         * We only kick in if the session flag is not set AND the user is not flagged for monitoring of their MFA status
         *
         * In case a user belongs to a group which requires MFA to be always enabled and they logged in without having
         * MFA enabled we have the recheck flag. This prevents the user from enabling and immediately disabling MFA,
         * circumventing the requirement for MFA.
         */
        $session             = $this->getSession();
        $isMFAComplete       = $session->get('com_users.mfa_checked', 0) != 0;
        $isMFASetupMandatory = $session->get('com_users.mandatory_mfa_setup', 0) != 0;

        if ($isMFAComplete && !$isMFASetupMandatory) {
            return false;
        }

        // Make sure we are logged in
        try {
            $user = $this->getIdentity();
        } catch (Exception $e) {
            // This would happen if we are in CLI or under an old Joomla! version. Either case is not supported.
            return false;
        }

        // The plugin only needs to kick in when you have logged in
        if (empty($user) || $user->guest) {
            return false;
        }

        // If we are in the administrator section we only kick in when the user has backend access privileges
        if ($isAdmin && !$user->authorise('core.login.admin')) {
            // @todo How exactly did you end up here if you didn't have the core.login.admin privilege to begin with?!
            return false;
        }

        // Do not redirect if we are already in a MFA management or captive page
        if ($this->isMultiFactorAuthenticationPage()) {
            return false;
        }

        $option       = strtolower($this->input->getCmd('option', ''));
        $task         = strtolower($this->input->getCmd('task', ''));

        // Allow the frontend user to log out (in case they forgot their MFA code or something)
        if (!$isAdmin && ($option == 'com_users') && in_array($task, ['user.logout', 'user.menulogout'])) {
            return false;
        }

        // Allow the backend user to log out (in case they forgot their MFA code or something)
        if ($isAdmin && ($option == 'com_login') && ($task == 'logout')) {
            return false;
        }

        // Allow the Joomla update finalisation to run
        if ($isAdmin && $option === 'com_joomlaupdate' && in_array($task, ['update.finalise', 'update.cleanup', 'update.finaliseconfirm'])) {
            return false;
        }

        return true;
    }

    /**
     * Is this a page concerning the Multi-factor Authentication feature?
     *
     * @param   bool  $onlyCaptive  Should I only check for the MFA captive page?
     *
     * @return  boolean
     * @since   4.2.0
     */
    public function isMultiFactorAuthenticationPage(bool $onlyCaptive = false): bool
    {
        $option = $this->input->get('option');
        $task   = $this->input->get('task');
        $view   = $this->input->get('view');

        if ($option !== 'com_users') {
            return false;
        }

        $allowedViews = ['captive', 'method', 'methods', 'callback'];
        $allowedTasks = [
            'captive.display', 'captive.captive', 'captive.validate',
            'methods.display',
        ];

        if (!$onlyCaptive) {
            $allowedTasks = array_merge(
                $allowedTasks,
                [
                    'method.display', 'method.add', 'method.edit', 'method.regenerateBackupCodes',
                    'method.delete', 'method.save', 'methods.disable', 'methods.doNotShowThisAgain',
                ]
            );
        }

        return in_array($view, $allowedViews) || in_array($task, $allowedTasks);
    }

    /**
     * Does the user have a "don't show this again" flag?
     *
     * @return  boolean
     * @since   4.2.0
     */
    private function hasRejectedMultiFactorAuthenticationSetup(): bool
    {
        $user       = $this->getIdentity();
        $profileKey = 'mfa.dontshow';
        /** @var DatabaseDriver $db */
        $db         = Factory::getContainer()->get('DatabaseDriver');
        $query      = $db->getQuery(true)
            ->select($db->quoteName('profile_value'))
            ->from($db->quoteName('#__user_profiles'))
            ->where($db->quoteName('user_id') . ' = :userId')
            ->where($db->quoteName('profile_key') . ' = :profileKey')
            ->bind(':userId', $user->id, ParameterType::INTEGER)
            ->bind(':profileKey', $profileKey);

        try {
            $result = $db->setQuery($query)->loadResult();
        } catch (Exception $e) {
            $result = 1;
        }

        return $result == 1;
    }

    /**
     * Automatically migrates a user's legacy MFA records into the new Captive MFA format.
     *
     * @return  void
     * @since 4.2.0
     */
    private function migrateFromLegacyMFA(): void
    {
        $user = $this->getIdentity();

        if (!($user instanceof User) || $user->guest || $user->id <= 0) {
            return;
        }

        /** @var DatabaseDriver $db */
        $db         = Factory::getContainer()->get('DatabaseDriver');

        $userTable = new UserTable($db);

        if (!$userTable->load($user->id) || empty($userTable->otpKey)) {
            return;
        }

        [$otpMethod, $otpKey] = explode(':', $userTable->otpKey, 2);
        $secret               = $this->get('secret');
        $otpKey               = $this->decryptLegacyTFAString($secret, $otpKey);
        $otep                 = $this->decryptLegacyTFAString($secret, $userTable->otep);
        $config               = @json_decode($otpKey, true);
        $hasConverted         = true;

        if (!empty($config)) {
            switch ($otpMethod) {
                case 'totp':
                    $this->getLanguage()->load('plg_multifactorauth_totp', JPATH_ADMINISTRATOR);

                    (new MfaTable($db))->save(
                        [
                            'user_id'    => $user->id,
                            'title'      => Text::_('PLG_MULTIFACTORAUTH_TOTP_METHOD_TITLE'),
                            'method'     => 'totp',
                            'default'    => 0,
                            'created_on' => Date::getInstance()->toSql(),
                            'last_used'  => null,
                            'options'    => ['key' => $config['code']],
                        ]
                    );
                    break;

                case 'yubikey':
                    $this->getLanguage()->load('plg_multifactorauth_yubikey', JPATH_ADMINISTRATOR);

                    (new MfaTable($db))->save(
                        [
                            'user_id'    => $user->id,
                            'title'      => sprintf("%s %s", Text::_('PLG_MULTIFACTORAUTH_YUBIKEY_METHOD_TITLE'), $config['yubikey']),
                            'method'     => 'yubikey',
                            'default'    => 0,
                            'created_on' => Date::getInstance()->toSql(),
                            'last_used'  => null,
                            'options'    => ['id' => $config['yubikey']],
                        ]
                    );
                    break;

                default:
                    $hasConverted = false;
                    break;
            }
        }

        // Convert the emergency codes
        if ($hasConverted && !empty(@json_decode($otep, true))) {
            // Delete any other record with the same user_id and Method.
            $method = 'emergencycodes';
            $userId = $user->id;
            $query  = $db->getQuery(true)
                ->delete($db->quoteName('#__user_mfa'))
                ->where($db->quoteName('user_id') . ' = :user_id')
                ->where($db->quoteName('method') . ' = :method')
                ->bind(':user_id', $userId, ParameterType::INTEGER)
                ->bind(':method', $method);
            $db->setQuery($query)->execute();

            // Migrate data
            (new MfaTable($db))->save(
                [
                    'user_id'    => $user->id,
                    'title'      => Text::_('COM_USERS_USER_BACKUPCODES'),
                    'method'     => 'backupcodes',
                    'default'    => 0,
                    'created_on' => Date::getInstance()->toSql(),
                    'last_used'  => null,
                    'options'    => @json_decode($otep, true),
                ]
            );
        }

        // Remove the legacy MFA
        $update = (object) [
            'id'     => $user->id,
            'otpKey' => '',
            'otep'   => '',
        ];
        $db->updateObject('#__users', $update, ['id']);
    }

    /**
     * Tries to decrypt the legacy MFA configuration.
     *
     * @param   string   $secret            Site's secret key
     * @param   string   $stringToDecrypt   Base64-encoded and encrypted, JSON-encoded information
     *
     * @return  string  Decrypted, but JSON-encoded, information
     *
     * @see     https://github.com/joomla/joomla-cms/pull/12497
     * @since   4.2.0
     */
    private function decryptLegacyTFAString(string $secret, string $stringToDecrypt): string
    {
        // Is this already decrypted?
        try {
            $decrypted = @json_decode($stringToDecrypt, true);
        } catch (Exception $e) {
            $decrypted = null;
        }

        if (!empty($decrypted)) {
            return $stringToDecrypt;
        }

        // No, we need to decrypt the string
        $aes       = new Aes($secret, 256);
        $decrypted = $aes->decryptString($stringToDecrypt);

        if (!is_string($decrypted) || empty($decrypted)) {
            $aes->setPassword($secret, true);

            $decrypted = $aes->decryptString($stringToDecrypt);
        }

        if (!is_string($decrypted) || empty($decrypted)) {
            return '';
        }

        // Remove the null padding added during encryption
        return rtrim($decrypted, "\0");
    }
}
