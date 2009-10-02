<?php
/**
 * @version		$Id: viewlevels.php 12186 2009-06-20 00:23:39Z eddieajau $
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
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
class JTableViewlevels extends JTable
{
	/**
	 * @var int unsigned
	 */
	var $id;

	/**
	 * @var varchar
	 */
	var $title;

	/**
	 * @var int unsigned
	 */
	var $ordering;

	/**
	 * @var varchar
	 */
	var $rules;

	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	object	Database object
	 * @return	void
	 * @since	1.0
	 */
	function __construct(&$db)
	{
		parent::__construct('#__viewlevels', 'id', $db);
	}

	/**
	 * Method to check the current record to save
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.0
	 */
	function check()
	{
		// Validate the title.
		if ((trim($this->title)) == '') {
			$this->setError(JText::_('Viewlevel must have a title'));
			return false;
		}

		return true;
	}
}
