<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

jimport('joomla.database.tablenested');

/**
 * Category table
 *
 * @package 	Joomla.Framework
 * @subpackage	Table
 * @since		1.0
 */
class JTableCategory extends JTableNested
{
	/**
	 * @param database A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__categories', 'id', $db);

		$this->access	= (int) JFactory::getConfig()->getValue('access');
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form `table_name.id`
	 * where id is the value of the primary key of the table.
	 *
	 * @return	string
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;
		return $this->extension.'.category.'.(int) $this->$k;
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return	string
	 * @since	1.6
	 */
	protected function _getAssetTitle()
	{
		return $this->title;
	}

	/**
	 * Get the parent asset id for the record
	 *
	 * @return	int
	 */
	protected function _getAssetParentId()
	{
		// Initialize variables.
		$assetId = null;

		// This is a category under a category.
		if ($this->parent_id > 1)
		{
			// Build the query to get the asset id for the parent category.
			$query = new JQuery;
			$query->select('asset_id');
			$query->from('#__categories');
			$query->where('id = '.(int) $this->parent_id);

			// Get the asset id from the database.
			$this->_db->setQuery($query);
			if ($result = $this->_db->loadResult()) {
				$assetId = (int) $result;
			}
		}
		// This is a category that needs to parent with the extension.
		elseif ($assetId === null)
		{
			// Build the query to get the asset id for the parent category.
			$query = new JQuery;
			$query->select('id');
			$query->from('#__assets');
			$query->where('name = '.$this->_db->quote($this->extension));

			// Get the asset id from the database.
			$this->_db->setQuery($query);
			if ($result = $this->_db->loadResult()) {
				$assetId = (int) $result;
			}
		}

		// Return the asset id.
		if ($assetId) {
			return $assetId;
		}
		else {
			return parent::_getAssetParentId();
		}
	}

	/**
	 * Override check function
	 *
	 * @return	boolean
	 * @see		JTable::check
	 * @since	1.5
	 */
	public function check()
	{
		// Check for a title.
		if (trim($this->title) == '') {
			$this->setError(JText::sprintf('must contain a title', JText::_('Category')));
			return false;
		}

		if (empty($this->alias)) {
			$this->alias = strtolower($this->title);
		}

		$this->alias = JFilterOutput::stringURLSafe($this->alias);
		if (trim(str_replace('-','',$this->alias)) == '') {
			$this->alias = JFactory::getDate()->toFormat('%Y-%m-%d-%H-%M-%S');
		}

		return true;
	}
}
