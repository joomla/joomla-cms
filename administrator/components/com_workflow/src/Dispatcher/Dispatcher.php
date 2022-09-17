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
        $extension = $this->getApplication()->input->getCmd('extension');

        $parts = explode('.', $extension);

        // Check the user has permission to access this component if in the backend
        if ($this->app->isClient('administrator') && !$this->app->getIdentity()->authorise('core.manage.workflow', $parts[0])) {
            throw new NotAllowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }
}
