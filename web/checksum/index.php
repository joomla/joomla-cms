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
define('JPATH_checksum', dirname(dirname(dirname(__FILE__))).'\cli\checksum\logs\jchecksum.php');
// Increase error reporting to that any errors are displayed.
// Note, you would not use these settings in production.
error_reporting(E_ALL);
ini_set('display_errors', true);

// Bootstrap the application.
//require dirname(dirname(dirname(__FILE__))).'/bootstrap.php';
require dirname(dirname(__FILE__)).'../../libraries/import.php';

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
//jimport('joomla.filesystem.file');
	$lines = file(JPATH_checksum);
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
		$this->appendBody('<p>Jchecksum an application for the  Joomla! Platform&apos;s <code style="font-size:140%">JWeb</code> class.</p>');
			$this->appendBody('<pre><ul>');
foreach($lines as $line)
{
	 if (strpos($line,'ERROR')){
      $this->appendBody('<ol style="color:#FF0000">'.$line.'</ol>');
   }else {
   	  $this->appendBody('<ol style="color:#000000">'.$line.'</ol>');
   }	
}
$this->appendBody('</ul></pre>');
// $this->appendBody('</a></pre>');
	
// Finished up the HTML repsonse.
		$this->appendBody('</body>')
			->appendBody('</html>');
	}	
}

// Instantiate the application object, passing the class name to JWeb::getInstance
// and use chaining to execute the application.
JWeb::getInstance('HelloWww')->execute();
