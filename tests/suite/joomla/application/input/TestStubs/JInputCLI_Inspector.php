<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Inspector classes for the JInputCLI library.
 */

/**
 * @package		Joomla.UnitTest
 * @subpackage  Application
 */
class JInputCLIInspector extends JInputCLI
{
	public $options;
	public $filter;
	public $data;
	public $inputs;
	public static $registered;

	public static function register()
	{
		return parent::register();
	}

	public function parseArguments()
	{
		return parent::parseArguments();
	}
}