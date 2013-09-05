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
}
