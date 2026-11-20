<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Dispatcher;

use Joomla\CMS\Dispatcher\ComponentDispatcher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * ComponentDispatcher class for com_users
 *
 * @since  4.0.0
 */
class Dispatcher extends ComponentDispatcher
{
    /**
     * Override checkAccess to allow users edit profile without having to have core.manager permission
     *
     * @return  void
     *
     * @since  4.0.0
     */
    protected function checkAccess()
    {
        $task         = $this->input->getCmd('task');
        $view         = $this->input->getCmd('view');

        $allowedTasks = ['user.edit', 'user.apply', 'user.save', 'user.cancel'];

        // Allow users to edit their own account
        if (\in_array($task, $allowedTasks, true) || (\in_array($view, ['user']) && !$task)) {
            $user = $this->app->getIdentity();
            $id   = $this->input->getInt('id');

            if ((int) $user->id === $id) {
                return;
            }
        }

        /**
         * Special case: Multi-factor Authentication
         *
         * We allow access to all MFA views and tasks. Access control for MFA tasks is performed in
         * the Controllers since what is allowed depends on who is logged in and whose account you
         * are trying to modify. Implementing these checks in the Dispatcher would violate the
         * separation of concerns.
         */
        $allowedTasks =  [
            'captive.captive', 'captive.validate', 'callback.callback', 'captive.select',
            'method.add', 'method.edit', 'method.regenerateBackupCodes', 'method.delete', 'method.save',
            'methods.display', 'methods.disable', 'methods.doNotShowThisAgain',
        ];

        if (\in_array($task, $allowedTasks) || (\in_array($view, ['captive', 'methods', 'method']) && !$task)) {
            return;
        }

        parent::checkAccess();
    }
}
