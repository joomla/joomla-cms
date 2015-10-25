<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/18/14 2:31 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;

defined('CBLIB') or die();

/**
 * cbSqlQueryPart Class implementation
 * SQL query-compiler for lists searches (in PHP)
 * (Could have extended SimpleXMLElement, but not needed here)
 */
class cbSqlQueryPart
{
	/**
	 * Node tag name ('column', 'where', 'joinkeys', 'data')
	 * @var string
	 */
	public $tag;
	/**
	 * Node name (column name)
	 * @var string
	 */
	public $name;
	/**
	 * Node table name (name of table)
	 * @var string
	 */
	public $table;
	/**
	 * Node type
	 * @var string
	 */
	public $type;
	/**
	 * Operator of the node
	 * @var string
	 */
	public $operator;
	/**
	 * Value of the node
	 * @var string
	 */
	public $value;
	/**
	 * Value type of the node
	 * @var string
	 */
	public $valuetype;
	/**
	 * Search mode for the node
	 * @var string
	 */
	public $searchmode;
	/**
	 * Search-mode: @see cbFieldHandler::_fieldSearchModeHtml()
	 * @var string
	 */
	public $valuetable;
	/**
	 * Key for joins
	 * @var string
	 */
	public $key;

	/**
	 * @var cbSqlQueryPart[]
	 */
	protected $_children	=	array();

	/**
	 * Adds children to $this
	 *
	 * @param  cbSqlQueryPart[]  $children
	 */
	public function addChildren( $children )
	{
		$this->_children	=	array_merge( $this->_children, $children );
	}

	/**
	 * Compiles $this SQL query into a real SQL query
	 *
	 * @param  array    $tableReferences
	 * @param  array    $joinsSQL
	 * @param  boolean  $wildcards        TRUE only at top recursion
	 * @return null|string
	 */
	public function reduceSqlFormula( &$tableReferences, &$joinsSQL, $wildcards = null )
	{
		static $replaceWildcards			=	false;
		static $joinedTableKey				=	'a';

		if ( $wildcards !== null ) {
			// Top call of recursion:
			$replaceWildcards				=	$wildcards;
			$joinedTableKey					=	'a';
		}
		$condition							=	null;

		$subFormulas						=	array();

		switch ( $this->getName() ) {
			case 'data':
				$table						=	$this->attributes( 'table' );
				if ( $table ) {
					if ( isset( $tableReferences[$table] ) ) {
						$prevJoinKey			=	$tableReferences[$table];
					} else {
						$prevJoinKey			=	null;
					}
					$joinKey					=	'j' . $joinedTableKey;
					$tableReferences[$table]	=	$joinKey;
					$joinedTableKey				=	chr( ord( $joinedTableKey ) + 1 );
				} else {
					$joinKey				=	null;
					$prevJoinKey			=	null;
				}
				break;

			default:
				$table						=	null;
				$joinKey					=	null;
				$prevJoinKey				=	null;
				break;
		}

		// Recurse:
		foreach ( $this->children() as $child ) {
			$subForm						=	$child->reduceSqlFormula( $tableReferences, $joinsSQL, null );
			if ( $subForm != '' ) {
				$subFormulas[]				=	$subForm;
			}
		}

		switch ( $this->getName() ) {
			case 'data':
				if ( substr( $this->attributes( 'type' ), 0, 6 ) == 'const:' ) {
					$condition					=	$this->_sqlCleanQuote( $this->attributes( 'value' ), $this->attributes( 'type' ) );
				} else {
					global $_CB_database;

					$joinType					=	'LEFT';
					if ( count( $subFormulas ) > 0 ) {
						$condition				=	'(' . implode( ') ' . $this->attributes( 'operator' ) . ' (', $subFormulas ) . ')';
						foreach ( $this->children() as $child ) {
							if ( $child->getName() == 'joinkeys' ) {
								if ( $child->attributes( 'type' ) === 'inner' ) {
									$joinType	=	'INNER';
								}
								break;
							}
						}
					} else {
						$condition				=	( $joinKey ? $joinKey . '.' : '' ) . $_CB_database->NameQuote( $this->attributes( 'key' ) ) . ' = ' . $_CB_database->NameQuote( $this->attributes( 'value' ) );
					}

					if ( $joinKey ) {
						$joinsSQL[]					=	$joinType . ' JOIN ' . $_CB_database->NameQuote( $table ) . ' AS ' . $joinKey . ' ON ' . $condition;
						$condition					=	$joinKey . '.' . $this->attributes( 'name' );
						if ( $prevJoinKey ) {
							$tableReferences[$table]	=	$prevJoinKey;
						} else {
							unset( $tableReferences[$table] );
						}
					}
				}
				break;

			case 'joinkeys':
				if ( count( $subFormulas ) > 0 ) {
					$condition				=	'(' . implode( ') ' . $this->attributes( 'operator' ) . ' (', $subFormulas ) . ')';
				}
				break;

			case 'column':
			case 'where':
				switch ( $this->attributes( 'type' ) ) {
					case 'sql:operator':
						if ( count( $subFormulas ) > 0 ) {
							$condition		=	'(' . implode( ') ' . $this->attributes( 'operator' ) . ' (', $subFormulas ) . ')';
						}
						break;

					case 'sql:function':
						$condition			=	$this->attributes( 'operator' ) . '( ' . implode( ', ', $subFormulas ) . ' )';
						break;

					case 'sql:field':
						if ( isset( $tableReferences[$this->attributes( 'table' )] ) ) {
							$operator		=	$this->attributes( 'operator' );
							$value			=	$this->attributes( 'value' );
							$valuetype		=	$this->attributes( 'valuetype' );
							$searchmode		=	$this->attributes( 'searchmode' );

							if ( in_array( $operator, array( '=', '<>', '!=' ) ) && ( $valuetype == 'const:string' ) ) {
								switch ( $searchmode ) {
									case 'all':
									case 'any':
									case 'anyis':
									case 'phrase':
									case 'allnot':
									case 'anynot':
									case 'anyisnot':
									case 'phrasenot':
										$precise				=	in_array( $searchmode, array( 'anyis', 'anyisnot' ) );

										if ( $replaceWildcards && ! $precise ) {
											$this->_replaceWildCards( $operator, $value );		// changes $operator and $value !
										}
										if ( is_array( $value ) ) {
											$eachValues			=	$value;
										} else {
											if ( cbStartOfStringMatch( $searchmode, 'phrase' ) ) {
												$eachValues		=	array( $value );
											} else {
												global $_CB_framework;
												if ( $_CB_framework->outputCharset() == 'UTF-8' ) {
													$eachValues	=	@preg_split( '/\p{Z}+/u', $value );

													if ( preg_last_error() == PREG_INTERNAL_ERROR ) {
														// PCRE has not been compiled with utf-8 support, do our best:
														$eachValues	=	preg_split( '/\W+/', $value );
													}
												} else {
													$eachValues	=	preg_split( '/\W+/', $value );
												}
											}
										}
										$conditions				=	array();

										foreach ( $eachValues as $v ) {
											if ( $v != '' ) {
												if ( ! ( $precise || in_array( $operator, array( 'LIKE', 'NOT LIKE' ) ) ) ) {
													$operator	=	$this->_operatorToLike( $operator );
												}
												$conditions[]	=	$this->_buildop( $operator, ( $precise ? $v : $this->_prepostfixPercent( $v ) ), $valuetype, $tableReferences );
											}
										}

										if ( count( $conditions ) > 1 ) {
											$op					=	( in_array( $searchmode, array( 'all', 'allnot' ) ) ? ') AND (' : ') OR (' );
											$condition			=	'(' . implode( $op, $conditions ) . ')';
										} elseif ( count( $conditions ) == 1 ) {
											$condition			=	implode( '', $conditions );
										} else {
											$condition			=	null;
										}

										if ( in_array( $searchmode, array( 'allnot', 'anynot', 'anyisnot', 'phrasenot' ) ) && $condition ) {
											$condition			=	'NOT(' . $condition . ')';
										}
										break;

									case 'isnot':
										$operator				=	( $operator == '=' ? '<>' : '=' );
										$condition				=	$this->_buildop( $operator, $value, $valuetype, $tableReferences );
										break;

									case 'is':
									default:
										$condition				=	$this->_buildop( $operator, $value, $valuetype, $tableReferences );
										break;

								}
							} else {
								$condition						=	$this->_buildop( $operator, $value, $valuetype, $tableReferences );
							}
						}
						break;
					default:
						break;
				}
				break;
			default:
				break;
		}
		return $condition;
	}

	/**
	 * Replaces wildcards * into SQL's % and adds them
	 * @param  string  $operator  IN+OUT: Input: '=', '<>' or '!=', OUTPUT: 'LIKE' or 'NOT LIKE'
	 * @param  string  $value     IN+OUT: Value to search, INPUT: with *, OUTPUT: %+sql-search-escaped
	 * @return boolean
	 */
	protected function _replaceWildCards( &$operator, &$value )
	{
		$changes				=	false;

		if ( is_array( $value ) ) {
			foreach ( array_keys( $value ) as $k ) {
				$changes		=	$this->_replaceWildCards( $operator, $value[$k] ) || $changes;
			}
		} else {
			$escSearch			=	str_replace( '|*|', '|`|', $value );

			if ( strpos( $escSearch, '*' ) !== false ) {
				$escSearch		=	Application::Database()->getEscaped( $escSearch, true );
				$escSearch		=	str_replace( '*', '%', $escSearch );
				$value			=	str_replace( '|`|', '|*|', $escSearch );
				$operator		=	$this->_operatorToLike( $operator );
				$changes		=	true;
			}
		}

		return $changes;
	}

	/**
	 * Returns string with added '%' before and after if not already there
	 *
	 * @param  string  $sqlSearchEscaped
	 * @return string
	 */
	protected function _prepostfixPercent( $sqlSearchEscaped )
	{
		if ( $sqlSearchEscaped[0] != '%' ) {
			$sqlSearchEscaped	=	'%' . $sqlSearchEscaped;
		}

		if ( $sqlSearchEscaped[strlen($sqlSearchEscaped) - 1] != '%' ) {
			$sqlSearchEscaped	.=	'%';
		}

		return $sqlSearchEscaped;
	}

	/**
	 * Replaces = with LIKE and '<>' and '!=' with 'NOT LIKE'
	 *
	 * @param  string  $operator
	 * @return string
	 */
	protected function _operatorToLike( $operator )
	{
		switch ( $operator ) {
			case '<>':
			case '!=':
				$operator	=	'NOT LIKE';
				break;

			case '=':
			default:
				$operator	=	'LIKE';
				break;
		}

		return $operator;
	}

	/**
	 * Builds a SQL query VALUE OPERATOR VALUE
	 * With valuetype for VALUE and using table references.
	 *
	 * @param  string  $operator
	 * @param  string  $value
	 * @param  string  $valuetype
	 * @param  array   $tableReferences
	 * @return string
	 */
	protected function _buildop( $operator, $value, $valuetype, &$tableReferences )
	{
		global $_CB_database;

		return	$tableReferences[$this->attributes( 'table' )] . '.' . $_CB_database->NameQuote( $this->attributes( 'name' ) )
		.	' ' . $operator . ' '
		.	( $valuetype == 'sql:field' ? ( isset( $tableReferences[$this->attributes( 'valuetable' )] ) ? $tableReferences[$this->attributes( 'valuetable' )] . '.' : '' ) : '' )
		.	$this->_sqlCleanQuote( $value, $valuetype )
			;
	}

	/**
	 * Cleans and makes a value SQL safe depending on the type that is enforced.
	 *
	 * @param  mixed   $fieldValue
	 * @param  string  $type
	 * @return string
	 */
	protected function _sqlCleanQuote( $fieldValue, $type )
	{
		global $_CB_database;

		$typeArray		=	explode( ':', $type, 3 );

		if ( count( $typeArray ) < 2 ) {
			$typeArray	=	array( 'const' , $type );
		}

		switch ( $typeArray[1] ) {
			case 'int':
				$value		=	(int) $fieldValue;
				break;
			case 'float':
				$value		=	(float) $fieldValue;
				break;
			case 'formula':
				$value		=	$fieldValue;
				break;
			case 'field':						// this is temporarly handled here
				$value		=	$_CB_database->NameQuote( $fieldValue );
				break;
			case 'datetime':
				if ( preg_match( '/[0-9]{4}-[01][0-9]-[0-3][0-9] [0-2][0-9](:[0-5][0-9]){2}/', $fieldValue ) ) {
					$value	=	$_CB_database->Quote( $fieldValue );
				} else {
					$value	=	"''";
				}
				break;
			case 'date':
				if ( preg_match( '/[0-9]{4}-[01][0-9]-[0-3][0-9]/', $fieldValue ) ) {
					$value	=	$_CB_database->Quote( $fieldValue );
				} else {
					$value	=	"''";
				}
				break;
			case 'string':
				$value		=	$_CB_database->Quote( $fieldValue );
				break;
			case 'null':
				if ( $fieldValue != 'NULL' ) {
					trigger_error( sprintf( 'cbSqlQueryPart::_sqlCleanQuote: ERROR: field type sql:null has not NULL value' ) );
				}
				$value		=	'NULL';
				break;

			default:
				trigger_error( 'cbSqlQueryPart::_sqlQuoteValueType: ERROR_UNKNOWN_TYPE: ' . htmlspecialchars( $type ), E_USER_NOTICE );
				$value		=	$_CB_database->Quote( $fieldValue );	// false;
				break;
		}

		return (string) $value;
	}

	/**
	 * Returns the name of the tag of $this
	 *
	 * @return string
	 */
	protected function getName()
	{
		return $this->tag;
	}

	/**
	 * Returns an attribut $name if exists, otherwise NULL
	 *
	 * @param  string|null   $name
	 * @return string|null
	 */
	protected function attributes( $name = null )
	{
		if ( isset( $this->$name ) ) {
			return $this->$name;
		}

		return null;
	}

	/**
	 * Returns an array of all children
	 *
	 * @return cbSqlQueryPart[]
	 */
	protected function children( )
	{
		return $this->_children;
	}
}
