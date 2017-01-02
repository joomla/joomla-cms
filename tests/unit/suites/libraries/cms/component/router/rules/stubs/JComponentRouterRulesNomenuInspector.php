<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Component
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Inspector for JComponentRouterRulesNomenu
 *
 * @package     Joomla.UnitTest
 * @subpackage  Component
 * @since       3.4
 */
class JComponentRouterRulesNomenuInspector extends JComponentRouterRulesNomenu
{
	/**
	 * Gets an attribute of the object
	 * 
	 * @param   string   $key  Attributename to return
	 *
	 * @return  mixed  Attributes of the object
	 *
	 * @since   3.4
	 */
	public function get($key)
	{
		return $this->$key;
	}

	/**
	 * Sets an attribute of the object
	 * 
	 * @param   string   $key    Attributename to return
	 * @param   mixed    $value  Value to be set
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function set($key, $value)
	{
		$this->$key = $value;
	}
}