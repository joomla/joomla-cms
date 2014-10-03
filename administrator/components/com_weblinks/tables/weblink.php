<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Weblink Table class
 *
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 * @since       1.5
 */
class WeblinksTableWeblink extends JTable
{
	/**
	 * Ensure the params and metadata in json encoded in the bind method
	 *
	 * @var    array
	 * @since  3.4
	 */
	protected $_jsonEncode = array('params', 'metadata', 'images');

	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__weblinks', 'id', $db);

		// Set the published column alias
		$this->setColumnAlias('published', 'state');

		JTableObserverTags::createObserver($this, array('typeAlias' => 'com_weblinks.weblink'));
		JTableObserverContenthistory::createObserver($this, array('typeAlias' => 'com_weblinks.weblink'));
	}

	/**
	 * Overload the store method for the Weblinks table.
	 *
	 * @param   boolean	Toggle whether null values should be updated.
	 * @return  boolean  True on success, false on failure.
	 * @since   1.6
	 */
	public function store($updateNulls = false)
	{
		$date	= JFactory::getDate();
		$user	= JFactory::getUser();

		$this->modified		= $date->toSql();

		if ($this->id)
		{
			// Existing item
			$this->modified_by	= $user->get('id');
		}
		else
		{
			// New weblink. A weblink created and created_by field can be set by the user,
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

		// Set publish_up to null date if not set
		if (!$this->publish_up)
		{
			$this->publish_up = $this->_db->getNullDate();
		}

		// Set publish_down to null date if not set
		if (!$this->publish_down)
		{
			$this->publish_down = $this->_db->getNullDate();
		}

		// Verify that the alias is unique
		$table = JTable::getInstance('Weblink', 'WeblinksTable');

		if ($table->load(array('alias' => $this->alias, 'catid' => $this->catid)) && ($table->id != $this->id || $this->id == 0))
		{
			$this->setError(JText::_('COM_WEBLINKS_ERROR_UNIQUE_ALIAS'));
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
		if (JFilterInput::checkAttribute(array ('href', $this->url)))
		{
			$this->setError(JText::_('COM_WEBLINKS_ERR_TABLES_PROVIDE_URL'));
			return false;
		}

		// check for valid name
		if (trim($this->title) == '')
		{
			$this->setError(JText::_('COM_WEBLINKS_ERR_TABLES_TITLE'));
			return false;
		}

		// Check for existing name
		$query = $this->_db->getQuery(true)
			->select($this->_db->quoteName('id'))
			->from($this->_db->quoteName('#__weblinks'))
			->where($this->_db->quoteName('title') . ' = ' . $this->_db->quote($this->title))
			->where($this->_db->quoteName('catid') . ' = ' . (int) $this->catid);
		$this->_db->setQuery($query);

		$xid = (int) $this->_db->loadResult();
		if ($xid && $xid != (int) $this->id)
		{
			$this->setError(JText::_('COM_WEBLINKS_ERR_TABLES_NAME'));
			return false;
		}

		if (empty($this->alias))
		{
			$this->alias = $this->title;
		}
		$this->alias = JApplication::stringURLSafe($this->alias);
		if (trim(str_replace('-', '', $this->alias)) == '')
		{
			$this->alias = JFactory::getDate()->format("Y-m-d-H-i-s");
		}

		// Check the publish down date is not earlier than publish up.
		if ($this->publish_down > $this->_db->getNullDate() && $this->publish_down < $this->publish_up)
		{
			$this->setError(JText::_('JGLOBAL_START_PUBLISH_AFTER_FINISH'));
			return false;
		}

		// clean up keywords -- eliminate extra spaces between phrases
		// and cr (\r) and lf (\n) characters from string
		if (!empty($this->metakey))
		{
			// only process if not empty
			$bad_characters = array("\n", "\r", "\"", "<", ">"); // array of characters to remove
			$after_clean = JString::str_ireplace($bad_characters, "", $this->metakey); // remove bad characters
			$keys = explode(',', $after_clean); // create array using commas as delimiter
			$clean_keys = array();

			foreach ($keys as $key)
			{
				if (trim($key)) {  // ignore blank keywords
					$clean_keys[] = trim($key);
				}
			}
			$this->metakey = implode(", ", $clean_keys); // put array back together delimited by ", "
		}

		return true;
	}
}
