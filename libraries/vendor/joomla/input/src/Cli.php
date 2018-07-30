<?php
/**
 * Part of the Joomla Framework Input Package
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Input;

use Joomla\Filter;

/**
 * Joomla! Input CLI Class
 *
 * @since  1.0
 */
class Cli extends Input
{
	/**
	 * The executable that was called to run the CLI script.
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $executable;

	/**
	 * The additional arguments passed to the script that are not associated
	 * with a specific argument name.
	 *
	 * @var    array
	 * @since  1.0
	 */
	public $args = array();

	/**
	 * Constructor.
	 *
	 * @param   array  $source   Source data (Optional, default is $_REQUEST)
	 * @param   array  $options  Array of configuration parameters (Optional)
	 *
	 * @since   1.0
	 */
	public function __construct($source = null, array $options = array())
	{
		if (isset($options['filter']))
		{
			$this->filter = $options['filter'];
		}
		else
		{
			$this->filter = new Filter\InputFilter;
		}

		// Get the command line options
		$this->parseArguments();

		// Set the options for the class.
		$this->options = $options;
	}

	/**
	 * Method to serialize the input.
	 *
	 * @return  string  The serialized input.
	 *
	 * @since   1.0
	 */
	public function serialize()
	{
		// Load all of the inputs.
		$this->loadAllInputs();

		// Remove $_ENV and $_SERVER from the inputs.
		$inputs = $this->inputs;
		unset($inputs['env']);
		unset($inputs['server']);

		// Serialize the executable, args, options, data, and inputs.
		return serialize(array($this->executable, $this->args, $this->options, $this->data, $inputs));
	}

	/**
	 * Gets a value from the input data.
	 *
	 * @param   string  $name     Name of the value to get.
	 * @param   mixed   $default  Default value to return if variable does not exist.
	 * @param   string  $filter   Filter to apply to the value.
	 *
	 * @return  mixed  The filtered input value.
	 *
	 * @since   1.0
	 */
	public function get($name, $default = null, $filter = 'string')
	{
		return parent::get($name, $default, $filter);
	}

	/**
	 * Method to unserialize the input.
	 *
	 * @param   string  $input  The serialized input.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function unserialize($input)
	{
		// Unserialize the executable, args, options, data, and inputs.
		list($this->executable, $this->args, $this->options, $this->data, $this->inputs) = unserialize($input);

		// Load the filter.
		if (isset($this->options['filter']))
		{
			$this->filter = $this->options['filter'];
		}
		else
		{
			$this->filter = new Filter\InputFilter;
		}
	}

	/**
	 * Initialise the options and arguments
	 *
	 * Not supported: -abc c-value
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function parseArguments()
	{
		$argv = $_SERVER['argv'];

		$this->executable = array_shift($argv);

		$out = array();

		for ($i = 0, $j = count($argv); $i < $j; $i++)
		{
			$arg = $argv[$i];

			// --foo --bar=baz
			if (substr($arg, 0, 2) === '--')
			{
				$eqPos = strpos($arg, '=');

				// --foo
				if ($eqPos === false)
				{
					$key = substr($arg, 2);

					// --foo value
					if ($i + 1 < $j && $argv[$i + 1][0] !== '-')
					{
						$value          = $argv[$i + 1];
						$i++;
					}
					else
					{
						$value          = isset($out[$key]) ? $out[$key] : true;
					}

					$out[$key]          = $value;
				}

				// --bar=baz
				else
				{
					$key                = substr($arg, 2, $eqPos - 2);
					$value              = substr($arg, $eqPos + 1);
					$out[$key]          = $value;
				}
			}
			// -k=value -abc
			elseif (substr($arg, 0, 1) === '-')
			{
				// -k=value
				if (substr($arg, 2, 1) === '=')
				{
					$key                = substr($arg, 1, 1);
					$value              = substr($arg, 3);
					$out[$key]          = $value;
				}
				// -abc
				else
				{
					$chars              = str_split(substr($arg, 1));

					foreach ($chars as $char)
					{
						$key            = $char;
						$value          = isset($out[$key]) ? $out[$key] : true;
						$out[$key]      = $value;
					}

					// -a a-value
					if ((count($chars) === 1) && ($i + 1 < $j) && ($argv[$i + 1][0] !== '-'))
					{
						$out[$key]      = $argv[$i + 1];
						$i++;
					}
				}
			}
			// Plain-arg
			else
			{
				$this->args[] = $arg;
			}
		}

		$this->data = $out;
	}
}
