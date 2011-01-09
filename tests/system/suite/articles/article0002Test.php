<?php
/**
 * @version		$Id:
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * Tests editing an article on the front end
 */

require_once 'SeleniumJoomlaTestCase.php';

class Article0002 extends SeleniumJoomlaTestCase
{
	function testEditArticle()
	{
		$this->gotoSite();
		$this->doFrontEndLogin();
		echo "Edit article in front end\n";
	    $this->click("//img[@alt='Edit']");
	    $this->waitForPageToLoad("30000");
	    $salt = mt_rand();
	    $testText="Test text $salt";
	    $this->setTinyText($testText);
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
	    $text="Congratulations! You have a Joomla! site! Joomla! makes your site easy to build a website " .
	    		"just the way you want it and keep it simple to update and maintain. " .
				"Joomla! is a flexible and powerful platform, whether you are building a small site " .
				"for yourself or a huge site with hundreds of thousands of visitors. ".
				"Joomla is open source, which means you can make it work just the way you want it to.";
	    $this->setTinyText($text);
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
	    $this->assertEquals($text, $this->getText("//div[@class='items-leading']/div[@class='leading-0']//p"));
	    $this->doFrontEndLogout();

	    echo "Finishing testEditArticle\n";
		$this->deleteAllVisibleCookies();
	}
}

