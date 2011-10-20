<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Inspector for the FinderIndexerAdapter class.
 *
 * @package		Joomla.UnitTest
 * @subpackage  com_finder
 * @since       2.5
 */
class FinderIndexerAdapterInspector extends FinderIndexerAdapter
{
	/**
	 * Abstract method.
	 *
	 * @param   FinderIndexerResult  $item  The item to index as a FinderIndexerResult object.
	 *
	 * @return  boolean
	 *
	 * @since   2.5
	 * @throws	Exception on error.
	 */
	public function index(FinderIndexerResult $item)
	{
		return parent::index($item);
	}

	/**
	 * Abstract method.
	 *
	 * @return  boolean
	 *
	 * @since   2.5
	 * @throws	Exception on error.
	 */
	public function setup()
	{
		return parent::setup();
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @param   string   $id        The ID of the item to change.
	 * @param   string   $property  The property that is being changed.
	 * @param   integer  $value     The new value of that property.
	 *
	 * @return  boolean
	 *
	 * @since   2.5
	 * @throws	Exception on error.
	 */
	public function change($id, $property, $value)
	{
		return parent::change($id, $property, $value);
	}

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
	 * @param   integer  $id         The id of the item.
	 * @param   string   $extension  The extension the category is in.
	 * @param   string   $view       The view for the URL.
	 *
	 * @return  boolean
	 *
	 * @since   2.5
	 * @throws	Exception on error.
	 */
	public function getURL($id, $extension, $view)
	{
		return parent::getURL($id, $extension, $view);
	}
}
