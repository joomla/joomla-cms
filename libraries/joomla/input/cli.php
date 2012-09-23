<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Input
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla! Input CLI Class
 *
 * @package     Joomla.Platform
 * @subpackage  Input
 * @since       11.1
 */
class JInputCLI extends JInput
{
	/**
	 * The executable that was called to run the CLI script.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $executable;

	/**
	 * The additional arguments passed to the script that are not associated
	 * with a specific argument name.
	 *
	 * @var    array
	 * @since  11.1
	 */
	public $args = array();

	/**
	 * Constructor.
	 *
	 * @param   array  $source   Source data (Optional, default is $_REQUEST)
	 * @param   array  $options  Array of configuration parameters (Optional)
	 *
	 * @since   11.1
	 */
	public function __construct(array $source = null, array $options = array())
	{
		if (isset($options['filter']))
		{
			$this->filter = $options['filter'];
		}
		else
		{
			$this->filter = JFilterInput::getInstance();
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
	 * @since   12.1
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
	 * Method to unserialize the input.
	 *
	 * @param   string  $input  The serialized input.
	 *
	 * @return  JInput  The input object.
	 *
	 * @since   12.1
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
			$this->filter = JFilterInput::getInstance();
		}
	}

	/**
	 * Initialise the options and arguments
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function parseArguments()
	{
		// Get the list of argument values from the environment.
		$args = $_SERVER['argv'];

		// Set the path used for program execution and remove it form the program arguments.
		$this->executable = array_shift($args);

		// We use a for loop because in some cases we need to look ahead.
		for ($i = 0; $i < count($args); $i++)
		{
			// Get the current argument to analyze.
			$arg = $args[$i];

			// First let's tackle the long argument case.  eg. --foo
			if (strlen($arg) > 2 && substr($arg, 0, 2) == '--')
			{

				// Attempt to split the thing over equals so we can get the key/value pair if an = was used.
				$arg = substr($arg, 2);
				$parts = explode('=', $arg);
				$this->data[$parts[0]] = true;

				// Does not have an =, so let's look ahead to the next argument for the value.
				if (count($parts) == 1 && isset($args[$i + 1]) && preg_match('/^--?.+/', $args[$i + 1]) == 0)
				{
					$this->data[$parts[0]] = $args[$i + 1];

					// Since we used the next argument, increment the counter so we don't use it again.
					$i++;
				}
				// We have an equals sign so take the second "part" of the argument as the value.
				elseif (count($parts) == 2)
				{
					$this->data[$parts[0]] = $parts[1];
				}
			}

			// Next let's see if we are dealing with a "bunch" of short arguments.  eg. -abc
			elseif (strlen($arg) > 2 && $arg[0] == '-')
			{

				// For each of these arguments set the value to TRUE since the flag has been set.
				for ($j = 1; $j < strlen($arg); $j++)
				{
					$this->data[$arg[$j]] = true;
				}
			}

			// OK, so it isn't a long argument or bunch of short ones, so let's look and see if it is a single
			// short argument.  eg. -h
			elseif (strlen($arg) == 2 && $arg[0] == '-')
			{

				// Go ahead and set the value to TRUE and if we find a value later we'll overwrite it.
				$this->data[$arg[1]] = true;

				// Let's look ahead to see if the next argument is a "value".  If it is, use it for this value.
				if (isset($args[$i + 1]) && preg_match('/^--?.+/', $args[$i + 1]) == 0)
				{
					$this->data[$arg[1]] = $args[$i + 1];

					// Since we used the next argument, increment the counter so we don't use it again.
					$i++;
				}
			}

			// Last but not least, we don't have a key/value based argument so just add it to the arguments list.
			else
			{
				$this->args[] = $arg;
			}
		}
	}
}
