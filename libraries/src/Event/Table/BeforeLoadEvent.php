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
 * Event class for JTable's onBeforeLoad event
 *
 * @since  4.0.0
 */
class BeforeLoadEvent extends AbstractEvent
{
    /**
     * Constructor.
     *
     * Mandatory arguments:
     * subject  JTableInterface The table we are operating on
     * keys     mixed           The optional primary key value to load the row by, or an array of fields to match.
     * reset    boolean         True to reset the default values before loading the new row.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     */
    public function __construct($name, array $arguments = [])
    {
        if (!\array_key_exists('keys', $arguments)) {
            throw new \BadMethodCallException("Argument 'keys' is required for event $name");
        }

        if (!\array_key_exists('reset', $arguments)) {
            throw new \BadMethodCallException("Argument 'reset' is required for event $name");
        }

        parent::__construct($name, $arguments);
    }

    /**
     * Setter for the reset attribute
     *
     * @param   mixed  $value  The value to set
     *
     * @return  boolean  Normalised value
     *
     * @deprecated 4.4.0 will be removed in 6.0
     *                Use counterpart with onSet prefix
     */
    protected function setReset($value)
    {
        return $value ? true : false;
    }

    /**
     * Setter for the reset attribute
     *
     * @param   mixed  $value  The value to set
     *
     * @return  boolean  Normalised value
     *
     * @since  4.4.0
     */
    protected function onSetReset($value)
    {
        return $this->setReset($value);
    }
}
