<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Page
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * Class for the back-end control panel screen.
 *
 */
class InstallationPage extends AdminPage
{
	protected $waitForXpath = "//div[@id='container-installation']";
	protected $url = 'installation/';

	public function clickNextButton($step)
	{
		$this->driver->findElement(By::linkText('Next'))->click();
		$this->waitForStepNumber($step);
	}

	protected function waitForStepNumber($stepNumber)
	{
		$xPath = $this->waitForXpath;
		switch ($stepNumber)
		{
			case 2:
				$xPath = "//div[@id='preinstall'][@class='step active']";
				break;
			case 3:
				$xPath = "//div[@id='license'][@class='step active']";
				break;
			case 4:
				$xPath = "//div[@id='database'][@class='step active']";
				break;
			case 5:
				$xPath = "//div[@id='filesystem'][@class='step active']";
				break;
			case 6:
				$xPath = "//div[@id='site'][@class='step active']";
				break;
			case 7:
				$xPath = "//div[@id='complete'][@class='step active']";
				break;
			default:
				break;
		}
		$this->driver->waitForElementUntilIsPresent(By::xPath($xPath));
	}

	public function setDatabaseType($value)
	{
		$this->driver->findElement(By::xPath("//div[@id='jform_db_type_chzn']/a/div/b"))->click();
		$this->driver->findElement(By::xPath("//div[@id='jform_db_type_chzn']//ul[@class='chzn-results']/li[contains(translate(.,'" . strtoupper($value) . "', '" . strtolower($value) . "'), '" . strtolower($value) . "')]"))->click();
// 		$select = new SelectElement($this->driver->findElement(By::Id('jform_db_type')));
// 		$element = $select->getElement();
// 		$options = $element->findElements(By::tagName('option'));
// 		$select->selectByValue(strtolower($value));
	}

	public function setField($label, $value)
	{
		switch ($label)
		{
			case 'Host Name':
				$id = 'jform_db_host';
				break;
			case 'Username':
				$id = 'jform_db_user';
				break;
			case 'Password':
				$id = 'jform_db_pass';
				break;
			case 'Database Name':
				$id = 'jform_db_name';
				break;
			case 'Table Prefix':
				$id = 'jform_db_prefix';
				break;
			case 'Site Name':
				$id = 'jform_site_name';
				break;
			case 'Your Email':
				$id = 'jform_admin_email';
				break;
			case 'Admin Username':
				$id = 'jform_admin_user';
				break;
			case 'Admin Password':
				$id = 'jform_admin_password';
				break;
			case 'Confirm Admin Password':
				$id = 'jform_admin_password2';
				break;
		}
		$this->driver->findElement(By::id($id))->clear();
		$this->driver->findElement(By::id($id))->sendKeys($value);
	}

	public function setOldDatabaseProcess($option = 'Backup')
	{
		$this->driver->findElement(By::xPath("//input[@value = '" . strtolower($option) . "']"))->click();
	}

	public function setSampleData($option = 'Default')
	{
		$this->driver->findElement(By::xPath("//label[contains(., '"  . $option . "')]"))->click();
	}

	public function installSampleData()
	{
		$this->driver->findElement(By::xPath("//label[contains(., '" . $this->cfg->sample_data_file . "')]"))->click();
	}

	public function install($cfg)
	{
		$this->setField('Site Name', $cfg->site_name);
		$this->setField('Your Email', $cfg->admin_email);
		$this->setField('Admin Username', $cfg->username);
		$this->setField('Admin Password', $cfg->password);
		$this->setField('Confirm Admin Password', $cfg->password);
		$this->driver->findElement(By::xPath("//li[@id='database']/a"))->click();
		$this->driver->waitForElementUntilIsPresent(By::xPath("//li[@id='database'][@class='step active']"));

		$this->setDatabaseType($cfg->db_type);
		$this->setField('Host Name', $cfg->db_host);
		$this->setField('Username', $cfg->db_user);
		$this->setField('Password', $cfg->db_pass);
		$this->setField('Database Name', $cfg->db_name);
		$this->setField('Table Prefix', $cfg->db_prefix);

		$this->driver->findElement(By::xPath("//label[@for='jform_db_old1']"))->click();
		
		$this->driver->findElement(By::xPath("//li[@id='summary']/a"))->click();
		$this->driver->waitForElementUntilIsPresent(By::xPath("//li[@id='summary'][@class='step active']"));


		if ($cfg->sample_data && isset($cfg->sample_data_file))
		{
			$this->setSampleData($cfg->sample_data_file);
		}
		else
		{
			$this->setSampleData('None');
		}

		$this->driver->findElement(By::xPath("//a[@title='Install']"))->click();
		$this->driver->waitForElementUntilIsPresent(By::xPath("//input[contains(@onclick, 'Install.removeFolder')]"), 60);

	}
}
