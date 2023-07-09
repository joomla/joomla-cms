<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\Table;

use BadMethodCallException;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event class for JTable's onBeforePublish event
 *
 * @since  4.0.0
 */
class BeforePublishEvent extends AbstractEvent
{
    /**
     * Constructor.
     *
     * Mandatory arguments:
     * subject      JTableInterface The table we are operating on
     * pks          mixed           An optional array of primary key values to update.
     * state        int             The publishing state. eg. [0 = unpublished, 1 = published]
     * userId       int             The user id of the user performing the operation.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  BadMethodCallException
     */
    public function __construct($name, array $arguments = [])
    {
        if (!\array_key_exists('pks', $arguments)) {
            throw new BadMethodCallException("Argument 'pks' is required for event $name");
        }

        if (!\array_key_exists('state', $arguments)) {
            throw new BadMethodCallException("Argument 'state' is required for event $name");
        }

        if (!\array_key_exists('userId', $arguments)) {
            throw new BadMethodCallException("Argument 'userId' is required for event $name");
        }

        parent::__construct($name, $arguments);
    }

    /**
     * Setter for the pks argument
     *
     * @param   array|null  $value  The value to set
     *
     * @return  mixed
     *
     * @throws  BadMethodCallException  if the argument is not of the expected type
     */
    protected function setQuery($value)
    {
        if (!empty($value) && !\is_array($value)) {
            throw new BadMethodCallException("Argument 'pks' of event {$this->name} must be empty or an array");
        }

        return $value;
    }

    /**
     * Setter for the state argument
     *
     * @param   int  $value  The value to set
     *
     * @return  integer
     *
     * @throws  BadMethodCallException  if the argument is not of the expected type
     */
    protected function setState($value)
    {
        if (!is_numeric($value)) {
            throw new BadMethodCallException("Argument 'state' of event {$this->name} must be an integer");
        }

        return (int) $value;
    }

    /**
     * Setter for the userId argument
     *
     * @param   int  $value  The value to set
     *
     * @return  integer
     *
     * @throws  BadMethodCallException  if the argument is not of the expected type
     */
    protected function setUserId($value)
    {
        if (!is_numeric($value)) {
            throw new BadMethodCallException("Argument 'userId' of event {$this->name} must be an integer");
        }

        return (int) $value;
    }
}
