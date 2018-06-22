<?php
/**
 * Part of the Joomla Framework Application Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application\Event;

use Joomla\Application\AbstractApplication;
use Joomla\Application\ApplicationEvents;
use Joomla\Event\Event;

/**
 * Event class thrown when an application error occurs.
 *
 * @since  __DEPLOY_VERSION__
 */
class ApplicationErrorEvent extends ApplicationEvent
{
	/**
	 * The Throwable object with the error data.
	 *
	 * @var    \Throwable
	 * @since  __DEPLOY_VERSION__
	 */
	private $error;

	/**
	 * Event constructor.
	 *
	 * @param   \Throwable           $error        The Throwable object with the error data.
	 * @param   AbstractApplication  $application  The active application.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(\Throwable $error, AbstractApplication $application)
	{
		parent::__construct(ApplicationEvents::ERROR, $application);

		$this->error = $error;
	}

	/**
	 * Get the error object.
	 *
	 * @return  \Throwable
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getError(): \Throwable
	{
		return $this->error;
	}

	/**
	 * Set the error object.
	 *
	 * @param   \Throwable  $error  The error object to set to the event.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setError(\Throwable $error)
	{
		$this->error = $error;
	}
}
