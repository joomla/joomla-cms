<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Authentication\Password;

use Joomla\Authentication\Password\HandlerInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Password handler supporting testing against a chain of handlers
 *
 * @since  4.0.0
 */
class ChainedHandler implements HandlerInterface, CheckIfRehashNeededHandlerInterface
{
    /**
     * The password handlers in use by this chain.
     *
     * @var    HandlerInterface[]
     * @since  4.0.0
     */
    private $handlers = [];

    /**
     * Add a handler to the chain
     *
     * @param   HandlerInterface  $handler  The password handler to add
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function addHandler(HandlerInterface $handler)
    {
        $this->handlers[] = $handler;
    }

    /**
     * Check if the password requires rehashing
     *
     * @param   string  $hash  The password hash to check
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    public function checkIfRehashNeeded(string $hash): bool
    {
        foreach ($this->handlers as $handler) {
            if ($handler instanceof CheckIfRehashNeededHandlerInterface && $handler::isSupported() && $handler->checkIfRehashNeeded($hash)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate a hash for a plaintext password
     *
     * @param   string  $plaintext  The plaintext password to validate
     * @param   array   $options    Options for the hashing operation
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws  \RuntimeException
     */
    public function hashPassword($plaintext, array $options = [])
    {
        throw new \RuntimeException('The chained password handler cannot be used to hash a password');
    }

    /**
     * Check that the password handler is supported in this environment
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    public static function isSupported()
    {
        return true;
    }

    /**
     * Validate a password
     *
     * @param   string  $plaintext  The plain text password to validate
     * @param   string  $hashed     The password hash to validate against
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    public function validatePassword($plaintext, $hashed)
    {
        foreach ($this->handlers as $handler) {
            if ($handler::isSupported() && $handler->validatePassword($plaintext, $hashed)) {
                return true;
            }
        }

        return false;
    }
}
