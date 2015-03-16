<?php
/**
 * @package     Joomla
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
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
	public function getConfig()
	{
		$configuration = array(
			"username" => $this->config['username'],
			"password" => $this->config['password'],
			"extension folder" => $this->config['extension_folder'],
			"joomla folder" => $this->config['joomla_folder'],
			"Database Host" => $this->config['db_host'],
			"Database User" => $this->config['db_user'],
			"Database Password" => $this->config['db_pass'],
			"Database Name" => $this->config['db_name'],
			"Database Type" => $this->config['db_type'],
			"Database Prefix" => $this->config['db_prefix'],
			"Admin email" => $this->config['admin_email'],
			"host" => $this->config['host'],
		);

		return $configuration;
	}
}
