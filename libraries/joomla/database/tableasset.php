<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Database
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.database.table');

/**
 * Access controlled asset table class.
 *
 * @package		Joomla.Framework
 * @subpackage	Database
 * @version		1.0
 */
class JTableAsset extends JTable
{
	/**
	 * Required property.  Maps to a value in the asset groups table.
	 * - Defaults to 1 (Public)
	 *
	 * @var	integer
	 */
	var $access = 1;

	/**
	 * Abstract method to return the access section name for the asset table.
	 *
	 * @abstract
	 * @access	public
	 * @return	string
	 * @since	1.0
	 */
	function getAssetSection()
	{
		die('Must provide an implementation of getAssetSection');
	}

	/**
	 * Abstract method to return the name prefix to use for the asset table.
	 *
	 * @abstract
	 * @access	public
	 * @return	string
	 * @since	1.0
	 */
	function getAssetNamePrefix()
	{
		die('Must provide an implementation of getAssetNamePrefix');
	}

	/**
	 * Abstract method to return the title to use for the asset table.
	 *
	 * @abstract
	 * @access	public
	 * @return	string
	 * @since	1.0
	 */
	function getAssetTitle()
	{
		die('Must provide an implementation of getAssetTitle');
	}

	/**
	 * Stores the record, adds/updates the assets table and maps it to the appropriate asset group.
	 *
	 * @access	public
	 * @param	boolean	True to update null values in the object.
	 * @return	boolean	True on success.
	 * @since	1.0
	 */
	function store($updateNulls = false)
	{
		// Attempt to store the record.
		if (!parent::store($updateNulls)) {
			return false;
		}

		// Get the database object.
		$db = & $this->_db;

		// Get the section id for the asset.
		$section = $this->getAssetSection();
		$db->setQuery(
			'SELECT `id`' .
			' FROM `#__access_sections`' .
			' WHERE `name` = '.$db->Quote($section)
		);
		$sectionId = $db->loadResult();

		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Make sure the section is valid.
		if (empty($sectionId)) {
			$this->setError(JText::_('Access_Section_Invalid'));
			return false;
		}

		// Get and sanitize the asset name.
		$prefix = $this->getAssetNamePrefix();
		$key = $this->_tbl_key;
		$suffix = $this->$key;
		$name = strtolower(preg_replace('#[\s\-]+#', '.', trim($prefix.'.'.$suffix, ' .')));

		// Get the asset id for the asset.
		$db->setQuery(
			'SELECT `id`' .
			' FROM `#__access_assets`' .
			' WHERE `name` = '.$db->Quote($name)
		);
		$assetId = $db->loadResult();

		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Is the asset new.
		$isNew = (empty($assetId)) ? true : false;

		// Build the asset object.
		$asset = new stdClass;
		$asset->section_id	= $sectionId;
		$asset->section		= $section;
		$asset->name		= $name;
		$asset->title		= $this->getAssetTitle();

		// Synchronize the assets table.
		if ($isNew) {
			$asset->id = null;
			$return = $db->insertObject('#__access_assets', $asset, 'id');
		}
		else {
			$asset->id = $assetId;
			$return = $db->updateObject('#__access_assets', $asset, 'id');
		}

		// Check for error.
		if (!$return) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Get the updated asset id.
		$assetId = $asset->id;

		// Get the asset group id[ default to 1 or public].
		$groupId = (!$this->access) ? 1 : $this->access;

		// Delete previous asset to group maps.
		$db->setQuery(
			'DELETE FROM `#__access_asset_assetgroup_map`' .
			' WHERE `asset_id` = '.(int) $assetId
		);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Insert asset to group map.
		$db->setQuery(
			'INSERT INTO `#__access_asset_assetgroup_map` (`asset_id`, `group_id`) VALUES' .
			' ('.(int) $assetId.', '.(int) $groupId.')'
		);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		return true;
	}

	/**
	 * Deletes the asset record and dependancies.
	 *
	 * @access	public
	 * @param	integer	Primary key of record to delete.
	 * @return	boolean	True on success.
	 * @since	1.0
	 */
	function delete($id = null)
	{
		// Delete the base object first
		if (!parent::delete($id)) {
			return false;
		}

		// Get the database object.
		$db = & $this->_db;

		// Get the section id for the asset.
		$section = $this->getAssetSection();
		$db->setQuery(
			'SELECT `id`' .
			' FROM `#__access_sections`' .
			' WHERE `name` = '.$db->Quote($section)
		);
		$sectionId = $db->loadResult();

		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Make sure the section is valid.
		if (empty($sectionId)) {
			$this->setError(JText::_('Access_Section_Invalid'));
			return false;
		}

		// Get the table key value.
		$key = (empty($id)) ? $this->$this->_tbl_key : $id;

		// Make sure the key is valid.
		if (empty($key)) {
			$this->setError(JText::_('Access_Asset_Key_Invalid'));
			return false;
		}

		// Get and sanitize the asset name.
		$prefix = $this->getAssetNamePrefix();
		$suffix = $key;
		$name = strtolower(preg_replace('#[\s\-]+#', '.', trim($prefix.'.'.$suffix, ' .')));

		// Get the asset id for the asset.
		$db->setQuery(
			'SELECT `id`' .
			' FROM `#__access_assets`' .
			' WHERE `name` = '.$db->Quote($name)
		);
		$assetId = $db->loadResult();

		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Delete asset to group maps.
		$db->setQuery(
			'DELETE FROM `#__access_asset_assetgroup_map`' .
			' WHERE `asset_id` = '.(int) $assetId
		);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Delete the asset.
		$db->setQuery(
			'DELETE FROM `#__access_assets`' .
			' WHERE `id` = '.(int) $assetId
		);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		return true;
	}
}
