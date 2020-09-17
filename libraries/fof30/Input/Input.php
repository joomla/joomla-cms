<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Input;

defined('_JEXEC') || die;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Input\Input as JInput;
use ReflectionObject;

class Input extends JInput
{
	/**
	 * Public constructor. Overridden to allow specifying the global input array
	 * to use as a string and instantiate from an object holding variables.
	 *
	 * @param   array|string|object|null  $source   Source data; set null to use the default Joomla input source
	 * @param   array                     $options  Filter options
	 */
	public function __construct($source = null, array $options = [])
	{
		$hash = null;

		if (is_string($source))
		{
			$hash = strtoupper($source);

			if (!in_array($hash, ['GET', 'POST', 'FILES', 'COOKIE', 'ENV', 'SERVER', 'REQUEST']))
			{
				$hash = 'REQUEST';
			}

			$source = $this->extractJoomlaSource($hash);
		}
		elseif (is_object($source) && ($source instanceof Input))
		{
			$source = $source->getData();
		}
		elseif (is_object($source) && ($source instanceof JInput))
		{
			$serialised = $source->serialize();
			[$xOptions, $xData, $xInput] = unserialize($serialised);
			unset ($xOptions);
			unset ($xInput);
			unset ($source);
			$source = $xData;
			unset ($xData);
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
			$source = null;
		}

		// TODO Joomla 4 -- get the data from the application input

		// If we are not sure use the REQUEST array
		if (empty($source))
		{
			$source = $this->extractJoomlaSource('REQUEST');
		}

		parent::__construct($source, $options);
	}

	/**
	 * Gets a value from the input data. Overridden to allow specifying a filter
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

	public function setData(array $data)
	{
		$this->data = $data;
	}

	/**
	 * Magic method to get filtered input data.
	 *
	 * @param   mixed   $name       Name of the value to get.
	 * @param   string  $arguments  [0] The name of the variable [1] The default value [2] Mask
	 *
	 * @return  boolean  The filtered boolean input value.
	 */
	public function __call($name, $arguments)
	{
		if (substr($name, 0, 3) == 'get')
		{
			$filter = substr($name, 3);

			$default = null;
			$mask    = 0;

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
			$temp = [];

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
		}
		elseif ($mask & 4)
		{
			// If the allow HTML flag is set, apply a safe HTML filter to the variable
			if (version_compare(JVERSION, '3.999.999', 'le'))
			{
				$safeHtmlFilter = InputFilter::getInstance(null, null, 1, 1);
			}
			else
			{
				$safeHtmlFilter = InputFilter::getInstance([], [], 1, 1);
			}
			$var = $safeHtmlFilter->clean($var, $type);
		}
		else
		{
			$var = $this->filter->clean($var, $type);
		}

		return $var;
	}

	protected function extractJoomlaSource($hash = 'REQUEST')
	{
		if (!in_array(strtoupper($hash), ['GET', 'POST', 'FILES', 'COOKIE', 'ENV', 'SERVER', 'REQUEST']))
		{
			$hash = 'REQUEST';
		}

		$hash = strtolower($hash);

		try
		{
			$input = Factory::getApplication()->input;
		}
		catch (Exception $e)
		{
			$input = new \Joomla\Input\Input();
		}

		if ($hash !== 'request')
		{
			$input = $input->{$hash};
		}

		$refObject = new ReflectionObject($input);
		$refProp   = $refObject->getProperty('data');
		$refProp->setAccessible(true);

		return $refProp->getValue($input);
	}
}
