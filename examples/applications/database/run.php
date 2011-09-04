#!/usr/bin/php
<?php
/**
 * An example command line application built on the Joomla Platform.
 *
 * To run this example, adjust the executable path above to suite your operating system,
 * make this file executable and run the file.
 *
 * Alternatively, run the file using:
 *
 * php -f run.php
 *
 * Note, this application requires configuration.php and the connection details
 * for the database may need to be changed to suit your local setup.
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
 * This application shows how to override the constructor
 * and connect to the database.
 *
 * @package  Joomla.Examples
 * @since    11.3
 */
class DatabaseApp extends JCli
{
	/**
	 * A database object for the application to use.
	 *
	 * @var    JDatabase
	 * @since  11.3
	 */
	protected $dbo = null;

	/**
	 * Class constructor.
	 *
	 * This constructor invokes the parent JCli class constructor,
	 * and then creates a connector to the database so that it is
	 * always available to the application when needed.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 * @throws  JDatabaseException
	 */
	protected function __construct()
	{
		// Call the parent __construct method so it bootstraps the application class.
		parent::__construct();

		jimport('joomla.database.database');

		// Note, this will throw an exception if there is an error
		// creating the database connection.
		$this->dbo = JDatabase::getInstance(
			array(
				'driver' => $this->get('dbDriver'),
				'host' => $this->get('dbHost'),
				'user' => $this->get('dbUser'),
				'password' => $this->get('dbPass'),
				'database' => $this->get('dbName'),
				'prefix' => $this->get('dbPrefix'),
			)
		);
	}

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function execute()
	{
		// Get the quey builder class from the database.
		$query = $this->dbo->getQuery(true);

		// Set up a query to select everything in the 'db' table.
		$query->select('*')
			->from($this->dbo->qn('db'));

		// Push the query builder object into the database connector.
		$this->dbo->setQuery($query);

		// Get all the returned rows from the query as an array of objects.
		$rows = $this->dbo->loadObjectList();

		// Just dump the value returned.
		var_dump($rows);
	}
}

// Wrap the execution in a try statement to catch any exceptions thrown anywhere in the script.
try
{
	// Instantiate the application object, passing the class name to JCli::getInstance
	// and use chaining to execute the application.
	JCli::getInstance('DatabaseApp')->execute();
}
catch (Exception $e)
{
	// An exception has been caught, just echo the message.
	fwrite(STDOUT, $e->getMessage() . "\n");
	exit($e->getCode());
}
