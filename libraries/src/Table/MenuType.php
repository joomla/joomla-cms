<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\ApplicationHelper;

/**
 * Menu Types table
 *
 * @since  1.6
 */
class MenuType extends Table
{
	/**
	 * Constructor
	 *
	 * @param   \JDatabaseDriver  $db  Database driver object.
	 *
	 * @since   1.6
	 */
	public function __construct(\JDatabaseDriver $db)
	{
		parent::__construct('#__menu_types', 'id', $db);
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @see     Table::check()
	 * @since   1.6
	 */
	public function check()
	{
		$this->menutype = ApplicationHelper::stringURLSafe($this->menutype);

		if (empty($this->menutype))
		{
			$this->setError(\JText::_('JLIB_DATABASE_ERROR_MENUTYPE_EMPTY'));

			return false;
		}

		// Sanitise data.
		if (trim($this->title) === '')
		{
			$this->title = $this->menutype;
		}

		// Check for unique menutype.
		$query = $this->_db->getQuery(true)
			->select('COUNT(id)')
			->from($this->_db->quoteName('#__menu_types'))
			->where($this->_db->quoteName('menutype') . ' = ' . $this->_db->quote($this->menutype))
			->where($this->_db->quoteName('id') . ' <> ' . (int) $this->id);
		$this->_db->setQuery($query);

		if ($this->_db->loadResult())
		{
			$this->setError(\JText::sprintf('JLIB_DATABASE_ERROR_MENUTYPE_EXISTS', $this->menutype));

			return false;
		}

		return true;
	}

	/**
	 * Method to store a row in the database from the Table instance properties.
	 *
	 * If a primary key value is set the row with that primary key value will be updated with the instance property values.
	 * If no primary key value is set a new row will be inserted into the database with the properties from the Table instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function store($updateNulls = false)
	{
		if ($this->id)
		{
			// Get the user id
			$userId = \JFactory::getUser()->id;

			// Get the old value of the table
			$table = Table::getInstance('Menutype', 'JTable', array('dbo' => $this->getDbo()));
			$table->load($this->id);

			// Verify that no items are checked out
			$query = $this->_db->getQuery(true)
				->select('id')
				->from('#__menu')
				->where('menutype=' . $this->_db->quote($table->menutype))
				->where('checked_out !=' . (int) $userId)
				->where('checked_out !=0');
			$this->_db->setQuery($query);

			if ($this->_db->loadRowList())
			{
				$this->setError(
					\JText::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', get_class($this), \JText::_('JLIB_DATABASE_ERROR_MENUTYPE_CHECKOUT'))
				);

				return false;
			}

			// Verify that no module for this menu are checked out
			$query->clear()
				->select('id')
				->from('#__modules')
				->where('module=' . $this->_db->quote('mod_menu'))
				->where('params LIKE ' . $this->_db->quote('%"menutype":' . json_encode($table->menutype) . '%'))
				->where('checked_out !=' . (int) $userId)
				->where('checked_out !=0');
			$this->_db->setQuery($query);

			if ($this->_db->loadRowList())
			{
				$this->setError(
					\JText::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', get_class($this), \JText::_('JLIB_DATABASE_ERROR_MENUTYPE_CHECKOUT'))
				);

				return false;
			}

			// Update the menu items
			$query->clear()
				->update('#__menu')
				->set('menutype=' . $this->_db->quote($this->menutype))
				->where('menutype=' . $this->_db->quote($table->menutype));
			$this->_db->setQuery($query);
			$this->_db->execute();

			// Update the module items
			$query->clear()
				->update('#__modules')
				->set(
				'params=REPLACE(params,' . $this->_db->quote('"menutype":' . json_encode($table->menutype)) . ',' .
				$this->_db->quote('"menutype":' . json_encode($this->menutype)) . ')'
			);
			$query->where('module=' . $this->_db->quote('mod_menu'))
				->where('params LIKE ' . $this->_db->quote('%"menutype":' . json_encode($table->menutype) . '%'));
			$this->_db->setQuery($query);
			$this->_db->execute();
		}

		return parent::store($updateNulls);
	}

	/**
	 * Method to delete a row from the database table by primary key value.
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function delete($pk = null)
	{
		$k = $this->_tbl_key;
		$pk = $pk === null ? $this->$k : $pk;

		// If no primary key is given, return false.
		if ($pk !== null)
		{
			// Get the user id
			$userId = \JFactory::getUser()->id;

			// Get the old value of the table
			$table = Table::getInstance('Menutype', 'JTable', array('dbo' => $this->getDbo()));
			$table->load($pk);

			// Verify that no items are checked out
			$query = $this->_db->getQuery(true)
				->select('id')
				->from('#__menu')
				->where('menutype=' . $this->_db->quote($table->menutype))
				->where('(checked_out NOT IN (0,' . (int) $userId . ') OR home=1 AND language=' . $this->_db->quote('*') . ')');
			$this->_db->setQuery($query);

			if ($this->_db->loadRowList())
			{
				$this->setError(\JText::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), \JText::_('JLIB_DATABASE_ERROR_MENUTYPE')));

				return false;
			}

			// Verify that no module for this menu are checked out
			$query->clear()
				->select('id')
				->from('#__modules')
				->where('module=' . $this->_db->quote('mod_menu'))
				->where('params LIKE ' . $this->_db->quote('%"menutype":' . json_encode($table->menutype) . '%'))
				->where('checked_out !=' . (int) $userId)
				->where('checked_out !=0');
			$this->_db->setQuery($query);

			if ($this->_db->loadRowList())
			{
				$this->setError(\JText::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), \JText::_('JLIB_DATABASE_ERROR_MENUTYPE')));

				return false;
			}

			// Delete the menu items
			$query->clear()
				->delete('#__menu')
				->where('menutype=' . $this->_db->quote($table->menutype));
			$this->_db->setQuery($query);
			$this->_db->execute();

			// Update the module items
			$query->clear()
				->delete('#__modules')
				->where('module=' . $this->_db->quote('mod_menu'))
				->where('params LIKE ' . $this->_db->quote('%"menutype":' . json_encode($table->menutype) . '%'));
			$this->_db->setQuery($query);
			$this->_db->execute();
		}

		return parent::delete($pk);
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form table_name.id
	 * where id is the value of the primary key of the table.
	 *
	 * @return  string
	 *
	 * @since   3.6
	 */
	protected function _getAssetName()
	{
		return 'com_menus.menu.' . $this->id;
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return  string
	 *
	 * @since   3.6
	 */
	protected function _getAssetTitle()
	{
		return $this->title;
	}

	/**
	 * Method to get the parent asset under which to register this one.
	 * By default, all assets are registered to the ROOT node with ID,
	 * which will default to 1 if none exists.
	 * The extended class can define a table and id to lookup.  If the
	 * asset does not exist it will be created.
	 *
	 * @param   Table    $table  A Table object for the asset parent.
	 * @param   integer  $id     Id to look up
	 *
	 * @return  integer
	 *
	 * @since   3.6
	 */
	protected function _getAssetParentId(Table $table = null, $id = null)
	{
		$assetId = null;
		$asset = Table::getInstance('asset');

		if ($asset->loadByName('com_menus'))
		{
			$assetId = $asset->id;
		}

		return $assetId === null ? parent::_getAssetParentId($table, $id) : $assetId;
	}
}
