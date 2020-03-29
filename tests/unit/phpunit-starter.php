<?php
/**
 * Prepares the environment for PHPUnit.
 *
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://www.phpunit.de/manual/current/en/installation.html
 */

/**
 * For Joomla 3.x we use PHPUnit 4.8, which uses each().
 * On PHP 7.2 this has been deprecated and removed in PHP 8.0
 * We are providing a shim for that here and then call the original
 * PHPUnit.
 */
if (!function_exists('each'))
{
	function each(&$array)
	{
		$return = array(
			1 => current($array),
			'value' => current($array),
			0 => key($array),
			'key' => key($array)
		);
		next($array);

		if (is_null($return['key']))
		{
			return false;
		}

		return $return;
	}
}

define('PHPUNIT_COMPOSER_INSTALL', __DIR__ . '/../../libraries/vendor/autoload.php');

require_once PHPUNIT_COMPOSER_INSTALL;

PHPUnit_TextUI_Command::main();
