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
 * This class tests the  Article: Add / Edit  Screen.
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
		 parent::setUp();
		 $cpPage = $this->doAdminLogin();
		 $this->articleManagerPage = $cpPage->clickMenu('Article Manager', 'ArticleManagerPage');
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
	 public function frontEndArchivedArticle_ChangeToArchived_ArticleArchived()
	 {
		 $cfg = new SeleniumConfig();
		 $archivedArticlePath = 'index.php/using-joomla/extensions/components/content-component/archived-articles';
		 $this->driver->get($cfg->host.$cfg->path.$archivedArticlePath);
		 $arrayElement = $this->driver->findElements(By::xPath("//h2//a[contains(text(), 'Beginners')]"));
		 $this->assertEquals(count($arrayElement),0, 'Beginners Must Not be Present');
		 $article_manager='administrator/index.php?option=com_content';
		 $this->driver->get($cfg->host.$cfg->path.$article_manager);
		 $this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		 $this->articleManagerPage->changeArticleState('Beginners', 'archived');
		 $this->driver->get($cfg->host.$cfg->path.$archivedArticlePath);
		 $arrayElement = $this->driver->findElements(By::xPath("//h2//a[contains(text(), 'Beginners')]"));
		 $this->assertTrue(count($arrayElement)>0, 'Beginners Must be Present');
		 $this->driver->get($cfg->host.$cfg->path.$article_manager);
		 $this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		 $this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']/a"))->click();
		 $this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']//ul[@class='chzn-results']/li[contains(.,'Archived')]"))->click();
		 $this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Beginners')]"),10);
		 $this->articleManagerPage->changeArticleState('Beginners', 'published');
		 $this->driver->get($cfg->host.$cfg->path.$archivedArticlePath);
		 $arrayElement = $this->driver->findElements(By::xPath("//h2//a[contains(text(), 'Beginners')]"));
		 $this->assertEquals(count($arrayElement),0, 'Beginners Must Not be Present');
	 }
	
        /**
	 * @test
	 */ 
	 public function frontEndSingleArticleState_ChangeArticleState_ArticleStateChanged()
	 {
		 $cfg = new SeleniumConfig();
		 $this->driver->get($cfg->host.$cfg->path);
		 $this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);
		 $this->driver->findElement(By::xPath("//a[contains(text(),'Getting Started')]"))->click();
		 $this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(), 'Getting Started')]"),10);
		 $arrayElement = $this->driver->findElements(By::xPath("//a[contains(text(), 'Getting Started')]"));
		 $this->assertTrue(count($arrayElement)>0, 'Getting Started Must be Present');
		 $article_manager='administrator/index.php?option=com_content';
		 $this->driver->get($cfg->host.$cfg->path.$article_manager);
		 $this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		 $this->articleManagerPage->changeArticleState('Getting Started', 'unpublished');
		 $this->driver->get($cfg->host.$cfg->path);
		 $this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);
		 $this->driver->findElement(By::xPath("//a[contains(text(),'Getting Started')]"))->click();
		 $arrayElement = $this->driver->findElements(By::xPath("//a[contains(text(),'Getting Started')]"));
		 $this->assertEquals(count($arrayElement),0, 'Getting Started Must Not be Present');
		 $arrayElement = $this->driver->findElements(By::xPath("//h1[contains(text(),'The requested page cannot be found')]"));
		 $this->assertTrue(count($arrayElement)>0, 'Getting Started Must Not be Present');
		 $this->driver->get($cfg->host.$cfg->path);
		 $this->doFrontEndLogin();
		 $this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);
		 $this->driver->findElement(By::xPath("//a[contains(text(),'Getting Started')]"))->click();
		 $this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(), 'Edit')]"),10);
		 $arrayElement=$this->driver->findElements(By::xPath("//a[contains(text(), 'Edit')]"));
		 $this->assertTrue(count($arrayElement)>0,'Edit Icons Must be Present');
		 $this->doFrontEndLogout();
		 
		 $cpPage = $this->doAdminLogin();
		 $this->articleManagerPage = $cpPage->clickMenu('Article Manager', 'ArticleManagerPage');
		 $this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		 $this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']/a"))->click();
		 $this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']//ul[@class='chzn-results']/li[contains(.,'Unpublished')]"))->click();
		 $this->articleManagerPage->changeArticleState('Getting Started', 'published');
		 $this->driver->get($cfg->host.$cfg->path);
		 $this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Getting Started')]"),10);
		 $this->driver->findElement(By::xPath("//a[contains(text(),'Getting Started')]"))->click();
		 $this->driver->waitForElementUntilIsPresent(By::xPath("//h2[contains(text(),'Getting Started')]"),10);
		 $arrayElement = $this->driver->findElements(By::xPath("//h2[contains(text(),'Getting Started')]"));
		 $this->assertTrue(count($arrayElement)>0, 'Getting Started Must be Present');
		 $this->driver->get($cfg->host.$cfg->path.$article_manager);
		 $this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		 $this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']/a"))->click();
		 $this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']//ul[@class='chzn-results']/li[contains(.,'Published')]"))->click();
		 $this->articleManagerPage->changeArticleState('Getting Started', 'archived');
		 $this->driver->get($cfg->host.$cfg->path);
		 $this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);
		 $this->driver->findElement(By::xPath("//a[contains(text(),'Getting Started')]"))->click();
		 $this->driver->waitForElementUntilIsPresent(By::xPath("//h2[contains(text(),'Getting Started')]"),10);
		 $arrayElement = $this->driver->findElements(By::xPath("//h2[contains(text(),'Getting Started')]"));
		 $this->assertTrue(count($arrayElement)>0, 'Getting Started Must be Present');
		 $this->driver->get($cfg->host.$cfg->path.$article_manager);
		 $this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		 $this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']/a"))->click();
		 $this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']//ul[@class='chzn-results']/li[contains(.,'Archived')]"))->click();
		 $this->articleManagerPage->changeArticleState('Getting Started', 'published');
	 }
	
	
	/**
	 * @test
	 */
	 public function batchAccessLevel_ChangeBatchAccessLevel_AccessLevelChanged()
	 {
		 $newAccessLevel = 'Special';
		 $this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		 $actualAccessLevel = $this->articleManagerPage->getAccessLevel('Archive Module');
		 $this->assertEquals($actualAccessLevel,'Public', 'Initial Access Level Must be Public');
		 $this->articleManagerPage->changeAccessLevel('Archive Module', $newAccessLevel);
		 $currentAccessLevel = $this->articleManagerPage->getAccessLevel('Archive Module');
		 $this->assertEquals($newAccessLevel,$currentAccessLevel, 'Current Access Level Should have changed to Special');
		 $this->articleManagerPage->changeAccessLevel('Archive Module', 'Public');$currentAccessLevel = $this->articleManagerPage->getAccessLevel('Archive Module');
		 $this->assertEquals('Public',$currentAccessLevel, 'Current Access Level Should have changed back to public');
	 }
	 
	 /**
	  * @test
	  */
	  public function batchCopy_BatchCopyArticle_ArticleCopied()
	  {
		  $this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		  $originalCategory = 'Content Modules';
		  //Category to which we will copy the artcile using Batch Process
		  $newCategory = 'Park Site';
		  $value = $this->driver->findElement(By::xPath("//tbody/tr[2]/td[4]/div/div[@class='small']"))->getText();
		  $this->assertEquals($value,'Category: Content Modules','Initially Archive Module Must belong to Content Modules Category');
		  $this->driver->findElement(By::xPath("//input[@id='cb1']"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='toolbar-batch']/button"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='batch_category_id_chzn']/a"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='batch_category_id_chzn']/div/div/input"))->sendKeys('Park');
		  $this->driver->findElement(By::xPath("//div[@id='batch_category_id_chzn']//ul[@class='chzn-results']/li[contains(.,'".$newCategory."')]"))->click();
		  $this->driver->findElement(By::xPath("//input[@id='batch[move_copy]c']"))->click();
		  $this->driver->findElement(By::xPath("//button[contains(text(),'Process')]"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='filter_category_id_chzn']/a"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='filter_category_id_chzn']/div/div/input"))->sendKeys('Park');
		  $this->driver->findElement(By::xPath("//div[@id='filter_category_id_chzn']//ul[@class='chzn-results']/li[contains(.,'".$newCategory."')]"))->click();
		  $this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Archive Module')]"),10);
		  $value = $this->driver->findElement(By::xPath("//tbody/tr[1]/td[4]/div/div[@class='small']"))->getText();
		  $this->assertEquals($value,'Category: Park Site','The Article Must have got copied into the new Category');
		  $this->driver->findElement(By::xPath("//input[@id='cb0']"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='toolbar-trash']/button"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']/a"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']//ul[@class='chzn-results']/li[contains(.,'Trashed')]"))->click();
		  $this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Archive Module')]"),10);
		  $this->driver->findElement(By::xPath("//input[@id='cb0']"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='toolbar-delete']/button"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']/a"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']//ul[@class='chzn-results']/li[contains(.,'Select Status')]"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='filter_category_id_chzn']/a"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='filter_category_id_chzn']/div/div/input"))->sendKeys('Select');
		  $this->driver->findElement(By::xPath("//div[@id='filter_category_id_chzn']//ul[@class='chzn-results']/li[contains(.,'Select Category')]"))->click();
		  $this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Archive Module')]"),10);	
		  //Now we will copy the article into same category using Batch Process		  
		  $this->driver->findElement(By::xPath("//input[@id='cb1']"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='toolbar-batch']/button"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='batch_category_id_chzn']/a"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='batch_category_id_chzn']/div/div/input"))->sendKeys('Content');
		  $this->driver->findElement(By::xPath("//div[@id='batch_category_id_chzn']//ul[@class='chzn-results']/li[contains(.,'".$originalCategory."')]"))->click();
		  $this->driver->findElement(By::xPath("//input[@id='batch[move_copy]c']"))->click();
		  $this->driver->findElement(By::xPath("//button[contains(text(),'Process')]"))->click();
		  $value = $this->driver->findElement(By::xPath("//tbody/tr[3]/td[4]/div/div[@class='small']"))->getText();
		  $this->assertEquals($value,'Category: Content Modules','The Article Must have got copied into the same Old Category');
		  $this->driver->findElement(By::xPath("//input[@id='cb2']"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='toolbar-trash']/button"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']/a"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']//ul[@class='chzn-results']/li[contains(.,'Trashed')]"))->click();
		  $this->driver->findElement(By::xPath("//input[@id='cb0']"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='toolbar-delete']/button"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']/a"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']//ul[@class='chzn-results']/li[contains(.,'Select Status')]"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='filter_category_id_chzn']/a"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='filter_category_id_chzn']/div/div/input"))->sendKeys('Select');
		  $this->driver->findElement(By::xPath("//div[@id='filter_category_id_chzn']//ul[@class='chzn-results']/li[contains(.,'Select Category')]"))->click();
	  }
	  
	  /**
	  * @test
	  */
	  public function batchMove_BatchMoveArticle_ArticleMoved()
	  {
		  $this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		  $originalCategory = 'Content Modules';
		  //Category to which we will copy the artcile using Batch Process
		  $newCategory = 'Languages';
		  $value = $this->driver->findElement(By::xPath("//tbody/tr[2]/td[4]/div/div[@class='small']"))->getText();
		  $this->assertEquals($value,'Category: Content Modules','Initially Archive Module Must belong to Content Modules Category');
		  $this->driver->findElement(By::xPath("//input[@id='cb1']"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='toolbar-batch']/button"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='batch_category_id_chzn']/a"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='batch_category_id_chzn']/div/div/input"))->sendKeys('lang');
		  $this->driver->findElement(By::xPath("//div[@id='batch_category_id_chzn']//ul[@class='chzn-results']/li[contains(.,'".$newCategory."')]"))->click();
		  $this->driver->findElement(By::xPath("//input[@id='batch[move_copy]m']"))->click();
		  $this->driver->findElement(By::xPath("//button[contains(text(),'Process')]"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='filter_category_id_chzn']/a"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='filter_category_id_chzn']/div/div/input"))->sendKeys('lang');
		  $this->driver->findElement(By::xPath("//div[@id='filter_category_id_chzn']//ul[@class='chzn-results']/li[contains(.,'".$newCategory."')]"))->click();
		  $this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Archive Module')]"),10);
		  $value = $this->driver->findElement(By::xPath("//tbody/tr[1]/td[4]/div/div[@class='small']"))->getText();
		  $this->assertEquals($value,'Category: Languages','The Article Must have got moved into the new Category');
		  
		  //Move Article Back to Original Category
		  $this->driver->findElement(By::xPath("//input[@id='cb0']"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='toolbar-batch']/button"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='batch_category_id_chzn']/a"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='batch_category_id_chzn']/div/div/input"))->sendKeys('content');
		  $this->driver->findElement(By::xPath("//div[@id='batch_category_id_chzn']//ul[@class='chzn-results']/li[contains(.,'".$originalCategory."')]"))->click();
		  $this->driver->findElement(By::xPath("//input[@id='batch[move_copy]m']"))->click();
		  $this->driver->findElement(By::xPath("//button[contains(text(),'Process')]"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']/a"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='filter_published_chzn']//ul[@class='chzn-results']/li[contains(.,'Select Status')]"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='filter_category_id_chzn']/a"))->click();
		  $this->driver->findElement(By::xPath("//div[@id='filter_category_id_chzn']/div/div/input"))->sendKeys('Select');
		  $this->driver->findElement(By::xPath("//div[@id='filter_category_id_chzn']//ul[@class='chzn-results']/li[contains(.,'Select Category')]"))->click();
		  $value = $this->driver->findElement(By::xPath("//tbody/tr[2]/td[4]/div/div[@class='small']"))->getText();
		  $this->assertEquals($value,'Category: Content Modules','The Article Must have got copied into the same Old Category');
	  }
}
