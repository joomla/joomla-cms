<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

jimport('joomla.database.table');

/**
 * Languages table.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @since		1.6
 */
class JTableLanguage extends JTable
{
	/**
	 * Constructor
	 *
	 * @param	JDatabase
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__languages', 'lang_id', $db);
	}

	/**
	 * Overloaded check method to ensure data integrity
	 *
	 * @return boolean True on success
	 */
	public function check()
	{
		if (trim($this->title) == '') {
			$this->setError(JText::_('JLIB_DATABASE_ERROR_LANGUAGE_NO_TITLE'));
			return false;
		}

		return true;
	}
}
