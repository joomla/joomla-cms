<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_messages
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Messages\Administrator\Helper;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_messages
 *
 * @since  5.1.0
 */
class MessagesHelper
{
    /**
     * Get count of unread messages.
     *
     * @param   Registry                  $params  Object holding the module parameters
     * @param   AdministratorApplication  $app     The application
     *
     * @return  integer
     *
     * @since   5.1.0
     */
    public function getUnreadMessagesCount(Registry $params, AdministratorApplication $app)
    {
        // Try to get the items from the messages model
        try {
            /**
             *  @var \Joomla\Component\Messages\Administrator\Model\MessagesModel $messagesModel
             *
             */
            $messagesModel = $app->bootComponent('com_messages')->getMVCFactory()
                ->createModel('Messages', 'Administrator', ['ignore_request' => true]);
            $messagesModel->setState('filter.state', 0);
            $messages = $messagesModel->getItems();

            return \count($messages);
        } catch (\RuntimeException $e) {
            // Still render the error message from the Exception object
            $app->enqueueMessage($e->getMessage(), 'error');

            return 0;
        }
    }
}
