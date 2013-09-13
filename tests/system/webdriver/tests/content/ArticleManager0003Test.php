<?php
/**
 * @package     Joomla.Test
 * @subpackage  Webdriver
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

/**
 * This class tests the  Article: Front End and Add/Edit Screens.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.2
 */
class ArticleManager0003Test extends JoomlaWebdriverTestCase
{
	/**
	 * The page class being tested.
	 *
	 * @var     ArticleManagerPage
	 * @since   3.2
	 */
	 protected $articleManagerPage = null;

	/**
	 * Login to back end and navigate to menu Tags.
	 *
	 * @since   3.2
	 */
	 public function setUp()
	 {
		 $cfg = new SeleniumConfig();
		 parent::setUp();
		 $this->driver->get($cfg->host.$cfg->path);

	 }

	/**
	 * Logout and close test.
	 *
	 * @since   3.0
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
		 $cfg = new SeleniumConfig();
		 $archivedArticlePath = 'index.php/using-joomla/extensions/components/content-component/archived-articles';
		 $url = $cfg->host . $cfg->path . $archivedArticlePath;
		 $this->driver->get($url);
		 $this->archivedArticlePage = $this->getPageObject('SiteArchivedArticlesPage', true, $url);
		 $arrayTitles = $this->archivedArticlePage->getArticleTitles();
		 $this->assertFalse(in_array('Beginners', $arrayTitles),'Beginners article must not be present');
		 $articleManager='administrator/index.php?option=com_content';
		 $this->doAdminLogin();
		 $this->driver->get($cfg->host . $cfg->path . $articleManager);
		 $this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		 $this->articleManagerPage->changeArticleState('Beginners', 'archived');
		 $this->driver->get($url);
		 $this->archivedArticlePage = $this->getPageObject('SiteArchivedArticlesPage', true, $url);
		 $arrayTitles = $this->archivedArticlePage->getArticleTitles();
		 $this->assertTrue(in_array('Beginners', $arrayTitles), 'Beginners article must be present');
		 $this->driver->get($cfg->host . $cfg->path . $articleManager);
		 $this->articleManagerPage = $this->getPageObject('ArticleManagerPage');

		 $this->articleManagerPage->changeFilter('Select Status', 'Archived');
		 $this->articleManagerPage->changeArticleState('Beginners', 'published');
		 $this->driver->get($url);
		 $this->archivedArticlePage = $this->getPageObject('SiteArchivedArticlesPage', true, $url);
		 $arrayTitles = $this->archivedArticlePage->getArticleTitles();
		 $this->assertFalse(in_array('Beginners',$arrayTitles),'Beginners article must not be present');
	 }
	 
	 /**
	 *@test
	 */
	 public function frontEndSingleArticleState_ChangeArticleState_ArticleStateChanged()
	 {
		 $cfg = new SeleniumConfig();
		 $urlHome = $this->cfg->host.$this->cfg->path.'index.php';
		 $homePage = $this->getPageObject('SiteContentFeaturedPage', true, $urlHome);
		 $urlGettingStarted = $this->cfg->host.$this->cfg->path.'index.php/getting-started';
		 $this->driver->get($urlGettingStarted);
		 $gettingStartedPage = $this->getPageObject('SiteSingleArticlePage', true, $urlGettingStarted);		 	 
		 $this->assertTrue($gettingStartedPage->isArticlePresent('Getting Started'), 'Getting Started Must be Present');
		 
		 $this->doAdminLogin();
		 $articleManager='administrator/index.php?option=com_content';
		 $this->driver->get($cfg->host.$cfg->path.$articleManager);
		 $this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		 $this->articleManagerPage->changeArticleState('Getting Started', 'unpublished');
		 $this->driver->get($urlGettingStarted);
		 $gettingStartedPage = $this->getPageObject('SiteSingleArticlePage', true, $urlGettingStarted);
		 $this->assertFalse($gettingStartedPage->isArticlePresent('Getting Started'), 'Getting Started Must not be Present');
		 $this->doSiteLogin();
		 $this->driver->get($urlGettingStarted);
		 $gettingStartedPage = $this->getPageObject('SiteSingleArticlePage', true, $urlGettingStarted);
		 $this->assertTrue($gettingStartedPage->isEditPresent(),'Edit Icons Must be Present');
		 $this->doSiteLogout();
		 
		 $cpPage = $this->doAdminLogin();
		 $this->articleManagerPage = $cpPage->clickMenu('Article Manager', 'ArticleManagerPage');
		 $this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		 $this->articleManagerPage->changeFilter('Status','Unpublished');
		 $this->articleManagerPage->changeArticleState('Getting Started', 'published');
		 $this->driver->get($urlGettingStarted);
		 $gettingStartedPage = $this->getPageObject('SiteSingleArticlePage', true, $urlGettingStarted);
		 $this->assertTrue($gettingStartedPage->isArticlePresent('Getting Started'), 'Getting Started Must be Present');
		 
		 $this->driver->get($cfg->host.$cfg->path.$articleManager);
		 $this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		 $this->articleManagerPage->changeFilter('Status','Published');	 
		 $this->articleManagerPage->changeArticleState('Getting Started', 'archived');
		 $this->driver->get($urlGettingStarted);
		 $gettingStartedPage = $this->getPageObject('SiteSingleArticlePage', true, $urlGettingStarted);
		 $this->assertTrue($gettingStartedPage->isArticlePresent('Getting Started'), 'Getting Started Must be Present');
		 $this->driver->get($cfg->host.$cfg->path.$articleManager);
		 $this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		 $this->articleManagerPage->changeFilter('Status','Archived');
		 $this->articleManagerPage->changeArticleState('Getting Started', 'published');
	 }
	 
	/**
	 * @test
	 */
	public function batchAccessLevel_ChangeBatchAccessLevel_AccessLevelChanged()
	{
		$newAccessLevel = 'Special';
		$cpPage = $this->doAdminLogin();
		$this->articleManagerPage = $cpPage->clickMenu('Article Manager', 'ArticleManagerPage');
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$actualAccessLevel = $this->articleManagerPage->getAccessLevel('Archive Module');
		$this->assertEquals($actualAccessLevel,'Public', 'Initial Access Level Must be Public');
		$this->articleManagerPage->changeAccessLevel('Archive Module', $newAccessLevel);
		$currentAccessLevel = $this->articleManagerPage->getAccessLevel('Archive Module');
		$this->assertEquals($newAccessLevel,$currentAccessLevel, 'Current Access Level Should have changed to Special');
		$this->articleManagerPage->changeAccessLevel('Archive Module', 'Public');
		$currentAccessLevel = $this->articleManagerPage->getAccessLevel('Archive Module');
		$this->assertEquals('Public',$currentAccessLevel, 'Current Access Level Should have changed back to public');
	}
	 
	/**
	 * @test
	 */ 
	public function batchCopy_BatchCopyArticle_ArticleCopied()
	{
		$cpPage = $this->doAdminLogin();
		$this->articleManagerPage = $cpPage->clickMenu('Article Manager', 'ArticleManagerPage');
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$originalCategory = 'Content Modules';
		
		// Category to which we will copy the article using Batch Process
		$newCategory = 'Park Site';
		$value = $this->articleManagerPage->getCategoryName('Archive Module');
		$this->assertEquals($value,'Category: Content Modules','Initially Archive Module Must belong to Content Modules Category');
		$this->articleManagerPage->doBatchAction('Archive Module', 'Park', $newCategory, 'copy'); 
		$this->articleManagerPage->changeCategoryFilter($newCategory, 'Park');
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$value = $this->articleManagerPage->getCategoryName('Archive Module');
		$this->assertEquals($value, 'Category: Park Site','The Article Must have got copied into the new Category');
		$this->articleManagerPage->trashAndDelete('Archive Module'); 
		$this->articleManagerPage->changeCategoryFilter();
		
		//Now we will copy the article into same category using Batch Process
		$this->articleManagerPage->doBatchAction('Archive Module', 'Content', $originalCategory, 'copy');
		$value = $this->articleManagerPage->getCategoryName('Archive Module (2)');
		$this->assertEquals($value, 'Category: Content Modules','The Article Must have got copied into the same original Category');
		$this->articleManagerPage->trashAndDelete('Archive Module (2)');  
		$this->articleManagerPage->changeCategoryFilter();
	}
	  
	/**
	 * @test
	 */
	public function batchMove_BatchMoveArticle_ArticleMoved()
	{
		$cpPage = $this->doAdminLogin();
		$this->articleManagerPage = $cpPage->clickMenu('Article Manager', 'ArticleManagerPage');
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$originalCategory = 'Content Modules';
		
		// Category to which we will move the article using Batch Process
		$newCategory = 'Languages';
		$value = $this->articleManagerPage->getCategoryName('Archive Module');
		$this->assertEquals($value, 'Category: Content Modules','Initially Archive Module Must belong to Content Modules Category');
		$this->articleManagerPage->doBatchAction('Archive Module', 'lang', $newCategory, 'move'); 
		$this->articleManagerPage->changeCategoryFilter($newCategory, 'lang');
		$value = $this->articleManagerPage->getCategoryName('Archive Module');
		$this->assertEquals($value, 'Category: Languages','The Article Must have got moved into the new Category');
		
		// Move Article Back to Original Category
		$this->articleManagerPage->doBatchAction('Archive Module', 'content', $originalCategory, 'move');
		$this->articleManagerPage->changeCategoryFilter($originalCategory, 'content');
		$value = $this->articleManagerPage->getCategoryName('Archive Module');
		$this->assertEquals($value, 'Category: Content Modules', 'The Article Must have got moved into the Original Category');
	}
	
	/** 
	 * @test
	 */ 
        public function frontEndEditArticle_ChangeArticleText_ArticleTextChanged()
	{
		$cfg = new SeleniumConfig();
		$checkingText = '<p>Testing Edit</p>';
		$validationText = 'Testing Edit';
		$this->doAdminLogin();
		$globalConfigUrl = 'administrator/index.php?option=com_config';
		$url = $this->cfg->host.$this->cfg->path . $globalConfigUrl;
		$this->driver->get($url);
		$gc= $this->getPageObject('GlobalConfigurationPage', true, $url);
		$gc->changeEditorMode();
		$articleManager='administrator/index.php?option=com_content';
		$this->driver->get($cfg->host.$cfg->path . $articleManager);
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->articleManagerPage->addArticle('Testing');
		$this->articleManagerPage->searchFor('Testing');
		$this->articleManagerPage->checkAll();
		$this->articleManagerPage->clickButton('toolbar-featured');
		$this->doSiteLogin();
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$this->assertTrue($this->siteHomePage->isEditPresent(), 'Edit Icons Must be Present');
		
		//Edit the Article
		$this->siteHomePage->clickEditArticle('Testing');
		$this->articleEditPage = $this->getPageObject('SiteContentEditPage');
		$this->articleEditPage->editArticle($checkingText);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$articleTexts = $this->siteHomePage->getArticleText();
		$this->assertTrue(in_array($validationText, $articleTexts), 'Text Must be Present');
		
		//Deleting the Article
		$cpPage = $this->doAdminLogin();
		$this->gcPage = $cpPage->clickMenu('Global Configuration', 'GlobalConfigurationPage');
		$this->gcPage->changeEditorMode('TINY');
		$this->driver->get($cfg->host.$cfg->path . $articleManager);
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->articleManagerPage->trashAndDelete('Testing');
        }
}

