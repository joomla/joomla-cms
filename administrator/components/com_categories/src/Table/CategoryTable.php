<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Categories\Administrator\Table;

/**
 * Category table
 *
 * @since  1.6
 */
class CategoryTable extends \Joomla\CMS\Table\Category
{
    /**
     * Method to delete a node and, optionally, its child nodes from the table.
     *
     * @param   integer|null  $pk        The primary key of the node to delete.
     * @param   boolean       $children  True to delete child nodes, false to move them up a level.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     */
    public function delete($pk = null, $children = false)
    {
        return parent::delete($pk, $children);
    }
}
