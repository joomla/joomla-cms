<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Dispatcher;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Application\ApiApplication;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * API Implementation for our dispatcher. It loads a component's administrator language files, and calls the API
 * Controller so that components that haven't implemented web services can add their own handling.
 *
 * @since  4.0.0
 */
class ApiDispatcher extends ComponentDispatcher
{
    /**
     * Load the component's administrator language
     *
     * @since   4.0.0
     *
     * @return  void
     */
    protected function loadLanguage()
    {
        // Load common and local language files.
        $this->app->getLanguage()->load($this->option, JPATH_BASE) ||
        $this->app->getLanguage()->load($this->option, JPATH_ADMINISTRATOR) ||
        $this->app->getLanguage()->load($this->option, JPATH_ADMINISTRATOR . '/components/' . $this->option);
    }

    /**
     * Method to check component access permission
     *
     * @return  void
     *
     * @since   4.0.0
     *
     * @throws  NotAllowed
     */
    protected function checkAccess(): void
    {
        $route = $this->app instanceof ApiApplication ? $this->app->getActiveRoute() : new Registry();

        if ($route->get('vars.public') === true) {
            return;
        }

        if (!$this->app->getIdentity()->authorise('core.api', $this->option)) {
            throw new NotAllowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

    /**
     * Dispatch a controller task. API Overrides to ensure there is no redirects.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function dispatch()
    {
        // Check component access permission
        $this->checkAccess();

        $task = $this->input->getCmd('task', 'display');

        // Build controller config data
        $config = ['option' => $this->option];

        // Set name of controller if it is passed in the request
        if ($this->input->exists('controller')) {
            $config['name'] = strtolower($this->input->get('controller'));
        }

        $controller = $this->input->get('controller');
        $controller = $this->getController($controller, ucfirst($this->app->getName()), $config);

        $controller->execute($task);
    }
}
