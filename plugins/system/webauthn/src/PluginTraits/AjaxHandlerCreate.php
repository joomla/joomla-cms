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
use Joomla\CMS\Event\Plugin\System\Webauthn\AjaxCreate;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Event\Event;
use RuntimeException;
use Webauthn\PublicKeyCredentialSource;

/**
 * Ajax handler for akaction=create
 *
 * Handles the browser postback for the credentials creation flow
 *
 * @since   4.0.0
 */
trait AjaxHandlerCreate
{
    /**
     * Handle the callback to add a new WebAuthn authenticator
     *
     * @param   AjaxCreate  $event  The event we are handling
     *
     * @return  void
     *
     * @throws  Exception
     * @since   4.0.0
     */
    public function onAjaxWebauthnCreate(AjaxCreate $event): void
    {
        /**
         * Fundamental sanity check: this callback is only allowed after a Public Key has been created server-side and
         * the user it was created for matches the current user.
         *
         * This is also checked in the validateAuthenticationData() so why check here? In case we have the wrong user
         * I need to fail early with a Joomla error page instead of falling through the code and possibly displaying
         * someone else's Webauthn configuration thus mitigating a major privacy and security risk. So, please, DO NOT
         * remove this sanity check!
         */
        $session = $this->getApplication()->getSession();
        $storedUserId = $session->get('plg_system_webauthn.registration_user_id', 0);
        $thatUser     = empty($storedUserId) ?
            Factory::getApplication()->getIdentity() :
            Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($storedUserId);
        $myUser = Factory::getApplication()->getIdentity();

        if ($thatUser->guest || ($thatUser->id != $myUser->id)) {
            // Unset the session variables used for registering authenticators (security precaution).
            $session->set('plg_system_webauthn.registration_user_id', null);
            $session->set('plg_system_webauthn.publicKeyCredentialCreationOptions', null);

            // Politely tell the presumed hacker trying to abuse this callback to go away.
            throw new RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_INVALID_USER'));
        }

        // Get the credentials repository object. It's outside the try-catch because I also need it to display the GUI.
        $credentialRepository = $this->authenticationHelper->getCredentialsRepository();

        // Try to validate the browser data. If there's an error I won't save anything and pass the message to the GUI.
        try {
            $input = $this->getApplication()->input;

            // Retrieve the data sent by the device
            $data = $input->get('data', '', 'raw');

            $publicKeyCredentialSource = $this->authenticationHelper->validateAttestationResponse($data);

            if (!\is_object($publicKeyCredentialSource) || !($publicKeyCredentialSource instanceof PublicKeyCredentialSource)) {
                throw new RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_NO_ATTESTED_DATA'));
            }

            $credentialRepository->saveCredentialSource($publicKeyCredentialSource);
        } catch (Exception $e) {
            $error                  = $e->getMessage();
            $publicKeyCredentialSource = null;
        }

        // Unset the session variables used for registering authenticators (security precaution).
        $session->set('plg_system_webauthn.registration_user_id', null);
        $session->set('plg_system_webauthn.publicKeyCredentialCreationOptions', null);

        // Render the GUI and return it
        $layoutParameters = [
            'user'                => $thatUser,
            'allow_add'           => $thatUser->id == $myUser->id,
            'credentials'         => $credentialRepository->getAll($thatUser->id),
            'knownAuthenticators' => $this->authenticationHelper->getKnownAuthenticators(),
            'attestationSupport'  => $this->authenticationHelper->hasAttestationSupport(),
        ];

        if (isset($error) && !empty($error)) {
            $layoutParameters['error'] = $error;
        }

        $layout = new FileLayout('plugins.system.webauthn.manage', JPATH_SITE . '/plugins/system/webauthn/layout');

        $event->addResult($layout->render($layoutParameters));
    }
}
