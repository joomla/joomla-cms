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

class FieldsTableField extends JTable
{

	public function __construct (&$db = null)
	{
		parent::__construct('#__fields', 'id', $db);

		$this->setColumnAlias('published', 'state');

		JObserverMapper::addObserverClassToClass('JTableObserverTags', 'FieldsTableField', array(
				'typeAlias' => 'com_fields.field'
		));
		JObserverMapper::addObserverClassToClass('JTableObserverContenthistory', 'FieldsTableField',
				array(
						'typeAlias' => 'com_fields.field'
				));
	}

	public function bind ($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new Registry();
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}
		if (isset($array['fieldparams']) && is_array($array['fieldparams']))
		{
			$registry = new Registry();
			$registry->loadArray($array['fieldparams']);
			$array['fieldparams'] = (string) $registry;
		}

		// Bind the rules.
		if (isset($array['rules']) && is_array($array['rules']))
		{
			$rules = new JRules($array['rules']);
			$this->setRules($rules);
		}

		return parent::bind($array, $ignore);
	}

	public function check ()
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

		// Check the publish down date is not earlier than publish up.
		if ($this->publish_down > $this->_db->getNullDate() && $this->publish_down < $this->publish_up)
		{
			$this->setError(JText::_('JGLOBAL_START_PUBLISH_AFTER_FINISH'));
			return false;
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
			$this->modified_by = $user->get('id');
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

	protected function _getAssetName ()
	{
		$k = $this->_tbl_key;
		return $this->context . '.field.' . (int) $this->$k;
	}

	protected function _getAssetTitle ()
	{
		return $this->title;
	}

	protected function _getAssetParentId (JTable $table = null, $id = null)
	{
		$parts = FieldsHelper::extract($this->context);
		$component = $parts ? $parts[0] : null;

		if ($component)
		{
			// Build the query to get the asset id for the parent category.
			$query = $this->_db->getQuery(true)
				->select($this->_db->quoteName('id'))
				->from($this->_db->quoteName('#__assets'))
				->where($this->_db->quoteName('name') . ' = ' . $this->_db->quote($component));

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
		}

		return parent::_getAssetParentId($table, $id);
	}
}
