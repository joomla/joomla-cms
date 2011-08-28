#!/usr/bin/php
<?php
/**
 * A "hello world" command line application built on the Joomla Platform.
 *
 * To run this example, adjust the executable path above to suite your operating system,
 * make this file executable and run the file.
 *
 * Alternatively, run the file using:
 *
 * php -f run.php
 *
 * @package    Joomla.Examples
 * @copyright  Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// We are a valid Joomla entry point.
// This is required to load the Joomla Platform import.php file.
define('_JEXEC', 1);

// Setup the base path related constant.
// This is one of the few, mandatory constants needed for the Joomla Platform.
define('JPATH_BASE', dirname(__FILE__));

// Bootstrap the application.
require dirname(dirname(dirname(dirname(__FILE__)))).'/libraries/import.php';

// Import the JCli class from the platform.
jimport('joomla.application.cli');

/**
 * A "hello world" command line application class.
 *
 * Simple command line applications extend the JCli class.
 *
 * @package  Joomla.Examples
 * @since    11.3
 */
class HelloWorld extends JCli
{
	/**
	 * Execute the application.
	 *
	 * The 'execute' method is the entry point for a command line application.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function execute()
	{
		// Send a string to standard output.
		$this->out('Hello world!');
	}
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JCli::getInstance('HelloWorld')->execute();
