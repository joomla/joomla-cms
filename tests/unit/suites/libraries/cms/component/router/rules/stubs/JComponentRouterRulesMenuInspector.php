<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Component
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Inspector for JComponentRouterRulesMenu
 *
 * @package     Joomla.UnitTest
 * @subpackage  Component
 * @since       3.4
 */
class JComponentRouterRulesMenuInspector extends JComponentRouterRulesMenu
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

	public function runBuildLookup($language = '*')
	{
		return $this->buildLookup($language);
	}
}
