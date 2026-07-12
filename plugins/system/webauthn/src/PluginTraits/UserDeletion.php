<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Webauthn
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Webauthn\PluginTraits;

use Joomla\CMS\Event\User\AfterDeleteEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Delete all WebAuthn credentials for a particular user
 *
 * @since   4.0.0
 */
trait UserDeletion
{
    /**
     * Remove all passwordless credential information for the given user ID.
     *
     * This method is called after user data is deleted from the database.
     *
     * @param   AfterDeleteEvent  $event  The event we are handling
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onUserAfterDelete(AfterDeleteEvent $event): void
    {
        $user    = $event->getUser();
        $success = $event->getDeletingResult();
        $msg     = $event->getErrorMessage();

        if (!$success) {
            return;
        }

        $userId = ArrayHelper::getValue($user, 'id', 0, 'int');

        if ($userId) {
            Log::add("Removing WebAuthn Passwordless Login information for deleted user #{$userId}", Log::DEBUG, 'webauthn.system');

            /** @var DatabaseInterface $db */
            $db         = Factory::getContainer()->get(DatabaseInterface::class);
            $repository = $this->authenticationHelper->getCredentialsRepository();
            $userHandle = $repository->getHandleFromUserId($userId);

            $query = $db->createQuery()
                ->delete($db->quoteName('#__webauthn_credentials'))
                ->where($db->quoteName('user_id') . ' = :userHandle')
                ->bind(':userHandle', $userHandle);

            try {
                $db->setQuery($query)->execute();
            } catch (\Exception) {
                // Don't worry if this fails
            }

            $this->returnFromEvent($event, true);
        }
    }
}
