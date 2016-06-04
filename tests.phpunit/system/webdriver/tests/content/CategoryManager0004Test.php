<?php
/**
 * @package     Joomla.Test
 * @subpackage  Webdriver
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

/**
 * This class tests the  Category: Add / Edit  Screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.2
 */
class CategoryManager0004Test extends JoomlaWebdriverTestCase
{
	/**
	 * The page class being tested.
	 *
	 * @var     CategoryManagerPage
	 * @since   3.2
	 */
	protected $categoryManagerPage = null;

	/**
	 * Open the Front End, Category Manager Page
	 *
	 * @since   3.2
	 *
	 * @return void
	 */
	public function setUp()
	{
		$cfg = new SeleniumConfig;
		parent::setUp();
		$this->driver->get($cfg->host . $cfg->path);
	}

	/**
	 * Logout and close test.
	 *
	 * @since   3.2
	 *
	 * @return void
	 */
	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}

	/**
	 * change the state from published to unpublished of the category and verify on the front end
	 *
	 * @return void
	 *
	 * @test
	 */
	public function frontEndCategoryChange_ChangeCategoryState_CategoryStateChanged()
	{
		$cfg = new SeleniumConfig;
		$homePageUrl = 'index.php';
		$this->driver->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$arrayTitles = $this->siteHomePage->getArticleTitles();
		$this->assertTrue(in_array('Professionals', $arrayTitles), 'Article Must be present');
		$this->assertTrue(in_array('Beginners', $arrayTitles), 'Article Must be present');
		$this->assertTrue(in_array('Upgraders', $arrayTitles), 'Article Must be present');
		$this->assertTrue(in_array('Joomla!', $arrayTitles), 'Article Must be present');
		$cpPage = $this->doAdminLogin();
		$this->categoryManagerPage = $cpPage->clickMenu('Category Manager', 'CategoryManagerPage');
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->categoryManagerPage->changeCategoryState('Sample Data-Articles', 'unpublished');
		$this->driver->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$arrayTitles = $this->siteHomePage->getArticleTitles();
		$this->assertFalse(in_array('Professionals', $arrayTitles), 'Article Must not be present');
		$this->assertFalse(in_array('Beginners', $arrayTitles), 'Article Must not be present');
		$this->assertFalse(in_array('Upgraders', $arrayTitles), 'Article Must not be present');
		$this->assertFalse(in_array('Joomla!', $arrayTitles), 'Article Must not be present');
		$this->doSiteLogin();
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$this->assertTrue($this->siteHomePage->isUnpublishedPresent('Beginners'), 'Article Must be Unpublished');
		$this->assertTrue($this->siteHomePage->isEditPresent(), 'Articles Must be Editable');
		$this->doSiteLogout();

		$cpPage = $this->doAdminLogin();
		$this->categoryManagerPage = $cpPage->clickMenu('Category Manager', 'CategoryManagerPage');
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->categoryManagerPage->setFilter('Select Status', 'Unpublished');
		$this->categoryManagerPage->changeCategoryState('Sample Data-Articles', 'published');
		$this->driver->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$arrayTitles = $this->siteHomePage->getArticleTitles();
		$this->assertTrue(in_array('Professionals', $arrayTitles), 'Article Must be present');
		$this->assertTrue(in_array('Beginners', $arrayTitles), 'Article Must be present');
		$this->assertTrue(in_array('Upgraders', $arrayTitles), 'Article Must be present');
		$this->assertTrue(in_array('Joomla!', $arrayTitles), 'Article Must be present');
	}

	/**
	 * change the state from published to archived of the category and verify on the front end
	 *
	 * @return void
	 *
	 * @test
	 */
	public function frontEndCategoryState_StateChangedToArchived_StateChanged()
	{
		$cfg = new SeleniumConfig;
		$homePageUrl = 'index.php';
		$this->driver->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$arrayTitles = $this->siteHomePage->getArticleTitles();
		$this->assertTrue(in_array('Professionals', $arrayTitles), 'Article Must be present');
		$this->assertTrue(in_array('Beginners', $arrayTitles), 'Article Must be present');
		$this->assertTrue(in_array('Upgraders', $arrayTitles), 'Article Must be present');
		$this->assertTrue(in_array('Joomla!', $arrayTitles), 'Article Must be present');
		$cpPage = $this->doAdminLogin();
		$this->categoryManagerPage = $cpPage->clickMenu('Category Manager', 'CategoryManagerPage');
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->categoryManagerPage->changeCategoryState('Sample Data-Articles', 'archived');
		$this->driver->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$arrayTitles = $this->siteHomePage->getArticleTitles();
		$this->assertFalse(in_array('Professionals', $arrayTitles), 'Article Must not be present');
		$this->assertFalse(in_array('Beginners', $arrayTitles), 'Article Must not be present');
		$this->assertFalse(in_array('Upgraders', $arrayTitles), 'Article Must not be present');
		$this->assertFalse(in_array('Joomla!', $arrayTitles), 'Article Must not be present');

		/*frontend after login*/
		$this->doSiteLogin();
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$this->assertTrue($this->siteHomePage->isUnpublishedPresent('Beginners'), 'Article Must be Unpublished');
		$this->assertTrue($this->siteHomePage->isEditPresent(), 'Article Must be Editable');
		$this->doSiteLogout();

		/*set back the category to published state*/
		$cpPage = $this->doAdminLogin();
		$this->categoryManagerPage = $cpPage->clickMenu('Category Manager', 'CategoryManagerPage');
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->categoryManagerPage->setFilter('Select Status', 'Archived');
		$this->categoryManagerPage->changeCategoryState('Sample Data-Articles', 'published');
		$articleManager = 'administrator/index.php?option=com_content';
		$this->driver->get($cfg->host . $cfg->path . $articleManager);
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->articleManagerPage->changeArticleState('Beginners', 'unpublished');
		$this->driver->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$arrayTitles = $this->siteHomePage->getArticleTitles();
		$this->assertTrue(in_array('Professionals', $arrayTitles), 'Article Must be present');
		$this->assertFalse(in_array('Beginners', $arrayTitles), 'Article Must not be present');
		$this->assertTrue(in_array('Upgraders', $arrayTitles), 'Article Must be present');
		$this->assertTrue(in_array('Joomla!', $arrayTitles), 'Article Must be present');

		$this->doSiteLogin();
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$this->assertTrue($this->siteHomePage->isUnpublishedPresent('Beginners'), 'Article Must be Unpublished');
		$this->assertTrue($this->siteHomePage->isEditPresent(), 'Article Must be Editable');
		$this->doSiteLogout();

		/*set back Beginners Article to Published State*/
		$cpPage = $this->doAdminLogin();
		$articleManager = 'administrator/index.php?option=com_content';
		$this->driver->get($cfg->host . $cfg->path . $articleManager);
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->articleManagerPage->changeFilter('Select Status', 'Unpublished');
		$this->articleManagerPage->changeArticleState('Beginners', 'Published');
		$this->driver->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$arrayTitles = $this->siteHomePage->getArticleTitles();
		$this->assertTrue(in_array('Beginners', $arrayTitles), 'Article Must be present');
	}

	/**
	 * change article state to trash and then verify on the front end
	 *
	 * @return void
	 *
	 * @test
	 */
	public function frontEndArticleState_ChangeArticleStateToTrashed_ArticleStateChanged()
	{
		$cfg = new SeleniumConfig;
		$homePageUrl = 'index.php';
		$articleManager = 'administrator/index.php?option=com_content';
		$this->driver->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$arrayTitles = $this->siteHomePage->getArticleTitles();
		$this->assertTrue(in_array('Beginners', $arrayTitles), 'Article Must be present');

		$cpPage = $this->doAdminLogin();
		$this->driver->get($cfg->host . $cfg->path . $articleManager);
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->articleManagerPage->changeArticleState('Beginners', 'Trashed');
		$this->driver->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$arrayTitles = $this->siteHomePage->getArticleTitles();
		$this->assertFalse(in_array('Beginners', $arrayTitles), 'Article Must not be present');

		$this->doSiteLogin();
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$this->assertTrue($this->siteHomePage->isEditPresent(), 'Articles Must be Editable');
		$arrayTitles = $this->siteHomePage->getArticleTitles();
		$this->assertFalse(in_array('Beginners', $arrayTitles), 'Article Must not be present');
		$this->doSiteLogout();

		/*set back Beginners Article to Published State*/
		$cpPage = $this->doAdminLogin();
		$articleManager = 'administrator/index.php?option=com_content';
		$this->driver->get($cfg->host . $cfg->path . $articleManager);
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->articleManagerPage->changeFilter('Select Status', 'Trashed');
		$this->articleManagerPage->changeArticleState('Beginners', 'Published');
		$this->driver->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$arrayTitles = $this->siteHomePage->getArticleTitles();
		$this->assertTrue(in_array('Beginners', $arrayTitles), 'Article Must be present');
	}

	/**
	 * change the state from published to unpublished of a category and verify on the front end
	 *
	 * @return void
	 *
	 * @test
	 */
	public function frontEndCategoryState_ChangeCategoryState_FrontEndCategoryChanged()
	{
		$cfg = new SeleniumConfig;
		$categoryUrl = 'index.php/using-joomla/extensions/components/content-component/article-categories';
		$this->driver->get($cfg->host . $cfg->path . $categoryUrl);
		$this->siteCategoryPage = $this->getPageObject('SiteContentCategoriesPage');
		$arrayTitles = $this->siteCategoryPage->getCategoryTitles();
		$this->assertTrue(in_array('Park Site', $arrayTitles), 'Category Must be present');
		$this->assertTrue(in_array('Joomla!', $arrayTitles), 'Category Must be present');
		$this->assertTrue(in_array('Fruit Shop Site', $arrayTitles), 'Category Must be present');
		$cpPage = $this->doAdminLogin();
		$this->categoryManagerPage = $cpPage->clickMenu('Category Manager', 'CategoryManagerPage');
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->categoryManagerPage->changeCategoryState('Park Site', 'unpublished');

		$this->driver->get($cfg->host . $cfg->path . $categoryUrl);
		$this->siteCategoryPage = $this->getPageObject('SiteContentCategoriesPage');
		$arrayTitles = $this->siteCategoryPage->getCategoryTitles();
		$this->assertFalse(in_array('Park Site', $arrayTitles), 'Category Must not be present');
		$this->assertTrue(in_array('Joomla!', $arrayTitles), 'Category Must be present');
		$this->assertTrue(in_array('Fruit Shop Site', $arrayTitles), 'Category Must be present');

		$this->doSiteLogin();
		$this->driver->get($cfg->host . $cfg->path . $categoryUrl);
		$this->siteCategoryPage = $this->getPageObject('SiteContentCategoriesPage');
		$arrayTitles = $this->siteCategoryPage->getCategoryTitles();
		$this->assertFalse(in_array('Park Site', $arrayTitles), 'Category Must not be present');
		$this->assertTrue(in_array('Joomla!', $arrayTitles), 'Category Must be present');
		$this->assertTrue(in_array('Fruit Shop Site', $arrayTitles), 'Category Must be present');
		$this->doSiteLogout();

		$cpPage = $this->doAdminLogin();
		$this->categoryManagerPage = $cpPage->clickMenu('Category Manager', 'CategoryManagerPage');
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->categoryManagerPage->setFilter('Select Status', 'Unpublished');
		$this->categoryManagerPage->changeCategoryState('Park Site', 'published');
		$this->driver->get($cfg->host . $cfg->path . $categoryUrl);
		$this->siteCategoryPage = $this->getPageObject('SiteContentCategoriesPage');
		$arrayTitles = $this->siteCategoryPage->getCategoryTitles();
		$this->assertTrue(in_array('Park Site', $arrayTitles), 'Category Must be present');
		$this->assertTrue(in_array('Joomla!', $arrayTitles), 'Category Must be present');
		$this->assertTrue(in_array('Fruit Shop Site', $arrayTitles), 'Category Must be present');
	}

	/**
	 * change the state from published to archived of a category and verify on the front end
	 *
	 * @return void
	 *
	 * @test
	 */
	public function frontEndCategoryStateChange_ChangeCategoryToArchive_StateChanged()
	{
		$cfg = new SeleniumConfig;
		$categoryUrl = 'index.php/using-joomla/extensions/components/content-component/article-categories';
		$this->driver->get($cfg->host . $cfg->path . $categoryUrl);
		$this->siteCategoryPage = $this->getPageObject('SiteContentCategoriesPage');
		$arrayTitles = $this->siteCategoryPage->getCategoryTitles();
		$this->assertTrue(in_array('Park Site', $arrayTitles), 'Category Must be present');
		$this->assertTrue(in_array('Joomla!', $arrayTitles), 'Category Must be present');
		$this->assertTrue(in_array('Fruit Shop Site', $arrayTitles), 'Category Must be present');

		/*Change Category State to Archive*/
		$cpPage = $this->doAdminLogin();
		$this->categoryManagerPage = $cpPage->clickMenu('Category Manager', 'CategoryManagerPage');
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->categoryManagerPage->changeCategoryState('Park Site', 'archived');
		$this->driver->get($cfg->host . $cfg->path . $categoryUrl);
		$this->siteCategoryPage = $this->getPageObject('SiteContentCategoriesPage');
		$arrayTitles = $this->siteCategoryPage->getCategoryTitles();
		$this->assertFalse(in_array('Park Site', $arrayTitles), 'Category Must not be present');
		$this->assertTrue(in_array('Joomla!', $arrayTitles), 'Category Must be present');
		$this->assertTrue(in_array('Fruit Shop Site', $arrayTitles), 'Category Must be present');

		$this->doSiteLogin();
		$this->driver->get($cfg->host . $cfg->path . $categoryUrl);
		$this->siteCategoryPage = $this->getPageObject('SiteContentCategoriesPage');
		$arrayTitles = $this->siteCategoryPage->getCategoryTitles();
		$this->assertFalse(in_array('Park Site', $arrayTitles), 'Category Must not be present');
		$this->assertTrue(in_array('Joomla!', $arrayTitles), 'Category Must be present');
		$this->assertTrue(in_array('Fruit Shop Site', $arrayTitles), 'Category Must be present');
		$this->doSiteLogout();

		/*change the category State Back to Published*/
		$cpPage = $this->doAdminLogin();
		$this->categoryManagerPage = $cpPage->clickMenu('Category Manager', 'CategoryManagerPage');
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->categoryManagerPage->setFilter('Select Status', 'Archived');
		$this->categoryManagerPage->changeCategoryState('Park Site', 'published');
		$this->driver->get($cfg->host . $cfg->path . $categoryUrl);
		$this->siteCategoryPage = $this->getPageObject('SiteContentCategoriesPage');
		$arrayTitles = $this->siteCategoryPage->getCategoryTitles();
		$this->assertTrue(in_array('Park Site', $arrayTitles), 'Category Must be present');
		$this->assertTrue(in_array('Joomla!', $arrayTitles), 'Category Must be present');
		$this->assertTrue(in_array('Fruit Shop Site', $arrayTitles), 'Category Must be present');
	}
}
