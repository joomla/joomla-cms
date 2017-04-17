<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 5/3/14 3:46 PM $
* @package CB\Database\Table
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CB\Database\Table;

use CBLib\Database\Table\Table;

defined('CBLIB') or die();

/**
 * CB\Database\Table\MemberTable Class implementation
 * 
 */
class MemberTable extends Table
{
	/** @var int */
	public $referenceid			=	null;
	/** @var int */
	public $memberid			=	null;
	/** @var int */
	public $accepted			=	null;
	/** @var int */
	public $pending				=	null;
	/** @var string (SQL:Date) */
	public $membersince			=	null;
	/** @var string */
	public $reason				=	null;
	/** @var string */
	public $description			=	null;
	/** @var string */
	public $type				=	null;

	/**
	 * Table name in database
	 * @var string
	 */
	protected $_tbl				=	'#__comprofiler_members';

	/**
	 * Primary key(s) of table
	 * @var string
	 */
	protected $_tbl_key			=	array( 'referenceid' => 'int', 'memberid' => 'int' );
}
