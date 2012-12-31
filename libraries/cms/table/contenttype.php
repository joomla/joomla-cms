<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Tags table
 *
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 * @since       1.6
 */
class TableContenttypes extends JTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabaseDriver A database connector object
	 */
	public function __construct($db)
	{
		parent::__construct('#__content_types', 'type_id', $db);
	}

	/**
	 * Overloaded check method to ensure data integrity.
	 *
	 * @return  boolean  True on success.
	 * @throws  UnexpectedValueException
	 */
	public function check()
	{
		// Check for valid name.
		if (trim($this->title) == '')
		{
			throw new UnexpectedValueException(sprintf('The title is empty'));
		}

		$this->title = ucfirst($this->title);

		if (empty($this->alias))
		{
			$this->alias = $this->title;
		}

		$this->alias = JApplication::stringURLSafe($this->alias);
		if (trim(str_replace('-', '', $this->alias)) == '')
		{
			$this->alias = JFactory::getDate()->format("Y-m-d-H-i-s");
		}
	}
	/**
	 * Overriden JTable::store.
	 *
	 * @param   boolean  True to update fields even if they are null.
	 * @return  boolean  True on success.
	 * @since   3.1
	 */
	public function store($updateNulls = false)
	{
		// Verify that the alias is unique
		$table = JTable::getInstance('Contenttype', 'Table');
		if ($table->load(array('alias' => $this->alias)) && ($table->id != $this->id || $this->id == 0))
		{
			$this->setError(JText::_('COM_TAGS_ERROR_UNIQUE_ALIAS'));
			return false;
		}
	}
}
