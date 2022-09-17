<?php

/**
 * @package     Joomla.Tests
 * @subpackage  AcceptanceTester.Step
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;

use Exception;
use Page\Acceptance\Administrator\BannerListPage;

/**
 * Acceptance Step object class contains suits for Content Manager.
 *
 * @package  Step\Acceptance\Administrator
 *
 * @since    4.0.0
 */
class Banner extends Admin
{
    /**
     * Method to create a banner.
     *
     * @param   string  $title    Title
     * @param   string  $message  Message
     *
     * @return void
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function createBanner($title, $message)
    {
        $I = $this;
        $I->amOnPage(BannerListPage::$url);
        $I->clickToolbarButton('New');
        $I->fillField(BannerListPage::$titleField, $title);
        $I->clickToolbarButton('Save & Close');
        $I->assertSuccessMessage($message);
    }

    /**
     * Method to see success message.
     *
     * @param   string   $message  Message
     *
     * @return void
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function assertSuccessMessage($message)
    {
        $I = $this;
        $I->waitForText($message, $I->getConfig('timeout'), BannerListPage::$systemMessageContainer);
        $I->see($message, BannerListPage::$systemMessageContainer);
    }

    /**
     * Method to modify a banner.
     *
     * @param   string   $bannerTitle   Banner Title
     * @param   string   $updatedTitle  Update Title
     * @param   string   $message       Message
     *
     * @return void
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function modifyBanner($bannerTitle, $updatedTitle, $message)
    {
        $I = $this;
        $I->amOnPage(BannerListPage::$url);
        $I->fillField(BannerListPage::$searchField, $bannerTitle);
        $I->click(BannerListPage::$filterSearch);
        $I->checkAllResults();
        $I->click($bannerTitle);
        $I->waitForElement(BannerListPage::$titleField, $I->getConfig('timeout'));
        $I->fillField(BannerListPage::$titleField, $updatedTitle);
        $I->fillField(BannerListPage::$aliasField, $updatedTitle);
        $I->clickToolbarButton('Save & Close');
        $I->assertSuccessMessage($message);
    }

    /**
     * Method to publish a banner.
     *
     * @param   string   $bannerTitle   Banner Title
     * @param   string   $message       Message
     *
     * @return void
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function publishBanner($bannerTitle, $message)
    {
        $I = $this;
        $I->amOnPage(BannerListPage::$url);
        $I->waitForElement(BannerListPage::$searchField, $I->getConfig('timeout'));
        $I->fillField(BannerListPage::$searchField, $bannerTitle);
        $I->Click(BannerListPage::$filterSearch);
        $I->checkAllResults();
        $I->clickToolbarButton('Publish');
        $I->assertSuccessMessage($message);
    }

    /**
     * Method to unpublish a banner.
     *
     * @param   string   $bannerTitle   Banner Title
     * @param   string   $message       Message
     *
     * @return void
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function unpublishBanner($bannerTitle, $message)
    {
        $I = $this;
        $I->amOnPage(BannerListPage::$url);
        $I->waitForElement(BannerListPage::$searchField, $I->getConfig('timeout'));
        $I->fillField(BannerListPage::$searchField, $bannerTitle);
        $I->Click(BannerListPage::$filterSearch);
        $I->checkAllResults();
        $I->clickToolbarButton('Unpublish');
        $I->assertSuccessMessage($message);
    }

    /**
     * Method to checkin a banner.
     *
     * @param   string   $bannerTitle   Banner Title
     * @param   string   $message       Message
     *
     * @return void
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function checkInBanner($bannerTitle, $message)
    {
        $I = $this;
        $I->amOnPage(BannerListPage::$url);
        $I->waitForElement(BannerListPage::$searchField, $I->getConfig('timeout'));
        $I->fillField(BannerListPage::$searchField, $bannerTitle);
        $I->Click(BannerListPage::$filterSearch);
        $I->checkAllResults();
        $I->clickToolbarButton('check-in');
        $I->assertSuccessMessage($message);
    }

    /**
     * Method to trash a banner.
     *
     * @param   string   $bannerTitle   Banner Title
     * @param   string   $message       Message
     *
     * @return void
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function trashBanner($bannerTitle, $message)
    {
        $I = $this;
        $I->amOnPage(BannerListPage::$url);
        $I->waitForElement(BannerListPage::$searchField, $I->getConfig('timeout'));
        $I->fillField(BannerListPage::$searchField, $bannerTitle);
        $I->Click(BannerListPage::$filterSearch);
        $I->checkAllResults();
        $I->clickToolbarButton('Trash');
        $I->assertSuccessMessage($message);
    }

    /**
     * Method to delete a banner.
     *
     * @param   string   $bannerTitle   Banner Title
     * @param   string   $message       Message
     *
     * @return void
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function deleteBanner($bannerTitle, $message)
    {
        $I = $this;
        $I->amOnPage(BannerListPage::$url);
        $I->waitForElement(BannerListPage::$searchField, $I->getConfig('timeout'));

        // Make sure that the element js-stools-container-filters is visible.
        // Filter is a toggle button and I never know what happened before.
        $I->executeJS("[].forEach.call(document.querySelectorAll('.js-stools-container-filters'), function (el) {
			el.classList.add('js-stools-container-filters-visible');
		  });");
        $I->selectOption('//*[@id="filter_published"]', "-2");
        $I->wait(2);

        $I->fillField(BannerListPage::$searchField, $bannerTitle);
        $I->Click(BannerListPage::$filterSearch);
        $I->checkAllResults();
        $I->clickToolbarButton('Empty trash');
        $I->acceptPopup();
    }
}
