<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Fields Table
 *
 * @since  __DEPLOY_VERSION__
 */
class FieldsTableField extends JTable
{
	/**
	 * Class constructor.
	 *
	 * @param   JDatabaseDriver  $db  JDatabaseDriver object.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($db = null)
	{
		parent::__construct('#__fields', 'id', $db);

		$this->setColumnAlias('published', 'state');

		JObserverMapper::addObserverClassToClass(
			'JTableObserverTags',
			'FieldsTableField',
			array('typeAlias' => 'com_fields.field'),
		);

		JObserverMapper::addObserverClassToClass(
			'JTableObserverContenthistory',
			'FieldsTableField',
			array('typeAlias' => 'com_fields.field')
		);
	}

	/**
	 * Method to bind an associative array or object to the JTable instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the JTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  InvalidArgumentException
	 */
	public function bind($src, $ignore = '')
	{
		if (isset($src['params']) && is_array($src['params']))
		{
			$registry = new Registry;
			$registry->loadArray($src['params']);
			$src['params'] = (string) $registry;
		}

		if (isset($src['fieldparams']) && is_array($src['fieldparams']))
		{
			$registry = new Registry;
			$registry->loadArray($src['fieldparams']);
			$src['fieldparams'] = (string) $registry;
		}

		// Bind the rules.
		if (isset($src['rules']) && is_array($src['rules']))
		{
			$rules = new JRules($src['rules']);
			$this->setRules($rules);
		}

		return parent::bind($src, $ignore);
	}

	/**
	 * Method to perform sanity checks on the JTable instance properties to ensure
	 * they are safe to store in the database.  Child classes should override this
	 * method to make sure the data they are storing in the database is safe and
	 * as expected before storage.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @link    https://docs.joomla.org/JTable/check
	 * @since   __DEPLOY_VERSION__
	 */
	public function check()
	{
		// Check for valid name
		if (trim($this->title) == '')
		{
			$this->setError(JText::_('COM_FIELDS_LOCATION_ERR_TABLES_TITLE'));

			return false;
		}

		if (empty($this->alias))
		{
			$this->alias = $this->title;
		}

		$this->alias = JApplication::stringURLSafe($this->alias);

		if (trim(str_replace('-', '', $this->alias)) == '')
		{
			$this->alias = JString::increment($alias, 'dash');
		}

		$this->alias = str_replace(',', '-', $this->alias);

		if (empty($this->type))
		{
			$this->type = 'text';
		}

		if (is_array($this->assigned_cat_ids))
		{
			$this->assigned_cat_ids = implode(',', $this->assigned_cat_ids);
		}

		$date = JFactory::getDate();
		$user = JFactory::getUser();

		if ($this->id)
		{
			// Existing item
			$this->modified_time = $date->toSql();
			$this->modified_by   = $user->get('id');
		}
		else
		{
			if (! (int) $this->created_time)
			{
				$this->created_time = $date->toSql();
			}

			if (empty($this->created_user_id))
			{
				$this->created_user_id = $user->get('id');
			}
		}

		return true;
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form table_name.id
	 * where id is the value of the primary key of the table.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return $this->context . '.field.' . (int) $this->$k;
	}

	/**
	 * Method to return the title to use for the asset table.  In
	 * tracking the assets a title is kept for each asset so that there is some
	 * context available in a unified access manager.  Usually this would just
	 * return $this->title or $this->name or whatever is being used for the
	 * primary name of the row. If this method is not overridden, the asset name is used.
	 *
	 * @return  string  The string to use as the title in the asset table.
	 *
	 * @link    https://docs.joomla.org/JTable/getAssetTitle
	 * @since   $this->$k
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
	 * @param   JTable   $table  A JTable object for the asset parent.
	 * @param   integer  $id     Id to look up
	 *
	 * @return  integer
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		$parts     = FieldsHelper::extract($this->context);
		$component = $parts ? $parts[0] : null;

		if ($parts && $this->catid)
		{
			$assetId = $this->getAssetId($parts[0] . '.' . $parts[1] . '.fields.category.' . $this->catid);

			if ($assetId !== false)
			{
				return $assetId;
			}
		}
		elseif ($component)
		{
			$assetId = $this->getAssetId($component);

			if ($assetId !== false)
			{
				return $assetId;
			}
		}

		return parent::_getAssetParentId($table, $id);
	}

	/**
	 * Returns an asset id for the given name or false.
	 *
	 * @param   string  $name  The asset name
	 *
	 * @return number|boolean
	 */
	private function getAssetId($name)
	{
		// Build the query to get the asset id for the name.
		$query = $this->_db->getQuery(true)
			->select($this->_db->quoteName('id'))
			->from($this->_db->quoteName('#__assets'))
			->where($this->_db->quoteName('name') . ' = ' . $this->_db->quote($name));

		// Get the asset id from the database.
		$this->_db->setQuery($query);

		$assetId = null;

		if ($result = $this->_db->loadResult())
		{
			$assetId = (int) $result;

			if ($assetId)
			{
				return $assetId;
			}
		}

		return false;
	}
}
