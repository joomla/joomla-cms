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
 * @since  5.1.0
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
     * @since   5.1.0
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
     * @since  5.1.0
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
     * @since  5.1.0
     */
    public function getRouter(): Router
    {
        return $this->arguments['router'];
    }
}
