<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  plg_finder_content
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Inspector for the PlgFinderContent class.
 *
 * @package		Joomla.UnitTest
 * @subpackage  plg_finder_content
 * @since       2.5
 */
class PlgFinderContentInspector extends PlgFinderContent
{
	/**
	 * Allows public access to protected method.
	 *
	 * @param   mixed  $sql  A JDatabaseQuery object or null.
	 *
	 * @return  JDatabaseQuery  A JDatabaseQuery object.
	 *
	 * @since   2.5
	 */
	public function getListQuery($sql = null)
	{
		return parent::getListQuery($sql);
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 */
	public function setup()
	{
		return parent::setup();
	}
}
