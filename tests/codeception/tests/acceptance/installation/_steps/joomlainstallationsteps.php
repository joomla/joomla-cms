<?php
/**
 * @package     Joomla
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace AcceptanceTester;

/**
 * Class JoomlaInstallationBeforeSteps
 *
 * @package  AcceptanceTester
 *
 * @since    1.4
 *
 * @link     http://codeception.com/docs/07-AdvancedUsage#StepObjects
 */
class JoomlaInstallationSteps extends \AcceptanceTester
{
	/**
	 * Ensures that the Joomla installation is clean.
	 *
	 * @description use this function when you do not download Joomla, but use an existing joomla instead
	 *
	 * @return void
	 */
	public function removeConfigFile()
	{
		$I = $this;
		$I->wantTo('prepare joomla installation');

		// Remove Joomla 3 CMS old configuration.php file before do a clean joomla installation
		$cfg = $I->getConfig();

		$joomla3ConfigurationFile = realpath($cfg['joomla folder'] . 'configuration.php');

		if (file_exists($joomla3ConfigurationFile))
		{
			$I->comment('removing Joomla 3 CMS old configuration.php file to do a clean joomla installation');
			chmod($joomla3ConfigurationFile, 0777);
			unlink($joomla3ConfigurationFile);
		}

		$I->comment('Joomla is ready to install');
	}
}