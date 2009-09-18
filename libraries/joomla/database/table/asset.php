<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Database
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.database.tablenested');

/**
 * Table class supporting modified pre-order tree traversal behavior.
 *
 * @package		Joomla.Framework
 * @subpackage	Database
 * @since		1.6
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
	 * @param database A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__access_assets', 'id', $db);
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
			' FROM `#__access_assets`' .
			' WHERE `name` = '.$this->_db->Quote($name)
		);
		$assetId = (int) $this->_db->loadResult();

		// Check for a database error.
		if ($error = $this->_db->getErrorMsg())
		{
			$this->setError($error);
			return false;
		}

		return $this->load($assetId);
	}
}
