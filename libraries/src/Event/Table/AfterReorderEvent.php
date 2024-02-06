<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Table;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event class for JTable's onAfterReorder event
 *
 * @since  4.0.0
 */
class AfterReorderEvent extends AbstractEvent
{
    /**
     * Constructor.
     *
     * Mandatory arguments:
     * subject      JTableInterface The table we are operating on
     * rows         stdClass[]|null The primary keys and ordering values for the selection.
     * where        string          WHERE clause which was used for limiting the selection of rows to compact the ordering values.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     */
    public function __construct($name, array $arguments = [])
    {
        if (!\array_key_exists('where', $arguments)) {
            throw new \BadMethodCallException("Argument 'ignore' is required for event $name");
        }

        parent::__construct($name, $arguments);
    }

    /**
     * Setter for the where argument
     *
     * @param   array|string|null  $value  A string or array of where conditions.
     *
     * @return  mixed
     *
     * @throws  \BadMethodCallException  if the argument is not of the expected type
     *
     * @deprecated 4.4.0 will be removed in 6.0
     *                Use counterpart with onSet prefix
     */
    protected function setWhere($value)
    {
        if (!empty($value) && !\is_string($value) && !\is_array($value)) {
            throw new \BadMethodCallException("Argument 'where' of event {$this->name} must be empty or string or array of strings");
        }

        return $value;
    }

    /**
     * Setter for the where argument
     *
     * @param   array|string|null  $value  A string or array of where conditions.
     *
     * @return  mixed
     *
     * @throws  \BadMethodCallException  if the argument is not of the expected type
     *
     * @since  4.4.0
     */
    protected function onSetWhere($value)
    {
        return $this->setWhere($value);
    }
}
