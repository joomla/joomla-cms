<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * checks that all menu choices are shown in back end
 */

require_once 'SeleniumJoomlaTestCase.php';

/**
 *
 */
class Security0001Test extends SeleniumJoomlaTestCase
{
	function testXSS()
	{
		print("Start testXSS" . "\n");
		$this->setUp();
		$this->gotoSite();
		echo "testing some XSS URLs\n";
		$link = $this->cfg->path . 'index.php?option=com_contact&view=category&catid=26&id=36&Itemid=-1"><script>alert(/XSS/)</script>';
		$this->open($link);
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isElementPresent("//form/script[contains(text(), 'alert')]"));
		$link = $this->cfg->path . 'index.php?option=com_contact&view=featured&id=16&Itemid=452&whateverehere="><script>alert(/XSS/)</script>';
		$this->open($link);
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isElementPresent("//form/script[contains(text(), 'alert')]"));
		$link = $this->cfg->path . 'index.php?option=com_content&view=category&id=19&Itemid=260&whateverehere="><script>alert(/XSS/)</script>';
		$this->open($link);
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isElementPresent("//form/script[contains(text(), 'alert')]"));
		$link = $this->cfg->path . 'index.php?option=com_newsfeeds&view=category&id=17&Itemid=253&limit=10&filter_order_Dir=ASC&filter_order=ordering&whateverehere="><script>alert(/XSS/)</script>';
		$this->open($link);
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isElementPresent("//form/script[contains(text(), 'alert')]"));
		$link = $this->cfg->path . 'index.php?option=com_weblinks&view=category&id=32&Itemid=274&whateverehere="><script>alert(/XSS/)</script>';
		$this->open($link);
		$this->waitForPageToLoad("30000");
		$link = $this->cfg->path . 'index.php/using-joomla/extensions/components/content-component/article-category-blog?dce01%2522%253e%253cscript%253ealert%25281%2529%253c%252fscript%253e865402a94b=1';
		$this->open($link);
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//link[contains(@href,\"<script>\")]"));
		print("Finish testXSS" . "\n");
		$this->deleteAllVisibleCookies();
	}

	function testPathDisclosure() {
		print("Start testPathDisclosure" . "\n");
		$this->setUp();
		$this->gotoSite();

		$link = $this->cfg->path . 'libraries/phpmailer/language/phpmailer.lang-joomla.php';
		$this->open($link);
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isTextPresent("Fatal error"));
		$link = $this->cfg->path . 'index.php/using-joomla/extensions/components/content-component/article-category-list?start=-10';
		$this->open($link, "true");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isElementPresent("//div[@class='error']"));
		print("Finish testPathDisclosure" . "\n");
		$this->deleteAllVisibleCookies();

	}

}
