<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Workflow;

use BadMethodCallException;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event class for Workflow Functionality Used events
 *
 * @since  4.0.0
 */
class WorkflowTransitionEvent extends AbstractEvent
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
    public function __construct($name, array $arguments = [])
    {
        $arguments['stopTransition'] = false;

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
    public function setStopTransition($value = true)
    {
        $this->arguments['stopTransition'] = $value;

        if ($value === true) {
            $this->stopPropagation();
        }
    }
}
