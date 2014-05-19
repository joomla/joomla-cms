<?php
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application\Tests;

use Joomla\Application\AbstractCliApplication;

/**
 * Concrete stub for the Joomla\Application\AbstractCliApplication class.
 *
 * @since  1.0
 */
class ConcreteCli extends AbstractCliApplication
{
	/**
	 * The exit code if the application was closed otherwise null.
	 *
	 * @var     integer
	 * @since   1.0
	 */
	public $closed;

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
	 * Allows public access to protected method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function doExecute()
	{
		return;
	}
}
