<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Media Table class
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.4
 */
class MediaTableMedia extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__media', 'id', $db);
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param   mixed  $array   An associative array or object to bind to the JTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JTable:bind
	 * @since   1.5
	 */
	public function bind($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		if (isset($array['metadata']) && is_array($array['metadata']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string) $registry;
		}

		if (isset($array['images']) && is_array($array['images']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['images']);
			$array['images'] = (string) $registry;
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Overload the store method for the Media table.
	 *
	 * @param   boolean	Toggle whether null values should be updated.
	 * @return  boolean  True on success, false on failure.
	 * @since   1.6
	 */
	public function store($updateNulls = false)
	{
		$date	= JFactory::getDate();
		$user	= JFactory::getUser();

		if ($this->id)
		{
			// Existing item
			$this->modified		= $date->toSql();
			$this->modified_by	= $user->get('id');
		}
		else
		{
			// New media. A media created and created_by field can be set by the user,
			// so we don't touch either of these if they are set.
			if (!(int) $this->created)
			{
				$this->created = $date->toSql();
			}
			if (empty($this->created_by))
			{
				$this->created_by = $user->get('id');
			}
		}

		// Verify that the alias is unique
		$table = JTable::getInstance('Media', 'MediaTable');

		if ($table->load(array('alias' => $this->alias, 'catid' => $this->catid)) && ($table->id != $this->id || $this->id == 0))
		{
			$this->setError(JText::_('COM_MEDIA_ERROR_UNIQUE_ALIAS'));
			return false;
		}

		// Convert IDN urls to punycode
		$this->url = JStringPunycode::urlToPunycode($this->url);

		return parent::store($updateNulls);
	}

	/**
	 * Overloaded check method to ensure data integrity.
	 *
	 * @return  boolean  True on success.
	 */
	public function check()
	{
		
	}
}
