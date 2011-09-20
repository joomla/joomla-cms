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
* This example shows how to use the setBody and appendBody methods,
* as well as access the client information.
*
* @package  Joomla.Examples
* @since    11.3
*/
class DetectClient extends JWeb
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
		//
		// The body of the response is stored internally as an array.
		// A call to setBody will initialise (or reset) the body of the repsonse.
		//

		// Initialise the body with the DOCTYPE.
		$this->setBody(
			'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
		);

		//
		// You can append to the body of the response using appendBody.
		//

		// Set up the beginning of the HTML repsonse.
		$this->appendBody('<html>')
			->appendBody('<head>')
			->appendBody('<title>JWeb - Detect Client</title>')
			->appendBody('</head>')
			->appendBody('<body style="font-family:verdana;">');

		// Introduce the page.
		$this->appendBody('<p>Welcome to the Joomla! Platform&apos;s <code style="font-size:140%">JWeb</code> class.</p>');

		// Start a list.
		$this->appendBody('<ul>');

		//
		// The client information is accessible via the get method.
		//

		// Get the user agent string.
		$this->appendBody(
			sprintf('<li>User-agent: <em>%s</em></li>', $this->client->userAgent)
		);

		// Determine if this is a mobile device.
		$this->appendBody(
			sprintf('<li>Is a mobile device? <em>%s</em></li>', $this->client->mobile ? 'Yes' : 'No')
		);

		// Get the platform.
		$this->appendBody(
			sprintf('<li>Platform: <em>%s</em></li>', $this->client->platform)
		);

		// Get the engine.
		$this->appendBody(
			sprintf('<li>Engine: <em>%s</em></li>', $this->client->engine)
		);

		// Get the browser and version.
		$this->appendBody(
			sprintf('<li>Browser: <em>%s (%s)</em></li>', $this->client->browser, $this->client->version)
		);

		$this->appendBody('</ul>');

		// Finished up the HTML repsonse.
		$this->appendBody('</body>')
			->appendBody('</html>');
	}
}

// Instantiate the application object, passing the class name to JWeb::getInstance
// and use chaining to execute the application.
JWeb::getInstance('DetectClient')->execute();
