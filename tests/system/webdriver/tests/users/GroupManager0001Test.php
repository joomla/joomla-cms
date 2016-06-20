<?php
/**
 * @package     Joomla.Test
 * @subpackage  Webdriver
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

/**
 * This class tests the  Control panel.
 *
 * @package     Joomla.Tests
 * @subpackage  Test
 *
 * @copyright   Copyright (c) 2005 - 2016 Open Source Matters, Inc.   All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       Joomla 3.3
 */
class GroupManager0001Test extends JoomlaWebdriverTestCase
{
	/**
	 *
	 * @var GroupManagerPage
	 */
	protected $groupManagerPage = null; /* Global configuration page*/

	/**
	 * Login to back end and navigate to menu Language Manager.
	 *
	 * @return void
	 *
	 * @since   3.0
	 */
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->groupManagerPage = $cpPage->clickMenu('Groups', 'GroupManagerPage');
	}

	/**
	 * Logout and close test.
	 *
	 * @return void
	 *
	 * @since   3.0
	 */
	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}

	/**
	 * open edit screen
	 *
	 * @return void
	 *
	 * @test
	 */
	public function constructor_OpenEditScreen_GroupEditOpened()
	{
		$this->groupManagerPage->clickButton('toolbar-new');
		$groupEditPage = $this->getPageObject('GroupEditPage');
		$groupEditPage->clickButton('toolbar-cancel');
		$this->groupManagerPage = $this->getPageObject('groupManagerPage');
	}

	/**
	 * Gets the actual input fields and checks them against the $inputFields property.
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getAllInputFields_ScreenDisplayed_EqualExpected()
	{
		$this->groupManagerPage->clickButton('toolbar-new');
		$groupEditPage = $this->getPageObject('GroupEditPage');

		/* Option to print actual element array
		/* @var $groupEditPage GroupEditPage */
		// 	 	$groupEditPage->printFieldArray($groupEditPage->getAllInputFields($groupEditPage->tabs));

		$testElements = $groupEditPage->getAllInputFields();
		$actualFields = $this->getActualFieldsFromElements($testElements);
		$this->assertEquals($groupEditPage->inputFields, $actualFields);
		$groupEditPage->clickButton('toolbar-cancel');
		$this->groupManagerPage = $this->getPageObject('groupManagerPage');
	}

	/**
	 * add group with default values
	 *
	 * @return void
	 *
	 * @test
	 */
	public function add_WithFieldDefaults_Added()
	{
		$this->assertFalse($this->groupManagerPage->getRowNumber('Test '), 'Test group should not be present');
		$this->groupManagerPage->addGroup();
		$message = $this->groupManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Group successfully saved') >= 0, 'Group save should return success');
		$this->assertEquals(12, $this->groupManagerPage->getRowNumber('Test Group'), 'Test group should be in row 2');
		$this->groupManagerPage->delete('Test Group');
		$this->assertFalse($this->groupManagerPage->getRowNumber('Test Group'), 'Test group should not be present');
	}

	/**
	 * add a group with given values
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addGroup_WithGivenFields_GroupAdded()
	{
		$salt = rand();
		$groupName = 'Group' . $salt;
		$parent = 'Administrator';
		$this->assertFalse($this->groupManagerPage->getRowNumber($groupName), 'Test group should not be present');
		$this->groupManagerPage->addGroup($groupName, $parent);
		$message = $this->groupManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Group successfully saved') >= 0, 'Group save should return success');
		$this->assertTrue($this->groupManagerPage->getRowNumber($groupName) > 0, 'Test group should be on the page');
		$values = $this->groupManagerPage->getFieldValues('GroupEditPage', $groupName, array('Group Parent'));
		$this->assertStringEndsWith($parent, $values[0], 'Actual group parent should match expected');
		$this->groupManagerPage->delete($groupName);
		$this->assertFalse($this->groupManagerPage->getRowNumber($groupName), 'Test group should not be present');
	}

	/**
	 * edit the values of input fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function editGroup_ChangeFields_FieldsChanged()
	{
		$salt = rand();
		$groupName = 'Group' . $salt;
		$parent = 'Author';
		$this->assertFalse($this->groupManagerPage->getRowNumber($groupName), 'Test group should not be present');
		$this->groupManagerPage->addGroup($groupName, $parent);
		$this->groupManagerPage->editGroup($groupName, array('Group Parent' => 'Publisher'));
		$rowText = $this->groupManagerPage->getRowText($groupName);
		$values = $this->groupManagerPage->getFieldValues('GroupEditPage', $groupName, array('Group Parent'));
		$this->assertStringEndsWith('Publisher', $values[0], 'Actual group parent should be Publisher');
		$this->groupManagerPage->delete($groupName);
	}

	/**
	 * test to order the groups
	 *
	 * @return void
	 *
	 * @test
	 */
	public function setFilter_TestOrdering_ShouldOrderGroups()
	{
		$orderings = array('Group Title', 'ID');
		$rows = array(
			'Administrator',
			'Author',
			'Customer Group',
			'Editor',
			'Guest',
			'Manager',
			'Public',
			'Publisher',
			'Registered',
			'Shop Suppliers',
			'Super Users'
		);
		$actualRowNumbers = $this->groupManagerPage->orderAndGetRowNumbers($orderings, $rows);

		$expectedRowNumbers = array(
				'Group Title' => array(
					'ascending' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11),
					'descending' => array(11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1)
					),
				'ID' => array(
					'ascending' => array(7, 3, 10, 4, 11, 6, 1, 5, 2, 9, 8),
					'descending' => array(5, 9, 2, 8, 1, 6, 11, 7, 10, 3, 4)
					)
				);

		foreach ($actualRowNumbers as $ordering => $orderingRowNumbers)
		{
			foreach ($orderingRowNumbers as $order => $rowNumbers)
			{
				foreach ($rowNumbers as $key => $rowNumber)
				{
					$this->assertEquals(
							$expectedRowNumbers[$ordering][$order][$key],
							$rowNumber,
							'When the table is sorted by ' . strtolower($ordering) . ' in the ' . $order . ' order '
							. $rows[$key] . ' should be in row ' . $expectedRowNumbers[$ordering][$order][$key]
					);
				}
			}
		}
	}
}
