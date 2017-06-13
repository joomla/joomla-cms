<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Class to mock JMenu.
 *
 * @package  Joomla.Test
 * @since    3.4
 */
class TestMockMenu
{
	protected static $data = [];

	/**
	 * Creates an instance of the mock JMenu object.
	 *
	 * @param   PHPUnit_Framework_TestCase  $test  A test object.
	 *
	 * @return  PHPUnit_Framework_MockObject_MockObject
	 *
	 * @since   3.4
	 */
	public static function create(PHPUnit_Framework_TestCase $test, $setDefault = true, $setActive = false)
	{
		// Collect all the relevant methods in JMenu (work in progress).
		$methods = [
			'getItem',
			'setDefault',
			'getDefault',
			'setActive',
			'getActive',
			'getItems',
			'getParams',
			'getMenu',
			'authorise',
			'load'
		];

		// Build the mock object.
		$mockObject = $test->getMockBuilder('JMenu')
					->setMethods($methods)
					->setConstructorArgs([])
					->setMockClassName('')
					->disableOriginalConstructor()
					->getMock();

		self::createMenuSampleData();

		$mockObject->expects($test->any())
				->method('getItem')
				->will($test->returnValueMap(self::prepareGetItemData()));

		$mockObject->expects($test->any())
				->method('getItems')
				->will($test->returnCallback([__CLASS__, 'prepareGetItemsData']));

		$mockObject->expects($test->any())
				->method('getMenu')
				->will($test->returnValue(self::$data));

		if ($setDefault)
		{
			$mockObject->expects($test->any())
				->method('getDefault')
				->will($test->returnValueMap(self::prepareDefaultData()));
		}

		if ($setActive)
		{
			$mockObject->expects($test->any())
				->method('getActive')
				->will($test->returnValue(self::$data[$setActive]));
		}

		return $mockObject;
	}

	protected static function prepareGetItemData()
	{
		$return = [];

		foreach (self::$data as $id => $item)
		{
			$return[] = [$id, $item];
			$return[] = [(string) $id, $item];
		}

		return $return;
	}

	protected static function prepareDefaultData()
	{
		$return   = [];
		$return[] = ['en-GB', self::$data[45]];

		return $return;
	}

	public static function prepareGetItemsData($attributes, $values)
	{
		$items      = [];
		$attributes = (array) $attributes;
		$values     = (array) $values;

		foreach (self::$data as $item)
		{
			$test = true;

			for ($i = 0, $count = count($attributes); $i < $count; $i++)
			{
				if (is_array($values[$i]))
				{
					if (!in_array($item->{$attributes[$i]}, $values[$i]))
					{
						$test = false;
						break;
					}
				}
				else
				{
					if ($item->{$attributes[$i]} != $values[$i])
					{
						$test = false;
						break;
					}
				}
			}

			if ($test)
			{
				$items[] = $item;
			}
		}

		return $items;
	}

	protected static function createMenuSampleData()
	{
		self::$data[42] = (object) [
			'id'           => '42',
			'menutype'     => 'testmenu',
			'title'        => 'Test1',
			'alias'        => 'test',
			'route'        => 'test',
			'link'         => 'index.php?option=com_test&view=test',
			'type'         => 'component',
			'level'        => '1',
			'language'     => '*',
			'access'       => '1',
			'params'       => '{}',
			'home'         => '0',
			'component_id' => '1000',
			'parent_id'    => '0',
			'component'    => 'com_test',
			'tree'         => [42],
			'query'        => ['option' => 'com_test', 'view' => 'test']
		];

		self::$data[43] = (object) [
			'id'           => '43',
			'menutype'     => 'testmenu',
			'title'        => 'Test2',
			'alias'        => 'test2',
			'route'        => 'test2',
			'link'         => 'index.php?option=com_test2&view=test',
			'type'         => 'component',
			'level'        => '1',
			'language'     => '*',
			'access'       => '1',
			'params'       => '{}',
			'home'         => '0',
			'component_id' => '1000',
			'parent_id'    => '0',
			'component'    => 'com_test2',
			'tree'         => [43],
			'query'        => ['option' => 'com_test2', 'view' => 'test']
		];

		self::$data[44] = (object) [
			'id'           => '44',
			'menutype'     => 'testmenu',
			'title'        => 'Submenu',
			'alias'        => 'sub-menu',
			'route'        => 'test2/sub-menu',
			'link'         => 'index.php?option=com_test2&view=test2',
			'type'         => 'component',
			'level'        => '2',
			'language'     => '*',
			'access'       => '1',
			'params'       => '{}',
			'home'         => '0',
			'component_id' => '1000',
			'parent_id'    => '43',
			'component'    => 'com_test2',
			'tree'         => [43, 44],
			'query'        => ['option' => 'com_test2', 'view' => 'test2']
		];

		self::$data[45] = (object) [
			'id'           => '45',
			'menutype'     => 'testmenu',
			'title'        => 'Home',
			'alias'        => 'home',
			'route'        => 'home',
			'link'         => 'index.php?option=com_test3&view=test3',
			'type'         => 'component',
			'level'        => '1',
			'language'     => '*',
			'access'       => '1',
			'params'       => '{}',
			'home'         => '1',
			'component_id' => '1000',
			'parent_id'    => '0',
			'component'    => 'com_test3',
			'tree'         => [43, 44],
			'query'        => ['option' => 'com_test3', 'view' => 'test3']
		];

		self::$data[46] = (object) [
			'id'           => '46',
			'menutype'     => 'testmenu',
			'title'        => 'Submenu',
			'alias'        => 'sub-menu',
			'route'        => 'test/sub-menu',
			'link'         => 'index.php?option=com_test&view=test2',
			'type'         => 'component',
			'level'        => '2',
			'language'     => '*',
			'access'       => '1',
			'params'       => '{}',
			'home'         => '0',
			'component_id' => '1000',
			'parent_id'    => '42',
			'component'    => 'com_test',
			'tree'         => [42, 46],
			'query'        => ['option' => 'com_test', 'view' => 'test2']
		];

		self::$data[47] = (object) [
			'id'           => '47',
			'menutype'     => 'testmenu',
			'title'        => 'English Test',
			'alias'        => 'english-test',
			'route'        => 'english-test',
			'link'         => 'index.php?option=com_test&view=test2',
			'type'         => 'component',
			'level'        => '1',
			'language'     => 'en-GB',
			'access'       => '1',
			'params'       => '{}',
			'home'         => '0',
			'component_id' => '1000',
			'parent_id'    => '0',
			'component'    => 'com_test',
			'query'        => ['option' => 'com_test', 'view' => 'test2']
		];

	/**	self::$data[48] = (object) [
			'id'           => '48',
			'menutype'     => '',
			'title'        => '',
			'alias'        => '',
			'route'        => '',
			'link'         => '',
			'type'         => '',
			'level'        => '',
			'language'     => '',
			'access'       => '',
			'params'       => '',
			'home'         => '',
			'component_id' => '',
			'parent_id'    => '',
			'component'    => '',
			'query'        => []
		];

		self::$data[49] = (object) [
			'id'           => '49',
			'menutype'     => '',
			'title'        => '',
			'alias'        => '',
			'route'        => '',
			'link'         => '',
			'type'         => '',
			'level'        => '',
			'language'     => '',
			'access'       => '',
			'params'       => '',
			'home'         => '',
			'component_id' => '',
			'parent_id'    => '',
			'component'    => '',
			'query'        => []
		];

		self::$data[50] = (object) [
			'id'           => '50',
			'menutype'     => '',
			'title'        => '',
			'alias'        => '',
			'route'        => '',
			'link'         => '',
			'type'         => '',
			'level'        => '',
			'language'     => '',
			'access'       => '',
			'params'       => '',
			'home'         => '',
			'component_id' => '',
			'parent_id'    => '',
			'component'    => '',
			'query'        => []
		];

		self::$data[51] = (object) [
			'id'           => '51',
			'menutype'     => '',
			'title'        => '',
			'alias'        => '',
			'route'        => '',
			'link'         => '',
			'type'         => '',
			'level'        => '',
			'language'     => '',
			'access'       => '',
			'params'       => '',
			'home'         => '',
			'component_id' => '',
			'parent_id'    => '',
			'component'    => '',
			'query'        => []
		];**/
	}
}
