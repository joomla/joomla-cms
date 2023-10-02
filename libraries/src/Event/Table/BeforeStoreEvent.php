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
 * Event class for JTable's onBeforeStore event
 *
 * @since  4.0.0
 */
class BeforeStoreEvent extends AbstractEvent
{
    /**
     * Constructor.
     *
     * Mandatory arguments:
     * subject      JTableInterface The table we are operating on
     * updateNulls  boolean         True to update fields even if they are null.
     * k            mixed           Name of the primary key fields in the table (string or array of strings).
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     */
    public function __construct($name, array $arguments = [])
    {
        if (!\array_key_exists('updateNulls', $arguments)) {
            throw new \BadMethodCallException("Argument 'updateNulls' is required for event $name");
        }

        if (!\array_key_exists('k', $arguments)) {
            throw new \BadMethodCallException("Argument 'k' is required for event $name");
        }

        parent::__construct($name, $arguments);
    }

    /**
     * Setter for the updateNulls attribute
     *
     * @param   mixed  $value  The value to set
     *
     * @return  boolean  Normalised value
     *
     * @deprecated 4.4.0 will be removed in 6.0
     *                Use counterpart with onSet prefix
     */
    protected function setUpdateNulls($value)
    {
        return $value ? true : false;
    }

    /**
     * Setter for the updateNulls attribute
     *
     * @param   mixed  $value  The value to set
     *
     * @return  boolean  Normalised value
     *
     * @since  4.4.0
     */
    protected function onSetUpdateNulls($value)
    {
        return $this->setUpdateNulls($value);
    }
}
