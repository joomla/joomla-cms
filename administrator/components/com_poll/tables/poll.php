<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Polls
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
* @package		Joomla
* @subpackage	Polls
*/
class TablePoll extends JTable
{
	/** @var int Primary key */
	var $id					= 0;
	/** @var string */
	var $title				= '';
	/** @var string */
	var $alias				= '';
	/** @var string */
	var $checked_out		= 0;
	/** @var time */
	var $checked_out_time	= 0;
	/** @var boolean */
	var $published			= 0;
	/** @var int */
	var $access				= 0;
	/** @var int */
	var $lag				= 0;

	/**
	* @param database A database connector object
	*/
	function __construct( &$db )
	{
		parent::__construct( '#__polls', 'id', $db );
	}

	/**
	 * Binds an array to the object
	 * @param 	array	Named array
	 * @param 	string	Space separated list of fields not to bind
	 * @return	boolean
	 */
	function bind( $array, $ignore='' )
	{
		$result = parent::bind( $array );
		// cast properties
		$this->id	= (int) $this->id;

		return $result;
	}

	/**
	 * Overloaded check function
	 *
	 * @access public
	 * @return boolean
	 * @see JTable::check
	 * @since 1.5
	 */
	function check()
	{
		// check for valid name
		if (trim( $this->title ) == '')
		{
			$this->setError(JText::_( 'Your Poll must contain a title.' ));
			return false;
		}
		// check for valid lag
		$this->lag = intval( $this->lag );
		if ($this->lag == 0) {
			$this->setError(JText::_( 'Your Poll must have a non-zero lag time.' ));
			return false;
		}

		$alias = JFilterOutput::stringURLSafe($this->title);

		if(empty($this->alias) || $this->alias === $alias ) {
			$this->alias = $alias;
		}

		return true;
	}

	// overloaded delete function
	function delete( $oid=null )
	{
		$k = $this->_tbl_key;
		if ( $oid ) {
			$this->$k = intval( $oid );
		}

		if ( parent::delete( $oid ))
		{
			$query = 'DELETE FROM #__poll_data'
			. ' WHERE pollid = '.(int) $this->$k
			;
			$this->_db->setQuery( $query );
			if ( !$this->_db->query() ) {
				$this->_error .= $this->_db->getErrorMsg() . "\n";
			}

			$query = 'DELETE FROM #__poll_date'
			. ' WHERE poll_id = '.(int) $this->$k
			;
			$this->_db->setQuery( $query );
			if ( !$this->_db->query() ) {
				$this->_error .= $this->_db->getErrorMsg() . "\n";
			}

			$query = 'DELETE from #__poll_menu'
			. ' WHERE pollid = '.(int) $this->$k
			;
			$this->_db->setQuery( $query );
			if ( !$this->_db->query() ) {
				$this->_error .= $this->_db->getErrorMsg() . "\n";
			}

			return true;
		}

		return false;
	}
}