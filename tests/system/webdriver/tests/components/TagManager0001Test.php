<?php

require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

/**
 * This class tests the Tag Components: Add / Edit Tag User Screens
 * @author Mark
 *
 */
class TagManager0001Test extends JoomlaWebdriverTestCase
{
  /**
	 *
	 * @var TagManagerPage
	 */
	protected $TagManagerPage = null;
	
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->TagManagerPage = $cpPage->clickMenu('Tags', 'TagManagerPage');
	}

	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}
	
	/**
	 * @test
	 */
	public function constructor_OpenEditScreen_TagEditOpened()
	{
		$this->TagManagerPage->clickButton('new');
		$TagEditPage = $this->getPageObject('TagEditPage');
		$TagEditPage->clickButton('cancel');
		$this->TagManagerPage = $this->getPageObject('TagManagerPage');
	}
	
	/**
	 * @test
	 */
	public function addTag_WithGivenFields_TagAdded()
	{
		$salt = rand();
		$TagName = 'Tag' . $salt;
		$this->assertFalse($this->TagManagerPage->getRowNumber($TagName), 'Test Tag should not be present');
		$this->TagManagerPage->addTag($TagName);
		$message = $this->TagManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Tag successfully saved') >= 0, 'Tag save should return success');
		$this->assertEquals(2, $this->TagManagerPage->getRowNumber($TagName), 'Test Tag should be in row 2');
		$this->TagManagerPage->deleteTag($TagName);
		$this->assertFalse($this->TagManagerPage->getRowNumber($TagName), 'Test Tag should not be present');
	}
	
	
}
