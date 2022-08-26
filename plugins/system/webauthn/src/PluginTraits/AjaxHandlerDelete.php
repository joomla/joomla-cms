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
use Joomla\CMS\Event\Plugin\System\Webauthn\AjaxDelete;
use Joomla\CMS\User\User;
use Joomla\Event\Event;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Ajax handler for akaction=savelabel
 *
 * Deletes a security key
 *
 * @since  4.0.0
 */
trait AjaxHandlerDelete
{
    /**
     * Handle the callback to remove an authenticator
     *
     * @param   AjaxDelete  $event  The event we are handling
     *
     * @return  void
     * @since   4.0.0
     */
    public function onAjaxWebauthnDelete(AjaxDelete $event): void
    {
        // Initialize objects
        $input      = $this->getApplication()->input;
        $repository = $this->authenticationHelper->getCredentialsRepository();

        // Retrieve data from the request
        $credentialId = $input->getBase64('credential_id', '');

        // Is this a valid credential?
        if (empty($credentialId)) {
            $event->addResult(false);

            return;
        }

        $credentialId = base64_decode($credentialId);

        if (empty($credentialId) || !$repository->has($credentialId)) {
            $event->addResult(false);

            return;
        }

        // Make sure I am editing my own key
        try {
            $user             = $this->getApplication()->getIdentity() ?? new User();
            $credentialHandle = $repository->getUserHandleFor($credentialId);
            $myHandle         = $repository->getHandleFromUserId($user->id);
        } catch (Exception $e) {
            $event->addResult(false);

            return;
        }

        if ($credentialHandle !== $myHandle) {
            $event->addResult(false);

            return;
        }

        // Delete the record
        try {
            $repository->remove($credentialId);
        } catch (Exception $e) {
            $event->addResult(false);

            return;
        }

        $event->addResult(true);
    }
}
