#!/usr/bin/php
<?php
/**
 * An example command line application built on the Joomla Platform.
 *
 * To run this example, adjust the executable path above to suite your operating system,
 * make this file executable and run the file.
 *
 * @package    Joomla.Examples
 * @copyright  Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// We are a valid Joomla entry point.
define('_JEXEC', 1);

// Setup the base path related constant.
define('JPATH_BASE', dirname(__FILE__));

// Bootstrap the application.
require dirname(dirname(dirname(dirname(__FILE__)))).'/libraries/import.php';

// Import the JCli class from the platform.
jimport('joomla.application.cli');

/**
 * An example command line application class.
 *
 * This application shows how to access configuration file values.
 *
 * @package  Joomla.Examples
 * @since    11.3
 */
class Argv extends JCli
{
	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function execute()
	{
		// Print a blank line.
		$this->out();
		$this->out('JOOMLA PLATFORM ARGV EXAMPLE');
		$this->out('============================');
		$this->out();

		// You can look for named command line arguments in the form of:
		// (a) -n value
		// (b) --name=value
		//
		// Try running file like this:
		// $ ./run.php -fa
		// $ ./run.php -f foo
		// $ ./run.php --set=match
		//
		// The values are accessed using the $this->input->get() method.
		// $this->input is an instance of a JInputCli object.

		// This is an example of an option using short args (-).
		$value = $this->input->get('a');
		$this->out(
			sprintf(
				'%25s = %s', 'a',
				var_export($value, true)
			)
		);

		$value = $this->input->get('f');
		$this->out(
			sprintf(
				'%25s = %s', 'f',
				var_export($value, true)
			)
		);

		// This is an example of an option using long args (--).
		$value = $this->input->get('set');
		$this->out(
			sprintf(
				'%25s = %s', 'set',
				var_export($value, true)
			)
		);

		// You can also apply defaults to the command line options.
		$value = $this->input->get('f', 'default');
		$this->out(
			sprintf(
				'%25s = %s', 'f (with default)',
				var_export($value, true)
			)
		);

		// You can also apply input filters found in the JFilterInput class.
		// Try running this file like this:
		// $ ./run.php -f one2

		$value = $this->input->get('f', 0, 'INT');
		$this->out(
			sprintf(
				'%25s = %s', 'f (cast to int)',
				var_export($value, true)
			)
		);

		// Print out all the remaining command line arguments used to run this file.
		if (!empty($this->input->args))
		{
			$this->out();
			$this->out('These are the remaining arguments passed:');
			$this->out();

			// Unallocated arguments are found in $this->input->args.
			// Try running the file like this:
			// $ ./run.php -f foo bar

			foreach ($this->input->args as $arg)
			{
				$this->out($arg);
			}
		}

		// Print a blank line at the end.
		$this->out();
	}
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JCli::getInstance('Argv')->execute();
