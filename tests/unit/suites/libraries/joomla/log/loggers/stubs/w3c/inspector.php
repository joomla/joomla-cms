<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Inspector classes for the JLog package.
 */

/**
 * JLogLoggerW3CInspector class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Log
 * @since       11.1
 */
class JLogLoggerW3CInspector extends JLogLoggerW3c
{
	public $file;

	public $format = '{DATE}	{TIME}	{PRIORITY}	{CLIENTIP}	{CATEGORY}	{MESSAGE}';

	public $options;

	public $fields;

	public $path;
}
