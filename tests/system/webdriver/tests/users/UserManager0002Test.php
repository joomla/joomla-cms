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

class UserManager0002Test extends JoomlaWebdriverTestCase
{
	/**
	 *
	 * @var UserManagerPage
	 */
	protected $userManagerPage = null; /* Global configuration page*/

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
		$this->userManagerPage = $cpPage->clickMenu('User Manager', 'UserManagerPage');
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
	 * get the available filters
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getFilters_GetListOfFilters_ShouldMatchExpected()
	{
		$actualIds = $this->userManagerPage->getFilters();
		$expectedIds = array_values($this->userManagerPage->filters);
		$this->assertEquals($expectedIds, $actualIds, 'Filter ids should match expected');
	}

	/**
	 * check the execution of the filters
	 *
	 * @return void
	 *
	 * @test
	 */
	public function setFilter_SetFilterValues_ShouldExecuteFilter()
	{
		$this->assertEquals(1, $this->userManagerPage->getRowNumber('Super User'), 'Super user should be in row 1');
		$test = $this->userManagerPage->setFilter('filter_state', 'Disabled');
		$this->userManagerPage = $this->getPageObject('UserManagerPage');
		$this->assertFalse($this->userManagerPage->getRowNumber('Super User'), 'Super user should not show');
		$test = $this->userManagerPage->setFilter('State', 'State');
		$this->assertEquals(1, $this->userManagerPage->getRowNumber('Super User'), 'Super user should be in row 1');
	}

	/**
	 * check the working of the filters
	 *
	 * @return void
	 *
	 * @test
	 */
	public function setFilter_TestFilters_ShouldFilterUsers()
	{
		$this->userManagerPage->addUser('Test User1', 'login1', 'password1', 'user1@test.com', array('Registered'));
		$this->userManagerPage->addUser('Test User2', 'login2', 'password2', 'user2@test.com', array('Manager'));
		$this->userManagerPage->addUser('Test User3', 'login3', 'password3', 'user3@test.com', array('Registered', 'Manager'));
		$this->assertTrue($this->userManagerPage->getRowNumber('Test User1') > 0, 'Test User1 should be present');
		$this->assertTrue($this->userManagerPage->getRowNumber('Test User2') > 0, 'Test User2 should be present');
		$this->assertTrue($this->userManagerPage->getRowNumber('Test User3') > 0, 'Test User3 should be present');

		$this->userManagerPage->changeUserState('Test User1', 'unpublished');
		$this->userManagerPage->editUser('Test User2', array('Block this User' => 'Yes'));

		$this->userManagerPage->setFilter('State', 'Disabled');
		$this->assertTrue($this->userManagerPage->getRowNumber('Test User1') > 0, 'Test User1 should be present');
		$this->assertTrue($this->userManagerPage->getRowNumber('Test User2') > 0, 'Test User2 should be present');
		$this->assertFalse($this->userManagerPage->getRowNumber('Test User3') > 0, 'Test User3 should not be present');

		$this->userManagerPage->setFilter('State', 'State');
		$this->userManagerPage->setFilter('Active', 'Unactivated');
		$this->assertFalse($this->userManagerPage->getRowNumber('Test User1') > 0, 'Test User1 should not be present');
		$this->assertFalse($this->userManagerPage->getRowNumber('Test User2') > 0, 'Test User2 should not be present');
		$this->assertFalse($this->userManagerPage->getRowNumber('Test User3') > 0, 'Test User3 should not be present');

		$this->userManagerPage->setFilter('Active', 'Active');
		$this->userManagerPage->setFilter('Group', 'Manager');
		$this->assertFalse($this->userManagerPage->getRowNumber('Test User1') > 0, 'Test User1 should not be present');
		$this->assertTrue($this->userManagerPage->getRowNumber('Test User2') > 0, 'Test User2 should be present');
		$this->assertTrue($this->userManagerPage->getRowNumber('Test User3') > 0, 'Test User3 should be present');

		$this->userManagerPage->searchFor();
		$this->userManagerPage->delete('Test User');
		$this->assertFalse($this->userManagerPage->getRowNumber('Test User1') > 0, 'Test User1 should not be present');
		$this->assertFalse($this->userManagerPage->getRowNumber('Test User2') > 0, 'Test User2 should not be present');
		$this->assertFalse($this->userManagerPage->getRowNumber('Test User3') > 0, 'Test User3 should not be present');
	}

	/**
	 * ordering the user according to the filter
	 *
	 * @return void
	 *
	 * @test
	 */
	public function setFilter_TestOrdering_ShouldOrderUsers()
	{
		$this->userManagerPage->addUser('Test User1', 'login2', 'password1', 'user1@test.com', array('Registered'));
		$this->userManagerPage->addUser('Test User3', 'login3', 'password3', 'user2@test.com', array('Registered', 'Manager'));
		$this->userManagerPage->addUser('Test User2', 'login1', 'password2', 'user3@test.com', array('Manager'));
		$this->assertTrue($this->userManagerPage->getRowNumber('Test User1') > 0, 'Test User1 should be present');
		$this->assertTrue($this->userManagerPage->getRowNumber('Test User2') > 0, 'Test User2 should be present');
		$this->assertTrue($this->userManagerPage->getRowNumber('Test User3') > 0, 'Test User3 should be present');

		$this->userManagerPage->changeUserState('Test User1', 'unpublished');
		$this->userManagerPage->editUser('Test User2', array('Block this User' => 'Yes'));

		$orderings = array('Name', 'Username', 'Enabled', 'Activated', 'Email', 'Last visit date', 'Registration date', 'ID');
		$rows = array('Super User', 'Test User1', 'Test User2', 'Test User3');
		$actualRowNumbers = $this->userManagerPage->orderAndGetRowNumbers($orderings, $rows);

		$userNames = array($this->cfg->username, 'login2', 'login1', 'login3');
		$userNamesSorted = $userNames;
		sort($userNamesSorted, SORT_STRING);
		$userNamesReversed = array_reverse($userNamesSorted);

		$emails = array($this->cfg->admin_email, 'user1@test.com', 'user3@test.com', 'user2@test.com');
		$emailsSorted = $emails;
		sort($emailsSorted, SORT_STRING);
		$emailsReversed = array_reverse($emailsSorted);

		$expectedRowNumbers = array(
			'Name' => array('ascending' => array(1, 2, 3, 4), 'descending' => array(4, 3, 2, 1)),
			'Username' => array(
					'ascending' => array(
							array_search($userNames[0], $userNamesSorted) + 1,
							array_search($userNames[1], $userNamesSorted) + 1,
							array_search($userNames[2], $userNamesSorted) + 1,
							array_search($userNames[3], $userNamesSorted) + 1,
					),
					'descending' => array(
							array_search($userNames[0], $userNamesReversed) + 1,
							array_search($userNames[1], $userNamesReversed) + 1,
							array_search($userNames[2], $userNamesReversed) + 1,
							array_search($userNames[3], $userNamesReversed) + 1,
					)
			),
			'Enabled' => array('ascending' => array(1, 3, 4, 2), 'descending' => array(3, 1, 2, 4)),
			'Activated' => array('ascending' => array(4, 1, 3, 2), 'descending' => array(1, 2, 4, 3)),
			'Email' => array(
					'ascending' => array(
							array_search($emails[0], $emailsSorted) + 1,
							array_search($emails[1], $emailsSorted) + 1,
							array_search($emails[2], $emailsSorted) + 1,
							array_search($emails[3], $emailsSorted) + 1,
					),
					'descending' => array(
							array_search($emails[0], $emailsReversed) + 1,
							array_search($emails[1], $emailsReversed) + 1,
							array_search($emails[2], $emailsReversed) + 1,
							array_search($emails[3], $emailsReversed) + 1,
					)
			),
			'Last visit date' => array('ascending' => array(4, 1, 3, 2), 'descending' => array(1, 2, 4, 3)),
			'Registration date' => array('ascending' => array(1, 2, 4, 3), 'descending' => array(4, 3, 1, 2)),
			'ID' => array('ascending' => array(1, 2, 4, 3), 'descending' => array(4, 3, 1, 2))
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

		$this->userManagerPage->searchFor();
		$this->userManagerPage->delete('Test User');
		$this->assertFalse($this->userManagerPage->getRowNumber('Test User1') > 0, 'Test User1 should not be present');
		$this->assertFalse($this->userManagerPage->getRowNumber('Test User2') > 0, 'Test User2 should not be present');
		$this->assertFalse($this->userManagerPage->getRowNumber('Test User3') > 0, 'Test User3 should not be present');
	}
}
