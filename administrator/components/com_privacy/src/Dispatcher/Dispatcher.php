<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Administrator\Dispatcher;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Dispatcher\ComponentDispatcher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Component dispatcher class for com_privacy
 *
 * @since  4.0.0
 */
class Dispatcher extends ComponentDispatcher
{
    /**
     * Method to check component access permission
     *
     * @return  void
     */
    protected function checkAccess()
    {
        // Check the user has permission to access this component if in the backend
        if ($this->app->isClient('administrator') && !$this->app->getIdentity()->authorise('core.admin', $this->option)) {
            throw new NotAllowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }
}
