<?php
/**
 * @package     Joomla.Test
 * @subpackage  AcceptanceTester.Step
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;

use Page\Acceptance\Administrator\BannerManagerPage;

/**
 * Acceptance Step object class contains suits for Content Manager.
 *
 * @package  Step\Acceptance\Administrator
 *
 * @since    __DEPLOY_VERSION__
 */
class Banner extends \AcceptanceTester
{
	/**
	 * @Given There is an add Banner link
	 */
	public function thereIsAnAddBannerLink()
	{
		$I = $this;
		$I->amOnPage(BannerManagerPage::$url);
		$I->clickToolbarButton('New');
	}
	/**
	 * @When I create a new banner with field title as :title
	 */
	public function iCreateANewBannerWithFieldTitleAs($title)
	{
		$I = $this;
		$I->fillField(BannerManagerPage::$titleField, $title);
	}
	/**
	 * @When I save a Banner
	 */
	public function iSaveABanner()
	{
		$I = $this;
		$I->clickToolbarButton('Save & Close');
	}
	/**
	 * @Then I should see the :Banner successfully saved. message
	 */
	public function iShouldSeeTheMessage($message)
	{
		$I = $this;
		$I->waitForText($message, TIMEOUT, BannerManagerPage::$systemMessageContainer);
		$I->see($message, BannerManagerPage::$systemMessageContainer);
	}
	/**
	 * @Given There is a Banner listing page
	 */
	public function thereIsABannerListingPage()
	{
		$I = $this;
		$I->amOnPage(BannerManagerPage::$url);
	}
	/**
	 * @When I Click the Banner with Name :Gsocbanner
	 */
	public function iClickTheBannerWithName($title)
	{
		$I = $this;
		$I->fillField(BannerManagerPage::$searchField, $title);
		$I->Click(BannerManagerPage::$searchButton);
		$I->checkAllResults();
		$I->clickToolbarButton('edit');
	}
	/**
	 * @When I have Change the Banner field title to :Gsocbanner
	 */
	public function iHaveChangeTheBannerFieldTitleTo($title)
	{
		$I = $this;
		$I->fillField(BannerManagerPage::$titleField, $title);
		$I->fillField(BannerManagerPage::$aliasField, $title);
	}
	/**
	 * @When I select the Banner with Name :Gsocbanner which needs to be published
	 */
	public function iSelectTheBannerWithNameWhichNeedsToBePublished($title)
	{
		$I = $this;
		$I->fillField(BannerManagerPage::$searchField, $title);
		$I->Click(BannerManagerPage::$searchButton);
		$I->checkAllResults();
	}
	/**
	 * @When I have publish the Banner
	 */
	public function iHavePublishTheBanner()
	{
		$I = $this;
		$I->clickToolbarButton('Publish');
	}
	/**
	 * @When I select the Banner with Name :Gsocbanner which needs to be unpublished
	 */
	public function iSelectTheBannerWithNameWhichNeedsToBeUnpublished($title)
	{
		$I = $this;
		$I->fillField(BannerManagerPage::$searchField, $title);
		$I->Click(BannerManagerPage::$searchButton);
		$I->checkAllResults();
	}
	/**
	 * @When I have unpublish the Banner
	 */
	public function iHaveUnpublishTheBanner()
	{
		$I = $this;
		$I->clickToolbarButton('Unpublish');
	}
	/**
	 * @When I select the Banner with Name :Gsocbanner which needs to be Trash
	 */
	public function iSelectTheBannerWithNameWhichNeedsToBeTrash($title)
	{
		$I = $this;
		$I->fillField(BannerManagerPage::$searchField, $title);
		$I->Click(BannerManagerPage::$searchButton);
		$I->checkAllResults();
	}
	/**
	 * @When I have Trash the Banner
	 */
	public function iTrashTheBanner()
	{
		$I = $this;
		$I->clickToolbarButton('Trash');
	}

	/**
	 * @When I select the Banner with Name "randombanner" which needs to be Remove Trash
	 */
	public function iSelectTheBannerWithNameWhichNeedsToBeRemoveTrash()
	{
		$I = $this;
		$I->Click(BannerManagerPage::$searchToolButton);
		$I->selectOptionInChosenByIdUsingJs('filter_published', "Trashed");
		$I->checkAllResults();
	}
	/**
	 * @When I Remove Trash the Banner
	 */
	public function iRemoveTrashTheBanner()
	{
		$I = $this;
		$I->clickToolbarButton('Empty trash');
		$I->acceptPopup();
	}
	/**
	 * @When I select the Banner with Name :Gsocbanner which needs to be archived
	 */
	public function iSelectTheBannerWithNameWhichNeedsToBeArchived($title)
	{
		$I = $this;
		$I->fillField(BannerManagerPage::$searchField, $title);
		$I->Click(BannerManagerPage::$searchButton);
		$I->checkAllResults();
	}
	/**
	 * @When I archived the Banner
	 */
	public function iArchivedTheBanner()
	{
		$I = $this;
		$I->clickToolbarButton('Archive');
	}
	/**
	 * @When I select the Banner with Name :Gsocbanner which needs to be Unarchive
	 */
	public function iSelectTheBannerWithNameWhichNeedsToBeUnarchive()
	{
		$I = $this;
		$I->Click(BannerManagerPage::$searchToolButton);
		$I->selectOptionInChosenById('filter_published', "Archived");
		$I->checkAllResults();
	}
	/**
	 * @When I Unarchive the Banner
	 */
	public function iUnarchiveTheBanner()
	{
		$I = $this;
		$I->clickToolbarButton('unarchive');
	}
	/**
	 * @When I select the Banner with Name :arg1 which needs to be Check-In
	 */
	public function iSelectTheBannerWithNameWhichNeedsToBeCheckIn($title)
	{
		$I = $this;
		$I->fillField(BannerManagerPage::$searchField, $title);
		$I->Click(BannerManagerPage::$searchButton);
		$I->checkAllResults();
	}
	/**
	 * @When I Check-In the Banner
	 */
	public function iCheckInTheBanner()
	{
		$I = $this;
		$I->clickToolbarButton('Check-in');
	}
}