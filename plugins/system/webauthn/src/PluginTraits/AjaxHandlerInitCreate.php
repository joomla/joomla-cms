<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Webauthn
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Webauthn\PluginTraits;

use Joomla\CMS\Event\Plugin\System\Webauthn\AjaxInitCreate;
use Joomla\CMS\Factory;
use Joomla\CMS\User\User;

/**
 * Ajax handler for akaction=initcreate
 *
 * Returns the Public Key Creation Options to start the attestation ceremony on the browser.
 *
 * @since  4.2.0
 */
trait AjaxHandlerInitCreate
{
    /**
     * Returns the Public Key Creation Options to start the attestation ceremony on the browser.
     *
     * @param   AjaxInitCreate  $event  The event we are handling
     *
     * @return  void
     * @throws  \Exception
     * @since   4.2.0
     */
    public function onAjaxWebauthnInitcreate(AjaxInitCreate $event): void
    {
        // Make sure I have a valid user
        $user = Factory::getApplication()->getIdentity();

        if (!($user instanceof User) || $user->guest) {
            $event->addResult(new \stdClass());

            return;
        }

        // I need the server to have either GMP or BCComp support to attest new authenticators
        if (function_exists('gmp_intval') === false && function_exists('bccomp') === false) {
            $event->addResult(new \stdClass());

            return;
        }

        $session = $this->getApplication()->getSession();
        $session->set('plg_system_webauthn.registration_user_id', $user->id);

        $event->addResult($this->authenticationHelper->getPubKeyCreationOptions($user));
    }
}
