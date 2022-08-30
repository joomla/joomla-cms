<?php

/**
 * @package     Joomla.Tests
 * @subpackage  AcceptanceTester.Page
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance\Administrator;

use AcceptanceTester;
use Exception;

/**
 * Acceptance Page object class to define administrator page objects.
 *
 * @package  Page\Acceptance\Administrator
 *
 * @since    3.7.3
 */
class AdminPage extends AcceptanceTester
{
    /**
     * The element id which contains system messages.
     *
     * @var    array
     * @since  3.7.3
     */
    public static $systemMessageContainer = ['id' => 'system-message-container'];

    /**
     * The form element with id "adminForm"
     *
     * @var    array
     * @since  4.0.0
     */
    public static $adminForm = ['id' => 'adminForm'];

    /**
     * The element id which contains page title in administrator header.
     *
     * @var    array
     * @since  3.7.3
     */
    public static $pageTitle = ['class' => 'page-title'];

    /**
     * Locator for page title.
     *
     * @var    array
     * @since  3.7.3
     */
    public static $title = ['id' => 'jform_title'];

    /**
     * Locator for search input field.
     *
     * @var    array
     * @since  3.7.3
     */
    public static $filterSearch = ['id' => 'filter_search'];

    /**
     * Locator for status filter under search tool.
     *
     * @var    array
     * @since  3.7.3
     */
    public static $filterPublished = 'filter_published';

    /**
     * Locator for search button icon.
     *
     * @var    array
     * @since  3.7.3
     */
    public static $iconSearch = ['xpath' => "//button[@title='Search']"];

    /**
     * Locator for the Tabs in Edit View.
     *
     * @var    array
     * @since  3.7.3
     */
    public static $tabsLocator = ['xpath' => "//div[@role='tablist']/button"];

    /**
     * Locator for the Check All checkbox.
     *
     * @var    array
     * @since  3.7.3
     */
    public static $checkAll = ['xpath' => "//thead//input[@name='checkall-toggle' or @name='toggle']"];

    /**
     * Method to search using given keyword.
     *
     * @param   string  $keyword  The keyword to search
     *
     * @return  void
     *
     * @since   3.7.3
     */
    public function search($keyword)
    {
        $I = $this;

        $I->amGoingTo('search for "' . $keyword . '"');
        $I->fillField(static::$filterSearch, $keyword);
        $I->click(static::$iconSearch);
    }

    /**
     * Method to search user with username.
     *
     * @param   string  $keyword  The username of user
     *
     * @return  void  Checkbox for given username will be checked.
     *
     * @since   3.7.3
     */
    public function haveItemUsingSearch($keyword)
    {
        $I = $this;

        $I->amOnPage(static::$url);
        $I->search($keyword);
        $I->checkAllResults();
        $I->wait(1);
    }

    /**
     * Method is used to see system message after waiting for page title.
     *
     * @param   string  $title    The webpage title
     * @param   string  $message  The unpublish successful message
     *
     * @return  void
     *
     * @since   3.7.3
     */
    public function seeSystemMessage($title, $message)
    {
        $I = $this;

        $I->waitForPageTitle($title);
        $I->waitForElementVisible(self::$systemMessageContainer, TIMEOUT);
        $I->see($message, self::$systemMessageContainer);
    }

    /**
     * Method is to Wait for page title until default timeout.
     *
     * @param   string  $title  Page Title text
     *
     * @return  void
     *
     * @since   3.7.3
     *
     * @throws Exception
     */
    public function waitForPageTitle($title)
    {
        $I = $this;
        $I->waitForText($title, $I->getConfig('timeout'), self::$pageTitle);
    }

    /**
     * Function to select all the item in the Search results in Administrator List.
     *
     * Note: We recommend use of checkAllResults function only after searchForItem to be sure you are selecting only
     * the desired result set.
     *
     * @return  void
     *
     * @since   3.7.3
     */
    public function checkAllResults()
    {
        $I = $this;

        $I->comment("Selecting Checkall button");
        $I->click(self::$checkAll);
    }

    /**
     * Selects an option in a Chosen Selector based on its id.
     *
     * @param   string  $selectId  The id of the <select> element
     * @param   string  $option    The text in the <option> to be selected in the chosen selector
     *
     * @return  void
     *
     * @since   3.7.3
     */
    public function selectOptionInChosenById($selectId, $option)
    {
        $chosenSelectID = $selectId . '_chzn';

        $I = $this;
        $I->comment("I open the $chosenSelectID chosen selector");
        $I->click(['xpath' => "//div[@id='$chosenSelectID']/a/div/b"]);
        $I->comment("I select $option");
        $I->click(['xpath' => "//div[@id='$chosenSelectID']//li[text()='$option']"]);

        // Gives time to chosen to close
        $I->wait(1);
    }

    /**
     * Function to Logout from Administrator Panel in Joomla!.
     *
     * @return  void
     *
     * @since   3.7.3
     *
     * @throws Exception
     */
    public function doAdministratorLogout()
    {
        $I = $this;
        $I->click(
            ['xpath' => "//ul[@class='nav nav-user pull-right']//li//a[@class='dropdown-toggle']"]
        );

        $I->comment("I click on Top Right corner toggle to Logout from Admin");
        $I->waitForElement(
            ['xpath' => "//li[@class='dropdown open']/ul[@class='dropdown-menu']//a[text() = 'Logout']"],
            TIMEOUT
        );

        $I->click(
            ['xpath' => "//li[@class='dropdown open']/ul[@class='dropdown-menu']//a[text() = 'Logout']"]
        );

        $I->waitForElement(['id' => 'mod-login-username'], $I->getConfig('timeout'));
        $I->waitForText(
            'Log in',
            TIMEOUT,
            ['xpath' => "//fieldset[@class='loginform']//button"]
        );
    }

    /**
     * Function to Verify the Tabs on a Joomla! screen.
     *
     * @param   array  $expectedTabs  Expected Tabs on the Page
     * @param   array  $tabsLocator   Locator for the Tabs in Edit View
     *
     * @return  void
     *
     * @since   3.7.3
     */
    public function verifyAvailableTabs($expectedTabs, $tabsLocator = null)
    {
        $I = $this;

        $actualArrayOfTabs = $I->grabMultiple(self::$tabsLocator);

        $I->comment(
            "Fetch the current list of Tabs in the edit view which is: " . implode(", ", $actualArrayOfTabs)
        );

        $I->assertEquals(
            $expectedTabs,
            $actualArrayOfTabs,
            "Tab Labels do not match on edit view of" . $I->grabFromCurrentUrl()
        );

        $I->comment('Verify the Tabs');
    }

    /**
     * Method to see that item is saved.
     *
     * @param   string  $item  The item Name
     *
     * @return  void
     *
     * @since   3.7.3
     */
    public function seeItemIsCreated($item)
    {
        $I = $this;

        $I->amOnPage(static::$url);
        $I->search($item);
        $I->see($item, static::$seeName);
    }

    /**
     * Assure the item is trashed.
     *
     * @param   string  $item       The item name
     * @param   string  $pageTitle  The page title
     *
     * @return  void
     *
     * @since   3.7.3
     */
    public function seeItemInTrash($item, $pageTitle)
    {
        $I = $this;

        $I->click('Search Tools');
        $I->wait(2);
        $I->selectOptionInChosenById(static::$filterPublished, 'Trashed');
        $I->waitForPageTitle($pageTitle);
        $I->see($item, static::$seeName);
    }

    /**
     * Assure the search tools are displayed.
     *
     * @return  void
     *
     * @since   3.7.3
     */
    public function displaySearchTools()
    {
        $I = $this;

        try {
            $I->seeElement(['class' => 'js-stools-btn-filter']);
        } catch (Exception $e) {
            $I->comment("Search tools button does not exist on this page, skipping");

            return;
        }

        try {
            $I->dontSeeElement(['class' => 'js-stools-container-filters']);
        } catch (Exception $e) {
            $I->comment("Search tools already visible on the page, skipping");

            return;
        }

        $I->click('Search Tools');
    }
}
