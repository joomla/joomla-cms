<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * Does a standard Joomla! installation
 */

require_once 'SeleniumJoomlaTestCase.php';

class Language0002Test extends SeleniumJoomlaTestCase
{
	function testDoInstall()
	{
		$this->setUp();
		$cfg = $this->cfg;
		$configFile = $cfg->folder.$cfg->path."configuration.php";

		if (file_exists($configFile)) {
			echo "Delete configuration file\n";
			chmod($configFile, 0777);
			unlink($configFile);
		}
		else {
			echo "No configuration file found\n";
		}

		echo("Starting Installation\n");
		echo "Page through screen 1\n";
		$this->open($cfg->path ."/installation/index.php");
		$this->select("id=jform_language", "label=Français (Fr)");

		$this->click("//a[@rel=\"next\"]");
		$this->waitforElement("//h3[contains(text(), 'Vérification de la pré-installation')]");

		echo "Page through screen 2\n";
		$this->click("//a[@rel=\"next\"]");
		$this->waitforElement("//a[contains(text(), 'GNU')]");

		echo "Page through screen 3\n";
		$this->click("//a[@rel=\"next\"]");
		$this->waitforElement("//select[@id='jform_db_type']");

		echo "Enter database information\n";
		$dbtype = (isset($cfg->db_type)) ? $cfg->db_type : 'MySQLi';
		$this->select("jform_db_type", "label=".$dbtype);
		$this->type("jform_db_host", $cfg->db_host);
		$this->type("jform_db_user", $cfg->db_user);
		$this->type("jform_db_pass", $cfg->db_pass);
		$this->type("jform_db_prefix", $cfg->db_prefix);
		$this->type("jform_db_name", $cfg->db_name);
		$this->click("jform_db_old0");
		$this->click("//a[@rel=\"next\"]");
		$this->waitforElement("//select[@id='jform_ftp_enable']");

		echo "Enter site information\n";
		$this->click("//a[@rel=\"next\"]");
		$this->waitforElement("//input[@id='jform_site_name']");

		$this->type("jform_site_name", $cfg->site_name);
		$this->type("jform_admin_user", $cfg->username);
		$this->type("jform_admin_email", $cfg->admin_email);
		$this->type("jform_admin_password", $cfg->password);
		$this->type("jform_admin_password2", $cfg->password);

		// Default is install with sample data
		if ($cfg->sample_data !== false)
		{
			echo "Install sample data and wait for success message\n";
			$this->click("instDefault");

			// wait up to 30 seconds for success message on sample data
			$this->waitforElement("//input[contains(translate(@value, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'),'avec succès')]");
		}
		else {
			echo "Install without sample data\n";
		}

		echo "Finish installation\n";
		$this->click("//a[@rel=\"next\"]");
		$this->waitforElement("//h3[contains(text(), 'Joomla! est installé')]");

		echo "Login to back end\n";
		$this->gotoAdmin();
		$this->doAdminLogin();

		echo "Check for site menu\n";
		$this->assertEquals("Site", $this->getText("link=Site"));

		echo "Change error level to maximum\n";
		$this->jClick('Global Configuration');
		$this->click("server");
		$this->select("jform_error_reporting", "label=Maximum");
		$this->click("//li[@id='toolbar-save']/a/span");
		$this->waitForPageToLoad("30000");

		$this->setCache($cfg->cache);

		// Check admin template -- change to hathor if specified in config file
		if (isset($cfg->adminTemplate) &&  $cfg->adminTemplate == 'hathor') {
			$this->click("link=Template Manager");
			$this->waitForPageToLoad("30000");
			$this->click("link=Hathor - Default");
			$this->waitForPageToLoad("30000");
			$this->click("jform_home1");
			$this->click("//li[@id='toolbar-save']/a/span");
			$this->waitForPageToLoad("30000");
		}

		$this->doAdminLogout();
		$this->deleteAllVisibleCookies();
	}
}
