<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Less
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Formatter ruleset for Joomla formatted CSS generated via LESS
 *
 * @package     Joomla.Libraries
 * @subpackage  Less
 * @since       3.4
 * @deprecated  4.0  without replacement
 */
class JLessFormatterJoomla extends lessc_formatter_classic
{
	public $disableSingle = true;

	public $breakSelectors = true;

	public $assignSeparator = ': ';

	public $selectorSeparator = ',';

	public $indentChar = "\t";
}
