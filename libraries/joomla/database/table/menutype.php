<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Menu Types table
 *
 * @package     Joomla.Platform
 * @subpackage  Table
 * @since       11.1
 */
class JTableMenuType extends JTable
{
	/**
	 * Constructor
	 *
	 * @param  database  A database connector object
	 *
	 * @since  11.1
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__menu_types', 'id', $db);
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @since   11.1
	 *
	 * @see     JTable::check
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

		$db	= $this->getDbo();

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

	/**
	 * Method to store a row in the database from the JTable instance properties.
	 * If a primary key value is set the row with that primary key value will be
	 * updated with the instance property values.  If no primary key value is set
	 * a new row will be inserted into the database with the properties from the
	 * JTable instance.
	 *
	 * @param   boolean True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 *
	 * @link    http://docs.joomla.org/JTable/store
	 */
	public function store($updateNulls = false)
	{
		if ($this->id) {
			// Get the user id
			$userId = JFactory::getUser()->id;

			// Get the old value of the table
			$table = JTable::getInstance('Menutype','JTable');
			$table->load($this->id);

			// Verify that no items are cheched out
			$query = $this->_db->getQuery(true);
			$query->select('id');
			$query->from('#__menu');
			$query->where('menutype='.$this->_db->quote($table->menutype));
			$query->where('checked_out !='.(int) $userId);
			$query->where('checked_out !=0');
			$this->_db->setQuery($query);
			if ($this->_db->loadRowList()) {
				$this->setError(JText::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', get_class($this), JText::_('JLIB_DATABASE_ERROR_MENUTYPE_CHECKOUT')));
				return false;
			}

			// Verify that no module for this menu are cheched out
			$query = $this->_db->getQuery(true);
			$query->select('id');
			$query->from('#__modules');
			$query->where('module='.$this->_db->quote('mod_menu'));
			$query->where('params LIKE '.$this->_db->quote('%"menutype":'.json_encode($table->menutype).'%'));
			$query->where('checked_out !='.(int) $userId);
			$query->where('checked_out !=0');
			$this->_db->setQuery($query);
			if ($this->_db->loadRowList()) {
				$this->setError(JText::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', get_class($this), JText::_('JLIB_DATABASE_ERROR_MENUTYPE_CHECKOUT')));
				return false;
			}

			// Update the menu items
			$query = $this->_db->getQuery(true);
			$query->update('#__menu');
			$query->set('menutype='.$this->_db->quote($this->menutype));
			$query->where('menutype='.$this->_db->quote($table->menutype));
			$this->_db->setQuery($query);
			if (!$this->_db->query()) {
				$this->setError(JText::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', get_class($this), $this->_db->getErrorMsg()));
				return false;
			}

			// Update the module items
			$query = $this->_db->getQuery(true);
			$query->update('#__modules');
			$query->set('params=REPLACE(params,'.$this->_db->quote('"menutype":'.json_encode($table->menutype)).','.$this->_db->quote('"menutype":'.json_encode($this->menutype)).')');
			$query->where('module='.$this->_db->quote('mod_menu'));
			$query->where('params LIKE '.$this->_db->quote('%"menutype":'.json_encode($table->menutype).'%'));
			$this->_db->setQuery($query);
			if (!$this->_db->query()) {
				$this->setError(JText::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', get_class($this), $this->_db->getErrorMsg()));
				return false;
			}
		}
		return parent::store($updateNulls);
	}
	/**
	 * Method to delete a row from the database table by primary key value.
	 *
	 * @param   mixed    An optional primary key value to delete.  If not set the
	 *                   instance property value is used.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 *
	 * @link    http://docs.joomla.org/JTable/delete
	 */
	public function delete($pk = null)
	{
		// Initialise variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// If no primary key is given, return false.
		if ($pk !== null)
		{
			// Get the user id
			$userId = JFactory::getUser()->id;

			// Get the old value of the table
			$table = JTable::getInstance('Menutype','JTable');
			$table->load($pk);

			// Verify that no items are cheched out
			$query = $this->_db->getQuery(true);
			$query->select('id');
			$query->from('#__menu');
			$query->where('menutype='.$this->_db->quote($table->menutype));
			$query->where('(checked_out NOT IN (0,'.(int) $userId.') OR home=1 AND language='.$this->_db->quote('*').')');
			$this->_db->setQuery($query);
			if ($this->_db->loadRowList()) {
				$this->setError(JText::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), JText::_('JLIB_DATABASE_ERROR_MENUTYPE')));
				return false;
			}

			// Verify that no module for this menu are cheched out
			$query = $this->_db->getQuery(true);
			$query->select('id');
			$query->from('#__modules');
			$query->where('module='.$this->_db->quote('mod_menu'));
			$query->where('params LIKE '.$this->_db->quote('%"menutype":'.json_encode($table->menutype).'%'));
			$query->where('checked_out !='.(int) $userId);
			$query->where('checked_out !=0');
			$this->_db->setQuery($query);
			if ($this->_db->loadRowList()) {
				$this->setError(JText::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), JText::_('JLIB_DATABASE_ERROR_MENUTYPE')));
				return false;
			}

			// Delete the menu items
			$query = $this->_db->getQuery(true);
			$query->delete();
			$query->from('#__menu');
			$query->where('menutype='.$this->_db->quote($table->menutype));
			$this->_db->setQuery($query);
			if (!$this->_db->query()) {
				$this->setError(JText::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), $this->_db->getErrorMsg()));
				return false;
			}

			// Update the module items
			$query = $this->_db->getQuery(true);
			$query->delete();
			$query->from('#__modules');
			$query->where('module='.$this->_db->quote('mod_menu'));
			$query->where('params LIKE '.$this->_db->quote('%"menutype":'.json_encode($table->menutype).'%'));
			$this->_db->setQuery($query);
			if (!$this->_db->query()) {
				$this->setError(JText::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), $this->_db->getErrorMsg()));
				return false;
			}
		}
		return parent::delete($pk);
	}
}