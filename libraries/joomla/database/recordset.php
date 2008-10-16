<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Database
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Simple Record Set object to allow our database connector to be used with
 * ADODB driven 3rd party libraries
 *
 * @package		Joomla.Framework
 * @subpackage	Database
 * @since		1.5
 */
class JRecordSet
{
	/** @var array */
	public $data = null;
	/** @var int Index to current record */
	public $pointer = null;
	/** @var int The number of rows of data */
	public $count = null;

	/**
	 * Constuctor
	 * @param array
	 */
	public function __construct( $data )
	{
		$this->data = $data;
		$this->pointer = 0;
		$this->count = count( $data );
	}
	/**
	 * @return int
	 */
	public function RecordCount() {
		return $this->count;
	}
	/**
	 * @return mixed A row from the data array or null
	 */
	public function FetchRow()
	{
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
	public function GetRows() {
		return $this->data;
	}
}
