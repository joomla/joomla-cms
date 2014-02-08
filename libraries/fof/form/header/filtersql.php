<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage form
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die;

/**
 * Generic filter, drop-down based on SQL query
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFFormHeaderFiltersql extends FOFFormHeaderFieldsql
{
	/**
	 * Get the header
	 *
	 * @return  string  The header HTML
	 */
	protected function getHeader()
	{
		return '';
	}
}
