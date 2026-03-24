<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Contact;

use Joomla\CMS\Event\AbstractImmutableEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Contact events
 *
 * @since  5.0.0
 */
class SubmitContactEvent extends AbstractImmutableEvent
{
    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     *
     * @since   5.0.0
     */
    public function __construct($name, array $arguments = [])
    {
        parent::__construct($name, $arguments);

        if (!\array_key_exists('subject', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'subject' of event {$name} is required but has not been provided");
        }

        if (!\array_key_exists('data', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'data' of event {$name} is required but has not been provided");
        }

        if (key($arguments) === 0) {
            $this->arguments['data'] = $arguments[1];
        } elseif (\array_key_exists('data', $arguments)) {
            $this->arguments['data'] = $arguments['data'];
        }
    }

    /**
     * Setter for the subject argument.
     *
     * @param   object  $value  The value to set
     *
     * @return  object
     *
     * @since  5.0.0
     */
    protected function onSetSubject(object $value): object
    {
        return $value;
    }

    /**
     * Setter for the data argument.
     *
     * @param   array  $value  The value to set
     *
     * @return  array
     *
     * @since  5.0.0
     */
    protected function onSetData(array $value): array
    {
        return $value;
    }

    /**
     * Getter for the contact.
     *
     * @return  object
     *
     * @since  5.0.0
     */
    public function getContact(): object
    {
        return $this->arguments['subject'];
    }

    /**
     * Getter for the data.
     *
     * @return  array
     *
     * @since  5.0.0
     */
    public function getData(): array
    {
        return $this->arguments['data'];
    }

    /**
     * Update the data.
     *
     * @param   array  $value  The value to set
     *
     * @return  static
     *
     * @since  5.0.0
     */
    public function updateData(array $value): static
    {
        $this->arguments['data'] = $this->onSetData($value);

        return $this;
    }
}
