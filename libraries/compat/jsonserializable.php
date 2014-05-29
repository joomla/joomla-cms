<?php
/**
 * @package    Joomla.Compat
 *
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JsonSerializable interface. This file provides backwards compatibility to PHP 5.3 and ensures
 * the interface is present in systems where JSON related code was removed.
 *
 * @package  Joomla.Compat
 * @link     http://www.php.net/manual/en/jsonserializable.jsonserialize.php
 * @since    12.2
 */
interface JsonSerializable
{
	/**
	 * Return data which should be serialized by json_encode().
	 *
	 * @return  mixed
	 *
	 * @since   12.2
	 */
	public function jsonSerialize();
}
