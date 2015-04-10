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
	public function checkNoConfigurationFile()
	{
		$I = $this;
		$I->expect('no configuration.php is in the Joomla CMS folder');

		$joomlaConfigurationFile = realpath($I->getConfiguration('Joomla folder') . 'configuration.php');

		$I->assertFalse(file_exists($joomlaConfigurationFile), "a Configuration.php file was found in Joomla CMS folder. Can't Install Joomla since is already installed");

		$I->comment('Joomla is ready to install');
	}
}