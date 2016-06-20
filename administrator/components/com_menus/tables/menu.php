<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Menu table
 *
 * @since  1.6
 */
class MenusTableMenu extends JTableMenu
{
	/**
	 * Method to delete a node and, optionally, its child nodes from the table.
	 *
	 * @param   integer  $pk        The primary key of the node to delete.
	 * @param   boolean  $children  True to delete child nodes, false to move them up a level.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @see     https://docs.joomla.org/JTableNested/delete
	 */
	public function delete($pk = null, $children = false)
	{
		return parent::delete($pk, $children);
	}
}
