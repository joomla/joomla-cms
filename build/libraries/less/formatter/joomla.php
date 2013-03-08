<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Less
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('lessphp.lessc');

/**
 * Help system class
 *
 * @package     Joomla.Libraries
 * @subpackage  Less
 * @since       3.0
 */
class JLessFormatterJoomla extends lessc_formatter_classic
{
	public $disableSingle = true;

	public $breakSelectors = true;

	public $assignSeparator = ": ";

	public $selectorSeparator = ",";

	public $indentChar = "\t";
}
