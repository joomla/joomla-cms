<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Error
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla! Exception object.
 *
 * @package     Joomla.Platform
 * @subpackage  Error
 * @since       11.1
 * @deprecated  12.1
 */
class JException extends Exception
{
	/**
	 * @var    string  Error level.
	 * @since  11.1
	 */
	protected $level = null;

	/**
	 * @var    string  Error code.
	 * @since  11.1
	 */
	protected $code = null;

	/**
	 * @var    string  Error message.
	 * @since  11.1
	 */
	protected $message = null;

	/**
	 * Additional info about the error relevant to the developer,
	 * for example, if a database connect fails, the dsn used
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $info = '';

	/**
	 * Name of the file the error occurred in [Available if backtrace is enabled]
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $file = null;

	/**
	 * Line number the error occurred in [Available if backtrace is enabled]
	 *
	 * @var    int
	 * @since  11.1
	 */
	protected $line = 0;

	/**
	 * Name of the method the error occurred in [Available if backtrace is enabled]
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $function = null;

	/**
	 * Name of the class the error occurred in [Available if backtrace is enabled]
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $class = null;

	/**
	 * @var    string  Error type.
	 * @since  11.1
	 */
	protected $type = null;

	/**
	 * Arguments recieved by the method the error occurred in [Available if backtrace is enabled]
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $args = array();

	/**
	 * @var    mixed  Backtrace information.
	 * @since  11.1
	 */
	protected $backtrace = null;

	/**
	 * Constructor
	 * - used to set up the error with all needed error details.
	 *
	 * @param   string   $msg        The error message
	 * @param   string   $code       The error code from the application
	 * @param   integer  $level      The error level (use the PHP constants E_ALL, E_NOTICE etc.).
	 * @param   string   $info       Optional: The additional error information.
	 * @param   boolean  $backtrace  True if backtrace information is to be collected
	 *
	 * @since   11.1
	 *
	 * @deprecated  12.1
	 */
	public function __construct($msg, $code = 0, $level = null, $info = null, $backtrace = false)
	{
		// Deprecation warning.
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
	 * @since   11.1
	 *
	 * @deprecated  12.1
	 */
	public function __toString()
	{
		return $this->message;
	}

	/**
	 * Returns to error message
	 *
	 * @return  string   Error message
	 *
	 * @since   11.1
	 * @deprecated    12.1
	 */
	public function toString()
	{
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
	 * @deprecated  12.1
	 * @see         getProperties()
	 * @since       11.1
	 */
	public function get($property, $default = null)
	{
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
	 * @deprecated    12.1
	 * @see     get()
	 * @since   11.1
	 */
	public function getProperties($public = true)
	{
		$vars = get_object_vars($this);
		if ($public)
		{
			foreach ($vars as $key => $value)
			{
				if ('_' == substr($key, 0, 1))
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
	 * @since   11.1
	 *
	 * @deprecated  12.1
	 */
	public function getError($i = null, $toString = true)
	{
		// Deprecation warning.
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
	 * @since   11.1
	 *
	 * @deprecated  12.1
	 */
	public function getErrors()
	{
		// Deprecation warning.
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
	 * @deprecated  12.1
	 * @see         setProperties()
	 * @since       11.1
	 */
	public function set($property, $value = null)
	{
		// Deprecation warning.
		JLog::add('JException::set is deprecated.', JLog::WARNING, 'deprecated');

		$previous = isset($this->$property) ? $this->$property : null;
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
	 * @deprecated  12.1
	 * @see         set()
	 * @since       11.1
	 */
	public function setProperties($properties)
	{
		// Deprecation warning.
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
	 * @since   11.1
	 *
	 * @deprecated  12.1
	 */
	public function setError($error)
	{
		// Deprecation warning.
		JLog::add('JException::setErrors is deprecated.', JLog::WARNING, 'deprecated');

		array_push($this->_errors, $error);
	}
}
