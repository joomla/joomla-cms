<?php
/**
 * Part of the Joomla Framework Application Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application\Event;

use Joomla\Application\AbstractApplication;
use Joomla\Event\Event;

/**
 * Base event class for application events.
 *
 * @since  __DEPLOY_VERSION__
 */
class ApplicationEvent extends Event
{
	/**
	 * The active application.
	 *
	 * @var    AbstractApplication
	 * @since  __DEPLOY_VERSION__
	 */
	private $application;

	/**
	 * Event constructor.
	 *
	 * @param   string               $name         The event name.
	 * @param   AbstractApplication  $application  The active application.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(string $name, AbstractApplication $application)
	{
		parent::__construct($name);

		$this->application = $application;
	}

	/**
	 * Get the active application.
	 *
	 * @return  AbstractApplication
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getApplication(): AbstractApplication
	{
		return $this->application;
	}
}
