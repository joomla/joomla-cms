<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Webauthn
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Webauthn\PluginTraits;

use Joomla\CMS\Event\Plugin\System\Webauthn\AjaxSaveLabel;
use Joomla\CMS\User\User;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Ajax handler for akaction=savelabel
 *
 * Stores a new label for a security key
 *
 * @since   4.0.0
 */
trait AjaxHandlerSaveLabel
{
    /**
     * Handle the callback to rename an authenticator
     *
     * @param   AjaxSaveLabel  $event  The event we are handling
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onAjaxWebauthnSavelabel(AjaxSaveLabel $event): void
    {
        // Load plugin language files
        $this->loadLanguage();

        // Initialize objects
        $input      = $this->getApplication()->getInput();
        $repository = $this->authenticationHelper->getCredentialsRepository();

        // Retrieve data from the request
        $credentialId = $input->getBase64('credential_id', '');
        $newLabel     = $input->getString('new_label', '');

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
            $credentialHandle = $repository->getUserHandleFor($credentialId);
            $user             = $this->getApplication()->getIdentity() ?? new User();
            $myHandle         = $repository->getHandleFromUserId($user->id);
        } catch (\Exception $e) {
            $event->addResult(false);

            return;
        }

        if ($credentialHandle !== $myHandle) {
            $event->addResult(false);

            return;
        }

        // Make sure the new label is not empty
        if (empty($newLabel)) {
            $event->addResult(false);

            return;
        }

        // Save the new label
        try {
            $repository->setLabel($credentialId, $newLabel);
        } catch (\Exception $e) {
            $event->addResult(false);

            return;
        }

        $event->addResult(true);
    }
}
