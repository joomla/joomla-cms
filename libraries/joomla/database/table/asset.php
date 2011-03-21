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
 * @package		Joomla.Platform
 * @subpackage	Database
 * @since		11.1
 * @link		http://docs.joomla.org/JTableAsset
 */
class JTableAsset extends JTableNested
{
	/**
	 * The primary key of the asset.
	 *
	 * @var int
	 */
	public $id = null;

	/**
	 * The unique name of the asset.
	 *
	 * @var string
	 */
	public $name = null;

	/**
	 * The human readable title of the asset.
	 *
	 * @var string
	 */
	public $title = null;

	/**
	 * @var	string
	 */
	public $rules = null;

	/**
	 * @param database A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__assets', 'id', $db);
	}

	/**
	 * Method to load an asset by it's name.
	 *
	 * @param	string	The name of the asset.
	 *
	 * @return	int
	 */
	public function loadByName($name)
	{
		// Get the asset id for the asset.
		$this->_db->setQuery(
			'SELECT `id`' .
			' FROM `#__assets`' .
			' WHERE `name` = '.$this->_db->Quote($name)
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
	 * @return	boolean	True if the instance is sane and able to be stored in the database.
	 * @since	1.0
	 * @link	http://docs.joomla.org/JTable/check
	 */
	public function check()
	{
		$this->parent_id = (int) $this->parent_id;

		// JTableNested does not allow parent_id = 0, override this.
		if ($this->parent_id > 0)
		{
			$this->_db->setQuery(
				'SELECT COUNT(id)' .
				' FROM '.$this->_db->nameQuote($this->_tbl).
				' WHERE `id` = '.$this->parent_id
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
