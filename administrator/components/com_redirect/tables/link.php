<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Link Table for Redirect.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 * @since       1.6
 */
class RedirectTableLink extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   object	Database object
	 *
	 * @return  void
	 * @since   1.6
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__redirect_links', 'id', $db);
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean
	 * @since   1.6
	 */
	public function check()
	{
		$this->old_url = trim($this->old_url);
		$this->new_url = trim($this->new_url);

		// Check for valid name.
		if (empty($this->old_url))
		{
			$this->setError(JText::_('COM_REDIRECT_ERROR_SOURCE_URL_REQUIRED'));
			return false;
		}

		// Check for valid name.
		if (empty($this->new_url))
		{
			$this->setError(JText::_('COM_REDIRECT_ERROR_DESTINATION_URL_REQUIRED'));
			return false;
		}

		// Check for duplicates
		if ($this->old_url == $this->new_url)
		{
			$this->setError(JText::_('COM_REDIRECT_ERROR_DUPLICATE_URLS'));
			return false;
		}

		$db = $this->getDbo();

		// Check for existing name
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from('#__redirect_links')
			->where($db->quoteName('old_url') . ' = ' . $db->quote($this->old_url));
		$db->setQuery($query);

		$xid = (int) $db->loadResult();

		if ($xid && $xid != (int) $this->id)
		{
			$this->setError(JText::_('COM_REDIRECT_ERROR_DUPLICATE_OLD_URL'));
			return false;
		}

		return true;
	}

	/**
	 * Overriden store method to set dates.
	 *
	 * @param   boolean	True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 * @see     JTable::store
	 * @since   1.6
	 */
	public function store($updateNulls = false)
	{
		$date = JFactory::getDate()->toSql();

		if ($this->id)
		{
			// Existing item
			$this->modified_date = $date;
		}
		else
		{
			// New record.
			$this->created_date = $date;
		}

		return parent::store($updateNulls);
	}
}
