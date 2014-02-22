<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Table class supporting modified pre-order tree traversal behavior.
 *
 * @package     Joomla.Platform
 * @subpackage  Table
 * @link        http://docs.joomla.org/JTableAsset
 * @since       11.1
 */
class JTableAsset extends JTableNested
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
	 * @param   JDatabaseDriver  $db  Database driver object.
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
		$query = $this->_db->getQuery(true)
			->select($this->_db->quoteName('id'))
			->from($this->_db->quoteName('#__assets'))
			->where($this->_db->quoteName('name') . ' = ' . $this->_db->quote($name));
		$this->_db->setQuery($query);
		$assetId = (int) $this->_db->loadResult();

		if (empty($assetId))
		{
			return false;
		}

		return $this->load($assetId);
	}

	/**
	 * Assert that the nested set data is valid.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @link    http://docs.joomla.org/JTable/check
	 * @since   11.1
	 */
	public function check()
	{
		$this->parent_id = (int) $this->parent_id;

		// JTableNested does not allow parent_id = 0, override this.
		if ($this->parent_id > 0)
		{
			// Get the JDatabaseQuery object
			$query = $this->_db->getQuery(true)
				->select('COUNT(id)')
				->from($this->_db->quoteName($this->_tbl))
				->where($this->_db->quoteName('id') . ' = ' . $this->parent_id);
			$this->_db->setQuery($query);

			if ($this->_db->loadResult())
			{
				return true;
			}
			else
			{
				$this->setError('Invalid Parent ID');

				return false;
			}
		}

		return true;
	}
}
