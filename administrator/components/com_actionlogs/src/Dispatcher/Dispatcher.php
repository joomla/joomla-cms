<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Actionlogs\Administrator\Dispatcher;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Dispatcher\ComponentDispatcher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * ComponentDispatcher class for com_admin
 *
 * @since  __DEPLOY_VERSION__
 */
class Dispatcher extends ComponentDispatcher
{
    /**
     * com_admin does not require check permission, so we override checkAccess method and have it empty
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    protected function checkAccess()
    {
        $user = $this->app->getIdentity();

        // Access check
        if (!$user->authorise('core.admin', )) {
            throw new NotAllowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }
}
