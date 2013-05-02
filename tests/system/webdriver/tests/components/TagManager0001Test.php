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
	 * @var tagManagerPage String 
	 */
	protected $tagManagerPage = null;
	
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->tagManagerPage = $cpPage->clickMenu('Tags', 'TagManagerPage');
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
		$this->tagManagerPage->clickButton('new');
		$tagEditPage = $this->getPageObject('TagEditPage');
		$tagEditPage->clickButton('cancel');
		$this->tagManagerPage = $this->getPageObject('TagManagerPage');
	}
	
	/**
	 * @test
	 */
	public function addTag_WithGivenFields_TagAdded()
	{
		$salt = rand();
		$tagName = 'Tag' . $salt;
		$this->assertFalse($this->tagManagerPage->getRowNumber($tagName), 'Test Tag should not be present');
		$this->tagManagerPage->addTag($tagName);
		$message = $this->tagManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Tag successfully saved') >= 0, 'Tag save should return success');
		$this->assertEquals(1, $this->tagManagerPage->getRowNumber($tagName), 'Test Tag should be in row 2');
		$this->tagManagerPage->deleteItem($tagName);
		$this->assertFalse($this->tagManagerPage->getRowNumber($tagName), 'Test Tag should not be present');
	}
	
	
}
