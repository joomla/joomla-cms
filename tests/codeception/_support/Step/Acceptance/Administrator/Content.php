<?php
/**
 * @package     Joomla.Test
 * @subpackage  AcceptanceTester.Step
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;

use Page\Acceptance\Administrator\ArticleManagerPage;

/**
 * Acceptance Step object class contains suits for Content Manager.
 *
 * @package  Step\Acceptance\Administrator
 *
 * @since    __DEPLOY_VERSION__
 */
class Content extends Admin
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
		$I->adminPage->clickToolbarButton('New');
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
		$this->articleManagerPage->fillContentCreateForm($title, $content);
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

		$I->adminPage->clickToolbarButton('Save');
	}

	/**
	 * Method to save an article
	 *
	 * @param   string  $article  The article Name
	 *
	 * @Then I should see the article :article is created
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function seeTheArticleIsCreated($article)
	{
		$I = $this;

		$I->articleManagerPage->seeItemIsCreated($article);
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
		$this->articleManagerPage->haveItemUsingSearch($title);
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

		$I->adminPage->clickToolbarButton('featured');
	}

	/**
	 * Method to assure that article is featured
	 *
	 * @Then I should see the article is now featured
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iShouldSeeTheArticleIsFeatured()
	{
		$I = $this;

		$I->seeNumberOfElements(ArticleManagerPage::$seeFeatured, 1);
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

		$I->articleManagerPage->haveItemUsingSearch($title);
		$I->adminPage->clickToolbarButton('edit');
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

		$I->adminPage->selectOptionInChosenById('jform_access', $accessLevel);
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

		$I->adminPage->clickToolbarButton('Save & Close');
	}

	/**
	 * Method to set access level
	 *
	 * @param   string  $accessLevel  The name of access level which needs to be verify
	 *
	 * @Then I should see the :accessLevel as article access level
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iShouldSeeTheArticleAsTheAccessLevel($accessLevel)
	{
		$I = $this;

		$I->see($accessLevel, ArticleManagerPage::$seeAccessLevel);
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
		$this->articleManagerPage->haveItemUsingSearch($title);
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

		$I->adminPage->clickToolbarButton('unpublish');
	}

	/**
	 * Confirm the article is unpublished
	 *
	 * @Then I should see the article is now unpublished
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iShouldSeeTheArticleIsNowUnpublished()
	{
		$I = $this;

		$I->seeNumberOfElements(ArticleManagerPage::$seeUnpublished, 1);
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
		$this->articleManagerPage->haveItemUsingSearch($title);
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

		$I->adminPage->clickToolbarButton('trash');
	}

	/**
	 * Assure the article is trashed.
	 *
	 * @param   string  $article  The article name
	 *
	 * @Then I should see the article :article in trash
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function iShouldSeeTheInTrash($article)
	{
		$I = $this;

		$I->articleManagerPage->seeItemInTrash($article, 'Articles');
	}
}
