<?php
/**
 * @package    FrameworkOnFramework
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die;

/**
 * FrameworkOnFramework model behavior class
 *
 * @package  FrameworkOnFramework
 * @since    2.1
 */
class FOFModelFieldBoolean extends FOFModelFieldNumber
{
	/**
	 * Is it a null or otherwise empty value?
	 *
	 * @param   mixed  $value  The value to test for emptiness
	 *
	 * @return  boolean
	 */
	public function isEmpty($value)
	{
		return is_null($value) || ($value === '');
	}
}
