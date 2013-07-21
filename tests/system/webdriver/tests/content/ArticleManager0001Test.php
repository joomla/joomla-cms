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
 * This class tests the  Tags: Add / Edit  Screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class ArticleManager0001Test extends JoomlaWebdriverTestCase
{
  /**
	 * The page class being tested.
	 *
	 * @var     ArticleManagerPage
	 * @since   3.0
	 */
	protected $articleManagerPage = null;

	/**
	 * Login to back end and navigate to menu Tags.
	 *
	 * @since   3.0
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
	public function getAllInputFields_ScreenDisplayed_EqualExpected()
	{
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->articleManagerPage->clickButton('toolbar-new');
		$articleEditPage = $this->getPageObject('ArticleEditPage');
		$testElements = $articleEditPage->getAllInputFields(array('general', 'publishing', 'attrib-basic','editor', 'metadata', 'permissions'));
		$actualFields = array();
		foreach ($testElements as $el)
		{
			$el->labelText = (substr($el->labelText, -2) == ' *') ? substr($el->labelText, 0, -2) : $el->labelText;
			$actualFields[] = array('label' => $el->labelText, 'id' => $el->id, 'type' => $el->tag, 'tab' => $el->tab);
		}
		$this->assertEquals($articleEditPage->inputFields, $actualFields);
		$articleEditPage->clickButton('toolbar-cancel');
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
	}


	/**
	 * @test
	 */
	public function constructor_OpenEditScreen_ArticleEditOpened()
	{
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->articleManagerPage->clickButton('new');
		$articleEditPage = $this->getPageObject('ArticleEditPage');
		$articleEditPage->clickButton('cancel');
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
	}

	/**
	 * @test
	 */
	public function addArticle_WithFieldDefaults_ArticleAdded()
	{
		$salt = rand();
		$articleName = 'ABC' . $salt;
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName), 'Test Article should not be present');
		$this->articleManagerPage->addArticle($articleName);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');
		$this->articleManagerPage->trashAndDelete($articleName);
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName), 'Test Article should not be present');
	}


	/**
	 * @test
	 */
	public function addArticle_WithGivenFields_ArticleAdded()
	{
		$salt = rand();
		$articleName = 'ABC' . $salt;
		$category = 'Joomla!';
		$expected_category = '- - Joomla!';
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName), 'Test Article should not be present');
		$this->articleManagerPage->addArticle($articleName,$category);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');
		$values = $this->articleManagerPage->getFieldValues('ArticleEditPage', $articleName, array('Title', 'Category'));
		$this->assertEquals(array($articleName,$expected_category), $values, 'Actual name, category should match expected');
		$this->articleManagerPage->trashAndDelete($articleName);
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName), 'Test article should not be present');
	}

	/**
	 * @test
	*/
	public function editArticle_ChangeFields_FieldsChanged()
	{
		$salt = rand();
		$articleName = 'ABC' . $salt;
		$category = 'Joomla!';
		$caption = 'Testing';
		$alt_text = 'Alternate Testing';
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName), 'Test Article should not be present');
		$this->articleManagerPage->addArticle($articleName);
		$this->articleManagerPage->editArticle($articleName, array('Category' => $category, 'Alt text' => $alt_text, 'Caption' => $caption));
		$values = $this->articleManagerPage->getFieldValues('ArticleEditPage', $articleName, array('Caption', 'Alt text'));
		$this->assertEquals(array($caption, $alt_text), $values, 'Actual values should match expected');
		$this->articleManagerPage->trashAndDelete($articleName);
	}

	/**
	 * @test
	 */
	public function changeTagState_ChangeEnabledUsingToolbar_EnabledChanged()
	{
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->articleManagerPage->addArticle('ABC_Test');
		$state = $this->articleManagerPage->getState('ABC_Test');
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->articleManagerPage->changeArticleState('ABC_Test', 'unpublished');
		$state = $this->articleManagerPage->getState('ABC_Test');
		$this->assertEquals('unpublished', $state, 'State should be unpublished');
		$this->articleManagerPage->trashAndDelete('ABC_Test');
	}
	
	/*
	 * @test
	 */
	public function testEditArticleModal_FrontEndTest()
	{
		$cfg = new SeleniumConfig();
		$this->driver->get($cfg->host.$cfg->path);
		$this->doFrontEndLogin();
		$d = $this->driver;
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);				
		$d->findElement(By::xPath("//a/span[contains(@class, 'icon-cog')]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//a/span[contains(@class, 'icon-edit')]"),10);
		$d->findElement(By::xPath("//a/span[contains(@class, 'icon-edit')]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//table[@id='jform_articletext_tbl']"),10);
		
		//Article Link Modal Window Testing the Search functionality
		$d->findElement(By::xPath("//a[contains(text(),'Article')]"))->click();
		$el = $d->waitForElementUntilIsPresent(By::xPath("//iframe[contains(@src, '&view=articles&layout=modal')]"),10);
		$el = $d->switchTo()->getFrameByWebElement($el);
		$el->waitForElementUntilIsPresent(By::xPath("//input[@id='filter_search']"),10);
		$el->findElement(By::xPath("//input[@id='filter_search']"))->sendKeys('Archive Module');
		$el->findElement(By::xPath("//a[contains(text(),'Archive Module')]"))->click();
		$d->switchTo()->getDefaultFrame();
		$d->waitForElementUntilIsPresent(By::xPath("//table[@id='jform_articletext_tbl']"),10);		
		$el = $d->waitForElementUntilIsPresent(By::xPath("//iframe[contains(@id, 'jform_articletext_ifr')]"),10);
		$el = $d->switchTo()->getFrameByWebElement($el);	
		$arrayElement=$el->findElements(By::xPath("//a[contains(text(), 'Archive Module')]"));
		$this->assertTrue(count($arrayElement)>0,'The link Must have appeared in the Editor');
		$d->switchTo()->getDefaultFrame();
				
		//Open the Article Link Modal and Close it using the Close button
		$d->findElement(By::xPath("//a[contains(text(),'Article')]"))->click();
		$el = $d->waitForElementUntilIsPresent(By::xPath("//iframe[contains(@src, '&view=articles&layout=modal')]"),10);
		$el = $d->switchTo()->getFrameByWebElement($el);	
		$el->waitForElementUntilIsPresent(By::xPath("//input[@id='filter_search']"),10);
		$d->switchTo()->getDefaultFrame();			
		$d->findElement(By::xPath("//a[@id='sbox-btn-close']"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//table[@id='jform_articletext_tbl']"),10);		
		
		//Open the Image Modal Window and CLose it using the Close Button
		$d->findElement(By::xPath("//a[contains(text(),'Image')]"))->click();		
		$el = $d->waitForElementUntilIsPresent(By::xPath("//iframe[contains(@src, '&view=images')]"),10);
		$el = $d->switchTo()->getFrameByWebElement($el);	
		$el->waitForElementUntilIsPresent(By::xPath("//button[contains(text(),'Insert')]"),10);
		$d->switchTo()->getDefaultFrame();			
		$d->findElement(By::xPath("//a[@id='sbox-btn-close']"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//table[@id='jform_articletext_tbl']"),10);		
		$arrayElement=$this->driver->findElements(By::xPath("//table[@id='jform_articletext_tbl']"));
		$this->assertTrue(count($arrayElement)>0,'We must be still editing the article');
		$d->findElement(By::xPath("//button[contains(@onclick,'article.cancel')]"))->click();
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);				
		$this->doFrontEndLogout();
	}


}
