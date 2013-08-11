<?php

require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

class GlobalConfiguration0001Test extends JoomlaWebdriverTestCase
{
	/**
	 *
	 * @var GlobalConfigurationPage
	 */
	protected $gcPage = null; // Global configuration page

	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->gcPage = $cpPage->clickMenu('Global Configuration', 'GlobalConfigurationPage');
	}

	public function tearDown()
	{
		$this->gcPage->saveAndClose('ControlPanelPage');
		$this->doAdminLogout();
		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function getTabIds_ScreenLoaded_TabIdsShouldEqualExpected()
	{
		$textArray = $this->gcPage->getTabIds();
		$this->assertEquals($this->gcPage->tabs, $textArray, 'Tab labels should match expected values.');
	}

	/**
	 * @test
	 * Gets the actual input fields from the Control Panel page and checks them against the $inputFields property.
	 */
	public function getAllInputFields_ScreenLoaded_InputFieldsShouldMatchExpected()
	{
		$gc = $this->gcPage;
		$testElements = $gc->getAllInputFields(array('page-site', 'page-system', 'page-server', 'page-permissions'));
		$actualFields = $this->getActualFieldsFromElements($testElements);
		$this->assertEquals($actualFields, $gc->inputFields);
	}
	
	/* 
	 * @test
	 *  
	 */
	public function frontEndEditArticle_ChangeArticleText_ArticleTextChanged()
	{
		$cfg = new SeleniumConfig();
		$checkingText = 'Testing Edit';
		$actualText = '<p>If this is your first Joomla! site or your first web site, you have come to the right place. Joomla will help you get your website up and running quickly and easily.</p><p>Start off using your site by logging in using the administrator account you created when you installed Joomla.</p><hr id="system-readmore"><p>Explore the articles and other resources right here on your site data to learn more about how Joomla works. (When you\'re done reading, you can delete or archive all of this.) You will also probably want to visit the Beginners\' Areas of the <a href="http://docs.joomla.org/Beginners" data-mce-href="http://docs.joomla.org/Beginners">Joomla documentation</a> and <a href="http://forum.joomla.org" data-mce-href="http://forum.joomla.org">support forums</a>.</p><p>You\'ll also want to sign up for the Joomla Security Mailing list and the Announcements mailing list. For inspiration visit the <a href="http://community.joomla.org/showcase/" data-mce-href="http://community.joomla.org/showcase/">Joomla! Site Showcase</a> to see an amazing array of ways people use Joomla to tell their stories on the web.</p><p>The basic Joomla installation will let you get a great site up and running, but when you are ready for more features the power of Joomla is in the creative ways that developers have extended it to do all kinds of things. Visit the <a href="http://extensions.joomla.org/" data-mce-href="http://extensions.joomla.org/">Joomla! Extensions Directory</a> to see thousands of extensions that can do almost anything you could want on a website. Can\'t find what you need? You may want to find a Joomla professional in the <a href="http://resources.joomla.org/" data-mce-href="http://resources.joomla.org/">Joomla! Resource Directory</a>.</p><p>Want to learn more? Consider attending a <a href="http://community.joomla.org/events.html" data-mce-href="http://community.joomla.org/events.html">Joomla! Day</a> or other event or joining a local <a href="http://community.joomla.org/user-groups.html" data-mce-href="http://community.joomla.org/user-groups.html">Joomla! Users Group</a>. Can\'t find one near you? Start one yourself.</p>';
		$validationText = 'If this is your first Joomla! site or your first web site, you have come to the right place. Joomla will help you get your website up and running quickly and easily.';	
		
		$gc= $this->gcPage;
		$this->driver->waitForElementUntilIsPresent(By::xPath("//div[@id='toolbar-save']/button"),10);						
		$gc->changeEditorMode();
		$this->driver->get($cfg->host.$cfg->path);
		$this->doFrontEndLogin();
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Home')]"),10);				
		$arrayElement=$this->driver->findElements(By::xPath("//a[contains(text(), 'Edit')]"));
		$this->assertTrue(count($arrayElement)>0,'Edit Icons Must be Present');
		$d = $this->driver;
		
		//Edit the Article
		$d->findElement(By::xPath("//a/span[contains(@class, 'icon-cog')]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//a/span[contains(@class, 'icon-edit')]"),10);
		$d->findElement(By::xPath("//a/span[contains(@class, 'icon-edit')]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//textarea[@id='jform_articletext']"),10);
		$d->findElement(By::xPath("//textarea[@id='jform_articletext']"))->clear();				
		$d->findElement(By::xPath("//textarea[@id='jform_articletext']"))->sendKeys('<p>'.$checkingText.'</p>');
		$d->findElement(By::xPath("//button[@type='button'][@class='btn btn-primary']"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Beginners')]"),10);		
		$textPresent=$d->findElement(By::xPath("//p[contains(text(),'".$checkingText."')]"))->getText();
		$this->assertEquals($textPresent,$checkingText,'Both Must be Equal');
		
		//Set Back to Previous Value
		$d->findElement(By::xPath("//a/span[contains(@class, 'icon-cog')]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//a/span[contains(@class, 'icon-edit')]"),10);
		$d->findElement(By::xPath("//a/span[contains(@class, 'icon-edit')]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//textarea[@id='jform_articletext']"),10);
		$d->findElement(By::xPath("//textarea[@id='jform_articletext']"))->clear();				
		$d->findElement(By::xPath("//textarea[@id='jform_articletext']"))->sendKeys('<p>'.$actualText.'</p>');
		$d->findElement(By::xPath("//button[@type='button'][@class='btn btn-primary']"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'Beginners')]"),10);
		$textPresent=$d->findElement(By::xPath("//p[contains(text(),'".$validationText."')]"))->getText();
		$this->assertEquals($validationText,$textPresent,'Both Must be Equal');
		
		//Set Back the Editor
		$cpPage = $this->doAdminLogin();
		$this->gcPage = $cpPage->clickMenu('Global Configuration', 'GlobalConfigurationPage');	
		$gc->changeEditorMode('TINY');
		$this->gcPage = $cpPage->clickMenu('Global Configuration', 'GlobalConfigurationPage');	
	}

}
