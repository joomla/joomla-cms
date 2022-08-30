<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_login
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Login\Administrator\Dispatcher;

use Joomla\CMS\Dispatcher\ComponentDispatcher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * ComponentDispatcher class for com_login
 *
 * @since  4.0.0
 */
class Dispatcher extends ComponentDispatcher
{
    /**
     * Dispatch a controller task.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function dispatch()
    {
        // Only accept two values login and logout for `task`
        $task = $this->input->get('task');

        if ($task != 'login' && $task != 'logout') {
            $this->input->set('task', '');
        }

        // Reset controller name
        $this->input->set('controller', null);

        parent::dispatch();
    }

    /**
     * com_login does not require check permission, so we override checkAccess method and have it empty
     *
     * @return  void
     */
    protected function checkAccess()
    {
    }
}
