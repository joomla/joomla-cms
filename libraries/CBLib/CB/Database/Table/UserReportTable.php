<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 5/3/14 3:32 PM $
* @package CB\Database\Table
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CB\Database\Table;

use CBLib\Database\Table\Table;

defined('CBLIB') or die();

/**
 * CB\Database\Table\UserReportTable Class implementation
 * 
 */
class UserReportTable extends Table
{
	/** @var int */
	public $reportid			=	null;
	/** @var int */
	public $reporteduser		=	null;
	/** @var int */
	public $reportedbyuser		=	null;
	/** @var string (SQL:Date) */
	public $reportedondate		=	null;
	/** @var string */
	public $reportexplaination	=	null;
	/** @var int */
	public $reportedstatus		=	null;

	/**
	 * Table name in database
	 * @var string
	 */
	protected $_tbl				=	'#__comprofiler_userreports';

	/**
	 * Primary key(s) of table
	 * @var string
	 */
	protected $_tbl_key			=	'reportid';

	/**
	 * Deletes all user reports from that user and for that user (called on user delete)
	 *
	 * @param  int     $userId  User id from whom to delete all reports
	 * @return boolean          true if ok, false with warning on sql error
	 */
	function deleteUserReports( $userId ) {
		$sql	=	'DELETE FROM ' . $this->_db->NameQuote( $this->_tbl )
				.	"\n WHERE " . $this->_db->NameQuote( 'reporteduser' ) . ' = ' . (int) $userId
				.	' OR ' . $this->_db->NameQuote( 'reportedbyuser' ) . ' = ' . (int) $userId;

		if ( ! $this->_db->query( $sql ) ) {
			$this->_error	=	'SQL error' . $this->_db->stderr(true);
			return false;
		}
		return true;
	}
}
