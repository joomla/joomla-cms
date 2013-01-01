<?php
/**
 * An example JOpenstreetmap application built on the Joomla Platform.
 *
 * To run this example, copy or soft-link this folder to your web server tree.
 *
 * @package    Joomla.Examples
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// We are a valid Joomla entry point.
define('_JEXEC', 1);

// Setup the base path related constant.
define('JPATH_BASE', dirname(__FILE__));

// Maximise error reporting.
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Bootstrap the application.
require dirname(__FILE__) . '/tests/bootstrap.php';

/**
 * An example JApplicationWeb application class.
 *
 * @package  Joomla.Examples
 * @since    12.3
 */
class OsmApp extends JApplicationWeb
{
	/**
	 * Display the application.
	 * 
	 * @return void
	 */
	function doExecute()
	{

		// Provide the key and secret obtained from Open Street Map account
		$key = "8DVVqjUxRiruXsMYZegClYbWODIMoKZxLl8w9pXR";
		$secret = "HvZkpFOsyq9oD7GEHYASyRgzKasRAMsvQ53Qb483";

		// Create a new Jregistry to store key and secret
		$option = new JRegistry;
		$option->set('consumer_key', $key);
		$option->set('consumer_secret', $secret);
		$option->set('sendheaders', true);

		// Oauth authentication purposes
		$oauth = new JOpenstreetmapOauth($option);

		$oauth->authenticate();

		// Create a new base object with authentication

		$osm = new JOpenstreetmap($oauth);

		// A new base object without authentication
		// $osm=new JOpenstreetmap();

		// New changeset object obtained
		$changeset = $osm->changesets;

		// New element object obtained
		$element = $osm->elements;

		// New gps object obtained
		$gps = $osm->gps;

		$changesets = array
		(
				array(
						"comment" => "my changeset comment",
						"created_by" => "JOSM/1.0 (5581 en)"
				)
		);

		// Creates a new changeset
		$result = $changeset->createChangeset($changesets);

		// Prints the number of newly created changeset
		print_r($result);
		echo '<br />';

		// Reads the 'node' with id = 123
		$result = $element->readElement('node', 123);

		// Prints the detail about the node read
		print_r($result);
		echo '<br />';

		// Download Metadetails of a trace
		$result = $gps->downloadTraceMetadetails('1370260', 'username', 'password');

		// Prints the downloaded metadetails
		print_r($result);
		echo '<br />';

	}
}

$web = JApplicationWeb::getInstance('OsmApp');
JFactory::$application = $web;

$session = JFactory::getSession();

if ($session->isActive() == false)
{
	$session->initialise(JFactory::getApplication()->input);
	$session->start();
}

// Run the application
$web->execute();
