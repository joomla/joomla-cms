<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Polls
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package Joomla
* @subpackage Polls
*/
class JModelPolls {

	var $dbo = null;

	function JModelPolls( &$dbo )
	{
		$this->_db = &$dbo;
	}

	function getDBO() {
		return $this->_db;
	}

	/**
	 * Add vote
	 * @param int The id of the poll
	 * @param int The id of the option selected
	 */
	function addVote( $poll_id, $option_id )
	{
		$db = $this->getDBO();
		$poll_id	= (int) $poll_id;
		$option_id	= (int) $option_id;
		
		$query = 'UPDATE #__poll_data'
			. ' SET hits = hits + 1'
			. ' WHERE pollid = ' . (int) $poll_id
			. ' AND id = ' . (int) $option_id
			;
		$db->setQuery( $query );
		$db->query();
	
		$query = 'UPDATE #__polls'
			. ' SET voters = voters + 1'
			. ' WHERE id = ' . $poll_id
			;
		$db->setQuery( $query );
		$db->query();

		$query = 'INSERT INTO #__poll_date'
			. ' SET date = NOW(), vote_id = '. $option_id . ', poll_id = ' . $poll_id
		;
		$db->setQuery( $query );
		$db->query();
	}
}


/**
* @package Joomla
* @subpackage Polls
*/
class mosPoll extends JTable {
	/** @var int Primary key */
	var $id					= null;
	/** @var string */
	var $title				= null;
	/** @var string */
	var $checked_out		= null;
	/** @var time */
	var $checked_out_time	= null;
	/** @var boolean */
	var $published			= null;
	/** @var int */
	var $access				= null;
	/** @var int */
	var $lag				= null;

	/**
	* @param database A database connector object
	*/
	function __construct( &$db ) {
		parent::__construct( '#__polls', 'id', $db );
	}

	// overloaded check function
	function check() {

		// check for valid name
		if (trim( $this->title ) == '') {
			$this->_error = JText::_( 'Your Poll must contain a title.' );
			return false;
		}
		// check for valid lag
		$this->lag = intval( $this->lag );
		if ($this->lag == 0) {
			$this->_error = JText::_( 'Your Poll must have a non-zero lag time.' );
			return false;
		}
		// check for existing title
		$query = "SELECT id"
		. "\n FROM #__polls"
		. "\n WHERE title = '$this->title'"
		;
		$this->_db->setQuery( $query );

		$xid = intval( $this->_db->loadResult() );
		if ( $xid && $xid != intval( $this->id ) ) {
			$this->_error = sprintf( JText::_( 'WARNNAMETRYAGAIN' ), JText::_( 'Module') );
			return false;
		}

		return true;
	}

	// overloaded delete function
	function delete( $oid=null ) {
		$k = $this->_tbl_key;
		if ( $oid ) {
			$this->$k = intval( $oid );
		}

		if (mosDBTable::delete( $oid )) {
			$query = "DELETE FROM #__poll_data"
			. "\n WHERE pollid = ". $this->$k
			;
			$this->_db->setQuery( $query );
			if ( !$this->_db->query() ) {
				$this->_error .= $this->_db->getErrorMsg() . "\n";
			}

			$query = "DELETE FROM #__poll_date"
			. "\n WHERE pollid = ". $this->$k
			;
			$this->_db->setQuery( $query );
			if ( !$this->_db->query() ) {
				$this->_error .= $this->_db->getErrorMsg() . "\n";
			}

			$query = "DELETE from #__poll_menu"
			. "\n WHERE pollid = ". $this->$k
			;
			$this->_db->setQuery( $query );
			if ( !$this->_db->query() ) {
				$this->_error .= $this->_db->getErrorMsg() . "\n";
			}

			return true;
		} else {
			return false;
		}
	}
}
?>
