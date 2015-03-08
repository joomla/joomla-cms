<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JTableCmsHelper
{
	/**
	 * Method to return the public properties of an object
	 * I had to create a new context in order for get_object_vars to return only public properties.
	 *
	 * @param object $object to return the properties of
	 *
	 * @return array associative array of public properties
	 */
	static function getPublicProperties($object)
	{
		return get_object_vars($object);
	}
}