<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\Application;

use Joomla\CMS\Router\ApiRouter;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for BeforeApiRoute event
 *
 * @since  __DEPLOY_VERSION__
 */
class BeforeApiRouteEvent extends ApplicationEvent
{
    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct($name, array $arguments = [])
    {
        if (!\array_key_exists('router', $arguments)) {
            throw new \BadMethodCallException("Argument 'router' of event {$name} is required but has not been provided");
        }

        parent::__construct($name, $arguments);
    }

    /**
     * Setter for the router argument.
     *
     * @param   ApiRouter  $value  The value to set
     *
     * @return  ApiRouter
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function setRouter(ApiRouter $value): ApiRouter
    {
        return $value;
    }

    /**
     * Get the event's document object
     *
     * @return  ApiRouter
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getRouter(): ApiRouter
    {
        return $this->arguments['router'];
    }
}
