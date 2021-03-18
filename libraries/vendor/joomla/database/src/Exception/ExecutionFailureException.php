<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Exception;

/**
 * Exception class defining an error executing a statement
 *
 * @since  1.5.0
 */
class ExecutionFailureException extends \RuntimeException
{
	/**
	 * The SQL statement that was executed.
	 *
	 * @var    string
	 * @since  1.5.0
	 */
	private $query;

	/**
	 * Construct the exception
	 *
	 * @param   string     $query     The SQL statement that was executed.
	 * @param   string     $message   The Exception message to throw. [optional]
	 * @param   integer    $code      The Exception code. [optional]
	 * @param   Exception  $previous  The previous exception used for the exception chaining. [optional]
	 *
	 * @since   1.5.0
	 */
	public function __construct($query, $message = '', $code = 0, \Exception $previous = null)
	{
		// PDO uses strings for exception codes, PHP forces numeric codes, so "force" the string code to be used
		parent::__construct($message, 0, $previous);

		$this->code  = $code;
		$this->query = $query;
	}

	/**
	 * Get the SQL statement that was executed
	 *
	 * @return  string
	 *
	 * @since   1.5.0
	 */
	public function getQuery()
	{
		return $this->query;
	}
}
