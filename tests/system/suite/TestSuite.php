<?php
/**
 * @version		$Id$
 * @package		Joomla.FunctionalTest
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'TestSuite::main');
}
set_include_path(get_include_path() . PATH_SEPARATOR . './PEAR/' . PATH_SEPARATOR . '../');

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'control_panel/control_panel0001Test.php';
require_once 'control_panel/control_panel0002Test.php';
require_once 'control_panel/control_panel0003Test.php';
require_once 'control_panel/control_panel0004Test.php';
require_once 'articles/article0001Test.php';
require_once 'com_users/user0001Test.php';
require_once 'modules/module0001Test.php';
require_once 'sample_data/sample_data0001Test.php';

class TestSuite
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit Framework');
        $suite->addTestSuite('ControlPanel0001');
        $suite->addTestSuite('ControlPanel0002');
        $suite->addTestSuite('ControlPanel0003');
        $suite->addTestSuite('ControlPanel0004');
        $suite->addTestSuite('Article0001');
        $suite->addTestSuite('User0001Test');
        $suite->addTestSuite('Module0001');
        $suite->addTestSuite('SampleData0001');

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


