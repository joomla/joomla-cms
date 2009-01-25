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
 * Section table
 *
 * @package 	Joomla.Framework
 * @subpackage		Table
 * @since	1.0
 */
class JTableSection extends JTable
{
	/** @var int Primary key */
	protected $id					= null;
	/** @var string The menu title for the section (a short name)*/
	protected $title				= null;
	/** @var string The full name for the section*/
	protected $name				= null;
	/** @var string The alias for the section*/
	protected $alias				= null;
	/** @var string */
	protected $image				= null;
	/** @var string */
	protected $scope				= null;
	/** @var int */
	protected $image_position		= null;
	/** @var string */
	protected $description		= null;
	/** @var boolean */
	protected $published			= null;
	/** @var boolean */
	protected $checked_out		= 0;
	/** @var time */
	protected $checked_out_time	= 0;
	/** @var int */
	protected $ordering			= null;
	/** @var int */
	protected $access				= null;
	/** @var string */
	protected $params				= null;

	/**
	* @param database A database connector object
	*/
	protected function __construct( &$db ) {
		parent::__construct( '#__sections', 'id', $db );
	}

	/** Overloaded check function
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
			$this->setError( JText::_( 'SECTION MUST HAVE A TITLE') );
			return false;
		}

		// check for existing name
		/*$query = "SELECT id"
		. ' FROM #__sections "
		. ' WHERE title = '. $this->_db->Quote($this->title)
		. ' AND scope = ' . $this->_db->Quote($this->scope)
		;
		$this->_db->setQuery( $query );

		$xid = intval( $this->_db->loadResult() );
		if ($xid && $xid != intval( $this->id )) {
			$this->_error = JText::sprintf( 'WARNNAMETRYAGAIN', JText::_( 'Section') );
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
		if (isset( $array['params'] ) && is_array($array['params']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}

		return parent::bind($array, $ignore);
	}
}
