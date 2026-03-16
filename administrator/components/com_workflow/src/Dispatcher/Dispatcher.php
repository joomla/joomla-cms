<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\Dispatcher;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Dispatcher\ComponentDispatcher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * ComponentDispatcher class for com_workflow
 *
 * @since  4.0.0
 */
class Dispatcher extends ComponentDispatcher
{
    /**
     * Workflows have to check for extension permission
     *
     * @return  void
     */
    protected function checkAccess()
    {
        $input      = $this->app->getInput();
        $view       = $input->getCmd('view');
        $layout     = $input->getCmd('layout');
        $task       = $input->getCmd('task');
        $extension  = $input->getCmd('extension');
        $parts      = explode('.', $extension);

        $allowedTasks = [
            'graph.getWorkflow',
            'graph.getStages',
            'graph.getTransitions',
        ];

        // Allow access to the 'graph' view for all users with access
        if ($this->app->isClient('administrator') && $view === 'graph' && \in_array($task, $allowedTasks, true)) {
            return;
        }

        // Check the user has permission to edit this component
        if (!$this->app->getIdentity()->authorise('core.manage.workflow', $parts[0])) {
            throw new NotAllowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }
}
