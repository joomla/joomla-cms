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
 * CB\Database\Table\UserViewTable Class implementation
 * 
 */
class UserViewTable extends Table
{
	/** @var int */
	public $viewer_id		=	null;
	/** @var int */
	public $profile_id		=	null;
	/** @var string */
	public $lastip			=	null;
	/** @var string (SQL:Date) */
	public $lastview		=	null;
	/** @var int */
	public $viewscount		=	null;
	/** @var int */
	public $vote			=	null;
	/** @var string (SQL:Date) */
	public $lastvote		=	null;

	/**
	 * Table name in database
	 * @var string
	 */
	protected $_tbl			=	'#__comprofiler_views';

	/**
	 * Primary key(s) of table
	 * @var array
	 */
	protected $_tbl_key		=	array( 'viewer_id', 'profile_id', 'lastip' );

	/**
	 * Deletes all user views from that user and for that user (called on user delete)
	 *
	 * @param  int     $userId  User id from whom to delete all views
	 * @return boolean          true if ok, false with warning on sql error
	 */
	function deleteUserViews( $userId ) {
		$sql	=	'DELETE FROM ' . $this->_db->NameQuote( $this->_tbl )
				.	"\n WHERE " . $this->_db->NameQuote( 'viewer_id' ) . ' = ' . (int) $userId
				.	' OR ' . $this->_db->NameQuote( 'profile_id' ) . ' = ' . (int) $userId;

		if ( ! $this->_db->query( $sql ) ) {
			$this->setError( 'SQL error' . $this->_db->getErrorMsg() );

			return false;
		}

		return true;
	}
}
