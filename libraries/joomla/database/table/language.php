<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Database
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.database.table');

/**
 * Languages table.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @since		11.1
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
