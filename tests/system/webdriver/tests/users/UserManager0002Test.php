<?php

require_once 'JoomlaWebdriverTestCase.php';

/**
 * This class tests the User Manager: Add / Edit User Screen
 * @author Mark
 *
 */
class UserManager0002Test extends JoomlaWebdriverTestCase
{
	/**
	 *
	 * @var UserManagerPage
	 */
	protected $userManagerPage = null; // Global configuration page

	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->userManagerPage = $cpPage->clickMenu('User Manager', 'UserManagerPage');
	}

	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function getFilters_GetListOfFilters_ShouldMatchExpected()
	{
		$actualIds = $this->userManagerPage->getFilters();
		$expectedIds = array_values($this->userManagerPage->filters);
		$this->assertEquals($expectedIds, $actualIds, 'Filter ids should match expected');
	}

	/**
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
	 * @test
	 */
	public function setFilter_TestFilters_ShouldFilterUsers()
	{
		$this->userManagerPage->addUser('Test User1', 'login1', 'password1', 'user1@test.com', array('Registered'));
		$this->userManagerPage->addUser('Test User2', 'login2', 'password2', 'user2@test.com', array('Manager'));;
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

		$this->userManagerPage->setFilter('Group', 'Group');
		$this->userManagerPage->deleteUser('Test User');
		$this->assertFalse($this->userManagerPage->getRowNumber('Test User1') > 0, 'Test User1 should not be present');
		$this->assertFalse($this->userManagerPage->getRowNumber('Test User2') > 0, 'Test User2 should not be present');
		$this->assertFalse($this->userManagerPage->getRowNumber('Test User3') > 0, 'Test User3 should not be present');
	}
}
