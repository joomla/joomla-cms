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

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * ComponentDispatcher class for com_config
 *
 * @since  __DEPLOY_VERSION__
 */
class Dispatcher extends ComponentDispatcher
{
    /**
     * Check if the user have the right access to the component config
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     *
     * @throws  \Exception
     */
    protected function checkAccess(): void
    {
        // sendtestmail expects json response, so we leave the method to handle the permission and send response itself
        if ($this->input->getCmd('task') === 'application.sendtestmail') {
            return;
        }

        $option = $this->input->getCmd('component');
        $user   = $this->app->getIdentity();

        if ($option && !in_array(strtolower($option), ['com_joomlaupdate', 'com_privacy'], true)) {
            $canAccess = $user->authorise('core.admin', $option) || $user->authorise('core.options', $option);
        } else {
            $canAccess = $user->authorise('core.admin', $option);
        }

        if (!$canAccess) {
            throw new NotAllowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }
}
