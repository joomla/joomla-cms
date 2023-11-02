<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Table;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Table\TableInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event class for the Table's events
 *
 * @since  4.0.0
 */
abstract class AbstractEvent extends AbstractImmutableEvent
{
    /**
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     *
     * @since   1.0
     */
    public function __construct($name, array $arguments = [])
    {
        if (!\array_key_exists('subject', $arguments)) {
            throw new \BadMethodCallException("Argument 'subject' of event {$this->name} is required but has not been provided");
        }

        parent::__construct($name, $arguments);
    }

    /**
     * Setter for the subject argument
     *
     * @param   TableInterface  $value  The value to set
     *
     * @return  TableInterface
     *
     * @throws  \BadMethodCallException  If the argument is not of the expected type.
     *
     * @deprecated 4.4.0 will be removed in 6.0
     *                Use counterpart with onSet prefix
     */
    protected function setSubject($value)
    {
        if (!\is_object($value) || !($value instanceof TableInterface)) {
            throw new \BadMethodCallException("Argument 'subject' of event {$this->name} is not of the expected type");
        }

        return $value;
    }

    /**
     * Setter for the subject argument
     *
     * @param   TableInterface  $value  The value to set
     *
     * @return  TableInterface
     *
     * @throws  \BadMethodCallException  If the argument is not of the expected type.
     *
     * @since  4.4.0
     */
    protected function onSetSubject($value): TableInterface
    {
        return $this->setSubject($value);
    }
}
