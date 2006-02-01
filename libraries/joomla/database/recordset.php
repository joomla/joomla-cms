<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.common.base.object' );

/**
 * Simple Record Set object to allow our database connector to be used with
 * ADODB driven 3rd party libraries
 *
 * @package		Joomla.Framework
 * @subpackage	Database
 * @since		1.1
 */
class JSimpleRecordSet
{
	/** @var array */
	var $data	= null;
	/** @var int Index to current record */
	var $pointer= null;
	/** @var int The number of rows of data */
	var $count	= null;

	/**
	 * Constuctor
	 * @param array
	 */
	function JSimpleRecordSet( $data ) {
		$this->data = $data;
		$this->pointer = 0;
		$this->count = count( $data );
	}
	/**
	 * @return int
	 */
	function RecordCount() {
		return $this->count;
	}
	/**
	 * @return mixed A row from the data array or null
	 */
	function FetchRow() {
		if ($this->pointer < $this->count) {
			$result = $this->data[$this->pointer];
			$this->pointer++;
			return $result;
		} else {
			return null;
		}
	}
	/**
	 * @return array
	 */
	function GetRows() {
		return $this->data;
	}
	/**
	 * TODO: No working
	 */
	function absolutepage() {
		return 1;
	}
	/**
	 * TODO: No working
	 */
	function atfirstpage() {
		return 1;
	}
	/**
	 * TODO: No working
	 */
	function atlastpage() {
		return 1;
	}
	/**
	 * TODO: No working
	 */
	function lastpageno() {
		return 1;
	}
}
?>