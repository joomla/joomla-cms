<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Menu Types table
 *
 * @package 	Joomla.Framework
 * @subpackage	Table
 * @since		1.5
 */
class JTableMenuTypes extends JTable
{
	/** @var int Primary key */
	var $id					= null;
	/** @var string */
	var $menutype			= null;
	/** @var string */
	var $title				= null;
	/** @var string */
	var $description		= null;

	/**
	 * Constructor
	 *
	 * @access protected
	 * @param database A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__menu_types', 'id', $db);
	}

	/**
	 * @return boolean
	 */
	function check()
	{
		$this->menutype = JFilterOutput::stringURLSafe($this->menutype);
		if (empty($this->menutype)) {
			$this->setError("Cannot save: Empty menu type");
			return false;
		}

		// correct spurious data
		if (trim($this->title) == '') {
			$this->title = $this->menutype;
		}

		$db		= &JFactory::getDbo();

		// check for unique menutype for new menu copy
		$query = 'SELECT menutype' .
				' FROM #__menu_types';
		if ($this->id) {
			$query .= ' WHERE id != '.(int) $this->id;
		}

		$db->setQuery($query);
		$menus = $db->loadResultArray();

		foreach ($menus as $menutype)
		{
			if ($menutype == $this->menutype)
			{
				$this->setError("Cannot save: Duplicate menu type '{$this->menutype}'");
				return false;
			}
		}

		return true;
	}
}
