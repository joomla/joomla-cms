<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_messages
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Messages\Administrator\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_messages
 *
 * @since   5.1.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Runs the dispatcher.
     *
     * @return  void
     *
     * @since   5.1.0
     */
    public function dispatch()
    {
        // Check permissions.
        if (
            !$this->getApplication()->getIdentity()->authorise('core.login.admin')
            || !$this->getApplication()->getIdentity()->authorise('core.manage', 'com_messages')
        ) {
            return;
        }

        parent::dispatch();
    }

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   5.1.0
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        $data['countUnread'] = $this->getHelperFactory()->getHelper('MessagesHelper')->getUnreadMessagesCount($data['params'], $this->getApplication());

        return $data;
    }
}
