<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Event;

use Joomla\Database\DatabaseInterface;
use Joomla\Event\Event;

/**
 * Database connection event
 *
 * @since  __DEPLOY_VERSION__
 */
class ConnectionEvent extends Event
{
	/**
	 * DatabaseInterface object for this event
	 *
	 * @var    DatabaseInterface
	 * @since  __DEPLOY_VERSION__
	 */
	private $driver;

	/**
	 * Constructor.
	 *
	 * @param   string             $name    The event name.
	 * @param   DatabaseInterface  $driver  The DatabaseInterface object for this event.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(string $name, DatabaseInterface $driver)
	{
		parent::__construct($name);

		$this->driver = $driver;
	}

	/**
	 * Retrieve the DatabaseInterface object attached to this event.
	 *
	 * @return  DatabaseInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getDriver(): DatabaseInterface
	{
		return $this->driver;
	}
}
