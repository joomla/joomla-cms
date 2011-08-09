#!/usr/bin/env php
<?php
define( '_JEXEC', 1 );
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__FILE__));
$parts = explode(DS, JPATH_BASE);

//Defines.
define('JPATH_ROOT',			implode(DS, $parts));

define('JPATH_SITE',			JPATH_ROOT);

require_once( 'libraries/import.php' );

jimport( 'joomla.application.cli.daemon' );

class Daemon extends JDaemon
{
	public $name = 'test';

	public function execute()
	{
		sleep(20);
	}
}

JCli::getInstance( 'Daemon' )->start();

?>
