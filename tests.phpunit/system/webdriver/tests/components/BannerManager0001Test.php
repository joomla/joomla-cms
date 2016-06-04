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
 * This class tests the  Banner: Add / Edit  Screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.2
 */
class BannerManager0001Test extends JoomlaWebdriverTestCase
{
	/**
	 * The page class being tested.
	 *
	 * @var     bannerManagerPage
	 * @since   3.2
	 */
	protected $bannerManagerPage = null;

	/**
	 * Login to back end and navigate to menu Banners.
	 *
	 * @return void
	 *
	 * @since   3.2
	 */
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->bannerManagerPage = $cpPage->clickMenu('Banners', 'BannerManagerPage');
	}

	/**
	 * Logout and close test.
	 *
	 * @return void
	 *
	 * @since   3.2
	 */
	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}

	/**
	 * check all input fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getAllInputFields_ScreenDisplayed_EqualExpected()
	{
		$this->bannerManagerPage->clickButton('toolbar-new');
		$bannerEditPage = $this->getPageObject('BannerEditPage');

		/* Option to print actual element array */
		/* @var $bannerEditPage BannerEditPage */
// 	 	$bannerEditPage->printFieldArray($bannerEditPage->getAllInputFields($bannerEditPage->tabs));

		$testElements = $bannerEditPage->getAllInputFields($bannerEditPage->tabs);
		$actualFields = $this->getActualFieldsFromElements($testElements);
		$this->assertEquals($bannerEditPage->inputFields, $actualFields);
		$bannerEditPage->clickButton('toolbar-cancel');
		$this->bannerManagerPage = $this->getPageObject('BannerManagerPage');
	}

	/**
	 * check bannner edit page
	 *
	 * @return void
	 *
	 * @test
	 */
	public function constructor_OpenEditScreen_BannerEditOpened()
	{
		$this->bannerManagerPage->clickButton('new');
		$bannerEditPage = $this->getPageObject('BannerEditPage');
		$bannerEditPage->clickButton('cancel');
		$this->bannerManagerPage = $this->getPageObject('BannerManagerPage');
	}

	/**
	 * check tab IDs
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getTabIds_ScreenDisplayed_EqualExpected()
	{
		$this->bannerManagerPage->clickButton('toolbar-new');
		$bannerEditPage = $this->getPageObject('BannerEditPage');
		$textArray = $bannerEditPage->getTabIds();
		$this->assertEquals($bannerEditPage->tabs, $textArray, 'Banner labels should match expected values.');
		$bannerEditPage->clickButton('toolbar-cancel');
		$this->bannerManagerPage = $this->getPageObject('BannerManagerPage');
	}

	/**
	 * add banner with default fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addBanner_WithFieldDefaults_BannerAdded()
	{
		$salt = rand();
		$bannerName = 'Banner' . $salt;
		$this->assertFalse($this->bannerManagerPage->getRowNumber($bannerName), 'Test Banner should not be present');
		$this->bannerManagerPage->addBanner($bannerName, false);
		$message = $this->bannerManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Banner successfully saved') >= 0, 'Banner save should return success');
		$this->assertEquals(1, $this->bannerManagerPage->getRowNumber($bannerName), 'Test Banner should be in row 1');
		$this->bannerManagerPage->trashAndDelete($bannerName);
		$this->assertFalse($this->bannerManagerPage->getRowNumber($bannerName), 'Test Banner should not be present');
	}

	/**
	 * add banner with given fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addBanner_WithGivenFields_BannerAdded()
	{
		$salt = rand();
		$bannerName = 'Banner' . $salt;
		$client = 'Joomla!';
		$TrackClicks = 'Yes';
		$width = '35';

		$this->assertFalse($this->bannerManagerPage->getRowNumber($bannerName), 'Test banner should not be present');
		$this->bannerManagerPage->addBanner($bannerName, array('Client' => $client, 'Track Clicks' => $TrackClicks, 'Width' => $width));
		$message = $this->bannerManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Banner successfully saved') >= 0, 'Banner save should return success');
		$this->assertEquals(1, $this->bannerManagerPage->getRowNumber($bannerName), 'Test banner should be in row 1');
		$values = $this->bannerManagerPage->getFieldValues('BannerEditPage', $bannerName, array('Name', 'Client', 'Track Clicks', 'Width'));
		$this->assertEquals(array($bannerName, $client, $TrackClicks, $width), $values, 'Actual name, client, track clicks and width should match expected');
		$this->bannerManagerPage->trashAndDelete($bannerName);
		$this->assertFalse($this->bannerManagerPage->getRowNumber($bannerName), 'Test banner should not be present');
	}

	/**
	 * edit banner and change the value of the fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function editBanner_ChangeFields_FieldsChanged()
	{
		$salt = rand();
		$bannerName = 'Banner' . $salt;
		$client = 'Joomla!';
		$TrackClicks = 'Yes';
		$width = '35';
		$this->assertFalse($this->bannerManagerPage->getRowNumber($bannerName), 'Test banner should not be present');
		$this->bannerManagerPage->addBanner($bannerName, false);
		$this->bannerManagerPage->editBanner($bannerName, array('Client' => $client, 'Track Clicks' => $TrackClicks, 'Width' => $width));
		$values = $this->bannerManagerPage->getFieldValues('BannerEditPage', $bannerName, array('Name', 'Client', 'Track Clicks', 'Width'));
		$this->assertEquals(array($bannerName, $client, $TrackClicks, $width), $values, 'Actual name, client, track clicks and width should match expected');
		$this->bannerManagerPage->trashAndDelete($bannerName);
	}

	/**
	 * change the state of the banner
	 *
	 * @return void
	 *
	 * @test
	 */
	public function changeBannerState_ChangeEnabledUsingToolbar_EnabledChanged()
	{
		$salt = rand();
		$bannerName = 'Banner' . $salt;
		$this->bannerManagerPage->addBanner($bannerName, false);
		$state = $this->bannerManagerPage->getState($bannerName);
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->bannerManagerPage->changeBannerState($bannerName, 'unpublished');
		$state = $this->bannerManagerPage->getState($bannerName);
		$this->assertEquals('unpublished', $state, 'State should be unpublished');
		$this->bannerManagerPage->trashAndDelete($bannerName);
	}
}
