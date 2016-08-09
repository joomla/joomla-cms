<?php
/**
 * @package     Joomla.Test
 * @subpackage  AcceptanceTester.Step
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;

use Page\Acceptance\Administrator\AdminPage;
use Page\Acceptance\Administrator\ArticleManagerPage;

/**
 * Acceptance Step object class contains suits for Content Manager.
 *
 * @package  Step\Acceptance\Administrator
 *
 * @since    __DEPLOY_VERSION__
 */
class Content extends \AcceptanceTester
{
	/**
	 * Method to click toolbar button new from article manager listing page.
	 *
	 * @Given   There is a add content link
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function thereIsAAddContentLink()
	{
		$I = $this;

		$I->amOnPage(ArticleManagerPage::$url);
		$I->clickToolbarButton('New');
	}

	/**
	 * Method to create new article
	 *
	 * @param   string  $title    The article title
	 * @param   string  $content  The article content
	 *
	 * @When    I create new content with field title as :title and content as a :content
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iCreateNewContent($title, $content)
	{
		$I = $this;

		$I->fillField(ArticleManagerPage::$title, $title);
		$I->click(ArticleManagerPage::$toggleEditor);
		$I->fillField(ArticleManagerPage::$content, $content);
	}

	/**
	 * Method to save an article
	 *
	 * @When I save an article
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iSaveAnArticle()
	{
		$I = $this;

		$I->clickToolbarButton('Save');
	}

	/**
	 * Method to confirm message appear
	 *
	 * @param   string  $message  The message to be confirm
	 *
	 * @Then I should see the :message message
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iShouldSeeTheMessage($message)
	{
		$I = $this;

		$I->waitForText($message, TIMEOUT, AdminPage::$systemMessageContainer);
		$I->see($message, AdminPage::$systemMessageContainer);
	}

	/**
	 * Method to search and select article
	 *
	 * @param   string  $title  The title of the article which should be searched
	 *
	 * @Given I search and select content article with title :title
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
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
	 * Method to featured an article
	 *
	 * @When I featured the article
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iFeatureTheContentWithTitle()
	{
		$I = $this;

		$I->clickToolbarButton('featured');
	}

	/**
	 * Method to select an article
	 *
	 * @param   string  $title  The article title which should be select
	 *
	 * @Given I select the content article with title :arg1
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
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
	 * Method to set access level
	 *
	 * @param   string  $accessLevel  The name of access level which needs to be set
	 *
	 * @When I set access level as a :accessLevel
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iSetAccessLevelAsA($accessLevel)
	{
		$I = $this;

		$I->selectOptionInChosenById('jform_access', $accessLevel);
	}

	/**
	 * Method to save an article
	 *
	 * @When I save the article
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iSaveTheArticle()
	{
		$I = $this;

		$I->clickToolbarButton('Save & Close');
	}

	/**
	 * Method to get an article
	 *
	 * @param   string  $title  The title of the article.
	 *
	 * @Given I have article with name :title
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
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
	 * Method to unpublish an article
	 *
	 * @When I unpublish the article
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iUnpublish()
	{
		$I = $this;

		$I->clickToolbarButton('unpublish');
	}

	/**
	 * Confirm the article unpublish message
	 *
	 * @param   string  $title    The webpage title
	 * @param   string  $message  The unpublish successful message
	 *
	 * @Then I wait for title :title and see the unpublish message :message
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iSeeArticleUnpublishMessage($title, $message)
	{
		$I = $this;

		$I->waitForPageTitle($title);
		$I->see($message, AdminPage::$systemMessageContainer);
	}

	/**
	 * Method to trash an article
	 *
	 * @param   string  $title  The article title
	 *
	 * @Given I have :title content article which needs to be Trash
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
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
	 * Click button to trash an article
	 *
	 * @When I Trash the article
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iTrashTheArticleWithName()
	{
		$I = $this;

		$I->clickToolbarButton('trash');
	}

	/**
	 * Assure the article is trashed.
	 *
	 * @param   string  $title    The webpage title.
	 * @param   string  $message  The article trashed successful message
	 *
	 * @Then I wait for the title :title and see article trash message :message
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iSeeArticleTrashMessage($title, $message)
	{
		$I = $this;

		$I->waitForPageTitle($title);
		$I->see($message, AdminPage::$systemMessageContainer);
	}
}
