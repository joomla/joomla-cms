<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Tags table
 *
 * @package     Joomla.Libraries
 * @subpackage  Table
 * @since       3.1
 */
class JTableContenttype extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  A database connector object
	 *
	 * @since   3.1
	 */
	public function __construct($db)
	{
		parent::__construct('#__content_types', 'type_id', $db);
	}

	/**
	 * Overloaded check method to ensure data integrity.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.1
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
			$this->alias = strtolower($this->title);
		}

		$this->alias = JApplication::stringURLSafe($this->alias);
		if (trim(str_replace('-', '', $this->alias)) == '')
		{
			$this->alias = JFactory::getDate()->format("Y-m-d-H-i-s");
		}
	}

	/**
	 * Overridden JTable::store.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
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

		return parent::store($updateNulls);
	}

	/**
	 * Method to expand the field mapping
	 *
	 * @param   boolean  $assoc  True to return an associative array.
	 *
	 * @return  mixed  Array or object with field mappings. Defaults to object.
	 *
	 * @since   3.1
	 */
	public function fieldmapExpand($assoc = true)
	{
		return $this->fieldmap = json_decode($this->fieldmappings, $assoc);
	}
}
