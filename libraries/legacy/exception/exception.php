<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Exception
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla! Exception object.
 *
 * @since       1.5
 * @deprecated  1.7
 */
class JException extends Exception
{
	/**
	 * Error level.
	 *
	 * @var    string
	 * @since  1.5
	 * @deprecated  1.7
	 */
	protected $level = null;

	/**
	 * Error code.
	 *
	 * @var    string
	 * @since  1.5
	 * @deprecated  1.7
	 */
	protected $code = null;

	/**
	 * Error message.
	 *
	 * @var    string
	 * @since  1.5
	 * @deprecated  1.7
	 */
	protected $message = null;

	/**
	 * Additional info about the error relevant to the developer,
	 * for example, if a database connect fails, the dsn used
	 *
	 * @var    string
	 * @since  1.5
	 * @deprecated  1.7
	 */
	protected $info = '';

	/**
	 * Name of the file the error occurred in [Available if backtrace is enabled]
	 *
	 * @var    string
	 * @since  1.5
	 * @deprecated  1.7
	 */
	protected $file = null;

	/**
	 * Line number the error occurred in [Available if backtrace is enabled]
	 *
	 * @var    integer
	 * @since  1.5
	 * @deprecated  1.7
	 */
	protected $line = 0;

	/**
	 * Name of the method the error occurred in [Available if backtrace is enabled]
	 *
	 * @var    string
	 * @since  1.5
	 * @deprecated  1.7
	 */
	protected $function = null;

	/**
	 * Name of the class the error occurred in [Available if backtrace is enabled]
	 *
	 * @var    string
	 * @since  1.5
	 * @deprecated  1.7
	 */
	protected $class = null;

	/**
	 * @var    string  Error type.
	 * @since  1.5
	 * @deprecated  1.7
	 */
	protected $type = null;

	/**
	 * Arguments received by the method the error occurred in [Available if backtrace is enabled]
	 *
	 * @var    array
	 * @since  1.5
	 * @deprecated  1.7
	 */
	protected $args = array();

	/**
	 * Backtrace information.
	 *
	 * @var    mixed
	 * @since  1.5
	 * @deprecated  1.7
	 */
	protected $backtrace = null;

	/**
	 * Container holding the error messages
	 *
	 * @var    string[]
	 * @since  1.6
	 * @deprecated  1.7
	 */
	protected $_errors = array();

	/**
	 * Constructor
	 * - used to set up the error with all needed error details.
	 *
	 * @param   string   $msg        The error message
	 * @param   integer  $code       The error code from the application
	 * @param   integer  $level      The error level (use the PHP constants E_ALL, E_NOTICE etc.).
	 * @param   string   $info       Optional: The additional error information.
	 * @param   boolean  $backtrace  True if backtrace information is to be collected
	 *
	 * @since   1.5
	 * @deprecated  1.7
	 */
	public function __construct($msg, $code = 0, $level = null, $info = null, $backtrace = false)
	{
		JLog::add('JException is deprecated.', JLog::WARNING, 'deprecated');

		$this->level = $level;
		$this->code = $code;
		$this->message = $msg;

		if ($info != null)
		{
			$this->info = $info;
		}

		if ($backtrace && function_exists('debug_backtrace'))
		{
			$this->backtrace = debug_backtrace();

			for ($i = count($this->backtrace) - 1; $i >= 0; --$i)
			{
				++$i;

				if (isset($this->backtrace[$i]['file']))
				{
					$this->file = $this->backtrace[$i]['file'];
				}

				if (isset($this->backtrace[$i]['line']))
				{
					$this->line = $this->backtrace[$i]['line'];
				}

				if (isset($this->backtrace[$i]['class']))
				{
					$this->class = $this->backtrace[$i]['class'];
				}

				if (isset($this->backtrace[$i]['function']))
				{
					$this->function = $this->backtrace[$i]['function'];
				}

				if (isset($this->backtrace[$i]['type']))
				{
					$this->type = $this->backtrace[$i]['type'];
				}

				$this->args = false;

				if (isset($this->backtrace[$i]['args']))
				{
					$this->args = $this->backtrace[$i]['args'];
				}

				break;
			}
		}

		// Store exception for debugging purposes!
		JError::addToStack($this);

		parent::__construct($msg, (int) $code);
	}

	/**
	 * Returns to error message
	 *
	 * @return  string  Error message
	 *
	 * @since   1.6
	 * @deprecated  1.7
	 */
	public function __toString()
	{
		JLog::add('JException::__toString is deprecated.', JLog::WARNING, 'deprecated');

		return $this->message;
	}

	/**
	 * Returns to error message
	 *
	 * @return  string   Error message
	 *
	 * @since   1.5
	 * @deprecated  1.7
	 */
	public function toString()
	{
		JLog::add('JException::toString is deprecated.', JLog::WARNING, 'deprecated');

		return (string) $this;
	}

	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @param   string  $property  The name of the property
	 * @param   mixed   $default   The default value
	 *
	 * @return  mixed  The value of the property or null
	 *
	 * @since   1.6
	 * @deprecated  1.7
	 * @see     JException::getProperties()
	 */
	public function get($property, $default = null)
	{
		JLog::add('JException::get is deprecated.', JLog::WARNING, 'deprecated');

		if (isset($this->$property))
		{
			return $this->$property;
		}

		return $default;
	}

	/**
	 * Returns an associative array of object properties
	 *
	 * @param   boolean  $public  If true, returns only the public properties
	 *
	 * @return  array  Object properties
	 *
	 * @since   1.6
	 * @deprecated  1.7
	 * @see     JException::get()
	 */
	public function getProperties($public = true)
	{
		JLog::add('JException::getProperties is deprecated.', JLog::WARNING, 'deprecated');

		$vars = get_object_vars($this);

		if ($public)
		{
			foreach ($vars as $key => $value)
			{
				if (strpos($key, '_') === 0)
				{
					unset($vars[$key]);
				}
			}
		}

		return $vars;
	}

	/**
	 * Get the most recent error message
	 *
	 * @param   integer  $i         Option error index
	 * @param   boolean  $toString  Indicates if JError objects should return their error message
	 *
	 * @return  string  Error message
	 *
	 * @since   1.6
	 * @deprecated  1.7
	 */
	public function getError($i = null, $toString = true)
	{
		JLog::add('JException::getError is deprecated.', JLog::WARNING, 'deprecated');

		// Find the error
		if ($i === null)
		{
			// Default, return the last message
			$error = end($this->_errors);
		}
		elseif (!array_key_exists($i, $this->_errors))
		{
			// If $i has been specified but does not exist, return false
			return false;
		}
		else
		{
			$error = $this->_errors[$i];
		}

		// Check if only the string is requested
		if ($error instanceof Exception && $toString)
		{
			return (string) $error;
		}

		return $error;
	}

	/**
	 * Return all errors, if any
	 *
	 * @return  array  Array of error messages or JErrors
	 *
	 * @since   1.6
	 * @deprecated  1.7
	 */
	public function getErrors()
	{
		JLog::add('JException::getErrors is deprecated.', JLog::WARNING, 'deprecated');

		return $this->_errors;
	}

	/**
	 * Modifies a property of the object, creating it if it does not already exist.
	 *
	 * @param   string  $property  The name of the property
	 * @param   mixed   $value     The value of the property to set
	 *
	 * @return  mixed  Previous value of the property
	 *
	 * @since   1.6
	 * @deprecated  1.7
	 * @see     JException::setProperties()
	 */
	public function set($property, $value = null)
	{
		JLog::add('JException::set is deprecated.', JLog::WARNING, 'deprecated');

		$previous = $this->$property ?? null;
		$this->$property = $value;

		return $previous;
	}

	/**
	 * Set the object properties based on a named array/hash
	 *
	 * @param   mixed  $properties  Either and associative array or another object
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 * @deprecated  1.7
	 * @see     JException::set()
	 */
	public function setProperties($properties)
	{
		JLog::add('JException::setProperties is deprecated.', JLog::WARNING, 'deprecated');

		// Cast to an array
		$properties = (array) $properties;

		if (is_array($properties))
		{
			foreach ($properties as $k => $v)
			{
				$this->$k = $v;
			}

			return true;
		}

		return false;
	}

	/**
	 * Add an error message
	 *
	 * @param   string  $error  Error message
	 *
	 * @return  void
	 *
	 * @since   1.6
	 * @deprecated  1.7
	 */
	public function setError($error)
	{
		JLog::add('JException::setErrors is deprecated.', JLog::WARNING, 'deprecated');

		$this->_errors[] = $error;
	}
}
