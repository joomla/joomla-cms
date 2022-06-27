<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\Workflow;

use BadMethodCallException;

/**
 * Event class for Workflow Functionality Used events
 *
 * @since  4.0.0
 */
class WorkflowFunctionalityUsedEvent extends AbstractEvent
{
    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  BadMethodCallException
     *
     * @since   4.0.0
     */
    public function __construct($name, array $arguments = array())
    {
        $arguments['used'] = false;

        parent::__construct($name, $arguments);
    }

    /**
     * Set used parameter to true
     *
     * @param   bool  $value  The value to set
     *
     * @return void
     *
     * @since   4.0.0
     */
    public function setUsed($value = true)
    {
        $this->arguments['used'] = $value;

        if ($value === true) {
            $this->stopPropagation();
        }
    }
}
