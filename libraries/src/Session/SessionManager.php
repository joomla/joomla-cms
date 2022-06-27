<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Session;

/**
 * Manager for interacting with the session handler to perform updates on sessions.
 *
 * @since  4.0.0
 */
final class SessionManager
{
    /**
     * Session handler.
     *
     * @var    \SessionHandlerInterface
     * @since  4.0.0
     */
    private $sessionHandler;

    /**
     * SessionManager constructor.
     *
     * @param   \SessionHandlerInterface  $sessionHandler  Session handler.
     *
     * @since   4.0.0
     */
    public function __construct(\SessionHandlerInterface $sessionHandler)
    {
        $this->sessionHandler = $sessionHandler;
    }

    /**
     * Destroys the given session ID.
     *
     * @param   string  $sessionId  The session ID to destroy.
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    public function destroySession(string $sessionId): bool
    {
        return $this->sessionHandler->destroy($sessionId);
    }

    /**
     * Destroys the given session IDs.
     *
     * @param   string[]  $sessionIds  The session IDs to destroy.
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    public function destroySessions(array $sessionIds): bool
    {
        $result = true;

        foreach ($sessionIds as $sessionId) {
            if (!$this->destroySession($sessionId)) {
                $result = false;
            }
        }

        return $result;
    }
}
