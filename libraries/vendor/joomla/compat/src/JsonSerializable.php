<?php
/**
 * Part of the Joomla Framework Compat Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * JsonSerializable interface. This file should only be loaded on PHP < 5.4
 * It allows us to implement it in classes without requiring PHP 5.4
 *
 * @link   http://www.php.net/manual/en/jsonserializable.jsonserialize.php
 * @since  1.0
 */
interface JsonSerializable
{
	/**
	 * Return data which should be serialized by json_encode().
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	public function jsonSerialize();
}
