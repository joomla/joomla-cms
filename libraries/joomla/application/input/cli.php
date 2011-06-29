<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.application.input');

/**
 * Joomla! Input CLI Class
 *
 * @package     Joomla.Platform
 * @subpackage  Application
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
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function __construct($source = null, $options = array ())
	{
		if (isset ($options['filter'])) {
			$this->filter = $options['filter'];
		} else {
			$this->filter = JFilterInput::getInstance();
		}

		// Get the command line options
		$this->parseArguments();

		// Set the options for the class.
		$this->options = $options;
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
			if (strlen($arg) > 2 && substr($arg, 0, 2) == '--') {

				// Attempt to split the thing over equals so we can get the key/value pair if an = was used.
				$arg = substr($arg, 2);
				$parts = explode('=', $arg);
				$this->data[$parts[0]] = true;

				// Does not have an =, so let's look ahead to the next argument for the value.
				if (count($parts) == 1 && isset ($args[$i+1]) && preg_match('/^--?.+/', $args[$i+1]) == 0) {
					$this->data[$parts[0]] = $args[$i+1];

					// Since we used the next argument, increment the counter so we don't use it again.
					$i++;
				}
				// We have an equals sign so take the second "part" of the argument as the value.
				elseif (count($parts) == 2) {
					$this->data[$parts[0]] = $parts[1];
				}
			}

			// Next let's see if we are dealing with a "bunch" of short arguments.  eg. -abc
			elseif (strlen($arg) > 2 && $arg[0] == '-') {

				// For each of these arguments set the value to TRUE since the flag has been set.
				for ($j = 1; $j < strlen($arg); $j++)
				{
					$this->data[$arg[$j]] = true;
				}
			}

			// OK, so it isn't a long argument or bunch of short ones, so let's look and see if it is a single
			// short argument.  eg. -h
			elseif (strlen($arg) == 2 && $arg[0] == '-') {

				// Go ahead and set the value to TRUE and if we find a value later we'll overwrite it.
				$this->data[$arg[1]] = true;

				// Let's look ahead to see if the next argument is a "value".  If it is, use it for this value.
				if (isset ($args[$i+1]) && preg_match('/^--?.+/', $args[$i+1]) == 0) {
					$this->data[$arg[1]] = $args[$i+1];

					// Since we used the next argument, increment the counter so we don't use it again.
					$i++;
				}
			}

			// Last but not least, we don't have a key/value based argument so just add it to the arguments list.
			else {
				$this->args[] = $arg;
			}
		}
	}
}
