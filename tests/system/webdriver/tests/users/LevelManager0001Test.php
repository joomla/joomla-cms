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
class LevelManager0001Test extends JoomlaWebdriverTestCase
{
	/**
	 *
	 * @var LevelManagerPage
	 */
	protected $levelManagerPage = null; /* Global configuration page*/

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
		$this->levelManagerPage = $cpPage->clickMenu('Access Levels', 'LevelManagerPage');
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
	public function constructor_OpenEditScreen_LevelEditOpened()
	{
		$this->levelManagerPage->clickButton('toolbar-new');
		$levelEditPage = $this->getPageObject('LevelEditPage');
		$levelEditPage->clickButton('toolbar-cancel');
		$this->levelManagerPage = $this->getPageObject('LevelManagerPage');
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
		$this->levelManagerPage->clickButton('toolbar-new');
		$levelEditPage = $this->getPageObject('LevelEditPage');

		$testElements = $levelEditPage->getAllInputFields();
		$actualFields = $this->getActualFieldsFromElements($testElements);
		$this->assertEquals($levelEditPage->inputFields, $actualFields);
		$levelEditPage->clickButton('toolbar-cancel');
		$this->levelManagerPage = $this->getPageObject('LevelManagerPage');
	}

	/**
	 * add a level with default fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function add_WithFieldDefaults_Added()
	{
		$this->assertFalse($this->levelManagerPage->getRowNumber('Test Level'), 'Test level should not be present');
		$this->levelManagerPage->addLevel();
		$message = $this->levelManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Level successfully saved') >= 0, 'Level save should return success');
		$this->assertGreaterThanOrEqual(1, $this->levelManagerPage->getRowNumber('Test Level'), 'Test level should be present');
		$this->levelManagerPage->delete('Test Level');
		$this->assertFalse($this->levelManagerPage->getRowNumber('Test Level'), 'Test level should not be present');
	}

	/**
	 * add a level with given fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addLevel_WithGivenFields_LevelAdded()
	{
		$salt = rand();
		$levelName = 'Level' . $salt;
		$groups = array('Registered', 'Manager');
		$this->assertFalse($this->levelManagerPage->getRowNumber($levelName), 'Test level should not be present');
		$this->levelManagerPage->addLevel($levelName, $groups);
		$message = $this->levelManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Level successfully saved') >= 0, 'Level save should return success');
		$this->assertTrue($this->levelManagerPage->getRowNumber($levelName) > 0, 'Test level should be on the page');
		$actualGroups = $this->levelManagerPage->getGroups($levelName);
		sort($groups);
		sort($actualGroups);
		$this->assertEquals($groups, $actualGroups, 'Assigned groups should be as expected');
		$this->levelManagerPage->delete($levelName);
		$this->assertFalse($this->levelManagerPage->getRowNumber($levelName), 'Test level should not be present');
	}

	/**
	 * edit the input fields of a level
	 *
	 * @return void
	 *
	 * @test
	 */
	public function editLevel_ChangeFields_FieldsChanged()
	{
		$salt = rand();
		$levelName = 'Level' . $salt;
		$groups = array('Customer', 'Administrator', 'Author');
		$this->assertFalse($this->levelManagerPage->getRowNumber($levelName), 'Test level should not be present');
		$this->levelManagerPage->addLevel($levelName, $groups);
		$newGroups = array('Manager', 'Publisher');
		$this->levelManagerPage->editLevel($levelName, $newGroups);
		$actualGroups = $this->levelManagerPage->getGroups($levelName);
		sort($actualGroups);
		sort($newGroups);
		$this->assertEquals($newGroups, $actualGroups, 'New groups should be assigned to level');
		$this->levelManagerPage->delete($levelName);
	}

	/**
	 * set filter to order the levels
	 *
	 * @return void
	 *
	 * @test
	 */
	public function setFilter_TestOrdering_ShouldOrderLevels()
	{
		$orderings = array('Level Name', 'ID');
		$rows = array('Customer Access', 'Guest', 'Public', 'Registered', 'Special', 'Super Users');
		$actualRowNumbers = $this->levelManagerPage->orderAndGetRowNumbers($orderings, $rows);

		$expectedRowNumbers = array(
				'Level Name' => array('ascending' => array(1, 2, 3, 4, 5, 6), 'descending' => array(6, 5, 4, 3, 2, 1)),
				'ID' => array('ascending' => array(4, 5, 1, 2, 3, 6), 'descending' => array(3, 2, 6, 5, 4, 1))
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
