<?php
/**
 * @package     Joomla
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Codeception\Module;

/**
 * Class AcceptanceHelper
 *
 * Here you can define custom actions all public methods declared in helper class will be available in $I.
 *
 * @link http://codeception.com/docs/03-ModulesAndHelpers#Helpers
 */
class AcceptanceHelper extends \Codeception\Module
{
	/**
	 * Function to getConfiguration from the acceptance.suite.yml to be used by the tests
	 *
	 * @return array
	 */
	public function getConfiguration($element = null)
	{
		if (!$element)
		{
			throw new InvalidArgumentException('empty value or non existing element was requested from configuration');
		}

		return $this->config[$element];
	}
}
