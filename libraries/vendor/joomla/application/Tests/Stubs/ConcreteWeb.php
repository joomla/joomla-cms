<?php
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application\Tests;

use Joomla\Application\AbstractWebApplication;

/**
 * Concrete stub for the Joomla\Application\AbstractWebApplication class.
 *
 * @since  1.0
 */
class ConcreteWeb extends AbstractWebApplication
{
	/**
	 * The exit code if the application was closed otherwise null.
	 *
	 * @var     integer
	 * @since   1.0
	 */
	public $closed;

	/**
	 * True to mimic the connection being alive.
	 *
	 * @var     boolean
	 * @since   1.0
	 */
	public $connectionAlive = true;

	/**
	 * A marker to check if doExecute executes.
	 *
	 * @var     boolean
	 * @since   1.0
	 */
	public $doExecute = false;

	/**
	 * List of sent headers for inspection. array($string, $replace, $code).
	 *
	 * @var     array
	 * @since   1.0
	 */
	public $headers = array();

	/**
	 * True to mimic the headers already being sent.
	 *
	 * @var     boolean
	 * @since   1.0
	 */
	public $headersSent = false;

	/**
	 * Allows public access to protected method.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function checkConnectionAlive()
	{
		return $this->connectionAlive;
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function checkHeadersSent()
	{
		return $this->headersSent;
	}

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
		$this->doExecute = true;
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @param   string   $string   The header string.
	 * @param   boolean  $replace  The optional replace parameter indicates whether the header should
	 *                             replace a previous similar header, or add a second header of the same type.
	 * @param   integer  $code     Forces the HTTP response code to the specified value. Note that
	 *                             this parameter only has an effect if the string is not empty.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function header($string, $replace = true, $code = null)
	{
		$this->headers[] = array($string, $replace, $code);
	}
}
