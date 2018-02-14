<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Application;

use Joomla\CMS\User\User;
use Joomla\Session\SessionInterface;

/**
 * Interface defining a Joomla! CMS Application class
 *
 * @since  4.0.0
 */
interface CMSApplicationInterface
{
	/**
	 * Constant defining an enqueued emergency message
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	const MSG_EMERGENCY = 'emergency';

	/**
	 * Constant defining an enqueued alert message
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	const MSG_ALERT = 'alert';

	/**
	 * Constant defining an enqueued critical message
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	const MSG_CRITICAL = 'critical';

	/**
	 * Constant defining an enqueued error message
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	const MSG_ERROR = 'error';

	/**
	 * Constant defining an enqueued warning message
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	const MSG_WARNING = 'warning';

	/**
	 * Constant defining an enqueued notice message
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	const MSG_NOTICE = 'notice';

	/**
	 * Constant defining an enqueued info message
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	const MSG_INFO = 'info';

	/**
	 * Constant defining an enqueued debug message
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	const MSG_DEBUG = 'debug';

	/**
	 * Enqueue a system message.
	 *
	 * @param   string  $msg   The message to enqueue.
	 * @param   string  $type  The message type.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function enqueueMessage($msg, $type = self::MSG_INFO);

	/**
	 * Get the system message queue.
	 *
	 * @return  array  The system message queue.
	 *
	 * @since   4.0.0
	 */
	public function getMessageQueue();

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function execute();

	/**
	 * Check the client interface by name.
	 *
	 * @param   string  $identifier  String identifier for the application interface
	 *
	 * @return  boolean  True if this application is of the given type client interface.
	 *
	 * @since   4.0.0
	 */
	public function isClient($identifier);

	/**
	 * Method to get the application session object.
	 *
	 * @return  SessionInterface  The session object
	 *
	 * @since   4.0.0
	 */
	public function getSession();

	/**
	 * Flag if the application instance is a CLI or web based application.
	 *
	 * Helper function, you should use the native PHP functions to detect if it is a CLI application.
	 *
	 * @return  boolean
	 *
	 * @since       4.0.0
	 * @deprecated  5.0  Will be removed without replacements
	 */
	public function isCli();

	/**
	 * Get the application identity.
	 *
	 * @return  User|null  A User object or null if not set.
	 *
	 * @since   4.0.0
	 */
	public function getIdentity();

	/**
	 * Allows the application to load a custom or default identity.
	 *
	 * @param   User  $identity  An optional identity object. If omitted, the factory user is created.
	 *
	 * @return  $this
	 *
	 * @since   4.0.0
	 */
	public function loadIdentity(User $identity = null);
}
