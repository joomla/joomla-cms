<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Webauthn
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Webauthn\PluginTraits;

use Exception;
use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Authentication\AuthenticationResponse;
use Joomla\CMS\Event\Plugin\System\Webauthn\AjaxLogin;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserFactoryInterface;
use RuntimeException;
use Throwable;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Ajax handler for akaction=login
 *
 * Verifies the response received from the browser and logs in the user
 *
 * @since  4.0.0
 */
trait AjaxHandlerLogin
{
    /**
     * Returns the public key set for the user and a unique challenge in a Public Key Credential Request encoded as
     * JSON.
     *
     * @param   AjaxLogin  $event  The event we are handling
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onAjaxWebauthnLogin(AjaxLogin $event): void
    {
        $session   = $this->getApplication()->getSession();
        $returnUrl = $session->get('plg_system_webauthn.returnUrl', Uri::base());
        $userId    = $session->get('plg_system_webauthn.userId', 0);

        try {
            $credentialRepository = $this->authenticationHelper->getCredentialsRepository();

            // No user ID: no username was provided and the resident credential refers to an unknown user handle. DIE!
            if (empty($userId)) {
                Log::add('Cannot determine the user ID', Log::NOTICE, 'webauthn.system');

                throw new RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_INVALID_LOGIN_REQUEST'));
            }

            // Do I have a valid user?
            $user = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($userId);

            if ($user->id != $userId) {
                $message = sprintf('User #%d does not exist', $userId);
                Log::add($message, Log::NOTICE, 'webauthn.system');

                throw new RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_INVALID_LOGIN_REQUEST'));
            }

            // Validate the authenticator response and get the user handle
            $userHandle           = $this->getUserHandleFromResponse($user);

            if (is_null($userHandle)) {
                Log::add('Cannot retrieve the user handle from the request; the browser did not assert our request.', Log::NOTICE, 'webauthn.system');

                throw new RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_INVALID_LOGIN_REQUEST'));
            }

            // Does the user handle match the user ID? This should never trigger by definition of the login check.
            $validUserHandle = $credentialRepository->getHandleFromUserId($userId);

            if ($userHandle != $validUserHandle) {
                $message = sprintf('Invalid user handle; expected %s, got %s', $validUserHandle, $userHandle);
                Log::add($message, Log::NOTICE, 'webauthn.system');

                throw new RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_INVALID_LOGIN_REQUEST'));
            }

            // Make sure the user exists
            $user = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($userId);

            if ($user->id != $userId) {
                $message = sprintf('Invalid user ID; expected %d, got %d', $userId, $user->id);
                Log::add($message, Log::NOTICE, 'webauthn.system');

                throw new RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_INVALID_LOGIN_REQUEST'));
            }

            // Login the user
            Log::add("Logging in the user", Log::INFO, 'webauthn.system');
            $this->loginUser((int) $userId);
        } catch (Throwable $e) {
            $session->set('plg_system_webauthn.publicKeyCredentialRequestOptions', null);

            $response                = $this->getAuthenticationResponseObject();
            $response->status        = Authentication::STATUS_UNKNOWN;
            $response->error_message = $e->getMessage();

            Log::add(sprintf("Received login failure. Message: %s", $e->getMessage()), Log::ERROR, 'webauthn.system');

            // This also enqueues the login failure message for display after redirection. Look for JLog in that method.
            $this->processLoginFailure($response, null, 'system');
        } finally {
            /**
             * This code needs to run no matter if the login succeeded or failed. It prevents replay attacks and takes
             * the user back to the page they started from.
             */

            // Remove temporary information for security reasons
            $session->set('plg_system_webauthn.publicKeyCredentialRequestOptions', null);
            $session->set('plg_system_webauthn.returnUrl', null);
            $session->set('plg_system_webauthn.userId', null);

            // Redirect back to the page we were before.
            $this->getApplication()->redirect($returnUrl);
        }
    }

    /**
     * Logs in a user to the site, bypassing the authentication plugins.
     *
     * @param   int   $userId   The user ID to log in
     *
     * @return  void
     * @throws  Exception
     * @since   4.2.0
     */
    private function loginUser(int $userId): void
    {
        // Trick the class auto-loader into loading the necessary classes
        class_exists('Joomla\\CMS\\Authentication\\Authentication', true);

        // Fake a successful login message
        $isAdmin = $this->getApplication()->isClient('administrator');
        $user    = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($userId);

        // Does the user account have a pending activation?
        if (!empty($user->activation)) {
            throw new RuntimeException(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'));
        }

        // Is the user account blocked?
        if ($user->block) {
            throw new RuntimeException(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'));
        }

        $statusSuccess = Authentication::STATUS_SUCCESS;

        $response                = $this->getAuthenticationResponseObject();
        $response->status        = $statusSuccess;
        $response->username      = $user->username;
        $response->fullname      = $user->name;
        $response->error_message = '';
        $response->language      = $user->getParam('language');
        $response->type          = 'Passwordless';

        if ($isAdmin) {
            $response->language = $user->getParam('admin_language');
        }

        /**
         * Set up the login options.
         *
         * The 'remember' element forces the use of the Remember Me feature when logging in with Webauthn, as the
         * users would expect.
         *
         * The 'action' element is actually required by plg_user_joomla. It is the core ACL action the logged in user
         * must be allowed for the login to succeed. Please note that front-end and back-end logins use a different
         * action. This allows us to provide the WebAuthn button on both front- and back-end and be sure that if a
         * used with no backend access tries to use it to log in Joomla! will just slap him with an error message about
         * insufficient privileges - the same thing that'd happen if you tried to use your front-end only username and
         * password in a back-end login form.
         */
        $options = [
            'remember' => true,
            'action'   => 'core.login.site',
        ];

        if ($isAdmin) {
            $options['action'] = 'core.login.admin';
        }

        // Run the user plugins. They CAN block login by returning boolean false and setting $response->error_message.
        PluginHelper::importPlugin('user');
        $eventClassName = self::getEventClassByEventName('onUserLogin');
        $event          = new $eventClassName('onUserLogin', [(array) $response, $options]);
        $result         = $this->getApplication()->getDispatcher()->dispatch($event->getName(), $event);
        $results        = !isset($result['result']) || \is_null($result['result']) ? [] : $result['result'];

        // If there is no boolean FALSE result from any plugin the login is successful.
        if (in_array(false, $results, true) === false) {
            // Set the user in the session, letting Joomla! know that we are logged in.
            $this->getApplication()->getSession()->set('user', $user);

            // Trigger the onUserAfterLogin event
            $options['user']         = $user;
            $options['responseType'] = $response->type;

            // The user is successfully logged in. Run the after login events
            $eventClassName = self::getEventClassByEventName('onUserAfterLogin');
            $event          = new $eventClassName('onUserAfterLogin', [$options]);
            $this->getApplication()->getDispatcher()->dispatch($event->getName(), $event);

            return;
        }

        // If we are here the plugins marked a login failure. Trigger the onUserLoginFailure Event.
        $eventClassName = self::getEventClassByEventName('onUserLoginFailure');
        $event          = new $eventClassName('onUserLoginFailure', [(array) $response]);
        $this->getApplication()->getDispatcher()->dispatch($event->getName(), $event);

        // Log the failure
        Log::add($response->error_message, Log::WARNING, 'jerror');

        // Throw an exception to let the caller know that the login failed
        throw new RuntimeException($response->error_message);
    }

    /**
     * Returns a (blank) Joomla! authentication response
     *
     * @return  AuthenticationResponse
     *
     * @since   4.2.0
     */
    private function getAuthenticationResponseObject(): AuthenticationResponse
    {
        // Force the class auto-loader to load the JAuthentication class
        class_exists('Joomla\\CMS\\Authentication\\Authentication', true);

        return new AuthenticationResponse();
    }

    /**
     * Have Joomla! process a login failure
     *
     * @param   AuthenticationResponse   $response   The Joomla! auth response object
     *
     * @return  boolean
     *
     * @since   4.2.0
     */
    private function processLoginFailure(AuthenticationResponse $response): bool
    {
        // Import the user plugin group.
        PluginHelper::importPlugin('user');

        // Trigger onUserLoginFailure Event.
        Log::add('Calling onUserLoginFailure plugin event', Log::INFO, 'plg_system_webauthn');

        $eventClassName = self::getEventClassByEventName('onUserLoginFailure');
        $event          = new $eventClassName('onUserLoginFailure', [(array) $response]);
        $this->getApplication()->getDispatcher()->dispatch($event->getName(), $event);

        // If status is success, any error will have been raised by the user plugin
        $expectedStatus = Authentication::STATUS_SUCCESS;

        if ($response->status !== $expectedStatus) {
            Log::add('The login failure has been logged in Joomla\'s error log', Log::INFO, 'webauthn.system');

            // Everything logged in the 'jerror' category ends up being enqueued in the application message queue.
            Log::add($response->error_message, Log::WARNING, 'jerror');
        } else {
            $message = 'A login failure was caused by a third party user plugin but it did not return any' .
                'further information.';
            Log::add($message, Log::WARNING, 'webauthn.system');
        }

        return false;
    }

    /**
     * Validate the authenticator response sent to us by the browser.
     *
     * @param   User  $user  The user we are trying to log in.
     *
     * @return  string|null  The user handle or null
     *
     * @throws  Exception
     * @since   4.2.0
     */
    private function getUserHandleFromResponse(User $user): ?string
    {
        // Retrieve data from the request and session
        $pubKeyCredentialSource = $this->authenticationHelper->validateAssertionResponse(
            $this->getApplication()->getInput()->getBase64('data', ''),
            $user
        );

        return $pubKeyCredentialSource ? $pubKeyCredentialSource->getUserHandle() : null;
    }
}
