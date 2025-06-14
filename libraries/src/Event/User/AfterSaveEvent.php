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
 * Class for User save event.
 * Example:
 *  new AfterSaveEvent('onEventName', ['subject' => $userArray, 'isNew' => $isNew, 'savingResult' => $result, 'errorMessage' => $errorStr]);
 *
 * @since  5.0.0
 */
class AfterSaveEvent extends AbstractSaveEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  5.0.0
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['subject', 'isNew', 'savingResult', 'errorMessage'];

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

        if (!\array_key_exists('savingResult', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'savingResult' of event {$name} is required but has not been provided");
        }
    }

    /**
     * Setter for the savingResult argument.
     *
     * @param   bool  $value  The value to set
     *
     * @return  bool
     *
     * @since  5.0.0
     */
    protected function onSetSavingResult(bool $value): bool
    {
        return $value;
    }

    /**
     * Setter for the errorMessage argument.
     *
     * @param   ?string  $value  The value to set
     *
     * @return  ?string
     *
     * @since  5.0.0
     */
    protected function onSetErrorMessage(?string $value): ?string
    {
        return $value;
    }

    /**
     * Getter for the saving result.
     *
     * @return  bool
     *
     * @since  5.0.0
     */
    public function getSavingResult(): bool
    {
        return $this->arguments['savingResult'];
    }

    /**
     * Getter for the error message.
     *
     * @return  string
     *
     * @since  5.0.0
     */
    public function getErrorMessage(): string
    {
        return $this->arguments['errorMessage'] ?? '';
    }
}
