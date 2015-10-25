<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 5/3/14 12:01 AM $
* @package CB\Database\Table
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CB\Database\Table;

use CBLib\Database\Table\OrderedTable;

defined('CBLIB') or die();

/**
 * CB\Database\Table\FieldValueTable Class implementation
 * 
 */
class FieldValueTable extends OrderedTable
{
	/** @var int */
	public $fieldvalueid	=	null;
	/** @var int */
	public $fieldid			=	null;
	/** @var string */
	public $fieldtitle		=	null;
	/** @var string */
	public $fieldlabel		=	null;
	/** @var int */
	public $ordering		=	null;
	/** @var int */
	public $sys				=	null;

	/**
	 * Table name in database
	 * @var string
	 */
	protected $_tbl			=	'#__comprofiler_field_values';

	/**
	 * Primary key(s) of table
	 * @var string
	 */
	protected $_tbl_key		=	'fieldvalueid';

	/**
	 * Ordering keys and for each their ordering groups.
	 * E.g.; array( 'ordering' => array( 'tab' ), 'ordering_registration' => array() )
	 * @var array
	 */
	protected $_orderings	=	array( 'ordering' => array( 'fieldid' ) );

	/**
	 * Get existing ordered field values for field $fieldId
	 * E.g. so they can be pushed to a field copy
	 *
	 * @param  int  $fieldId
	 * @return FieldValueTable[]
	 */
	public function getFieldValuesOfField( $fieldId )
	{
		$query	=	'SELECT *'
				.	"\n FROM " .	 $this->_db->NameQuote( $this->_tbl )
				.	"\n WHERE " .	 $this->_db->NameQuote( 'fieldid' ) . " = " . (int) $fieldId
				.	"\n ORDER BY " . $this->_db->NameQuote( 'ordering' );
		$this->_db->setQuery( $query );

		return $this->_db->loadObjectList( 'fieldvalueid', '\CB\Database\Table\FieldValueTable', array( $this->_db ) );
	}

	/**
	 * Update all field values for a given $fieldId to match $fieldValues[]
	 *
	 * @param  int    $fieldId      Id of field
	 * @param  array  $fieldValues  New or existing values: ordered array( array( 'fieldtitle' => 'Title of field', 'fieldlabel' => 'Label of field' ) )
	 * @return boolean              Result
	 */
	public function updateFieldValues( $fieldId, array $fieldValues )
	{
		$existingFieldValues			=	$this->getFieldValuesOfField( $fieldId );

		if ( $fieldValues ) {
			// Remove deleted field values:
			foreach ( $existingFieldValues as $i => $existingFieldValue ) {
				$i						=	(int) $i;
				$exists					=	false;

				foreach ( $fieldValues as $fieldValue ) {
					$fieldValue			=	(array) $fieldValue;
					$id					=	(int) cbGetParam( $fieldValue, 'fieldvalueid' );		//TODO: Use new Input class
					$title				=	trim( stripslashes( cbGetParam( $fieldValue, 'fieldtitle' ) ) );

					if ( $id && ( $i == $id ) && ( $title != '' ) ) {
						$exists			=	true;
						break;
					}
				}

				if ( ! $exists ) {
					if ( ! $this->delete( $i ) ) {
						return false;
					}

					unset( $existingFieldValues[$i] );
				}
			}

			// Insert new field values or update existing:
			foreach ( $fieldValues as $i => $fieldValue ) {
				$fieldValue				=	(array) $fieldValue;
				$id						=	(int) cbGetParam( $fieldValue, 'fieldvalueid' );		//TODO: Use new Input class
				$title					=	trim( stripslashes( cbGetParam( $fieldValue, 'fieldtitle' ) ) );
				$label					=	trim( stripslashes( cbGetParam( $fieldValue, 'fieldlabel' ) ) );

				if ( $title != '' ) {
					if ( isset( $existingFieldValues[$id] ) ) {
						$newFieldValue	=	$existingFieldValues[$id];

						if ( ( (int) $newFieldValue->get( 'fieldid' ) == (int) $fieldId )
								&& ( $newFieldValue->get( 'fieldtitle' ) == $title )
								&& ( $newFieldValue->get( 'fieldlabel' ) == $label )
								&& ( (int) $newFieldValue->get( 'ordering' ) == (int) ( $i + 1 ) ) )
						{
							continue;
						}
					} else {
						$newFieldValue	=	new FieldValueTable( $this->_db );
					}

					$newFieldValue->set( 'fieldid', (int) $fieldId );
					$newFieldValue->set( 'fieldtitle', $title );
					$newFieldValue->set( 'fieldlabel', $label );
					$newFieldValue->set( 'ordering', (int) ( $i + 1 ) );

					if ( ! $newFieldValue->store() ) {
						return false;
					}
				}
			}

			$this->updateOrder( $this->_db->NameQuote( 'fieldid' ) . " = " . (int) $fieldId );
		} else {
			// Delete all current field values:
			$query						=	'DELETE'
										.	"\n FROM " . $this->_db->NameQuote( $this->_tbl )
										.	"\n WHERE " . $this->_db->NameQuote( 'fieldid' ) . " = " . (int) $fieldId;
			$this->_db->setQuery( $query );
			if ( ! $this->_db->query() ) {
				return false;
			}
		}

		return true;
	}
}
