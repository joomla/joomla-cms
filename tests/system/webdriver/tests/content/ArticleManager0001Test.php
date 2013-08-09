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

	/**
	 * @test
	 */
	public function changeArticleStatus_TestAtFrontEnd()
	{
		$cfg = new SeleniumConfig();
		$this->driver->get($cfg->host.$cfg->path);
		$this->assertTrue($this->driver->findElement(By::xPath("//h2//a[contains(text(), 'Professionals')]"))->isDisplayed(), 'Professionals Must be Present');
		$article_manager='administrator/index.php?option=com_content';
		$this->driver->get($cfg->host.$cfg->path.$article_manager);
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->articleManagerPage->changeArticleState('Professionals', 'unpublished');
		$this->driver->get($cfg->host.$cfg->path);
		$arrayElement = $this->driver->findElements(By::xPath("//h2//a[contains(text(), 'Professionals')]"));
		$this->assertEquals(count($arrayElement),0, 'Professionals Must Not be Present');
		$this->driver->get($cfg->host.$cfg->path.$article_manager);
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->articleManagerPage->changeArticleState('Professionals', 'published');
		$this->driver->get($cfg->host.$cfg->path);
		$this->assertTrue($this->driver->findElement(By::xPath("//h2//a[contains(text(), 'Professionals')]"))->isDisplayed(), 'Professionals Must be Present');
	}

	/**
	 * @test
	 */
	public function articleEditPermission_TestAtFrontEnd()
	{
		$cfg=new SeleniumConfig();
		$this->driver->get($cfg->host.$cfg->path);
		$this->doFrontEndLogin();
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);
		$arrayElement=$this->driver->findElements(By::xPath("//a[contains(text(), 'Edit')]"));
		$this->assertTrue(count($arrayElement)>0,'Edit Icons Must be Present');
		$d = $this->driver;
		$d->findElement(By::xPath("//a[contains(text(),'Sample Sites')]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Sample Sites')]"));
		$d->waitForElementUntilIsPresent(By::xPath("//a[contains(text(), 'Edit')]"));
		$arrayElement=$this->driver->findElements(By::xPath("//a[contains(text(), 'Edit')]"));
		$this->assertTrue(count($arrayElement)>0,'Edit Icons Must be Present');
		$d->findElement(By::xPath("//a[contains(text(), 'Home')]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Login')]"),10);
		$this->doFrontEndLogout();
		$arrayElement=$this->driver->findElements(By::xPath("//a[contains(text(), 'Edit')]"));
		$this->assertEquals(count($arrayElement),0,'Edit Icons Must Not be Present');
		$d->findElement(By::xPath("//a[contains(text(),'Sample Sites')]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Sample Sites')]"),10);
		$arrayElement=$this->driver->findElements(By::xPath("//a[contains(text(), 'Edit')]"));
		$this->assertEquals(count($arrayElement),0,'Edit Icons Must Not be Present');
	}


}
