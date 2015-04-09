<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Less
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Formatter ruleset for Joomla formatted CSS generated via LESS
 *
 * @package     Joomla.Libraries
 * @subpackage  Less
 * @since       3.4
 */
class JLessFormatterJoomla extends lessc_formatter_classic
{
	public $indentChar = "\t";

	public $break = "\n";
	public $open = " {";
	public $close = "}";
	public $selectorSeparator = ", ";
	public $assignSeparator = ": ";

	public $openSingle = " { ";
	public $closeSingle = " }";

	public $disableSingle = true;
	public $breakSelectors = true;

	public $compressColors = false;
}

class JLessFormatterJoomlaCompressed extends lessc_formatter_classic
{
	public $indentChar = "";

	public $break = "";
	public $open = "{";
	public $close = "}";
	public $selectorSeparator = ",";
	public $assignSeparator = ":";

	public $openSingle = "{";
	public $closeSingle = "}";

	public $disableSingle = false;
	public $breakSelectors = false;

	public $compressColors = true;
}
