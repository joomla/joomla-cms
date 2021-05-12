<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Session;

\defined('JPATH_PLATFORM') or die;

/**
 * Manager for interacting with the session handler to perform updates on sessions.
 *
 * @since  __DEPLOY_VERSION__
 */
final class SessionManager
{
	/**
	 * Session handler.
	 *
	 * @var    \SessionHandlerInterface
	 * @since  __DEPLOY_VERSION__
	 */
	private $sessionHandler;

	/**
	 * SessionManager constructor.
	 *
	 * @param   \SessionHandlerInterface  $sessionHandler  Session handler.
	 *
	 * @since   __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function destroySessions(array $sessionIds): bool
	{
		$result = true;

		foreach ($sessionIds as $sessionId)
		{
			if (is_resource($sessionId) && get_resource_type($sessionId) === 'stream')
			{
				$sessionId = stream_get_contents($sessionId);
			}

			if (!$this->destroySession($sessionId))
			{
				$result = false;
			}
		}

		return $result;
	}
}
