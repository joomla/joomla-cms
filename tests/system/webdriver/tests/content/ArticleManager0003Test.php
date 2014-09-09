<?php
/**
 * @package     Joomla.Test
 * @subpackage  Webdriver
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

/**
 * This class tests the Article: Front End and Add/Edit Screens.
 *
 * @package    Joomla.Test
 * @subpackage Webdriver
 * @since      3.2
 */
class ArticleManager0003Test extends JoomlaWebdriverTestCase
{

	/**
	 * The page class being tested.
	 *
	 * @var ArticleManagerPage
	 * @since 3.2
	 */
	protected $articleManagerPage = null;

	/**
	 * Login to back end and navigate to menu Tags.
	 *
	 * @since 3.2
	 */
	public function setUp()
	{
		$cfg = new SeleniumConfig();
		parent::setUp();
		$this->driver->get($cfg->host . $cfg->path);
	}

	/**
	 * Logout and close test.
	 *
	 * @since 3.0
	 */
	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function SiteArchivedArticle_ChangeToArchived_ArticleArchived()
	{
		$salt = rand();
		$newArticle = 'Test Article ' . $salt;
		$cfg = new SeleniumConfig();
		$archivedArticlePath = 'index.php/using-joomla/extensions/components/content-component/archived-articles';
		$url = $cfg->host . $cfg->path . $archivedArticlePath;
		$articleManager = 'administrator/index.php?option=com_content';
		$this->doAdminLogin();
		$this->driver->get($cfg->host . $cfg->path . $articleManager);
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');

		$this->articleManagerPage->addArticle($newArticle, 'Uncategorised');
		$this->articleManagerPage->changeArticleState($newArticle, 'archived');
		$this->driver->get($url);
		$this->archivedArticlePage = $this->getPageObject('SiteArchivedArticlesPage', true, $url);
		$arrayTitles = $this->archivedArticlePage->getArticleTitles();
		$this->assertTrue(in_array($newArticle, $arrayTitles), 'New article must be present');
		$this->driver->get($cfg->host . $cfg->path . $articleManager);
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');

		$this->articleManagerPage->changeArticleState($newArticle, 'published');
		$this->driver->get($url);
		$this->archivedArticlePage = $this->getPageObject('SiteArchivedArticlesPage', true, $url);
		$arrayTitles = $this->archivedArticlePage->getArticleTitles();
		$this->assertFalse(in_array($newArticle, $arrayTitles), 'New article must not be present');

		$this->driver->get($cfg->host . $cfg->path . $articleManager);
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->articleManagerPage->trashAndDelete($newArticle);
	}

	/**
	 * @test
	 */
	public function frontEndSingleArticleState_ChangeArticleState_ArticleStateChanged()
	{
		$salt = rand();
		$articleName = 'Test Article ' . $salt;
		$cfg = new SeleniumConfig();
		$urlHome = $this->cfg->host . $this->cfg->path . 'index.php';
		$homePage = $this->getPageObject('SiteContentFeaturedPage', true, $urlHome);
		$articleUrl = $this->cfg->host . $this->cfg->path . 'index.php/test-article-' . $salt;
		$this->doAdminLogin();
		$articleManager = 'administrator/index.php?option=com_content';
		$this->driver->get($cfg->host . $cfg->path . $articleManager);
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->articleManagerPage->addArticle($articleName, 'Uncategorised', array('text' => '<p>This is a test.</p>'));
		$articleId = $this->articleManagerPage->getFieldValues('ArticleEditPage', $articleName, array('ID'));
		$articleUrl = $this->cfg->host . $this->cfg->path . 'index.php/' . $articleId[0] . '-test-article-' . $salt;

		$this->driver->get($articleUrl);
		$singleArticlePage = $this->getPageObject('SiteSingleArticlePage', true, $articleUrl);
		$this->assertTrue($singleArticlePage->isArticlePresent($articleName), 'Test Article Must be Present');

		$this->doAdminLogin();
		$articleManager = 'administrator/index.php?option=com_content';
		$this->driver->get($cfg->host . $cfg->path . $articleManager);
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->articleManagerPage->changeArticleState($articleName, 'unpublished');
		$this->assertEquals('unpublished', $this->articleManagerPage->getState($articleName), 'Test Article should be unpublished');
		$this->driver->get($articleUrl);
		$singleArticlePage = $this->getPageObject('SiteSingleArticlePage', true, $articleUrl);
		$this->assertFalse($singleArticlePage->isArticlePresent($articleName), 'Test Article Must not be Present');
		$this->doSiteLogin();
		$this->driver->get($articleUrl);
		$singleArticlePage = $this->getPageObject('SiteSingleArticlePage', true, $articleUrl);
		$this->assertTrue($singleArticlePage->isEditPresent(), 'Edit Icons Must be Present');
		$this->doSiteLogout();

		$cpPage = $this->doAdminLogin();
		$this->articleManagerPage = $cpPage->clickMenu('Article Manager', 'ArticleManagerPage');
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');

		$this->articleManagerPage->changeArticleState($articleName, 'published');
		$this->assertEquals('published', $this->articleManagerPage->getState($articleName), 'Test Article should be published');
		$this->driver->get($articleUrl);
		$singleArticlePage = $this->getPageObject('SiteSingleArticlePage', true, $articleUrl);
		$this->assertTrue($singleArticlePage->isArticlePresent($articleName), 'Test Article Must be Present');

		$this->driver->get($cfg->host . $cfg->path . $articleManager);
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->articleManagerPage->changeArticleState($articleName, 'archived');
		$this->assertEquals('archived', $this->articleManagerPage->getState($articleName), 'Test Article should be archived');

		$this->driver->get($articleUrl);
		$singleArticlePage = $this->getPageObject('SiteSingleArticlePage', true, $articleUrl);
		$this->assertTrue($singleArticlePage->isArticlePresent($articleName), 'Test Article Must be Present');
		$this->driver->get($cfg->host . $cfg->path . $articleManager);
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');

		$this->articleManagerPage->trashAndDelete($articleName);
		$this->driver->get($articleUrl);
		$singleArticlePage = $this->getPageObject('SiteSingleArticlePage', true, $articleUrl);
		$this->assertFalse($singleArticlePage->isArticlePresent($articleName), 'Test Article Must not be Present');
	}

	/**
	 * @test
	 */
	public function batchAccessLevel_ChangeBatchAccessLevel_AccessLevelChanged()
	{
		$salt = rand();
		$articleName = 'Test Article ' . $salt;
		$newAccessLevel = 'Special';
		$cpPage = $this->doAdminLogin();
		$this->articleManagerPage = $cpPage->clickMenu('Article Manager', 'ArticleManagerPage');
		$this->articleManagerPage->addArticle($articleName, 'Uncategorised', array('text' => '<p>This is a test.</p>'));

		$this->articleManagerPage->changeAccessLevel($articleName, $newAccessLevel);
		$currentAccessLevel = $this->articleManagerPage->getAccessLevel($articleName);
		$this->assertEquals($newAccessLevel, $currentAccessLevel, 'Current Access Level Should have changed to Special');
		$this->articleManagerPage->changeAccessLevel($articleName, 'Public');
		$currentAccessLevel = $this->articleManagerPage->getAccessLevel($articleName);
		$this->assertEquals('Public', $currentAccessLevel, 'Current Access Level Should have changed back to public');
		$this->articleManagerPage->trashAndDelete($articleName);
	}

	/**
	 * @test
	 */
	public function batchCopy_BatchCopyArticle_ArticleCopied()
	{
		$salt = rand();
		$articleName = 'Test Article ' . $salt;
		$cpPage = $this->doAdminLogin();
		$this->articleManagerPage = $cpPage->clickMenu('Article Manager', 'ArticleManagerPage');
		$originalCategory = 'Uncategorised';
		$this->articleManagerPage->addArticle($articleName, $originalCategory, array('text' => '<p>This is a test.</p>'));

		// Category to which we will copy the article using Batch Process
		$newCategory = 'Park Site';
		$value = $this->articleManagerPage->getCategoryName($articleName);
		$this->assertEquals($value, 'Category: ' . $originalCategory, 'Article should belong to Original Category');
		$this->articleManagerPage->doBatchAction($articleName, 'Park', $newCategory, 'copy');
		$this->articleManagerPage->changeCategoryFilter($newCategory);
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$value = $this->articleManagerPage->getCategoryName($articleName);
		$this->assertEquals($value, 'Category: ' . $newCategory, 'The Article should be copied into the new Category');
		$this->articleManagerPage->trashAndDelete($articleName);
		$this->articleManagerPage->changeCategoryFilter();

		// Now we will copy the article into same category using Batch Process
		$this->articleManagerPage->doBatchAction($articleName, 'Uncat', $originalCategory, 'copy');
		$value = $this->articleManagerPage->getCategoryName($articleName . ' (2)');
		$this->assertEquals($value, 'Category: ' . $originalCategory, 'The Article should be copied into the same original Category');
		$this->articleManagerPage->trashAndDelete($articleName);
		$this->articleManagerPage->changeCategoryFilter();
		$this->articleManagerPage->searchFor($articleName);
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName), 'Test Article should not be present');
	}

	/**
	 * @test
	 */
	public function batchMove_BatchMoveArticle_ArticleMoved()
	{
		$cpPage = $this->doAdminLogin();
		$this->articleManagerPage = $cpPage->clickMenu('Article Manager', 'ArticleManagerPage');
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$originalCategory = 'Uncategorised';
		$salt = rand();
		$articleName = 'Test Article ' . $salt;
		$this->articleManagerPage->addArticle($articleName, $originalCategory, array('text' => '<p>This is a test.</p>'));

		// Category to which we will move the article using Batch Process
		$newCategory = 'Languages';
		$value = $this->articleManagerPage->getCategoryName($articleName);
		$this->assertEquals($value, 'Category: ' . $originalCategory, 'Initially new article should be in Uncategorised Category');
		$this->articleManagerPage->doBatchAction($articleName, 'lang', $newCategory, 'move');
		$this->articleManagerPage->changeCategoryFilter($newCategory);
		$value = $this->articleManagerPage->getCategoryName($articleName);
		$this->assertEquals($value, 'Category: ' . $newCategory, 'The Article Must have got moved into the new Category');
		$this->articleManagerPage->trashAndDelete($articleName);
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName), 'Test Article should not be present');
	}

	/**
	 * @test
	 */
	public function frontEndEditArticle_ChangeArticleText_ArticleTextChanged()
	{
		$cfg = new SeleniumConfig();
		$checkingText = '<p>Testing Edit</p>';
		$validationText = 'Testing Edit';
		$salt = rand();
		$articleName = 'Test Article ' . $salt;
		$this->doAdminLogin();
		$articleManager = 'administrator/index.php?option=com_content';
		$this->driver->get($cfg->host . $cfg->path . $articleManager);
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->articleManagerPage->addArticle($articleName, 'Sample Data-Articles', array('Featured' => 'Yes'));
		$this->doSiteLogin();
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$this->assertTrue($this->siteHomePage->isEditPresent(), 'Edit Icons Must be Present');

		// Edit the Article
		$this->siteHomePage->clickEditArticle($articleName);
		$this->articleEditPage = $this->getPageObject('SiteContentEditPage');
		$this->articleEditPage->editArticle($checkingText);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$articleTexts = $this->siteHomePage->getArticleText();
		$this->assertTrue(in_array($validationText, $articleTexts), 'Text Must be Present');

		// Delete the Article
		$cpPage = $this->doAdminLogin();
		$this->driver->get($cfg->host . $cfg->path . $articleManager);
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->articleManagerPage->trashAndDelete($articleName);
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName), 'Test Article should not be present');
	}
}

