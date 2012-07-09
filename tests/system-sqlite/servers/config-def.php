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
	public $folder = '/home/jtester/srv/www/htdocs'; // typical windows example with XAMPP
//	public $folder = '/usr/local/apache/htdocs'; // typical linux example

	// $host is normally 'http://localhost'
	public $host = 'http://dev.local';

	// $path is the rest of the URL to the Joomla! home page
	// Example: Your full URL to Joomla! is http://localhost/joomla_16/index.php
	// then $path would be '/joomla_16/'
	public $path = '/joomla-cms-testing';

	// set the database host, database username, database pasword, and database name
#	public $db_type = 'MySQL';
	public $db_host = 'localhost';
	public $db_user = 'root';
	public $db_pass = '';
	public $db_name = 'jsystemtestssqlite';
	public $db_type = 'Sqlite';
	public $db_prefix = 'xxx_';

	// optional setting to turn on Cache: values are off, on-basic, on-full
	// change this value to set the caching in the doInstall.php test
	public $cache = 'off';

	// optional setting to set administive template to hathor: set to 'hathor' to make hathor the default
	// public $adminTemplate = 'hathor';

	// optional setting to install sample data
	// If not set or true, sample data is installed. Set to false to not install sample data
	public $sample_data = true;

	// set the site name
	public $site_name = 'Joomla! - Sqlite Test';

	// set the admin login, admin password, and admin email address
	public $username = 'admin';
	public $password = 'test';
	public $admin_email = 'demo@example.org';

	// this setting will use the default browser for your system
	public $browser = '*chrome';

	// Screenshots
	public $captureScreenshotOnFailure = true;
	public $screenshotPath = '/home/jtester/repos/joomla-cms-testing/build/screenshots/';
	public $screenshotUrl = 'http://dev.local/joomla-cms-testing/build/screenshots/';

	public function __construct()
	{
		$this->baseURI = $this->folder . $this->path;
	}
}
