<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Menu Types table
 *
 * @package		Joomla.Framework
 * @subpackage	Table
 * @since		1.5
 */
class JTableMenuType extends JTable
{
	/**
	 * Constructor
	 *
	 * @param database A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__menu_types', 'id', $db);
	}

	/**
	 * @return boolean
	 */
	function check()
	{
		$this->menutype = JApplication::stringURLSafe($this->menutype);
		if (empty($this->menutype)) {
			$this->setError(JText::_('JLIB_DATABASE_ERROR_MENUTYPE_EMPTY'));
			return false;
		}

		// Sanitise data.
		if (trim($this->title) == '') {
			$this->title = $this->menutype;
		}

		$db	= &$this->getDbo();

		// Check for unique menutype.
		$db->setQuery(
			'SELECT COUNT(id)' .
			' FROM #__menu_types' .
			' WHERE menutype = '.$db->quote($this->menutype).
			'  AND id <> '.(int) $this->id
		);

		if ($db->loadResult())
		{
			$this->setError(JText::sprintf('JLIB_DATABASE_ERROR_MENUTYPE_EXISTS', $this->menutype));
			return false;
		}

		return true;
	}
}
