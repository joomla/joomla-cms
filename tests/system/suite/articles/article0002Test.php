<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * Tests editing an article on the front end
 */

require_once 'SeleniumJoomlaTestCase.php';

class Article0002 extends SeleniumJoomlaTestCase
{
	function testEditArticle()
	{
//		Use no editor until tinymce issue fixes
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->setEditor('No Editor');
		$this->doAdminLogout();

		$this->gotoSite();
		$this->doFrontEndLogin();
		echo "Edit article in front end\n";
	    $this->click("//img[@alt='Edit']");
	    $this->waitForPageToLoad("30000");
	    $salt = mt_rand();
	    $testText="Test text $salt";

//		Use no editor until tinymce issue fixes
// 	    $this->setTinyText($testText);
	    $this->type("id=jform_articletext", "<p>$testText</p>");

	    echo "Save article\n";
	    $this->click("//button[@type='button']");
	    $this->waitForPageToLoad("30000");
		try {
	        $this->assertEquals("Article successfully saved", $this->getText("//dl[@id='system-message']/dd/ul/li"));
	    } catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
	    try {
	        $this->assertTrue($this->isTextPresent($testText));
	    } catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }

	    echo "Check that new text shows on page\n";
	    $this->assertEquals($testText, $this->getText("//div[@class='items-leading']/div[@class='leading-0']//p"));

	    echo "Open again for editing in front end\n";
	    $this->click("//img[@alt='Edit']");
	    $this->waitForPageToLoad("30000");
	    $text="<p>Congratulations! You have a Joomla! site! Joomla! makes your site easy to build a website " .
	    		"just the way you want it and keep it simple to update and maintain.</p> " .
				"<p>Joomla! is a flexible and powerful platform, whether you are building a small site " .
				"for yourself or a huge site with hundreds of thousands of visitors. ".
				"Joomla is open source, which means you can make it work just the way you want it to.</p>";

	    //		Use no editor until tinymce issue fixes
	    // 	    $this->setTinyText($text);
	    $this->type("id=jform_articletext", $text);

	    $this->click("//button[@type='button']");
	    $this->waitForPageToLoad("30000");
	    echo "Check for success message\n";
	    try {
	        $this->assertEquals("Article successfully saved", $this->getText("//dl[@id='system-message']/dd/ul/li"));
	    } catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
		try {
	        $this->assertFalse($this->isTextPresent($testText));
	    } catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $this->getTraceFiles($e));
	    }
	    echo "Check that new text shows on page\n";
	    $this->assertTrue($this->isElementPresent("//div[@class='items-leading']/div[@class='leading-0']//p[contains(text(), 'Congratulations!')]"));

	    $this->doFrontEndLogout();
	    $this->gotoAdmin();
	    $this->doAdminLogin();
	    $this->setEditor('Tiny');
	    $this->doAdminLogout();

	    echo "Finishing testEditArticle\n";
		$this->deleteAllVisibleCookies();
	}

	function testEditArticleModals()
	{
		$this->gotoSite();
		$this->doFrontEndLogin();
		echo "Edit Upgraders article in front end\n";
		$this->click("//h2/a[contains(text(),'Upgraders')]/../../ul/li/span/a");
		$this->waitForPageToLoad("30000");

		echo "Insert an article link and check that link is added to article.\n";
		$this->click("link=Article");
		$this->waitforElement("//iframe[contains(@src, '&view=articles&layout=modal')]");
		$this->click("link=Archive Module");
		$this->waitforElement("//fieldset/legend[contains(text(),'Metadata')]");

		$this->assertTrue($this->isElementPresent("//a[contains(text(),'Archive Module')]"));

		echo "Click Article button and close modal\n";
		$this->click("link=Article");
		$this->waitforElement("//iframe[contains(@src, '&view=articles&layout=modal')]");
		$this->click("id=sbox-btn-close");
		sleep(3);
		$this->waitforElement("//fieldset/legend[contains(text(),'Metadata')]");
		echo "Check that we are still editing the article.\n";
		$this->assertTrue($this->isElementPresent("//fieldset/legend[contains(text(),'Metadata')]"));

		echo "Click Image button and close modal\n";
		$this->click("link=Image");
		$this->waitforElement("//td/label[contains(text(),'Image URL')]");
		$this->click("//button[@type='button' and @type='button' and @type='button' and @onclick='window.parent.SqueezeBox.close();']");
		$this->waitforElement("//fieldset/legend[contains(text(),'Metadata')]");
		echo "Check that we are still editing the article.\n";
		$this->assertTrue($this->isElementPresent("//fieldset/legend[contains(text(),'Metadata')]"));

		echo "Cancel article edit\n";
		$this->click("//button[contains(@onclick,'article.cancel')]");
		$this->waitForPageToLoad("30000");

		$this->doFrontEndLogout();

		echo "Finishing testEditArticleModals\n";
		$this->deleteAllVisibleCookies();
	}

}

