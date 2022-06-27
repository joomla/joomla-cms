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
use Joomla\CMS\Event\Plugin\System\Webauthn\AjaxChallenge;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\CMS\User\UserHelper;
use Joomla\Event\Event;

/**
 * Ajax handler for akaction=challenge
 *
 * Generates the public key and challenge which is used by the browser when logging in with Webauthn. This is the bit
 * which prevents tampering with the login process and replay attacks.
 *
 * @since   4.0.0
 */
trait AjaxHandlerChallenge
{
    /**
     * Returns the public key set for the user and a unique challenge in a Public Key Credential Request encoded as
     * JSON.
     *
     * @param   AjaxChallenge  $event  The event we are handling
     *
     * @return  void
     *
     * @throws  Exception
     * @since   4.0.0
     */
    public function onAjaxWebauthnChallenge(AjaxChallenge $event): void
    {
        // Initialize objects
        $session    = $this->getApplication()->getSession();
        $input      = $this->getApplication()->input;

        // Retrieve data from the request
        $username  = $input->getUsername('username', '');
        $returnUrl = base64_encode(
            $session->get('plg_system_webauthn.returnUrl', Uri::current())
        );
        $returnUrl = $input->getBase64('returnUrl', $returnUrl);
        $returnUrl = base64_decode($returnUrl);

        // For security reasons the post-login redirection URL must be internal to the site.
        if (!Uri::isInternal($returnUrl)) {
            // If the URL wasn't internal redirect to the site's root.
            $returnUrl = Uri::base();
        }

        $session->set('plg_system_webauthn.returnUrl', $returnUrl);

        // Do I have a username?
        if (empty($username)) {
            $event->addResult(false);

            return;
        }

        // Is the username valid?
        try {
            $userId = UserHelper::getUserId($username);
        } catch (Exception $e) {
            $userId = 0;
        }

        if ($userId <= 0) {
            $event->addResult(false);

            return;
        }

        try {
            $myUser = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($userId);
        } catch (Exception $e) {
            $myUser = new User();
        }

        if ($myUser->id != $userId || $myUser->guest) {
            $event->addResult(false);

            return;
        }

        $publicKeyCredentialRequestOptions = $this->authenticationHelper->getPubkeyRequestOptions($myUser);

        $session->set('plg_system_webauthn.userId', $userId);

        // Return the JSON encoded data to the caller
        $event->addResult(json_encode($publicKeyCredentialRequestOptions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
