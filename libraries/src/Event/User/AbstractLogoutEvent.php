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
 * Base class for User logout event
 *
 * @since  5.0.0
 */
abstract class AbstractLogoutEvent extends UserEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  5.0.0
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['subject', 'options'];

    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     *
     * @since   5.2.0
     */
    public function __construct($name, array $arguments = [])
    {
        if (\count($arguments) === 1) {
            // @TODO: Remove in Joomla 7, and set 'options' argument as required.
            $missingKey = empty($arguments['subject']) ? 'subject' : 'options';

            $arguments[key($arguments) === 0 ? 1 : $missingKey] = [];

            @trigger_error(
                \sprintf('The event %s requires 2 arguments. Use of 1 argument will throw an exception in Joomla 7.', $this->getName()),
                E_USER_DEPRECATED
            );
        }

        parent::__construct($name, $arguments);
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
     * Setter for the options argument.
     *
     * @param   array  $value  The value to set
     *
     * @return  array
     *
     * @since  5.0.0
     */
    protected function onSetOptions(array $value): array
    {
        return $value;
    }

    /**
     * Getter for the parameters.
     *
     * @return  array
     *
     * @since  5.0.0
     */
    public function getParameters(): array
    {
        return $this->arguments['subject'];
    }

    /**
     * Getter for the options.
     *
     * @return  array
     *
     * @since  5.0.0
     */
    public function getOptions(): array
    {
        return $this->arguments['options'] ?? [];
    }
}
