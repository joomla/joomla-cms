<?php
namespace Codeception\Module;

use Codeception\Configuration;

// here you can define custom actions
// all public methods declared in helper class will be available in $I
class AcceptanceHelper extends \Codeception\Module
{
	protected static $acceptanceSuiteConfiguration = [];

	/**
	 * Function to get Configuration from the acceptance.suite.yml to be used by a test
	 *
	 * @return array
	 *
	 * @throws InvalidArgumentException
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