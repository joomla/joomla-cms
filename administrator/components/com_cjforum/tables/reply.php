<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_PLATFORM') or die();

class CjForumTableReply extends JTable
{

	public function __construct (JDatabaseDriver $db)
	{
		JObserverMapper::addObserverClassToClass('JTableObserverTags', 'CjForumTableReply', array(
				'typeAlias' => 'com_cjforum.reply'
		));
		
		parent::__construct('#__cjforum_replies', 'id', $db);
		
		// JTableObserverTags::createObserver($this, array(
		// 'typeAlias' => 'com_cjforum.topic'
		// ));
		// JObserverFactory::addObserverClassToClass('JTableObserverTags',
	// 'JTableContent', array(
		// 'typeAlias' => 'com_cjforum.topic'
		// ));
	}

	protected function _getAssetName ()
	{
		$k = $this->_tbl_key;
		
		return 'com_cjforum.reply.' . (int) $this->$k;
	}

	protected function _getAssetTitle ()
	{
		return $this->title;
	}

	protected function _getAssetParentId (JTable $table = null, $id = null)
	{
		$assetId = null;
		
		// This is a topic under a category.
		if ($this->topic_id)
		{
			// Build the query to get the asset id for the parent category.
			$query = $this->_db->getQuery(true)
				->select($this->_db->quoteName('asset_id'))
				->from($this->_db->quoteName('#__cjforum_topics'))
				->where($this->_db->quoteName('id') . ' = ' . (int) $this->topic_id);
			
			// Get the asset id from the database.
			$this->_db->setQuery($query);
			
			if ($result = $this->_db->loadResult())
			{
				$assetId = (int) $result;
			}
		}
		
		// Return the asset id.
		if ($assetId)
		{
			return $assetId;
		}
		else
		{
			return parent::_getAssetParentId($table, $id);
		}
	}

	public function bind ($array, $ignore = '')
	{
		if (isset($array['attribs']) && is_array($array['attribs']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['attribs']);
			$array['attribs'] = (string) $registry;
		}
		
		// Bind the rules.
		if (isset($array['rules']) && is_array($array['rules']))
		{
			$rules = new JAccessRules($array['rules']);
			$this->setRules($rules);
		}
		
		return parent::bind($array, $ignore);
	}

	public function check ()
	{
		if (trim(str_replace('&nbsp;', '', $this->description)) == '')
		{
			$this->description = '';
		}
		
		// Check the publish down date is not earlier than publish up.
		if ($this->publish_down > $this->_db->getNullDate() && $this->publish_down < $this->publish_up)
		{
			// Swap the dates.
			$temp = $this->publish_up;
			$this->publish_up = $this->publish_down;
			$this->publish_down = $temp;
		}
		
		return true;
	}

	public function store ($updateNulls = false)
	{
		$date = JFactory::getDate();
		$user = JFactory::getUser();
		
		if ($this->id)
		{
			// Existing item
			$this->modified = $date->toSql();
			$this->modified_by = $user->get('id');
		}
		else
		{
			// New topic. A topic created and created_by field can be set
			// by the user,
			// so we don't touch either of these if they are set.
			if (! (int) $this->created)
			{
				$this->created = $date->toSql();
			}
			
			if (empty($this->created_by))
			{
				$this->created_by = $user->get('id');
			}
		}
		
		return parent::store($updateNulls);
	}

	public function publish ($pks = null, $state = 1, $userId = 0)
	{
		$k = $this->_tbl_key;
		
		// Sanitize input.
		JArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state = (int) $state;
		
		// If there are no primary keys set check to see if the instance key is
		// set.
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = array(
						$this->$k
				);
			}
			// Nothing to set publishing state on, return false.
			else
			{
				$this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
				
				return false;
			}
		}
		
		// Build the WHERE clause for the primary keys.
		$where = $k . '=' . implode(' OR ' . $k . '=', $pks);
		
		// Determine if there is checkin support for the table.
		if (! property_exists($this, 'checked_out') || ! property_exists($this, 'checked_out_time'))
		{
			$checkin = '';
		}
		else
		{
			$checkin = ' AND (checked_out = 0 OR checked_out = ' . (int) $userId . ')';
		}
		
		// Update the publishing state for rows with the given primary keys.
		$query = $this->_db->getQuery(true)
			->update($this->_db->quoteName($this->_tbl))
			->set($this->_db->quoteName('state') . ' = ' . (int) $state)
			->where('(' . $where . ')' . $checkin);
		$this->_db->setQuery($query);
		
		try
		{
			$this->_db->execute();
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());
			
			return false;
		}
		
		// If checkin is supported and all rows were adjusted, check them in.
		if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
		{
			// Checkin the rows.
			foreach ($pks as $pk)
			{
				$this->checkin($pk);
			}
		}
		
		// If the JTable instance value is in the list of primary keys that were
		// set, set the instance.
		if (in_array($this->$k, $pks))
		{
			$this->state = $state;
		}
		
		$this->setError('');
		
		return true;
	}
}
