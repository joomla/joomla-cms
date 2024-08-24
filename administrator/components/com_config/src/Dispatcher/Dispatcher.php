<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Administrator\Dispatcher;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\Component\Config\Administrator\Helper\ConfigHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * ComponentDispatcher class for com_config
 *
 * @since  4.2.9
 */
class Dispatcher extends ComponentDispatcher
{
    /**
     * Check if the user have the right access to the component config
     *
     * @return  void
     *
     * @since  4.2.9
     *
     * @throws  \Exception
     */
    protected function checkAccess(): void
    {
        // sendtestmail and store do their own checks, so leave the method to handle the permission and send response itself
        if (\in_array($this->input->getCmd('task'), ['application.sendtestmail', 'application.store'], true)) {
            return;
        }

        $task      = $this->input->getCmd('task', 'display');
        $view      = $this->input->getCmd('view');
        $component = $this->input->getCmd('component');

        if ($component && (substr($task, 0, 10) === 'component.' || $view === 'component')) {
            // User is changing component settings, check if he has permission to do that
            $canAccess = ConfigHelper::canChangeComponentConfig($component);
        } else {
            // For everything else, user is required to have global core.admin permission to perform action
            $canAccess = $this->app->getIdentity()->authorise('core.admin');
        }

        if (!$canAccess) {
            throw new NotAllowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }
}
