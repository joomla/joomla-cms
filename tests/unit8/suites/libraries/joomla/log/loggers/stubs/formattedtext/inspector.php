<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Inspector classes for the JLog package.
 */

/**
 * JLogLoggerFormattedTextInspector class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Log
 * @since       1.7.0
 */
class JLogLoggerFormattedTextInspector extends JLogLoggerFormattedtext
{
	public $file;

	public $format = '{DATETIME}	{PRIORITY}	{CATEGORY}	{MESSAGE}';

	public $options;

	public $fields;

	public $path;

	public $defer;
}
