<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

if (!defined('PHPUnit_MAIN_METHOD')) {
	define('PHPUnit_MAIN_METHOD', 'TestSuite::main');
}
set_include_path(get_include_path() . PATH_SEPARATOR . './PEAR/' . PATH_SEPARATOR . '../');

require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'installation/InstallTest.php';
require_once 'control_panel/ControlPanel0001Test.php';
require_once 'control_panel/GlobalConfiguration0001Test.php';
require_once 'users/GroupManager0001Test.php';
require_once 'users/LevelManager0001Test.php';
require_once 'users/UserManager0001Test.php';
require_once 'users/UserManager0002Test.php';

class TestSuite
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('PHPUnit Framework');
		$suite->addTestSuite('InstallTest');
		$suite->addTestSuite('ControlPanel0001Test');
		$suite->addTestSuite('GlobalConfiguration0001Test');
		$suite->addTestSuite('GroupManager0001Test');
		$suite->addTestSuite('LevelManager0001Test');
		$suite->addTestSuite('UserManager0001Test');
		$suite->addTestSuite('UserManager0002Test');
		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'Framework_AllTests::main') {
	print "running Framework_AllTests::main()";
	Framework_AllTests::main();
}
// the following section allows you to run this either from phpunit as
// phpunit.bat --bootstrap servers\configdef.php tests\testsuite.php
// or to run as a PHP Script from inside Eclipse. If you are running
// as a PHP Script, the SeleniumConfig class doesn't exist so you must import it
// and you must also run the TestSuite::main() method.
if (!class_exists('SeleniumConfig')) {
	require_once 'servers/configdef.php';
	TestSuite::main();
}


