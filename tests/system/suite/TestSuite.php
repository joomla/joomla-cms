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
require_once 'doInstall.php';
require_once 'control_panel/control_panel0001Test.php';
require_once 'control_panel/control_panel0002Test.php';
require_once 'control_panel/control_panel0003Test.php';
require_once 'control_panel/control_panel0004Test.php';
require_once 'control_panel/control_panel0005Test.php';
require_once 'menus/menu0001Test.php';
require_once 'menus/menu0002Test.php';
require_once 'articles/article0001Test.php';
require_once 'articles/article0002Test.php';
require_once 'articles/article0003Test.php';
require_once 'articles/article0004Test.php';
require_once 'articles/featured0001Test.php';
require_once 'articles/featured0002Test.php';
require_once 'com_users/user0001Test.php';
require_once 'com_users/user0002Test.php';
require_once 'com_users/group0001Test.php';
require_once 'com_users/group0002Test.php';
require_once 'com_users/group0003Test.php';
require_once 'modules/module0001Test.php';
require_once 'modules/module0002Test.php';
require_once 'redirect/redirect0001Test.php';
require_once 'sample_data/sample_data0001Test.php';
require_once 'acl/acl0001Test.php';
require_once 'acl/acl0002Test.php';
require_once 'acl/acl0003Test.php';
require_once 'acl/acl0004Test.php';
require_once 'acl/acl0005Test.php';
require_once 'acl/acl0006Test.php';
require_once 'language/language0001Test.php';
require_once 'language/language0002Test.php';
require_once 'cache/cache0001Test.php';
require_once 'security/security0001Test.php';


class TestSuite
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('PHPUnit Framework');
		$suite->addTestSuite('DoInstall');
		$suite->addTestSuite('ControlPanel0001');
		$suite->addTestSuite('ControlPanel0002');
		$suite->addTestSuite('ControlPanel0003');
		$suite->addTestSuite('ControlPanel0004');
		$suite->addTestSuite('ControlPanel0005');
		$suite->addTestSuite('Menu0001');
		$suite->addTestSuite('Menu0002');
		$suite->addTestSuite('Article0001');
		$suite->addTestSuite('Article0002');
		$suite->addTestSuite('Article0003');
		$suite->addTestSuite('Article0004');
		$suite->addTestSuite('Featured0001Test');
		$suite->addTestSuite('Featured0002Test');
		$suite->addTestSuite('User0001Test');
		$suite->addTestSuite('User0002Test');
		$suite->addTestSuite('Group0001Test');
		$suite->addTestSuite('Group0002Test');
		$suite->addTestSuite('Group0003Test');
		$suite->addTestSuite('Module0001');
		$suite->addTestSuite('Module0002');
		$suite->addTestSuite('Redirect0001Test');
		$suite->addTestSuite('SampleData0001');
		$suite->addTestSuite('Acl0001Test');
		$suite->addTestSuite('Acl0002Test');
		$suite->addTestSuite('DoInstall');
		$suite->addTestSuite('Acl0003Test');
		$suite->addTestSuite('Acl0004Test');
		$suite->addTestSuite('Acl0005Test');
		$suite->addTestSuite('Acl0006Test');
		$suite->addTestSuite('Language0001Test');
		$suite->addTestSuite('Language0002Test');
		$suite->addTestSuite('Cache0001Test');
		$suite->addTestSuite('Security0001Test');
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


