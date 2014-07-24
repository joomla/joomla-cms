<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  input
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * FrameworkOnFramework input handling class. Extends upon the JInput class.
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFInput extends JInput
{
	/**
	 * Public constructor. Overriden to allow specifying the global input array
	 * to use as a string and instantiate from an objetc holding variables.
	 *
	 * @param   array|string|object|null  $source   Source data; set null to use $_REQUEST
	 * @param   array                     $options  Filter options
	 */
	public function __construct($source = null, array $options = array())
	{
		$hash = null;

		if (is_string($source))
		{
			$hash = strtoupper($source);

			switch ($hash)
			{
				case 'GET':
					$source = $_GET;
					break;
				case 'POST':
					$source = $_POST;
					break;
				case 'FILES':
					$source = $_FILES;
					break;
				case 'COOKIE':
					$source = $_COOKIE;
					break;
				case 'ENV':
					$source = $_ENV;
					break;
				case 'SERVER':
					$source = $_SERVER;
					break;
				default:
					$source = $_REQUEST;
					$hash = 'REQUEST';
					break;
			}
		}
		elseif (is_object($source))
		{
			try
			{
				$source = (array) $source;
			}
			catch (Exception $exc)
			{
				$source = null;
			}
		}
		elseif (is_array($source))
		{
			// Nothing, it's already an array
		}
		else
		{
			// Any other case
			$source = $_REQUEST;
			$hash = 'REQUEST';
		}

		// Magic quotes GPC handling (something JInput simply can't handle at all)

		if (($hash == 'REQUEST') && get_magic_quotes_gpc() && class_exists('JRequest', true))
		{
			$source = JRequest::get('REQUEST', 2);
		}

		parent::__construct($source, $options);
	}

	/**
	 * Gets a value from the input data. Overriden to allow specifying a filter
	 * mask.
	 *
	 * @param   string  $name     Name of the value to get.
	 * @param   mixed   $default  Default value to return if variable does not exist.
	 * @param   string  $filter   Filter to apply to the value.
	 * @param   int     $mask     The filter mask
	 *
	 * @return  mixed  The filtered input value.
	 */
	public function get($name, $default = null, $filter = 'cmd', $mask = 0)
	{
		if (isset($this->data[$name]))
		{
			return $this->_cleanVar($this->data[$name], $mask, $filter);
		}

		return $default;
	}

	/**
	 * Returns a copy of the raw data stored in the class
	 *
	 * @return  array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Old static methods are now deprecated. This magic method makes sure there
	 * is a continuity in our approach. The downside is that it's only compatible
	 * with PHP 5.3.0. Sorry!
	 *
	 * @param   string  $name       Name of the method we're calling
	 * @param   array   $arguments  The arguments passed to the method
	 *
	 * @return  mixed
	 */
	public static function __callStatic($name, $arguments)
	{
		FOFPlatform::getInstance()->logDeprecated('FOFInput: static getXXX() methods are deprecated. Use the input object\'s methods instead.');

		if (substr($name, 0, 3) == 'get')
		{
			// Initialise arguments
			$key = array_shift($arguments);
			$default = array_shift($arguments);
			$input = array_shift($arguments);
			$type = 'none';
			$mask = 0;

			$type = strtolower(substr($name, 3));

			if ($type == 'var')
			{
				$type = array_shift($arguments);
				$mask = array_shift($arguments);
			}

			if (is_null($type))
			{
				$type = 'none';
			}

			if (is_null($mask))
			{
				$mask = 0;
			}

			if (!($input instanceof FOFInput) && !($input instanceof JInput))
			{
				$input = new FOFInput($input);
			}

			return $input->get($key, $default, $type, $mask);
		}

		return false;
	}

	/**
	 * Magic method to get filtered input data.
	 *
	 * @param   mixed   $name       Name of the value to get.
	 * @param   string  $arguments  Default value to return if variable does not exist.
	 *
	 * @return  boolean  The filtered boolean input value.
	 */
	public function __call($name, $arguments)
	{
		if (substr($name, 0, 3) == 'get')
		{
			$filter = substr($name, 3);

			$default = null;
			$mask = 0;

			if (isset($arguments[1]))
			{
				$default = $arguments[1];
			}

			if (isset($arguments[2]))
			{
				$mask = $arguments[2];
			}

			return $this->get($arguments[0], $default, $filter, $mask);
		}
	}

	/**
	 * Sets an input variable. WARNING: IT SHOULD NO LONGER BE USED!
	 *
	 * @param   string   $name       The name of the variable to set
	 * @param   mixed    $value      The value to set it to
	 * @param   array    &$input     The input array or FOFInput object
	 * @param   boolean  $overwrite  Should I overwrite existing values (default: true)
	 *
	 * @return  string   Previous value
	 *
	 * @deprecated
	 */
	public static function setVar($name, $value = null, &$input = array(), $overwrite = true)
	{
		FOFPlatform::getInstance()->logDeprecated('FOFInput::setVar() is deprecated. Use set() instead.');

		if (empty($input))
		{
			return JRequest::setVar($name, $value, 'default', $overwrite);
		}
		elseif (is_string($input))
		{
			return JRequest::setVar($name, $value, $input, $overwrite);
		}
		else
		{
			if (!$overwrite && array_key_exists($name, $input))
			{
				return $input[$name];
			}

			$previous = array_key_exists($name, $input) ? $input[$name] : null;

			if (is_array($input))
			{
				$input[$name] = $value;
			}
			elseif ($input instanceof FOFInput)
			{
				$input->set($name, $value);
			}

			return $previous;
		}
	}

	/**
	 * Custom filter implementation. Works better with arrays and allows the use
	 * of a filter mask.
	 *
	 * @param   mixed    $var   The variable (value) to clean
	 * @param   integer  $mask  The clean mask
	 * @param   string   $type  The variable type
	 *
	 * @return   mixed
	 */
	protected function _cleanVar($var, $mask = 0, $type = null)
	{
		if (is_array($var))
		{
			$temp = array();

			foreach ($var as $k => $v)
			{
				$temp[$k] = self::_cleanVar($v, $mask);
			}

			return $temp;
		}

		// If the no trim flag is not set, trim the variable
		if (!($mask & 1) && is_string($var))
		{
			$var = trim($var);
		}

		// Now we handle input filtering
		if ($mask & 2)
		{
			// If the allow raw flag is set, do not modify the variable
			$var = $var;
		}
		elseif ($mask & 4)
		{
			// If the allow HTML flag is set, apply a safe HTML filter to the variable
			$safeHtmlFilter = JFilterInput::getInstance(null, null, 1, 1);
			$var = $safeHtmlFilter->clean($var, $type);
		}
		else
		{
			$var = $this->filter->clean($var, $type);
		}

		return $var;
	}
}
