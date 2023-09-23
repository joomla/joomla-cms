<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\User;

use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Authentication\AuthenticationResponse;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for User event.
 * Example:
 *  new AuthenticationEvent('onEventName', ['credentials' => $credentials, 'options' => $options, 'subject' => $authenticationResponse]);
 *
 * @since  5.0.0
 */
class AuthenticationEvent extends UserEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  5.0.0
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['credentials', 'options', 'subject'];

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

        if (!\array_key_exists('credentials', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'credentials' of event {$name} is required but has not been provided");
        }
    }

    /**
     * Tell if the event propagation is stopped.
     * Also, the event considered "stopped" when AuthenticationResponse has STATUS_SUCCESS.
     *
     * @return  boolean  True if stopped, false otherwise.
     *
     * @since   5.0.0
     */
    public function isStopped()
    {
        if (parent::isStopped()) {
            return true;
        }

        // Check Response status
        $response = $this->getAuthenticationResponse();
        if ($response->status === Authentication::STATUS_SUCCESS) {
            return true;
        }

        return false;
    }

    /**
     * Setter for the subject argument.
     *
     * @param   AuthenticationResponse  $value  The value to set
     *
     * @return  AuthenticationResponse
     *
     * @since  5.0.0
     */
    protected function onSetSubject(AuthenticationResponse $value): AuthenticationResponse
    {
        return $value;
    }

    /**
     * Setter for the credentials argument.
     *
     * @param   array  $value  The value to set
     *
     * @return  array
     *
     * @since  5.0.0
     */
    protected function onSetCredentials(array $value): array
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
     * Getter for the response.
     *
     * @return  AuthenticationResponse
     *
     * @since  5.0.0
     */
    public function getAuthenticationResponse(): AuthenticationResponse
    {
        return $this->arguments['subject'];
    }

    /**
     * Getter for the credentials.
     *
     * @return  array
     *
     * @since  5.0.0
     */
    public function getCredentials(): array
    {
        return $this->arguments['credentials'];
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
