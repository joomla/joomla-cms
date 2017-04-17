<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 5/2/14 8:37 AM $
* @package CB\Database\Table
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CB\Database\Table;

use CBLib\Database\Table\OrderedTable;
use CBLib\Database\Table\TableInterface;

defined('CBLIB') or die();

/**
 * CB\Database\Table\ListTable Class implementation
 * 
 */
class ListTable extends OrderedTable
{
	/** @var int */
	public $listid				=	null;
	/** @var string */
	public $title				=	null;
	/** @var string */
	public $description			=	null;
	/** @var int */
	public $published			=	null;
	/** @var int */
	public $default				=	null;
	/** @var int */
	public $viewaccesslevel		=	null;
	/** @var string */
	public $usergroupids		=	null;
	/** @var int */
	public $ordering			=	null;
	/** @var string */
	public $params				=	null;

	/**
	 * Table name in database
	 * @var string
	 */
	protected $_tbl				=	'#__comprofiler_lists';

	/**
	 * Primary key(s) of table
	 * @var string
	 */
	protected $_tbl_key			=	'listid';

	/**
	 * Ordering keys and for each their ordering groups.
	 * E.g.; array( 'ordering' => array( 'tab' ), 'ordering_registration' => array() )
	 * @var array
	 */
	protected $_orderings		=	array( 'ordering' => array( /* here ordering groups, if any, could be e.g. 'type' */ ) );

	/**
	 * If table key (id) is NULL : inserts a new row
	 * otherwise updates existing row in the database table
	 *
	 * Can be overridden or overloaded by the child class
	 *
	 * @param  boolean  $updateNulls  TRUE: null object variables are also updated, FALSE: not.
	 * @return boolean                TRUE if successful otherwise FALSE
	 *
	 * @throws \RuntimeException
	 */
	public function store( $updateNulls = false )
	{
		$k				=	$this->_tbl_key;

		if( $this->default == 1 ) {
			// Ensures maximum 1 default entry: If this field is the new default field, set default to 0 to any other row which was default:
			$sql		=	'UPDATE '	. $this->_db->NameQuote( $this->_tbl )
						.	' SET '		. $this->_db->NameQuote( 'default' ) . ' = 0'
						.	"\n WHERE "	. $this->_db->NameQuote( 'default' ) . ' <> 0';
			if ( $this->$k !== null ) {
				// existing record, avoid changing it:
				$sql	.=	' AND '		. $this->_db->NameQuote( $this->_tbl_key ) . ' <> ' . (int) $this->$k;
			}
			$this->_db->query( $sql );
		}

		// Fix HTML-editor messy addition of <p> or <div> in description which messes with translation keys:
		$this->description		=	$this->cleanEditorsTranslationJunk( $this->description );

		// Parent handles ordering of new record without any ordering by giving it highest ordering (below 10000 which is our ordering limit):
		return parent::store( $updateNulls );
	}

	/**
	 * Copies this record (no checks) (tab and its fields)
	 *
	 * @param  null|TableInterface|self  $object  The object being copied otherwise create new object and add $this
	 * @return self|boolean                       OBJECT: The new object copied successfully, FALSE: Failed to copy
	 */
	public function copy( $object = null ) {
		if ( $object === null ) {
			$object					=	clone $this;
		}

		// TODO: This algorithm below to determine the new name could be factored out as reusable:

		// Grab index of list from lists with same title
		$query						=	'SELECT ' . $this->_db->NameQuote( 'title' )
									.	"\n FROM " . $this->_db->NameQuote( $this->_tbl )
									.	"\n WHERE " . $this->_db->NameQuote( 'title' ) . " REGEXP " . $this->_db->Quote( '^' . preg_quote( $object->title ) . '[0-9]*$' )
									.	"\n ORDER BY " . $this->_db->NameQuote( 'title' );
		$this->_db->setQuery( $query );
		$titles						=	$this->_db->loadResultArray();
		$count						=	count( $titles );

		// Only increment if there's something to increment as the title could be changed before copy is called, which would be a 0 count:
		if ( $count ) {
			// Increment index by 1 based off similar list title count:
			$index					=	( $count + 1 );

			// Loop through and make sure the index is unique; if not keep incrementing until it is:
			do {
				$changed			=	false;

				foreach ( $titles as $v ) {
					if ( $v == ( $object->title . $index ) ) {
						$index++;

						$changed	=	true;
					}
				}
			} while ( $changed );

			$object->title			=	$object->title . ' (' . $index . ')';
		}

		return parent::copy( $object );
	}
}
