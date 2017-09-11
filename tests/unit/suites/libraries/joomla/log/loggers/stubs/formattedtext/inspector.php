<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
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
 * @since       11.1
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
