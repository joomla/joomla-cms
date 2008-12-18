<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

// No direct access
defined('JPATH_BASE') or die();

/**
 * Abstract table used for object that map to the access control system
 *
 * @since	1.6
 */
abstract class JTableAsset extends JTable
{
	/**
	 * Required property.  Maps to a value in the AXO Groups Table.
	 *
	 * @var		int
	 */
	public $access = 0;

	/**
	 * Abstract method to return the title of the object to insert into the AXO table
	 *
	 * @return	string
	 */
	protected abstract function getAssetSection()
	{
		die('Must provide an implementation of getAssetSection');
	}

	/**
	 * Abstract method to return the section of the object to insert into the AXO table
	 *
	 * @return	string
	 */
	protected abstract function getAssetTitle()
	{
		die('Must provide an implementation of getAssetTitle');
	}

	/**
	 * Stores the record, adds/updates the AXO Table and maps it to the appropriate AXO Group
	 *
	 * @param	boolean		Update null values in the object
	 *
	 * @return	boolean
	 */
	function store($updateNulls = false)
	{
		if (!parent::store($updateNulls)) {
			return false;
		}

		$name		= $this->getAssetTitle();
		$section	= $this->getAssetSection();
		$key		= $this->_tbl_key;
		$id			= $this->$key;

		jimport('joomla.acl.acladmin');
		$group = JAclAdmin::getGroupForAssets($this->access);
		if (JError::isError($group)) {
			// Could not find the group so run with public
			$group	= JAclAdmin::getGroupForAssets(0);
		}

		$result = JAclAdmin::registerAsset($section, $name, $id);
		if (JError::isError($result)) {
			$this->setError($result->getMessage());
			return false;
		}
		else {
			$axoId = $result;
		}

		$result = JAclAdmin::registerAssetInGroups($axoId, $group->id);
		if (JError::isError($result)) {
			$this->setError($result->getMessage());
			return false;
		}

		return true;
	}

	/**
	 * Deletes the AXO record and dependancies
	 *
	 * @param	int $id
	 *
	 * @return	boolean
	 */
	function delete($id = null)
	{
		// Delete the base object first
		if (!parent::delete($id)) {
			return false;
		}

		if (empty($id)) {
			$key	= $this->_tbl_key;
			$id		= $this->$key;
		}

		$section = $this->getAssetSection();

		jimport('joomla.acl.acladmin');
		$result = JAclAdmin::removeAsset($section, $id);
		if (JError::isError($result)) {
			$this->setError($result->getMessage());
			return false;
		}

		return true;
	}
}
