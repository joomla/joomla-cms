<?php
/**
 * An example JWeb application built on the Joomla Platform.
 *
 * To run this example, copy or soft-link this folder to your web server tree.
 *
 * @package    Joomla.Examples
 * @copyright  Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// We are a valid Joomla entry point.
define('_JEXEC', 1);

// Setup the base path related constant.
define('JPATH_BASE', dirname(__FILE__));

// Increase error reporting to that any errors are displayed.
// Note, you would not use these settings in production.
error_reporting(E_ALL);
ini_set('display_errors', true);

// Bootstrap the application.
require dirname(dirname(dirname(dirname(__FILE__)))).'/libraries/import.php';

// Import the JWeb class from the platform.
jimport('joomla.application.web');

/**
* An example JWeb application class.
*
* @package  Joomla.Examples
* @since    11.3
*/
class HelloWww extends JWeb
{
	/**
	 * Overrides the parent doExecute method to run the web application.
	 *
	 * This method should include your custom code that runs the application.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function doExecute()
	{
		// This application will just output a simple HTML document.
		// Use the setBody method to set the output.
		// JWeb will take care of all the headers and such for you.

		$this->setBody('<html>
			<head>
				<title>Hello WWW</title>
			</head>
			<body style="font-family:verdana;">
				<p>Hello WWW!</p>
			</body>
			</html>'
		);
	}
}

// Instantiate the application object, passing the class name to JWeb::getInstance
// and use chaining to execute the application.
JWeb::getInstance('HelloWww')->execute();
