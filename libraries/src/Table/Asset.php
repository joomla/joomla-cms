<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

defined('JPATH_PLATFORM') or die;

/**
 * Table class supporting modified pre-order tree traversal behavior.
 *
 * @since  11.1
 */
class Asset extends Nested
{
	/**
	 * The primary key of the asset.
	 *
	 * @var    integer
	 * @since  11.1
	 */
	public $id = null;

	/**
	 * The unique name of the asset.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $name = null;

	/**
	 * The human readable title of the asset.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $title = null;

	/**
	 * The rules for the asset stored in a JSON string
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $rules = null;

	/**
	 * Constructor
	 *
	 * @param   \JDatabaseDriver  $db  Database driver object.
	 *
	 * @since   11.1
	 */
	public function __construct($db)
	{
		parent::__construct('#__assets', 'id', $db);
	}

	/**
	 * Method to load an asset by its name.
	 *
	 * @param   string  $name  The name of the asset.
	 *
	 * @return  integer
	 *
	 * @since   11.1
	 */
	public function loadByName($name)
	{
		return $this->load(array('name' => $name));
	}

	/**
	 * Assert that the nested set data is valid.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @since   11.1
	 */
	public function check()
	{
		$this->parent_id = (int) $this->parent_id;

		if (empty($this->rules))
		{
			$this->rules = '{}';
		}

		// Nested does not allow parent_id = 0, override this.
		if ($this->parent_id > 0)
		{
			// Get the \JDatabaseQuery object
			$query = $this->_db->getQuery(true)
				->select('1')
				->from($this->_db->quoteName($this->_tbl))
				->where($this->_db->quoteName('id') . ' = ' . $this->parent_id);

			if ($this->_db->setQuery($query, 0, 1)->loadResult())
			{
				return true;
			}

			$this->setError('Invalid Parent ID');

			return false;
		}

		return true;
	}
}
