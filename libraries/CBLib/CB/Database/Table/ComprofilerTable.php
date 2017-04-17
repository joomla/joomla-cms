<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 5/4/14 12:48 AM $
* @package CB\Database\Table
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CB\Database\Table;

use CBLib\Database\Table\Table;

defined('CBLIB') or die();

/**
 * CB\Database\Table\ComprofilerTable Class implementation
 * 
 */
class ComprofilerTable extends Table
{
	/** @var int */
	public $id						=	null;
	/** @var int */
	public $user_id					=	null;
	/** @var string */
	public $firstname				=	null;
	/** @var string */
	public $middlename				=	null;
	/** @var string */
	public $lastname				=	null;
	/** @var int */
	public $hits					=	null;
	/** @var string (SQL:Date) */
	public $message_last_sent		=	null;
	/** @var int */
	public $message_number_sent		=	null;
	/** @var string */
	public $avatar					=	null;
	/** @var int */
	public $avatarapproved			=	null;
	/** @var int */
	public $approved				=	null;
	/** @var int */
	public $confirmed				=	null;
	/** @var string (SQL:Date) */
	public $lastupdate				=	null;
	/** @var string */
	public $registeripaddr			=	null;
	/** @var string */
	public $cbactivation			=	null;
	/** @var int */
	public $banned					=	null;
	/** @var string (SQL:Date) */
	public $banneddate				=	null;
	/** @var string (SQL:Date) */
	public $unbanneddate			=	null;
	/** @var int */
	public $bannedby				=	null;
	/** @var int */
	public $unbannedby				=	null;
	/** @var string */
	public $bannedreason			=	null;
	/** @var int */
	public $acceptedterms			=	null;

	/**
	 * Table name in database
	 * @var string
	 */
	protected $_tbl				=	'#__comprofiler';

	/**
	 * Primary key(s) of table
	 * @var string
	 */
	protected $_tbl_key			=	'id';

	/**
	 * Inserts a new row in the database table
	 * Only for use by UserTable !
	 *
	 * @return boolean  TRUE if successful otherwise FALSE
	 */
	public function storeNew() {
		$ok					=	$this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key );
		if ( ! $ok ) {
			$this->_error	=	strtolower(get_class($this))."::storeNew failed: " . $this->_db->getErrorMsg();
		}
		return $ok;
	}
}
