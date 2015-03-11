<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  less
 * @copyright   Copyright (C) 2010 - 2015 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * This class is taken verbatim from:
 *
 * lessphp v0.3.9
 * http://leafo.net/lessphp
 *
 * LESS css compiler, adapted from http://lesscss.org
 *
 * Copyright 2012, Leaf Corcoran <leafot@gmail.com>
 * Licensed under MIT or GPLv3, see LICENSE
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFLessFormatterCompressed extends FOFLessFormatterClassic
{
	public $disableSingle = true;

	public $open = "{";

	public $selectorSeparator = ",";

	public $assignSeparator = ":";

	public $break = "";

	public $compressColors = true;

	/**
	 * Indent a string by $n positions
	 *
	 * @param   integer  $n  How many positions to indent
	 *
	 * @return  string  The indented string
	 */
	public function indentStr($n = 0)
	{
		return "";
	}
}
