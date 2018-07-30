<?php
/**
 * Part of the Joomla Framework Application Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application;

/**
 * Class defining the events available in the application.
 *
 * @since  __DEPLOY_VERSION__
 */
final class ApplicationEvents
{
	/**
	 * The ERROR event is an event triggered when a Throwable is uncaught.
	 *
	 * This event allows you to inspect the Throwable and implement additional error handling/reporting mechanisms.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	const ERROR = 'application.error';

	/**
	 * The BEFORE_EXECUTE event is an event triggered before the application is executed.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	const BEFORE_EXECUTE = 'application.before_execute';

	/**
	 * The AFTER_EXECUTE event is an event triggered after the application is executed.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	const AFTER_EXECUTE = 'application.after_execute';

	/**
	 * The BEFORE_RESPOND event is an event triggered before the application response is sent.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	const BEFORE_RESPOND = 'application.before_respond';

	/**
	 * The AFTER_RESPOND event is an event triggered after the application response is sent.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	const AFTER_RESPOND = 'application.after_respond';
}
