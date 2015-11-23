<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_PLATFORM') or die();

class CjForumTableProfile extends JTable
{
	public function __construct (JDatabaseDriver $db)
	{
		parent::__construct('#__cjforum_users', 'id', $db);
	}

	protected function _getAssetName ()
	{
		$k = $this->_tbl_key;
		return 'com_cjforum.profile.' . (int) $this->$k;
	}

	protected function _getAssetTitle ()
	{
		return $this->title;
	}

	public function check ()
	{
		if (trim($this->handle) == '')
		{
			$this->setError(JText::_('COM_CJFORUM_WARNING_PROVIDE_VALID_NAME'));
			return false;
		}
		
		$this->handle = JApplication::stringURLSafe($this->handle);
		
		if (trim(str_replace('-', '', $this->handle)) == '')
		{
			$this->handle = JFactory::getUser($this->id)->username;
		}
		
		if (trim(str_replace('&nbsp;', '', $this->about)) == '')
		{
			$this->about = '';
		}
		
		return true;
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
				$pks = array($this->$k);
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
		$banDate = $state ? JFactory::getDate()->toSql() : '0000-00-00 00:00';
		$query = $this->_db->getQuery(true)
			->update($this->_db->quoteName($this->_tbl))
			->set($this->_db->quoteName('banned') . ' = ' . $banDate)
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

	public function bind ($array, $ignore = '')
	{
		if (isset($array['attribs']) && is_array($array['attribs']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['attribs']);
			$array['attribs'] = (string) $registry;
		}
		
		if (isset($array['metadata']) && is_array($array['metadata']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string) $registry;
		}
		
		return parent::bind($array, $ignore);
	}
}
