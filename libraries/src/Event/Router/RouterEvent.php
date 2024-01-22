<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Router;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Router\Router;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Application's Router events
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class RouterEvent extends AbstractImmutableEvent
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
     * @param   Router  $value  The value to set
     *
     * @return  Router
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function onSetRouter(Router $value): Router
    {
        return $value;
    }

    /**
     * Get the event's router object
     *
     * @return  Router
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getRouter(): Router
    {
        return $this->arguments['router'];
    }
}
