<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\User;

use Joomla\CMS\Authentication\AuthenticationResponse;
use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultAwareInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for User event.
 * Example:
 *  new AuthorisationEvent('onEventName', ['subject' => $authenticationResponse, 'options' => $options]);
 *
 * @since  5.0.0
 */
class AuthorisationEvent extends UserEvent implements ResultAwareInterface
{
    use ResultAware;

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
     * Checks the type of the data being appended to the result argument.
     *
     * @param   mixed  $data  The data to type check
     *
     * @return  void
     * @throws  \InvalidArgumentException
     *
     * @internal
     * @since   5.0.0
     */
    public function typeCheckResult($data): void
    {
        if (!$data instanceof AuthenticationResponse) {
            throw new \InvalidArgumentException(\sprintf('Event %s only accepts AuthenticationResponse results.', $this->getName()));
        }
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
