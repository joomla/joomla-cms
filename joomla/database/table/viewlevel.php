<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.database.table');

/**
 * Viewlevels table class.
 *
 * @package		Joomla.Framework
 * @subpackage	Database
 * @version		1.0
 */
class JTableViewlevel extends JTable
{
	/**
	 * Constructor
	 *
	 * @param	object	Database object
	 * @return	void
	 * @since	1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__viewlevels', 'id', $db);
	}

	/**
	 * Method to bind the data.
	 *
	 * @param	array		$array		The data to bind.
	 * @param	mixed		$ignore		An array or space separated list of fields to ignore.
	 * @return	boolean		True on success, false on failure.
	 * @since	1.6
	 */
	public function bind($array, $ignore = '')
	{
		// Bind the rules as appropriate.
		if (isset($array['rules'])) {
			if (is_array($array['rules'])) {
				$array['rules'] = json_encode($array['rules']);
			}
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Method to check the current record to save
	 *
	 * @return	boolean	True on success
	 * @since	1.0
	 */
	public function check()
	{
		// Validate the title.
		if ((trim($this->title)) == '') {
			$this->setError(JText::_('JLIB_DATABASE_ERROR_VIEWLEVEL'));
			return false;
		}

		return true;
	}
}
