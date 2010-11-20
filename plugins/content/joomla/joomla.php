<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * Example Content Plugin
 *
 * @package		Joomla
 * @subpackage	Content
 * @since		1.6
 */
class plgContentJoomla extends JPlugin
{
	/**
	 * Don't allow categories to be deleted if they contain items or subcategories with items
	 *
	 * @param	string	The context for the content passed to the plugin.
	 * @param	object	The data relating to the content that was deleted.
	 * @return	boolean
	 * @since	1.6
	 */
	public function onContentBeforeDelete($context, $data)
	{
		// Skip plugin if we are deleting something other than categories
		if ($context != 'com_categories.category') {
			return true;
		}

		$extension = JRequest::getString('extension');

		// Default to true if not a core extension
		$result = true;

		$tableInfo = array (
			'com_banners' => array('table_name' => '#__banners'),
			'com_contact' => array('table_name' => '#__contact_details'),
			'com_content' => array('table_name' => '#__content'),
			'com_newsfeeds' => array('table_name' => '#__newsfeeds'),
			'com_weblinks' => array('table_name' => '#__weblinks')
		);

		// Now check to see if this is a known core extension
		if (isset($tableInfo[$extension]))
		{
			// Get table name for known core extensions
			$table = $tableInfo[$extension]['table_name'];
			// See if this category has any content items
			$count = $this->_countItemsInCategory($table, $data->get('id'));
			// Return false if db error
			if ($count === false)
			{
				$result = false;
			}
			else
			{
				// Show error if items are found in the category
				if ($count > 0 ) {
					$msg = JText::sprintf('COM_CATEGORIES_DELETE_NOT_ALLOWED', $data->get('title')) .
					JText::plural('COM_CATEGORIES_N_ITEMS_ASSIGNED', $count);
					JError::raiseWarning(403, $msg);
					$result = false;
				}
				// Check for items in any child categories (if it is a leaf, there are no child categories)
				if (!$data->isLeaf()) {
					$count = $this->_countItemsInChildren($table, $data->get('id'), $data);
					if ($count === false)
					{
						$result = false;
					}
					elseif ($count > 0)
					{
						$msg = JText::sprintf('COM_CATEGORIES_DELETE_NOT_ALLOWED', $data->get('title')) .
						JText::plural('COM_CATEGORIES_HAS_SUBCATEGORY_ITEMS', $count);
						JError::raiseWarning(403, $msg);
						$result = false;
					}
				}
			}

			return $result;
		}
	}

	/**
	 * Get count of items in a category
	 *
	 * @param	string	table name of component table (column is catid)
	 * @param	int		id of the category to check
	 * @return	mixed	count of items found or false if db error
	 * @since	1.6
	 */
	private function _countItemsInCategory($table, $catid)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Count the items in this category
		$query->select('COUNT(id)');
		$query->from($table);
		$query->where('catid = ' . $catid);
		$db->setQuery($query);
		$count = $db->loadResult();

		// Check for DB error.
		if ($error = $db->getErrorMsg())
		{
			JError::raiseWarning(500, $error);
			return false;
		}
		else {
			return $count;
		}
	}

	/**
	 * Get count of items in a category's child categories
	 *
	 * @param	string	table name of component table (column is catid)
	 * @param	int		id of the category to check
	 * @return	mixed	count of items found or false if db error
	 * @since	1.6
	 */
	private function _countItemsInChildren($table, $catid, $data)
	{
		$db = JFactory::getDbo();
		// Create subquery for list of child categories
		$childCategoryTree = $data->getTree();
		// First element in tree is the current category, so we can skip that one
		unset($childCategoryTree[0]);
		$childCategoryIds = array();
		foreach ($childCategoryTree as $node) {
			$childCategoryIds[] = $node->id;
		}

		// Make sure we only do the query if we have some categories to look in
		if (count($childCategoryIds))
		{
			// Count the items in this category
			$query = $db->getQuery(true);
			$query->select('COUNT(id)');
			$query->from($table);
			$query->where('catid IN (' . implode(',', $childCategoryIds) . ')');
			$db->setQuery($query);
			$count = $db->loadResult();

			// Check for DB error.
			if ($error = $db->getErrorMsg())
			{
				JError::raiseWarning(500, $error);
				return false;
			}
			else
			{
				return $count;
			}
		}
		else
		// If we didn't have any categories to check, return 0
		{
			return 0;
		}
	}
}