<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Table
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Category table
 *
 * @package 	Joomla.Framework
 * @subpackage		Table
 * @since	1.0
 */
class JTableCategory extends JTable
{
	/** @var int Primary key */
	protected $id					= null;
	/** @var int */
	protected $parent_id			= null;
	/** @var string The menu title for the category (a short name)*/
	protected $title				= null;
	/** @var string The full name for the category*/
	protected $name				= null;
	/** @var string The the alias for the category*/
	protected $alias				= null;
	/** @var string */
	protected $image				= null;
	/** @var string */
	protected $section				= null;
	/** @var int */
	protected $image_position		= null;
	/** @var string */
	protected $description			= null;
	/** @var boolean */
	protected $published			= null;
	/** @var boolean */
	protected $checked_out			= 0;
	/** @var time */
	protected $checked_out_time		= 0;
	/** @var int */
	protected $ordering			= null;
	/** @var int */
	protected $access				= null;
	/** @var string */
	protected $params				= null;

	/**
	* @param database A database connector object
	*/
	protected function __construct( &$db )
	{
		parent::__construct( '#__categories', 'id', $db );
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
		if (trim( $this->title ) == '') {
			$this->setError(JText::sprintf( 'must contain a title', JText::_( 'Category') ));
			return false;
		}

		// check for existing name
		/*$query = 'SELECT id'
		. ' FROM #__categories '
		. ' WHERE title = '.$this->_db->Quote($this->title)
		. ' AND section = '.$this->_db->Quote($this->section)
		;
		$this->_db->setQuery( $query );

		$xid = intval( $this->_db->loadResult() );
		if ($xid && $xid != intval( $this->id )) {
			$this->_error = JText::sprintf( 'WARNNAMETRYAGAIN', JText::_( 'Category') );
			return false;
		}*/

		if(empty($this->alias)) {
			$this->alias = $this->title;
		}
		$this->alias = JFilterOutput::stringURLSafe($this->alias);
		if(trim(str_replace('-','',$this->alias)) == '') {
			$datenow =& JFactory::getDate();
			$this->alias = $datenow->toFormat("%Y-%m-%d-%H-%M-%S");
		}

		return true;
	}
}
