<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * Does a standard Joomla! installation
 */

require_once 'SeleniumJoomlaTestCase.php';

class DoInstall extends SeleniumJoomlaTestCase
{
	function testDoInstall()
	{
		$this->setUp();
		$cfg = $this->cfg;
		$configFile = $cfg->folder.$cfg->path."configuration.php";

		if (file_exists($configFile)) {
			$this->jPrint ("Delete configuration file\n");
			chmod($configFile, 0777);
			unlink($configFile);
		}
		else {
			$this->jPrint ("No configuration file found\n");
		}

		$this->jPrint("Starting Installation\n");
		$this->jPrint ("Page through screen 1\n");
		$this->open($cfg->path ."/installation/index.php");
		$this->select("id=jform_language", "value=en-GB");
		$this->waitforElement("//a/span[contains(text(), 'English (United Kingdom')]");
		$this->checkNotices();

		$this->type("jform_site_name", $cfg->site_name);
		$this->type("jform_admin_user", $cfg->username);
		$this->type("jform_admin_email", $cfg->admin_email);
		$this->type("jform_admin_password", $cfg->password);
		$this->type("jform_admin_password2", $cfg->password);

		$this->click("//a[@rel=\"next\"]");
		$this->waitforElement("//h3[contains(text(), 'Database Configuration')]");
		$this->checkNotices();

		$this->jPrint ("Enter database information\n");
		$dbtype = (isset($cfg->db_type)) ? strtolower($cfg->db_type) : 'mysqli';
		$this->select("jform_db_type", "value=".$dbtype);
		$this->type("jform_db_host", $cfg->db_host);
		$this->type("jform_db_user", $cfg->db_user);
		$this->type("jform_db_pass", $cfg->db_pass);
		$this->type("jform_db_prefix", $cfg->db_prefix);
		$this->type("jform_db_name", $cfg->db_name);
		$this->click("jform_db_old0");
		$this->click("//a[@rel=\"next\"]");
		$this->waitforElement("//h3[contains(text(), 'Finalisation')]");
		$this->checkNotices();

		// Default is install with sample data
		if ($cfg->sample_data !== false)
		{
			$this->jPrint ("Install sample data and wait for success message\n");
			$this->click("//input[@id='jform_sample_file4']");
		}
		else {
			$this->jPrint ("Install without sample data\n");
		}

		$this->jPrint ("Finish installation\n");
		$this->click("link=Install");
		$this->checkNotices();
		$this->waitforElement("//h3[contains(text(), 'Joomla! is now installed')]");
		$this->checkNotices();

		$this->jPrint ("Login to back end\n");
		$this->gotoAdmin();
		$this->doAdminLogin();

		$this->jPrint ("Check for site menu\n");
		$this->assertEquals($cfg->site_name, $this->getText("link=" . $cfg->site_name));

		$this->jPrint ("Change error level to value from config\n");
		$this->jClick('Global Configuration');
		$this->click("//a[@href='#page-server']");
		$this->select("jform_error_reporting", "value=" . $cfg->errorReporting);
		$this->click("//div[@id='toolbar-save']/button");
		$this->waitForPageToLoad("30000");

		$this->setCache($cfg->cache);

		// Check admin template -- change to hathor if specified in config file
		if (isset($cfg->adminTemplate) && $cfg->adminTemplate == 'hathor') {
			$this->click("link=Template Manager");
			$this->waitForPageToLoad("30000");
			$this->click("link=Hathor - Default");
			$this->waitForPageToLoad("30000");
			$this->click("jform_home1");
			$this->click("//div[@id='toolbar-save']/button");
			$this->waitForPageToLoad("30000");
		}

		$this->doAdminLogout();
		$this->deleteAllVisibleCookies();
	}
}
