<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Import library dependencies
require_once(dirname(__FILE__).DS.'extension.php');

/**
 * Installer Components Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @since		1.5
 */
class InstallerModelComponents extends InstallerModel
{
	/**
	 * Extension Type
	 * @var	string
	 */
	var $_type = 'component';

	/**
	 * Enable a component
	 *
	 * @static
	 * @return boolean True on success
	 * @since 1.0
	 */
	function enable($eid=array())
	{
		// Initialize variables
		$result	= false;

		/*
		 * Ensure eid is an array of extension ids
		 * TODO: If it isn't an array do we want to set an error and fail?
		 */
		if (!is_array($eid)) {
			$eid = array ($eid);
		}

		// Get a database connector
		$db = &JFactory::getDbo();

		// Get a table object for the extension type
		$table = & JTable::getInstance($this->_type);

		// Enable the extension in the table and store it in the database
		foreach ($eid as $id)
		{
			$table->load($id);
			$table->enabled = '1';
			$result |= $table->store();
		}

		return $result;
	}

	/**
	 * Disable a component
	 *
	 * @return boolean True on success
	 * @since 1.5
	 */
	function disable($eid=array())
	{
		// Initialize variables
		$result		= false;

		/*
		 * Ensure eid is an array of extension ids
		 * TODO: If it isn't an array do we want to set an error and fail?
		 */
		if (!is_array($eid)) {
			$eid = array ($eid);
		}

		// Get a database connector
		$db = &JFactory::getDbo();

		// Get a table object for the extension type
		$table = & JTable::getInstance($this->_type);

		// Disable the extension in the table and store it in the database
		foreach ($eid as $id)
		{
			$table->load($id);
			$table->enabled = '0';
			$result |= $table->store();
		}

		return $result;
	}

	function _loadItems()
	{
		jimport('joomla.filesystem.folder');

		/* Get a database connector */
		$db = &JFactory::getDbo();

		$query = 'SELECT *' .
				' FROM #__components' .
				' WHERE parent = 0' .
				' ORDER BY iscore, name';
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		/* Get the component base directory */
		$adminDir = JPATH_ADMINISTRATOR .DS. 'components';
		$siteDir = JPATH_SITE .DS. 'components';

		$numRows = count($rows);
		for ($i=0;$i < $numRows; $i++)
		{
			$row = &$rows[$i];

			 /* Get the component folder and list of xml files in folder */
			$folder = $adminDir.DS.$row->option;
			if (JFolder::exists($folder)) {
				$xmlFilesInDir = JFolder::files($folder, '.xml$');
			} else {
				$folder = $siteDir.DS.$row->option;
				if (JFolder::exists($folder)) {
					$xmlFilesInDir = JFolder::files($folder, '.xml$');
				} else {
					$xmlFilesInDir = null;
				}
			}

			if (count($xmlFilesInDir))
			{
				foreach ($xmlFilesInDir as $xmlfile)
				{
					if ($data = JApplicationHelper::parseXMLInstallFile($folder.DS.$xmlfile)) {
						foreach($data as $key => $value) {
							$row->$key = $value;
						}
					}
					$row->jname = JString::strtolower(str_replace(" ", "_", $row->name));
				}
			}
		}
		$this->setState('pagination.total', $numRows);

		// if the offset is greater than the total, then can the offset
		if ($this->_state->get('pagination.offset') > $this->_state->get('pagination.total')) {
			$this->setState('pagination.offset',0);
		}

		if ($this->_state->get('pagination.limit') > 0) {
			$this->_items = array_slice($rows, $this->_state->get('pagination.offset'), $this->_state->get('pagination.limit'));
		} else {
			$this->_items = $rows;
		}
	}
}