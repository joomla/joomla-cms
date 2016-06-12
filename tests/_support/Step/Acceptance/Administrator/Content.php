<?php
namespace Step\Acceptance\Administrator;

class Content extends \AcceptanceTester
{
	/**
	 * @Given There is a Add Content link
	 */
	public function thereIsAAddContentLink()
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_content&view=articles');
		$I->clickToolbarButton('New');
	}

	/**
	 * @When I fill mandatory fields for creating article
	 */
	public function iFillMandatoryFieldsForCreatingArticle(\Behat\Gherkin\Node\TableNode $fields)
	{

		$I = $this;
		// iterate over all rows
		foreach ($fields->getRows() as $index => $row) {
			if ($index === 0) { // first row to define fields
				$keys = $row;
				continue;
			}
			else
			{
				if ($row[0] == "title")
				{
					$I->fillField(['id' => 'jform_title'], $row[1]);
				}
				if ($row[0] == "content")
				{
					$I->click('Toggle editor');
					$I->fillField(['id' => 'jform_articletext'], $row[1]);
				}
			}
		}
	}

	/**
	 * @When I save an article
	 */
	public function iSaveAnArticle()
	{
		$I = $this;
		$I->clickToolbarButton('Save');
	}

	/**
	 * @Then I should see the :arg1 message
	 */
	public function iShouldSeeTheMessage($message)
	{
		$I = $this;
		$I->waitForPageTitle('Articles');
		$I->see($message, ['id' => 'system-message-container']);
	}

	/**
	 * @Given I search and select content article with title :arg1
	 */
	public function iSearchAndSelectContentArticleWithTitle($title)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_content&view=articles');
		$I->fillField(['id' => 'filter_search'], $title);
		$I->click('.icon-search');
		$I->checkAllResults();
	}

	/**
	 * @When I featured the article
	 */
	public function iFeatureTheContentWithTitle()
	{
		$I = $this;
		$I->click(['xpath' => "//div[@id='toolbar-featured']//button"]);
	}

	/**
	 * @Then I save and see the :arg1 message
	 */
	public function iSaveAndSeeTheMessage($message)
	{
		$I = $this;
		$I->waitForPageTitle('Articles');
		$I->see($message, ['id' => 'system-message-container']);
	}

	/**
	 * @Given I select the content article with title :arg1
	 */
	public function iSelectTheContentArticleWithTitle($title)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_content&view=articles');
		$I->fillField(['id' => 'filter_search'], $title);
		$I->click('.icon-search');
		$I->checkAllResults();
		$I->click(['xpath' => "//div[@id='toolbar-edit']/button"]);
	}

	/**
	 * @Given I set access level as a :arg1
	 */
	public function iSetAccessLevelAsA($accessLevel)
	{
		$I = $this;
		$I->selectOptionInChosenById('jform_access', $accessLevel);
	}

	/**
	 * @When I save the article
	 */
	public function iSaveTheArticle()
	{
		$I = $this;
		$I->clickToolbarButton('Save & Close');
	}

	/**
	 * @Given I have article with name :arg1
	 */
	public function iHaveArticleWithName($title)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_content&view=articles');
		$I->fillField(['id' => 'filter_search'], $title);
		$I->click('.icon-search');
		$I->checkAllResults();
	}

	/**
	 * @When I unpublish the article
	 */
	public function iUnpublish()
	{
		$I = $this;
		$I->clickToolbarButton('unpublish');
	}
	/**
	 * @Then I see article unpublish message :arg1
	 */
	public function iSeeArticleUnpublishMessage($message)
	{
		$I = $this;
		$I->waitForPageTitle('Articles');
		$I->see($message, ['id' => 'system-message-container']);
	}


	/**
	 * @Given I have :arg1 content article which needs to be Trash
	 */
	public function iHaveContentArticleWhichNeedsToBeTrash($title)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_content&view=articles');
		$I->fillField(['id' => 'filter_search'], $title);
		$I->click('.icon-search');
		$I->checkAllResults();
	}

	/**
	 * @When I Trash the article
	 */
	public function iTrashTheArticleWithName()
	{
		$I = $this;
		$I->clickToolbarButton('trash');
	}

	/**
	 * @Then I see article trash message :arg1
	 */
	public function iSeeArticleTrashMessage($message)
	{
		$I = $this;
		$I->waitForPageTitle('Articles');
		$I->see($message, ['id' => 'system-message-container']);
	}
}