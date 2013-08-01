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
	 * Login to back end and navigate to menu Tags.
	 *
	 * @since   3.2
	 */
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->categoryManagerPage = $cpPage->clickMenu('Category Manager', 'CategoryManagerPage');
	}

	/**
	 * Logout and close test.
	 *
	 * @since   3.2
	 */
	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}
	
	/**
	 * @test
	 */
	public function frontEndCategoryChange_ChangeCategoryState_CategoryStateChanged()
	{
		
    		$cfg = new SeleniumConfig();
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');			
		$this->categoryManagerPage->changeCategoryState('Sample Data-Articles','unpublished');
		$this->driver->get($cfg->host.$cfg->path);
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);
		$arrayElement = $this->driver->findElements(By::xPath("//h2//a[contains(text(), 'Professionals')]"));
		$this->assertEquals(count($arrayElement),0, 'Professionals Must Not be Present');					
		$arrayElement = $this->driver->findElements(By::xPath("//h2//a[contains(text(), 'Beginners')]"));
		$this->assertEquals(count($arrayElement),0, 'Beginners Must Not be Present');					
		$arrayElement = $this->driver->findElements(By::xPath("//h2//a[contains(text(), 'Upgraders')]"));
		$this->assertEquals(count($arrayElement),0, 'Upgraders Must Not be Present');					
		$arrayElement = $this->driver->findElements(By::xPath("//h2//a[contains(text(), 'Joomla!')]"));
		$this->assertEquals(count($arrayElement),0, 'Joomla Must Not be Present');					
		$this->doFrontEndLogin();
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);
		$arrayElement = $this->driver->findElements(By::xPath("//div[@class='system-unpublished']/h2[contains(., 'Beginners')]"));
		$this->assertTrue(count($arrayElement)>0, 'Article Must be Unpublished and Editable');					
		$arrayElement=$this->driver->findElements(By::xPath("//a[contains(text(), 'Edit')]"));
		$this->assertTrue(count($arrayElement)>0,'Edit Icons Must be Present');
		$this->doFrontEndLogout();
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);
		
		//change the category state to Archived and validate the change in frontend
		$cpPage = $this->doAdminLogin();
		$this->categoryManagerPage = $cpPage->clickMenu('Category Manager', 'CategoryManagerPage');
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']/a"))->click();
		$this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']//ul[@class='chzn-results']/li[contains(.,'Unpublished')]"))->click();					
		$this->categoryManagerPage->changeCategoryState('Sample Data-Articles','archived');
		$this->driver->get($cfg->host.$cfg->path);
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);
		$arrayElement = $this->driver->findElements(By::xPath("//h2//a[contains(text(), 'Professionals')]"));
		$this->assertEquals(count($arrayElement),0, 'Professionals Must Not be Present');					
		$arrayElement = $this->driver->findElements(By::xPath("//h2//a[contains(text(), 'Beginners')]"));
		$this->assertEquals(count($arrayElement),0, 'Beginners Must Not be Present');					
		$arrayElement = $this->driver->findElements(By::xPath("//h2//a[contains(text(), 'Upgraders')]"));
		$this->assertEquals(count($arrayElement),0, 'Upgraders Must Not be Present');					
		$arrayElement = $this->driver->findElements(By::xPath("//h2//a[contains(text(), 'Joomla!')]"));
		$this->assertEquals(count($arrayElement),0, 'Joomla Must Not be Present');					
		
		//frontend after login		
		$this->doFrontEndLogin();
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);
		$arrayElement = $this->driver->findElements(By::xPath("//div[@class='system-unpublished']/h2[contains(., 'Beginners')]"));
		$this->assertTrue(count($arrayElement)>0, 'Article Must be Unpublished and Editable');					
		$arrayElement=$this->driver->findElements(By::xPath("//a[contains(text(), 'Edit')]"));
		$this->assertTrue(count($arrayElement)>0,'Edit Icons Must be Present');
		$this->doFrontEndLogout();
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);
		
		//set back the category to published state
		$cpPage = $this->doAdminLogin();
		$this->categoryManagerPage = $cpPage->clickMenu('Category Manager', 'CategoryManagerPage');
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');			
		$this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']/a"))->click();
		$this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']//ul[@class='chzn-results']/li[contains(.,'Archived')]"))->click();				
		$this->categoryManagerPage->changeCategoryState('Sample Data-Articles','published');
		$article_manager='administrator/index.php?option=com_content';
		$this->driver->get($cfg->host.$cfg->path.$article_manager);
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->articleManagerPage->changeArticleState('Beginners', 'unpublished');
		$this->driver->get($cfg->host.$cfg->path);
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);
		$arrayElement = $this->driver->findElements(By::xPath("//h2//a[contains(text(), 'Beginners')]"));
		$this->assertEquals(count($arrayElement),0, 'Beginners Must Not be Present');
		$arrayElement=$this->driver->findElements(By::xPath("//h2//a[contains(text(), 'Professionals')]"));
		$this->assertTrue(count($arrayElement)>0,'Professionals Must be Present');
		$arrayElement=$this->driver->findElements(By::xPath("//h2//a[contains(text(), 'Joomla!')]"));
		$this->assertTrue(count($arrayElement)>0,'Joomla! Must be Present');
		$this->doFrontEndLogin();
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);
		$arrayElement = $this->driver->findElements(By::xPath("//div[@class='system-unpublished']/h2[contains(., 'Beginners')]"));
		$this->assertTrue(count($arrayElement)>0, 'Article Must be Unpublished and Editable');					
		$arrayElement=$this->driver->findElements(By::xPath("//a[contains(text(), 'Edit')]"));
		$this->assertTrue(count($arrayElement)>0,'Edit Icons Must be Present');
		$this->doFrontEndLogout();
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);
		
		//set Beginners Article State as Trashed 
		$cpPage = $this->doAdminLogin();
		$this->categoryManagerPage = $cpPage->clickMenu('Category Manager', 'CategoryManagerPage');
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$article_manager='administrator/index.php?option=com_content';
		$this->driver->get($cfg->host.$cfg->path.$article_manager);
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']/a"))->click();
		$this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']//ul[@class='chzn-results']/li[contains(.,'Unpublished')]"))->click();				
		$this->articleManagerPage->changeArticleState('Beginners', 'Trashed');
		$this->driver->get($cfg->host.$cfg->path);
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);
		$arrayElement = $this->driver->findElements(By::xPath("//h2//a[contains(text(), 'Beginners')]"));
		$this->assertEquals(count($arrayElement),0, 'Beginners Must Not be Present');
		$this->doFrontEndLogin();
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);
		$arrayElement = $this->driver->findElements(By::xPath("//div[@class='blog-featured']//a[contains(text(),'Edit')]"));
		$this->assertTrue(count($arrayElement)>0, 'Editable Link Must be Present and Beginners Must not be Present');					
		$arrayElement = $this->driver->findElements(By::xPath("//h2//a[contains(text(), 'Beginners')]"));
		$this->assertEquals(count($arrayElement),0, 'Beginners Must Not be Present');
		$this->doFrontEndLogout();
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);
		
		//set back Beginners Article to Published State
		$cpPage = $this->doAdminLogin();
		$this->categoryManagerPage = $cpPage->clickMenu('Category Manager', 'CategoryManagerPage');
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$article_manager='administrator/index.php?option=com_content';
		$this->driver->get($cfg->host.$cfg->path.$article_manager);
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']/a"))->click();
		$this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']//ul[@class='chzn-results']/li[contains(.,'Trashed')]"))->click();						
		$this->articleManagerPage->changeArticleState('Beginners', 'Published');
		$this->driver->get($cfg->host.$cfg->path);
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);
		$arrayElement=$this->driver->findElements(By::xPath("//h2//a[contains(text(), 'Beginners')]"));
		$this->assertTrue(count($arrayElement)>0,'Beginners Must be Present');
  	}
     
     
  	/**
  	 * @test
  	 */
  	public function frontEndCategoryState_ChangeCategoryState_FrontEndCategoryChanged()
  	{
		
    		$cfg = new SeleniumConfig();
		$categoryUrl = 'index.php/using-joomla/extensions/components/content-component/article-categories';
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');			
		$this->categoryManagerPage->changeCategoryState('Park Site','unpublished');
		$this->driver->get($cfg->host.$cfg->path);
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);
		$this->driver->get($cfg->host.$cfg->path.$categoryUrl);
		$this->driver->waitForElementUntilIsPresent(By::xPath("//h3//a[contains(text(),'Joomla!')]"),10);				
		$arrayElement = $this->driver->findElements(By::xPath("//h3//a[contains(text(),'Park Site')]"));
		$this->assertEquals(count($arrayElement),0, 'Park Site Must Not be Present');
		$arrayElement = $this->driver->findElements(By::xPath("//h3//a[contains(text(),'Joomla!')]"));
		$this->assertTrue(count($arrayElement)>0, 'Joomla! Must be Present');					
		$arrayElement = $this->driver->findElements(By::xPath("//h3//a[contains(text(),'Fruit Shop Site')]"));
		$this->assertTrue(count($arrayElement)>0, 'Fruit Shop Site Must be Present');					
		$this->doFrontEndLogin();
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);
		$this->driver->get($cfg->host.$cfg->path.$categoryUrl);
		$this->driver->waitForElementUntilIsPresent(By::xPath("//h3//a[contains(text(),'Joomla!')]"),10);				
		$arrayElement = $this->driver->findElements(By::xPath("//h3//a[contains(text(),'Park Site')]"));
		$this->assertEquals(count($arrayElement),0, 'Park Site Must Not be Present');
		$arrayElement = $this->driver->findElements(By::xPath("//h3//a[contains(text(),'Joomla!')]"));
		$this->assertTrue(count($arrayElement)>0, 'Joomla! Must be Present');					
		$arrayElement = $this->driver->findElements(By::xPath("//h3//a[contains(text(),'Fruit Shop Site')]"));
		$this->assertTrue(count($arrayElement)>0, 'Fruit Shop Site Must be Present');					
		$this->doFrontEndLogout();
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);
		
		//change the Category State to Archived and Repeat the Test
		$cpPage = $this->doAdminLogin();
		$this->categoryManagerPage = $cpPage->clickMenu('Category Manager', 'CategoryManagerPage');
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']/a"))->click();
		$this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']//ul[@class='chzn-results']/li[contains(.,'Unpublished')]"))->click();								
		$this->categoryManagerPage->changeCategoryState('Park Site','archived');
		$this->driver->get($cfg->host.$cfg->path);
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);
		$this->driver->get($cfg->host.$cfg->path.$categoryUrl);
		$this->driver->waitForElementUntilIsPresent(By::xPath("//h3//a[contains(text(),'Joomla!')]"),10);				
		$arrayElement = $this->driver->findElements(By::xPath("//h3//a[contains(text(),'Park Site')]"));
		$this->assertEquals(count($arrayElement),0, 'Park Site Must Not be Present');
		$arrayElement = $this->driver->findElements(By::xPath("//h3//a[contains(text(),'Joomla!')]"));
		$this->assertTrue(count($arrayElement)>0, 'Joomla! Must be Present');					
		$arrayElement = $this->driver->findElements(By::xPath("//h3//a[contains(text(),'Fruit Shop Site')]"));
		$this->assertTrue(count($arrayElement)>0, 'Fruit Shop Site Must be Present');					
		$this->doFrontEndLogin();
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);
		$this->driver->get($cfg->host.$cfg->path.$categoryUrl);
		$this->driver->waitForElementUntilIsPresent(By::xPath("//h3//a[contains(text(),'Joomla!')]"),10);				
		$arrayElement = $this->driver->findElements(By::xPath("//h3//a[contains(text(),'Park Site')]"));
		$this->assertEquals(count($arrayElement),0, 'Park Site Must Not be Present');
		$arrayElement = $this->driver->findElements(By::xPath("//h3//a[contains(text(),'Joomla!')]"));
		$this->assertTrue(count($arrayElement)>0, 'Joomla! Must be Present');					
		$arrayElement = $this->driver->findElements(By::xPath("//h3//a[contains(text(),'Fruit Shop Site')]"));
		$this->assertTrue(count($arrayElement)>0, 'Fruit Shop Site Must be Present');					
		$this->doFrontEndLogout();
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);
		
		//change the category State Back to Published
		$cpPage = $this->doAdminLogin();
		$this->categoryManagerPage = $cpPage->clickMenu('Category Manager', 'CategoryManagerPage');
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']/a"))->click();
		$this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']//ul[@class='chzn-results']/li[contains(.,'Archived')]"))->click();								
		$this->categoryManagerPage->changeCategoryState('Park Site','published');
		$this->driver->get($cfg->host.$cfg->path);
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);
		$this->driver->get($cfg->host.$cfg->path.$categoryUrl);
		$this->driver->waitForElementUntilIsPresent(By::xPath("//h3//a[contains(text(),'Joomla!')]"),10);				
		$arrayElement = $this->driver->findElements(By::xPath("//h3//a[contains(text(),'Park Site')]"));
		$this->assertTrue(count($arrayElement)>0, 'Park Site Must be Present Now');					
  	}	
}
