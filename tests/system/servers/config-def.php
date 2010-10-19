<?php
/*
 * This is an example of a config class. To run tests,
 * copy this file to a file called "configdef.php" (in tests/system/server)
 * and set the variables according to the values in your test system.
 *
 * You can create more than one configuration file.
 *
 * You can select the desired config class at runtime with the following command:
 * phpunit --bootstrap servers\configtest.php tests\control_panel_menu.php
 * In this way, you can create multiple configurations and run them separately
 * using a batch file or shell script.
 */

class SeleniumConfig
{

	// $folder is the path to the apache root folder
	var $folder = '/Users/eddieajau/htdocs'; // typical windows example with XAMPP
//	var $folder = '/usr/local/apache/htdocs'; // typical linux example

	// $host is normally 'http://localhost'
	var $host = 'http://localhost';

	// $path is the rest of the URL to the Joomla! home page
	// Example: Your full URL to Joomla! is http://localhost/joomla_16/index.php
	// then $path would be '/joomla_16/'
	var $path = '/Joomla/trunk/';

	// set the database host, database username, database pasword, and database name
	var $db_host = 'localhost';
	var $db_user = 'root';
	var $db_pass = 'root';
	var $db_name = 'joomla_unittests';
	
	// optional setting to turn on Cache: values are off, on-basic, on-full
	// change this value to set the caching in the doInstall.php test
	var $cache = 'off';

	// set the site name
	var $site_name = 'Joomla! 1.6 Source';

	// set the admin login, admin password, and admin email address
	var $username = 'admin';
	var $password = 'admin';
	var $admin_email = 'mamboblue@gmail.com';

	// this setting will use the default browser for your system
	var $browser = '*chrome';

	public function __construct() {
		$this->baseURI = $this->folder . $this->path;
	}

}
