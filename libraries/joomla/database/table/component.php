<?php
/**
 * @version		$Id: component.php 10381 2008-06-01 03:35:53Z pasamio $
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
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
	function __construct(&$db)
	{
		parent::__construct('#__components', 'id', $db);
	}

	/**
	 * Loads a data row by option
	 *
	 * @param string The component option value
	 * @return boolean
	 */
	function loadByOption($option)
	{
		$db = &$this->getDbo();
		$query = 'SELECT id' .
				' FROM #__components' .
				' WHERE ' . $db->nameQuote('option') . '=' . $db->Quote($option) .
				' AND parent = 0';
		$db->setQuery($query, 0, 1);
		$id = $db->loadResult();

		if ($id === null) {
			return false;
		} else {
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
	* @access public
	* @param array $hash named array
	* @return null|string	null is operation was satisfactory, otherwise returns an error
	* @see JTable:bind
	* @since 1.5
	*/
	function bind($array, $ignore = '')
	{
		if (is_array($array['params']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}

		return parent::bind($array, $ignore);
	}
}
