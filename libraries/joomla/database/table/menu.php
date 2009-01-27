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
 * Menu table
 *
 * @package 	Joomla.Framework
 * @subpackage		Table
 * @since	1.0
 */
class JTableMenu extends JTable
{
	/** @var int Primary key */
	protected $id					= null;
	/** @var string */
	protected $menutype			= null;
	/** @var string */
	protected $name				= null;
	/** @var string */
	protected $alias				= null;
	/** @var string */
	protected $link				= null;
	/** @var int */
	protected $type				= null;
	/** @var int */
	protected $published			= null;
	/** @var int */
	protected $componentid		= null;
	/** @var int */
	protected $parent				= null;
	/** @var int */
	protected $sublevel			= null;
	/** @var int */
	protected $ordering			= null;
	/** @var boolean */
	protected $checked_out		= 0;
	/** @var datetime */
	protected $checked_out_time	= 0;
	/** @var boolean */
	protected $pollid				= null;
	/** @var string */
	protected $browserNav			= null;
	/** @var int */
	protected $access				= null;
	/** @var int */
	protected $utaccess			= null;
	/** @var string */
	protected $params				= null;
	/** @var int Pre-order tree traversal - left value */
	protected $lft				= null;
	/** @var int Pre-order tree traversal - right value */
	protected $rgt				= null;
	/** @var int */
	protected $home				= null;

	/**
	 * Constructor
	 *
	 * @access protected
	 * @param database A database connector object
	 */
	protected function __construct(&$db) {
		parent::__construct('#__menu', 'id', $db);
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
		if (empty($this->alias)) {
			$this->alias = $this->name;
		}
		$this->alias = JFilterOutput::stringURLSafe($this->alias);
		if (trim(str_replace('-','',$this->alias)) == '') {
			$datenow =& JFactory::getDate();
			$this->alias = $datenow->toFormat("%Y-%m-%d-%H-%M-%S");
		}

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

	public function bind($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}

		return parent::bind($array, $ignore);
	}
}
