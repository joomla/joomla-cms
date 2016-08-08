<?php
use Page\Acceptance\Administrator\LoginPage;
use Page\Acceptance\Administrator\AdminPage;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */
class AcceptanceTester extends \Codeception\Actor
{
	use _generated\AcceptanceTesterActions;

	/**
	 * Method is to set Wait for page title
	 *
	 * @param   string   $title    Page Title text
	 * @param   integer  $timeout  timeout time
	 *
	 * @return  void
	 */
	public function waitForPageTitle($title)
	{
		$I = $this;
		$I->waitForText($title, TIMEOUT, AdminPage::$pageTitle);
	}

	/**
	 * Function to check for PHP Notices or Warnings
	 *
	 * @param string $page Optional, if not given checks will be done in the current page
	 *
	 * @note: doAdminLogin() before
	 */
	public function checkForPhpNoticesOrWarnings($page = null)
	{
		$I = $this;

		if ($page)
		{
			$I->amOnPage($page);
		}

		$I->dontSeeInPageSource('Notice:');
		$I->dontSeeInPageSource('<b>Notice</b>:');
		$I->dontSeeInPageSource('Warning:');
		$I->dontSeeInPageSource('<b>Warning</b>:');
		$I->dontSeeInPageSource('Strict standards:');
		$I->dontSeeInPageSource('<b>Strict standards</b>:');
		$I->dontSeeInPageSource('The requested page can\'t be found');
	}

	/**
	 * Function to select Toolbar buttons in Joomla! Admin Toolbar Panel
	 *
	 * @param   string  $button  The full name of the button
	 *
	 * @return  void
	 */
	public function clickToolbarButton($button)
	{
		$I = $this;
		$input = strtolower($button);

		$suiteConfiguration = $I->getSuiteConfiguration();
		$screenWidth = explode("x", $suiteConfiguration['modules']['config']['JoomlaBrowser']['window_size']);

		if ($screenWidth[0] <= 480)
		{
			$I->click('Toolbar');
		}

		switch ($input)
		{
			case "new":
				$I->click(['xpath' => "//div[@id='toolbar-new']//button"]);
				break;
			case "edit":
				$I->click(['xpath' => "//div[@id='toolbar-edit']//button"]);
				break;
			case "publish":
				$I->click(['xpath' => "//div[@id='toolbar-publish']//button"]);
				break;
			case "unpublish":
				$I->click(['xpath' => "//div[@id='toolbar-unpublish']//button"]);
				break;
			case "archive":
				$I->click(['xpath' => "//div[@id='toolbar-archive']//button"]);
				break;
			case "check-in":
				$I->click(['xpath' => "//div[@id='toolbar-checkin']//button"]);
				break;
			case "batch":
				$I->click(['xpath' => "//div[@id='toolbar-batch']//button"]);
				break;
			case "rebuild":
				$I->click(['xpath' => "//div[@id='toolbar-refresh']//button"]);
				break;
			case "trash":
				$I->click(['xpath' => "//div[@id='toolbar-trash']//button"]);
				break;
			case "save":
				$I->click(['xpath' => "//div[@id='toolbar-apply']//button"]);
				break;
			case "save & close":
				$I->click(['xpath' => "//div[@id='toolbar-save']//button"]);
				break;
			case "save & new":
				$I->click(['xpath' => "//div[@id='toolbar-save-new']//button"]);
				break;
			case "cancel":
				$I->click(['xpath' => "//div[@id='toolbar-cancel']//button"]);
				break;
			case "options":
				$I->click(['xpath' => "//div[@id='toolbar-options']//button"]);
				break;
			case "empty trash":
				$I->click(['xpath' => "//div[@id='toolbar-delete']//button"]);
				break;
			case "unblock":
				$I->click(['xpath' => "//div[@id='toolbar-unblock']//button"]);
				break;
			case "delete":
				$I->click(['xpath' => "//div[@id='toolbar-delete']//button"]);
				break;
			case "featured":
				$I->click(['xpath' => "//div[@id='toolbar-featured']//button"]);
				break;
		}
	}

	/**
	 * Function to select all the item in the Search results in Administrator List
	 *
	 * Note: We recommend use of checkAllResults function only after searchForItem to be sure you are selecting only the desired result set
	 *
	 * @return void
	 */
	public function checkAllResults()
	{
		$I = $this;
		$I->comment("Selecting Checkall button");
		$I->click(['xpath' => "//thead//input[@name='checkall-toggle' or @name='toggle']"]);
	}

	/**
	 * Selects an option in a Chosen Selector based on its id
	 *
	 * @param   string  $selectId  The id of the <select> element
	 * @param   string  $option    The text in the <option> to be selected in the chosen selector
	 *
	 * @return void
	 */
	public function selectOptionInChosenById($selectId, $option)
	{
		$chosenSelectID = $selectId . '_chzn';
		$I = $this;
		$I->comment("I open the $chosenSelectID chosen selector");
		$I->click(['xpath' => "//div[@id='$chosenSelectID']/a/div/b"]);
		$I->comment("I select $option");
		$I->click(['xpath' => "//div[@id='$chosenSelectID']//li[text()='$option']"]);
		$I->wait(1); // Gives time to chosen to close
	}
	/**
	 * Function to Verify the Tabs on a Joomla! screen
	 *
	 * @param  Array  $expectedTabs   Expected Tabs on the Page
	 * @param  Mixed  $tabsLocator    Locator for the Tabs in Edit View
	 *
	 * @return void
	 */
	public function verifyAvailableTabs($expectedTabs, $tabsLocator = ['xpath' => "//ul[@id='myTabTabs']/li/a"])
	{
		$I = $this;
		$actualArrayOfTabs = $I->grabMultiple($tabsLocator);
		$I->comment("Fetch the current list of Tabs in the edit view which is: " . implode(", ", $actualArrayOfTabs));
		$url = $I->grabFromCurrentUrl();
		$I->assertEquals($expectedTabs, $actualArrayOfTabs, "Tab Labels do not match on edit view of" . $url);
		$I->comment('Verify the Tabs');
	}
	/**
	 * Function to Logout from Administrator Panel in Joomla!
	 *
	 * @return void
	 */
	public function doAdministratorLogout()
	{
		$I = $this;
		$I->click(['xpath' => "//ul[@class='nav nav-user pull-right']//li//a[@class='dropdown-toggle']"]);
		$I->comment("I click on Top Right corner toggle to Logout from Admin");
		$I->waitForElement(['xpath' => "//li[@class='dropdown open']/ul[@class='dropdown-menu']//a[text() = 'Logout']"], TIMEOUT);
		$I->click(['xpath' => "//li[@class='dropdown open']/ul[@class='dropdown-menu']//a[text() = 'Logout']"]);
		$I->waitForElement(['id' => 'mod-login-username'], TIMEOUT);
		$I->waitForText('Log in', TIMEOUT, ['xpath' => "//fieldset[@class='loginform']//button"]);

	}

	/**
	 * Selects an option in a Joomla Radio Field based on its label
	 *
	 * @return void
	 */
	public function selectOptionInRadioField($label, $option)
	{
		$I = $this;
		$I->comment("Trying to select the $option from the $label");
		$label = $webDriver->findField(['xpath' => "//label[contains(normalize-space(string(.)), '" . $label . "')]"]);
		$radioId = $label->getAttribute('for');
		$I->click(['xpath' => "//fieldset[@id='$radioId']/label[contains(normalize-space(string(.)), '$option')]"]);
	}

    /**
     * Prepare menu item creation by choosing menu and adding title.
     *
     * @param   string  $title  The Menu Item title
     * @param   string  $menu   The menu in which menu item will be created.
     *
     * @since   3.7
     *
     * @return  void
     */
	public function prepareMenuItemCreate($title, $menu = 'Main Menu')
    {
        $I = $this;

        $I->amOnPage('administrator/index.php?option=com_menus&view=menus');
        $I->waitForText('Menus', '60', ['css' => 'H1']);
        $I->checkForPhpNoticesOrWarnings();

        $I->click(['link' =>  $menu]);
        $I->waitForText('Menus: Items', '60', ['css' => 'H1']);
        $I->checkForPhpNoticesOrWarnings();

        $I->click("New");
        $I->waitForText('Menus: New Item', '60', ['css' => 'h1']);
        $I->checkForPhpNoticesOrWarnings();
        $I->fillField(['id' => 'jform_title'], $title);
    }

    /**
     * Creates a menu item with the Joomla menu manager, only working for menu items without additional required fields
     *
     * @param   string  $menuTitle     The menu item title
     * @param   string  $menuCategory  The category of the menu type (for example Weblinks)
     * @param   string  $menuItem      The menu item type / link text (for example List all Web Link Categories)
     * @param   string  $menu          The menu where the item should be created
     */
    public function selectMenuItemType($menuCategory, $menuItem)
    {
        $I = $this;

        $I->comment("Open the menu types iframe");
        $I->click(['link' => "Select"]);
        $I->waitForElement(['id' => 'menuTypeModal'], '60');
        $I->wait(1);
        $I->switchToIFrame("Menu Item Type");

        $I->comment("Open the menu category: $menuCategory");
        // Open the category
        $I->wait(1);
        $I->waitForElement(['link' => $menuCategory], '60');
        $I->click(['link' => $menuCategory]);

        $I->comment("Choose the menu item type: $menuItem");
        $I->wait(1);
        $I->waitForElement(['xpath' => "//a[contains(text()[normalize-space()], '$menuItem')]"], '60');
        $I->click(['xpath' => "//div[@id='collapseTypes']//a[contains(text()[normalize-space()], '$menuItem')]"]);
        $I->comment('I switch back to the main window');
    }
}
