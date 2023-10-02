<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\User;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base class for User save event
 *
 * @since  5.0.0
 */
abstract class AbstractSaveEvent extends UserEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  5.0.0
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['subject', 'isNew'];

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

        if (!\array_key_exists('isNew', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'isNew' of event {$name} is required but has not been provided");
        }
    }

    /**
     * Setter for the subject argument.
     *
     * @param   array  $value  The value to set
     *
     * @return  array
     *
     * @since  5.0.0
     */
    protected function onSetSubject(array $value): array
    {
        return $value;
    }

    /**
     * Setter for the isNew argument.
     *
     * @param   bool  $value  The value to set
     *
     * @return  bool
     *
     * @since  5.0.0
     */
    protected function onSetIsNew($value): bool
    {
        return (bool) $value;
    }

    /**
     * Getter for the user.
     *
     * @return  array
     *
     * @since  5.0.0
     */
    public function getUser(): array
    {
        return $this->arguments['subject'];
    }

    /**
     * Getter for the isNew state.
     *
     * @return  boolean
     *
     * @since  5.0.0
     */
    public function getIsNew(): bool
    {
        return $this->arguments['isNew'];
    }
}
