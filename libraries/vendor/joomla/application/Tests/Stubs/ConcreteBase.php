<?php
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application\Tests;

use Joomla\Application\AbstractApplication;

/**
 * Concrete stub for the Joomla\Application\AbstractApplication class.
 *
 * @since  1.0
 */
class ConcreteBase extends AbstractApplication
{
	/**
	 * The exit code if the application was closed otherwise null.
	 *
	 * @var     integer
	 * @since   1.0
	 */
	public $closed;

	/**
	 * A marker to check if doExecute executes.
	 *
	 * @var     boolean
	 * @since   1.0
	 */
	public $doExecute = false;

	/**
	 * Mimic exiting the application.
	 *
	 * @param   integer  $code  The exit code (optional; default is 0).
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function close($code = 0)
	{
		$this->closed = $code;
	}

	/**
	 * Method to run the application routines.  Most likely you will want to instantiate a controller
	 * and execute it, or perform some sort of task directly.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function doExecute()
	{
		$this->doExecute = true;
	}
}
