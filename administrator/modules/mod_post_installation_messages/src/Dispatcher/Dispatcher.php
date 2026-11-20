<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_post_installation_messages
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\PostInstallationMessages\Administrator\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Extension\ExtensionHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_post_installation_messages
 *
 * @since  5.1.0
 */
class Dispatcher extends AbstractModuleDispatcher
{
    /**
     * Runs the dispatcher.
     *
     * @return  void
     *
     * @since   5.1.0
     */
    public function dispatch()
    {
        // Load the com_postinstall language file
        $this->getApplication()->getLanguage()->load('com_postinstall', JPATH_ADMINISTRATOR);

        parent::dispatch();
    }

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   5.1.0
     */
    public function getLayoutData()
    {
        $data = parent::getLayoutData();

        $app = $this->getApplication();

        // Try to get the items from the post-installation model
        try {
            /** @var \Joomla\Component\Postinstall\Administrator\Model\MessagesModel $messagesModel */
            $messagesModel = $app->bootComponent('com_postinstall')->getMVCFactory()
                ->createModel('Messages', 'Administrator', ['ignore_request' => true]);
            $data['messagesCount'] = $messagesModel->getItemsCount();
        } catch (\RuntimeException $e) {
            $data['messagesCount'] = 0;

            // Still render the error message from the Exception object
            $app->enqueueMessage($e->getMessage(), 'error');
        }

        $data['joomlaFilesExtensionId'] = ExtensionHelper::getExtensionRecord('joomla', 'file')->extension_id;

        return $data;
    }
}
