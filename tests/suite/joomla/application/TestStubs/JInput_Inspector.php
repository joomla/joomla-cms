<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Inspector classes for the JInput library.
 */

/**
 * @package		Joomla.UnitTest
 * @subpackage	Application
 */
class JInputInspector extends JInput
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
}