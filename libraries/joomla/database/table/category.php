<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Table
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// No direct access
defined('JPATH_BASE') or die();

/**
 * Category table
 *
 * @package 	Joomla.Framework
 * @subpackage		Table
 * @since	1.0
 */
class JTableCategory extends JTableAsset
{
	/** @var int Primary key */
	protected $id					= null;
	protected $lft					= null;
	protected $rgt					= null;
	protected $ref_id				= null;
	protected $parent_id			= null;
	/** @var int */
	protected $extension			= null;
	protected $lang					= null;
	/** @var string The menu title for the category (a short name)*/
	protected $title				= null;
	/** @var string The the alias for the category*/
	protected $alias				= null;
	/** @var string */
	protected $description			= null;
	/** @var boolean */
	protected $published			= null;
	/** @var boolean */
	protected $checked_out			= 0;
	/** @var time */
	protected $checked_out_time		= 0;
	/** @var int */
	protected $access				= null;
	/** @var string */
	protected $params				= null;

	/**
	* @param database A database connector object
	*/
	public function __construct(&$db)
	{
		parent::__construct('#__categories', 'id', $db);
	}

	protected function getAssetSection()
	{
		return $this->extension;
	}
	
	protected function getAssetNamePrefix()
	{
		return 'category';
	}
	
	protected function getAssetTitle()
	{
		return $this->title;
	}
	
	/**
	 * Overloaded check function
	 *
	 * @access public
	 * @return boolean
	 * @see JTable::check
	 * @since 1.5
	 */
	public function check()
	{
		// check for valid name
		if (trim($this->title) == '') {
			$this->setError(JText::sprintf('must contain a title', JText::_('Category')));
			return false;
		}

		if (empty($this->alias)) {
			$this->alias = $this->title;
		}
		$this->alias = JFilterOutput::stringURLSafe($this->alias);
		if (trim(str_replace('-','',$this->alias)) == '') {
			$datenow =& JFactory::getDate();
			$this->alias = $datenow->toFormat("%Y-%m-%d-%H-%M-%S");
		}

		return true;
	}
	
	public function move($dirn, $where='')
	{
		if($dirn > 0)
			$query = 'SELECT lft, rgt FROM #__categories WHERE ';
		
		$k = $this->_tbl_key;

		$sql = "SELECT $this->_tbl_key, ordering FROM $this->_tbl";

		if ($dirn < 0)
		{
			$sql .= ' WHERE ordering < '.(int) $this->ordering;
			$sql .= ($where ? ' AND '.$where : '');
			$sql .= ' ORDER BY ordering DESC';
		}
		else if ($dirn > 0)
		{
			$sql .= ' WHERE ordering > '.(int) $this->ordering;
			$sql .= ($where ? ' AND '. $where : '');
			$sql .= ' ORDER BY ordering';
		}
		else
		{
			$sql .= ' WHERE ordering = '.(int) $this->ordering;
			$sql .= ($where ? ' AND '.$where : '');
			$sql .= ' ORDER BY ordering';
		}

		$this->_db->setQuery($sql, 0, 1);

		try {
			$row = $this->_db->loadObject();
			if (!empty($row))
			{
				$query = 'UPDATE '. $this->_tbl
				. ' SET ordering = '. (int) $row->ordering
				. ' WHERE '. $this->_tbl_key .' = '. $this->_db->Quote($this->$k)
				;
				$this->_db->setQuery($query);

				$this->_db->query();

				$query = 'UPDATE '.$this->_tbl
				. ' SET ordering = '.(int) $this->ordering
				. ' WHERE '.$this->_tbl_key.' = '.$this->_db->Quote($row->$k)
				;
				$this->_db->setQuery($query);

				$this->_db->query();

				$this->ordering = $row->ordering;
			}
			else
			{
					$query = 'UPDATE '. $this->_tbl
				. ' SET ordering = '.(int) $this->ordering
				. ' WHERE '. $this->_tbl_key .' = '. $this->_db->Quote($this->$k)
				;
				$this->_db->setQuery($query);

				$this->_db->query();
			}
		} catch(JException $e) {
			$this->setError($e->getMessage());
			return false;
		}
	}
}
