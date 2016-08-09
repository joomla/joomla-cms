<?php
/**
 * @package     Joomla.Test
 * @subpackage  AcceptanceHelper
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Codeception\Module;

use Codeception\Configuration;

/**
 * Helper class for acceptance tester.
 * Here you can define custom actions
 * All public methods declared in helper class will be available in $I
 *
 * @package  Codeception\Module
 *
 * @since    __DEPLOY_VERSION__
 */
class AcceptanceHelper extends \Codeception\Module
{
	protected static $acceptanceSuiteConfiguration = [];

	/**
	 * Function to get Configuration from the acceptance.suite.yml to be used by a test
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getSuiteConfiguration()
	{
		if (empty(self::$acceptanceSuiteConfiguration))
		{
			self::$acceptanceSuiteConfiguration = Configuration::suiteSettings('acceptance', Configuration::config());
		}

		return self::$acceptanceSuiteConfiguration;
	}
}
