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
 * Formatter ruleset for Joomla formatted CSS (minified) generated via LESS
 *
 * @package     Joomla.Libraries
 * @subpackage  Less
 * @since       3.4.2
 */
class JLessFormatterCompressed extends lessc_formatter_classic
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
