<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JApplicationHelper
 *
 * @package     Joomla.UnitTest
 * @subpackage  Application
 * @since       3.2
 */
class JApplicationHelperInspector extends JApplicationHelper
{
	/**
	 * Method to get the current application data
	 *
	 * @return  array  The array of application data objects.
	 *
	 * @since   3.2
	 */
	public static function get()
	{
		return self::$_clients;
	}

	/**
	 * Set the application data.
	 *
	 * @param   string  $apps  The app to set.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public static function set($apps)
	{
		self::$_clients = $apps;
	}
}

/**
 * Test class for JApplicationHelper.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Application
 * @since       3.2
 */
class JApplicationHelperTest extends TestCase
{

	/**
	 * Test JApplicationHelper::getComponentName
	 *
	 * @return  void
	 */
	public function testGetComponentName()
	{
        $testComponentName = 'com_test_component';

        $mockApplication = $this->getMockApplication();
        $mockInput = $this->getMockBuilder('JInput')
                          ->setConstructorArgs(array(array('option' => $testComponentName)))
                          ->getMock();

        $mockApplication->input = $mockInput;
        JFactory::$application = $mockApplication;

        $mockInput->expects($this->once())->method('get')->with($this->equalTo('option'))->will($this->returnValue($testComponentName));
        $componentName = JApplicationHelper::getComponentName();

        $this->assertEquals($testComponentName, $componentName);

	}

	/**
	 * Test JApplicationHelper::getClientInfo
	 *
	 * @return  void
	 */
	public function testGetClientInfo()
	{
        $infoId = 1;
        $infoName = 'installation';

        $clientInfo = JApplicationHelper::getClientInfo();

        $existedClientInfoById = JApplicationHelper::getClientInfo($infoId);
        $nonExistedClientInfoById = JApplicationHelper::getClientInfo(-1);

        $existedClientInfoByName = JApplicationHelper::getClientInfo($infoName, true);
        $nonExistedClientInfoByName = JApplicationHelper::getClientInfo('non-installation', true);

        $expectedClientInfo = $this->expectedClientInfo();

        $this->assertEquals($expectedClientInfo, $clientInfo);

        $this->assertEquals($expectedClientInfo[$infoId], $existedClientInfoById);
        $this->assertNull($nonExistedClientInfoById);

        $this->assertNotNull($existedClientInfoByName);
        $this->assertEquals($infoName, $existedClientInfoByName->name);
        $this->assertNull($nonExistedClientInfoByName);

	}


    private function expectedClientInfo()
    {
        $clientInfo = array();

        $obj = new stdClass;

        // Site Client
        $obj->id = 0;
        $obj->name = 'site';
        $obj->path = JPATH_SITE;
        $clientInfo[0] = clone $obj;

        // Administrator Client
        $obj->id = 1;
        $obj->name = 'administrator';
        $obj->path = JPATH_ADMINISTRATOR;
        $clientInfo[1] = clone $obj;

        // Installation Client
        $obj->id = 2;
        $obj->name = 'installation';
        $obj->path = JPATH_INSTALLATION;
        $clientInfo[2] = clone $obj;

        return $clientInfo;
    }


}
