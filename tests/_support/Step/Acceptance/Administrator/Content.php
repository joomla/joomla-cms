<?php
namespace Step\Acceptance\Administrator;

use Page\Acceptance\Administrator\AdminPage;
use Page\Acceptance\Administrator\ArticleManagerPage;

class Content extends \AcceptanceTester
{
	/**
	 * @Given There is a add content link
	 */
	public function thereIsAAddContentLink()
	{
		$I = $this;
		$I->amOnPage(ArticleManagerPage::$url);
		$I->clickToolbarButton('New');
	}

	/**
	 * @When I create new content with field title as :title and content as a :content
	 */
	public function iCreateNewContent($title, $content)
	{
		$I = $this;
		$I->fillField(ArticleManagerPage::$articleTitleField, $title);
		$I->click(ArticleManagerPage::$toggleEditor);
		$I->fillField(ArticleManagerPage::$articleContentField, $content);
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
		$I->waitForText($message, TIMEOUT, AdminPage::$systemMessageContainer);
		$I->see($message, AdminPage::$systemMessageContainer);
	}

	/**
	 * @Given I search and select content article with title :arg1
	 */
	public function iSearchAndSelectContentArticleWithTitle($title)
	{
		$I = $this;
		$I->amOnPage(ArticleManagerPage::$url);
		$I->fillField(ArticleManagerPage::$filterSearch, $title);
		$I->click(ArticleManagerPage::$iconSearch);
		$I->checkAllResults();
	}

	/**
	 * @When I featured the article
	 */
	public function iFeatureTheContentWithTitle()
	{
		$I = $this;
		$I->clickToolbarButton('featured');
	}

	/**
	 * @Given I select the content article with title :arg1
	 */
	public function iSelectTheContentArticleWithTitle($title)
	{
		$I = $this;
		$I->amOnPage(ArticleManagerPage::$url);
		$I->fillField(ArticleManagerPage::$filterSearch, $title);
		$I->click(ArticleManagerPage::$iconSearch);
		$I->checkAllResults();
		$I->clickToolbarButton('edit');
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
	 * @Given I have article with name :title
	 */
	public function iHaveArticleWithName($title)
	{
		$I = $this;
		$I->amOnPage(ArticleManagerPage::$url);
		$I->fillField(ArticleManagerPage::$filterSearch, $title);
		$I->click(ArticleManagerPage::$iconSearch);
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
	 * @Then I wait for title :title and see the unpublish message :message
	 */
	public function iSeeArticleUnpublishMessage($title, $message)
	{
		$I = $this;
		$I->waitForPageTitle($title);
		$I->see($message, AdminPage::$systemMessageContainer);
	}


	/**
	 * @Given I have :arg1 content article which needs to be Trash
	 */
	public function iHaveContentArticleWhichNeedsToBeTrash($title)
	{
		$I = $this;
		$I->amOnPage(ArticleManagerPage::$url);
		$I->fillField(ArticleManagerPage::$filterSearch, $title);
		$I->click(ArticleManagerPage::$iconSearch);
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
	 * @Then I wait for the title :title and see article trash message :message
	 */
	public function iSeeArticleTrashMessage($title, $message)
	{
		$I = $this;
		$I->waitForPageTitle($title);
		$I->see($message, AdminPage::$systemMessageContainer);
	}
}
