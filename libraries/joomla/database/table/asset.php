<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.database.tablenested');

/**
 * Table class supporting modified pre-order tree traversal behavior.
 *
 * @package     Joomla.Platform
 * @subpackage  Database
 * @link        http://docs.joomla.org/JTableAsset
 * @since       11.1
 */
class JTableAsset extends JTableNested
{
	/**
	 * The primary key of the asset.
	 *
	 * @var     integer
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
	 * @var string
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
	 * @param  database  $db  A database connector object
	 *
	 * @return  JTableAsset
	 *
	 * @since  11.1
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__assets', 'id', $db);
	}

	/**
	 * Method to load an asset by it's name.
	 *
	 * @param   string  $name  The name of the asset.
	 *
	 * @return  integer
	 *
	 * @since   11.1
	 */
	public function loadByName($name)
	{
		// Get the asset id for the asset.
		$this->_db->setQuery(
			'SELECT '.$this->_db->quoteName('id') .
			' FROM '.$this->_db->quoteName('#__assets') .
			' WHERE '.$this->_db->quoteName('name').' = '.$this->_db->Quote($name)
		);
		$assetId = (int) $this->_db->loadResult();
		if (empty($assetId)) {
			return false;
		}
		// Check for a database error.
		if ($error = $this->_db->getErrorMsg())
		{
			$this->setError($error);
			return false;
		}
		return $this->load($assetId);
	}

	/**
	 * Asset that the nested set data is valid.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @link	http://docs.joomla.org/JTable/check
	 * @since   11.1
	 */
	public function check()
	{
		$this->parent_id = (int) $this->parent_id;

		// JTableNested does not allow parent_id = 0, override this.
		if ($this->parent_id > 0)
		{
			$this->_db->setQuery(
				'SELECT COUNT(id)' .
				' FROM '.$this->_db->quoteName($this->_tbl).
				' WHERE '.$this->_db->quoteName('id').' = '.$this->parent_id
			);
			if ($this->_db->loadResult()) {
				return true;
			}
			else
			{
				if ($error = $this->_db->getErrorMsg()) {
					$this->setError($error);
				}
				else {
					$this->setError(JText::_('JLIB_DATABASE_ERROR_INVALID_PARENT_ID'));
				}
				return false;
			}
		}

		return true;
	}
}