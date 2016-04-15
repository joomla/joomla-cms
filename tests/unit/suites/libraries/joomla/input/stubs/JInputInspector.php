<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Input
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Inspector class for the JInput library.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Input
 * @since       11.1
 */
class JInputInspector extends JInput
{
	public $options;

	public $filter;

	public $data;

	public $inputs;

	public static $registered;
	/**
	 * Test...
	 *
	 * @return void
	 */

	public static function register()
	{
		return parent::register();
	}
}
