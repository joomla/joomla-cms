<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Component
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/JComponentRouterBaseInspector.php';

/**
 * Test class for JComponentRouterBase.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Component
 * @since       3.4
 */
class JComponentRouterBaseTest extends TestCase
{
	/**
	 * Test JComponentRouterBase::__construct
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @covers  JComponentRouterBase::__construct
	 */
	public function testConstruct()
	{
		$app_bkp = JFactory::$application;
		$app = TestMockApplication::create($this);
		JFactory::$application = $app;
		$menu = TestMockMenu::create($this);

		/**
		 * Test if standard setup of JComponentRouterBase works and $app and
		 * $menu are properly populated
		 */
		$object = new JComponentRouterBaseInspector;
		$this->assertInstanceOf('JComponentRouterInterface', $object);
		$this->assertInstanceOf('JComponentRouterBase', $object);
		$this->assertEquals($app, $object->app);
		$this->assertEquals($app->getMenu(), $object->menu);
		$this->assertEquals(null, $object->app->get('value'));

		/**
		 * Test if the setup works when an app object is handed over
		 * Especially test if the app objects are different
		 */
		$app2 = TestMockApplication::create($this);
		$object = new JComponentRouterBaseInspector($app2);
		$this->assertEquals($app2, $object->app);
		//The original $app is not the same object as $app2, thus we did not use JFactory
		$this->assertFalse($app === $object->app);

		/**
		 * Test if the setup works when both an app and menu object is handed over
		 */
		$menu2 = new stdClass;
		$object = new JComponentRouterBaseInspector($app, $menu2);
		$this->assertEquals($app, $object->app);
		$this->assertEquals($menu2, $object->menu);

		/**
		 * Test what happens when no application, but a menu object is handed over
		 */
		$object = new JComponentRouterBaseInspector(false, $menu);
		$this->assertEquals($app, $object->app);
		$this->assertEquals($menu, $object->menu);

		JFactory::$language = $app_bkp;
	}

	/**
	 * Test JComponentRouterBase::preprocess
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @covers  JComponentRouterBase::preprocess
	 */
	public function testPreprocess()
	{
		$app = TestMockApplication::create($this);
		$menu = TestMockMenu::create($this);
		$object = new JComponentRouterBaseInspector($app, $menu);

		$array = array('option' => 'com_test', 'view' => 'test');
		$this->assertEquals($array, $object->preprocess($array));
	}
}
