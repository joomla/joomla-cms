<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 5/3/14 1:13 AM $
* @package CB\Database\Table
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CB\Database\Table;

use CBLib\Database\Table\OrderedTable;
use CBLib\Database\Table\TableInterface;
use CBLib\Language\CBTxt;

defined('CBLIB') or die();

/**
 * CB\Database\Table\TabTable Class implementation
 * 
 */
class TabTable extends OrderedTable
{
	/** @var int */
	public $tabid				=	null;
	/** @var string */
	public $title				=	null;
	/** @var string */
	public $description			=	null;
	/** @var int */
	public $ordering			=	null;
	/** @var int */
	public $ordering_register	=	null;
	/** @var string */
	public $width				=	null;
	/** @var int */
	public $enabled				=	null;
	/** @var string */
	public $pluginclass			=	null;
	/** @var int */
	public $pluginid			=	null;
	/** @var int */
	public $fields				=	null;
	/** @var string */
	public $params				=	null;
	/**
	 * system tab: >=1: from comprofiler core: can't be deleted. ==2: always enabled. ==3: collecting element (menu+status): rendered at end.
	 * @var int
	 */
	public $sys					=	null;
	/** @var string */
	public $displaytype			=	null;
	/** @var string */
	public $position			=	null;
	/** @var int */
	public $viewaccesslevel		=	null;
	/** @var string */
	public $cssclass			=	null;

	/**
	 * Table name in database
	 * @var string
	 */
	protected $_tbl				=	'#__comprofiler_tabs';

	/**
	 * Primary key(s) of table
	 * @var string
	 */
	protected $_tbl_key			=	'tabid';

	/**
	 * Ordering keys and for each their ordering groups.
	 * E.g.; array( 'ordering' => array( 'tab' ), 'ordering_registration' => array() )
	 * @var array
	 */
	protected $_orderings		=	array( 'ordering' => array( 'position' ), 'ordering_register' => array() );

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
		// Fix HTML-editor messy addition of <p> or <div> in description which messes with translation keys:
		$this->description		=	$this->cleanEditorsTranslationJunk( $this->description );

		return parent::store( $updateNulls );
	}

	/**
	 *	Check values before store method  (override if needed)
	 *
	 *	@return boolean  TRUE if the object is safe for saving
	 */
	function check() {
		if ( ! $this->title ) {
			$this->_error		=	CBTxt::T( 'Title missing!' );

			return false;
		}

		return parent::check();
	}

	/**
	 * Check for whether dependencies exist for this object in the db schema
	 *
	 * @param  null|self  $object  The object being copied to otherwise $this
	 * @return boolean             True: Can Copy, False: Cannot Copy
	 */
	public function canCopy( $object = null ) {
		if ( $object === null ) {
			$object				=	$this;
		}

		if ( ! $object->tabid ) {
			$object->_error		=	CBTxt::T( 'Select a tab to copy.' );

			return false;
		} elseif ( $object->sys == 1 ) {
			$object->_error		=	CBTxt::T( 'System tabs cannot be copied!' );

			return false;
		} elseif ( $object->pluginid ) {
			$object->_error		=	CBTxt::T( 'Plugin tabs cannot be copied!' );

			return false;
		}

		return true;
	}

	/**
	 * Copies this record (no checks) (tab and its fields)
	 *
	 * @param  null|TableInterface|self  $object  The object being copied otherwise create new object and add $this
	 * @return self|boolean                       OBJECT: The new object copied successfully, FALSE: Failed to copy
	 */
	public function copy( $object = null ) {
		if ( $object === null ) {
			$object						=	clone $this;
		}

		// TODO: This algorithm below to determine the new name could be factored out as reusable:

		// Grab index of tab from tabs with same title
		$query							=	'SELECT ' . $this->_db->NameQuote( 'title' )
										.	"\n FROM " . $this->_db->NameQuote( $this->_tbl )
										.	"\n WHERE " . $this->_db->NameQuote( 'title' ) . " REGEXP " . $this->_db->Quote( '^' . preg_quote( $object->title ) . '[0-9]*$' )
										.	"\n ORDER BY " . $this->_db->NameQuote( 'title' );
		$this->_db->setQuery( $query );
		$titles							=	$this->_db->loadResultArray();
		$count							=	count( $titles );

		// Only increment if there's something to increment as the title could be changed before copy is called, which would be a 0 count:
		if ( $count ) {
			// Increment index by 1 based off similar tab title count:
			$index						=	( $count + 1 );

			// Loop through and make sure the index is unique; if not keep incrementing until it is:
			do {
				$changed				=	false;

				foreach ( $titles as $v ) {
					if ( $v == ( $object->title . $index ) ) {
						$index++;

						$changed		=	true;
					}
				}
			} while ( $changed );

			$object->title				=	$object->title . ' (' . $index . ')';
		}

		// We need to complete the copy before copying the field as we need the new tabid:
		$copied							=	parent::copy( $object );

		if ( $copied ) {
			// Grab the tabs fields and loop through copying them with the new tab:
			$query		=	'SELECT *'
						.	"\n FROM " . $this->_db->NameQuote( '#__comprofiler_fields' )
						.	"\n WHERE " . $this->_db->NameQuote( 'tabid' ) . " = " . (int) $this->tabid;
			$this->_db->setQuery( $query );
			$fields		=	$this->_db->loadObjectList( null, '\CB\Database\Table\FieldTable' );

			foreach ( $fields as $field ) {
				/** @var FieldTable $field */
				if ( $field->canCopy() ) {
					$field->tabid		=	(int) $object->tabid;

					$field->copy();
				}
			}
		}

		return $copied;
	}

	/**
	 * Generic check for whether dependancies exist for this object in the db schema
	 *
	 * @param  int  $oid  key index (only int supported here)
	 * @return boolean
	 */
	function canDelete( $oid = null ) {
		if ( $oid === null ) {
			$k					=	$this->_tbl_key;
			$oid				=	$this->$k;
		}

		if ( $this->sys ) {
			$this->_error		=	CBTxt::T( 'System tabs cannot be deleted!' );

			return false;
		}

		if ( $this->pluginid ) {
			$plugin				=	new PluginTable( $this->_db );

			if ( $plugin->load( $this->pluginid ) ) {
				$this->_error	=	CBTxt::T( 'Plugin tabs cannot be deleted!' );

				return false;
			}
		}

		// Check if tab still has fields:
		$fieldObject			=	new FieldTable( $this->_db );
		if ( $fieldObject->countFieldsOfTab( $oid ) ) {
			$this->_error		=	CBTxt::T( 'Tabs with fields cannot be deleted!' );

			return false;
		}

		return parent::canDelete( $oid );
	}


	/**
	 * XML functions
	 */

	/**
	 * returns true or false if plugin is compatible with current major CB release
	 * Used by Backend XML only
	 * @deprecated Do not use directly, only for XML tabs backend
	 *
	 * @return boolean
	 */
	public function checkPluginCompatibility( ) {
		global $_PLUGINS;
		return $_PLUGINS->checkPluginCompatibility( $this->pluginid );
	}
}
