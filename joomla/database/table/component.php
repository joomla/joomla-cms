<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Component table
 *
 * @package 	Joomla.Framework
 * @subpackage	Table
 * @since		1.0
 */
class JTableComponent extends JTable
{
	/** @var int Primary key */
	var $id					= null;
	/** @var string */
	var $name				= null;
	/** @var string */
	var $link				= null;
	/** @var int */
	var $menuid				= null;
	/** @var int */
	var $parent				= null;
	/** @var string */
	var $admin_menu_link	= null;
	/** @var string */
	var $admin_menu_alt		= null;
	/** @var string */
	var $option				= null;
	/** @var string */
	var $ordering			= null;
	/** @var string */
	var $admin_menu_img		= null;
	/** @var int */
	var $iscore				= null;
	/** @var string */
	var $params				= null;
	/** @var int */
	var $enabled			= null;

	/**
	 * @param database A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__components', 'id', $db);
	}

	/**
	 * Loads a data row by option.
	 *
	 * @param	string	The component option value.
	 * @return	boolean
	 */
	public function loadByOption($option)
	{
		$db = &$this->getDbo();
		$query = 'SELECT id' .
				' FROM #__components' .
				' WHERE ' . $db->nameQuote('option') . '=' . $db->Quote($option) .
				' AND parent = 0';
		$db->setQuery($query, 0, 1);
		$id = $db->loadResult();

		if (empty($id)) {
			return false;
		}
		else {
			return $this->load($id);
		}
	}

	/**
	 * Validate and filter fields
	 */
	function check()
	{
		$this->parent = intval($this->parent);
		$this->ordering = intval($this->ordering);
		return true;
	}

	/**
	 * Overloaded bind function
	 *
	 * @param	array $hash	named array
	 *
	 * @return	null|string	null is operation was satisfactory, otherwise returns an error
	 * @see		JTable:bind
	 * @since	1.5
	*/
	public function bind($array, $ignore = '')
	{
		if (array_key_exists('params', $array) && is_array($array['params']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}
		return parent::bind($array, $ignore);
	}
}
