<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Table;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event class for Table onBeforeBind event
 *
 * @since  4.0.0
 */
class BeforeBindEvent extends AbstractEvent
{
    /**
     * Constructor.
     *
     * Mandatory arguments:
     * subject      TableInterface The table we are operating on
     * src          mixed           An associative array or object to bind to the Table instance.
     * ignore       mixed           An optional array or space separated list of properties to ignore while binding.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     */
    public function __construct($name, array $arguments = [])
    {
        if (!\array_key_exists('src', $arguments)) {
            throw new \BadMethodCallException("Argument 'src' is required for event $name");
        }

        if (!\array_key_exists('ignore', $arguments)) {
            throw new \BadMethodCallException("Argument 'ignore' is required for event $name");
        }

        parent::__construct($name, $arguments);
    }

    /**
     * Setter for the src argument
     *
     * @param   mixed  $value  The value to set
     *
     * @return  mixed
     *
     * @throws  \BadMethodCallException  if the argument is not of the expected type
     *
     * @deprecated 4.4.0 will be removed in 6.0
     *                Use counterpart with onSet prefix
     */
    protected function setSrc($value)
    {
        if (!empty($value) && !\is_object($value) && !\is_array($value)) {
            throw new \BadMethodCallException("Argument 'src' of event {$this->name} must be empty, object or array");
        }

        return $value;
    }

    /**
     * Setter for the ignore argument
     *
     * @param   mixed  $value  The value to set
     *
     * @return  mixed
     *
     * @throws  \BadMethodCallException  if the argument is not of the expected type
     *
     * @deprecated 4.4.0 will be removed in 6.0
     *                Use counterpart with onSet prefix
     */
    protected function setIgnore($value)
    {
        if (!empty($value) && !\is_array($value)) {
            throw new \BadMethodCallException("Argument 'ignore' of event {$this->name} must be empty or array");
        }

        return $value;
    }

    /**
     * Setter for the src argument
     *
     * @param   mixed  $value  The value to set
     *
     * @return  mixed
     *
     * @throws  \BadMethodCallException  if the argument is not of the expected type
     *
     * @since  4.4.0
     */
    protected function onSetSrc($value)
    {
        return $this->setSrc($value);
    }

    /**
     * Setter for the ignore argument
     *
     * @param   mixed  $value  The value to set
     *
     * @return  mixed
     *
     * @throws  \BadMethodCallException  if the argument is not of the expected type
     *
     * @since  4.4.0
     */
    protected function onSetIgnore($value)
    {
        return $this->setIgnore($value);
    }
}
