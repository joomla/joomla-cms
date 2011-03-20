<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.UnitTest
 */

defined('JPATH_PLATFORM') or die;

/**
 * Inspector classes for the JLog package.
 */

/**
 * @package		Joomla.UnitTest
 * @subpackage	Log
 */
class JLogInspector extends JLog
{
	public $configurations;
	public $loggers;
	public $lookup;

	public function __construct()
	{
		return parent::__construct();
	}

	public function addLogEntry(JLogEntry $entry)
	{
		return parent::addLogEntry($entry);
	}

	public function findLoggers($priority, $category)
	{
		return parent::findLoggers($priority, $category);
	}
}