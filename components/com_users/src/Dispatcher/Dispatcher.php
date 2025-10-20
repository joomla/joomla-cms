<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Site\Dispatcher;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * ComponentDispatcher class for com_users
 *
 * @since  5.2.3
 */
class Dispatcher extends ComponentDispatcher
{
    /**
     * Method to check component access permission
     *
     * @since   5.2.3
     *
     * @return  void
     */
    protected function checkAccess()
    {
        parent::checkAccess();

        $view = $this->input->get('view');
        $user = $this->app->getIdentity();
        $task = $this->input->get('task', 'display');

        // Ignore any-non-"display" tasks
        if (str_contains($task, '.')) {
            $task = explode('.', $task)[1];
        }

        if ($task !== 'display') {
            return;
        }

        // Do any specific processing by view.
        switch ($view) {
            case 'registration':
                // If the user is already logged in, redirect to the profile page.
                if ($user->guest != 1) {
                    // Redirect to profile page.
                    $this->app->redirect(Route::_('index.php?option=com_users&view=profile', false));
                }

                // Check if user registration is enabled
                if (ComponentHelper::getParams('com_users')->get('allowUserRegistration') == 0) {
                    // Registration is disabled - Redirect to login page.
                    $this->app->redirect(Route::_('index.php?option=com_users&view=login', false));
                }
                break;

                // Handle view specific models.
            case 'profile':
                if ($user->guest == 1) {
                    // Redirect to login page.
                    $this->app->redirect(Route::_('index.php?option=com_users&view=login', false));
                }
                break;

            case 'remind':
            case 'reset':
                if ($user->guest != 1) {
                    // Redirect to profile page.
                    $this->app->redirect(Route::_('index.php?option=com_users&view=profile', false));
                }
        }
    }
}
