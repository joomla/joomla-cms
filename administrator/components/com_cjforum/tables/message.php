<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_PLATFORM') or die();

class CjForumTableMessage extends JTable
{

	public function __construct (JDatabaseDriver $db)
	{
		parent::__construct('#__cjforum_messages', 'id', $db);
	}

	protected function _getAssetName ()
	{
		$k = $this->_tbl_key;
		return 'com_cjforum.message.' . (int) $this->$k;
	}

	public function check ()
	{
		if (trim($this->title) == '')
		{
			$this->setError(JText::_('COM_CJFORUM_WARNING_PROVIDE_VALID_NAME'));
			return false;
		}
	
		if (trim($this->alias) == '')
		{
			$this->alias = $this->title;
		}
	
		$this->alias = JApplication::stringURLSafe($this->alias);
	
		if (trim(str_replace('-', '', $this->alias)) == '')
		{
			$this->alias = JFactory::getDate()->format('Y-m-d-H-i-s');
		}
	
		if (trim(str_replace('&nbsp;', '', $this->description)) == '')
		{
			$this->setError(JText::_('COM_CJFORUM_WARNING_MESSAGE_CANNOT_BE_EMPTY'));
			return false;
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
	
	public function store($updateNulls = false)
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
