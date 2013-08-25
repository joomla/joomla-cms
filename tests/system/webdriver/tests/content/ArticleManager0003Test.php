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
}