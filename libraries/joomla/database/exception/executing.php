<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Exception class defining an error executing a statement
 *
 * @since  3.6
 */
class JDatabaseExceptionExecuting extends RuntimeException
{
	/**
	 * The SQL statement that was executed.
	 *
	 * @var    string
	 * @since  3.6
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
	 * @since   3.6
	 */
	public function __construct($query, $message = '', $code = 0, Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);

		$this->query = $query;
	}

	/**
	 * Get the SQL statement that was executed
	 *
	 * @return  string
	 *
	 * @since   3.6
	 */
	public function getQuery()
	{
		return $this->query;
	}
}
