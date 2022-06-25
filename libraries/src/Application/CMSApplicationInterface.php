<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Application;

use Joomla\Application\ConfigurationAwareApplicationInterface;
use Joomla\CMS\Extension\ExtensionManagerInterface;
use Joomla\CMS\Language\Language;
use Joomla\CMS\User\User;
use Joomla\Input\Input;

/**
 * Interface defining a Joomla! CMS Application class
 *
 * @since  4.0.0
 * @note   In Joomla 5 this interface will no longer extend EventAwareInterface
 * @property-read   Input  $input  {@deprecated 5.0} The Joomla Input property. Deprecated in favour of getInput()
 */
interface CMSApplicationInterface extends ExtensionManagerInterface, ConfigurationAwareApplicationInterface, EventAwareInterface
{
    /**
     * Constant defining an enqueued emergency message
     *
     * @var    string
     * @since  4.0.0
     */
    public const MSG_EMERGENCY = 'emergency';

    /**
     * Constant defining an enqueued alert message
     *
     * @var    string
     * @since  4.0.0
     */
    public const MSG_ALERT = 'alert';

    /**
     * Constant defining an enqueued critical message
     *
     * @var    string
     * @since  4.0.0
     */
    public const MSG_CRITICAL = 'critical';

    /**
     * Constant defining an enqueued error message
     *
     * @var    string
     * @since  4.0.0
     */
    public const MSG_ERROR = 'error';

    /**
     * Constant defining an enqueued warning message
     *
     * @var    string
     * @since  4.0.0
     */
    public const MSG_WARNING = 'warning';

    /**
     * Constant defining an enqueued notice message
     *
     * @var    string
     * @since  4.0.0
     */
    public const MSG_NOTICE = 'notice';

    /**
     * Constant defining an enqueued info message
     *
     * @var    string
     * @since  4.0.0
     */
    public const MSG_INFO = 'info';

    /**
     * Constant defining an enqueued debug message
     *
     * @var    string
     * @since  4.0.0
     */
    public const MSG_DEBUG = 'debug';

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
     * Method to get the application input object.
     *
     * @return  Input
     *
     * @since   4.0.0
     */
    public function getInput(): Input;

    /**
     * Method to get the application language object.
     *
     * @return  Language  The language object
     *
     * @since   4.0.0
     */
    public function getLanguage();

    /**
     * Gets the name of the current running application.
     *
     * @return  string  The name of the application.
     *
     * @since   4.0.0
     */
    public function getName();

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
