<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 11/25/13 5:33 PM $
* @package CBLib\AhaWow\Model
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\AhaWow\Model;

use CBLib\Database\Table\CheckedOrderedTable;
use CBLib\Registry\ParamsInterface;
use CBLib\Xml\SimpleXMLElement;
use CBLib\Database\DatabaseDriverInterface;
use CBLib\Database\Table\TableInterface;
use CBLib\Language\CBTxt;

defined('CBLIB') or die();

/**
 * CBLib\AhaWow\Model\XmlQuery Query Compiler from AHA-WOW XML formal query description language Class implementation
 * 
 */
class XmlQuery {
	/** main table "AS" alias (should stay same throughout object lifetime)
	 *  @var string */
	private $maintableAs				=	'a';
	/** next joined table "AS" alias (should be incremented using incrementTableAs() method after each use )
	 *  @var string */
	private $tableAs					=	'a';
	/** next joined table "AS" alias (should be incremented using incrementTableAs() method after each use )
	 *  @var string */
	private $_currentTableAs			=	'a';
	/** next joined table "AS" alias (should be incremented using incrementTableAs() method after each use )
	 *  @var string */
	private $_currentTableAsStack		=	array( 'a' );
	/** next joined table "AS" alias (should be incremented using incrementTableAs() method after each use )
	 *  @var string */
	private $_currentTableAsStackIdx	=	0;
	/** individual fields (or formula) expressions for SELECT
	 *  @var array of string */
	private $fieldsArray				=	array();
	/** individual fields types for UPDATE and INSERT
	 *  @var array of string */
	private $fieldsTypesArray			=	array();
	/** array of individual table-alias => "LEFT JOIN ... ON ... " expressions for SELECT
	 *  @var array of string */
	private $leftJoinArray				=	array();
	/** array of individual [table][key][value] -alias => "LEFT JOIN ... ON ... " expressions for SELECT
	 *  @var array of string */
	private $leftJoindTableKeyValueArray =	array();
	/** Array Maps individual fieldname => leftjoin table alias (if it's leftjoined)
	 *  Used for subsequent leftjoins
	 *  @var array of string */
	private $leftJoinedFieldsTable		=	array();
	/** If we have anything else than left joins we need to add them to the counting query.
	 *  @var boolean */
	private $joinsNeededForCount		=	false;
	/** individual fields (or formula) expressions for "WHERE" (will be imploded with "AND")
	 *  @var array of string */
		private $where						=	array();
	/** array of individual expressions used for "GROUP BY" (will be imploded with "AND")
	 *  @var array of string */
	private $groupByArray				=	array();
	/** is $groupByArray WITH ROLLUP ?
	 * @var boolean */
	private $groupByArrayWithRollup	=	false;
	/** individual fields (or formula) expressions for "HAVING"
	 *  @var array of string */
	private $having						=	array();
	/** individual fields (or formula) with "ASC/DESC" expressions for "ORDER BY"
	 *  @var array of string */
	private $orderArray					=	array();
	/** database object
	 * @var DatabaseDriverInterface */
	private $_db;
	/** name of main table (with "#__" prefix for Joomla
	 *  @var string */
	private $_table;
	/** CB plugin parameters for "param:" statements
	 * @var ParamsInterface */
	private $_pluginParams;
	/** array holding references to external data models (objects or arrays for "ext:datatype:xxx:yyy" valuetype)
	 * @var ParamsInterface[] */
	private $_extDataModels				=	array();
	/** Internal state for XML->SQL traversal and query-generation mode
	 * @var boolean */
	private $_reverse					=	false;
	/**
	 * Did we not yet bump group_concat_max_len ?
	 * @var boolean
	 */
	protected $_group_concat_max_len_todo	=	true;

	/**
	 * Constructor
	 *
	 * @param  DatabaseDriverInterface  $db            Database object
	 * @param  string                   $table         Name of main table (with "#__" prefix for Joomla
	 * @param  ParamsInterface          $pluginParams  CB plugin parameters for "param:" statements
	 */
	public function __construct( $db, $table, $pluginParams ) {
		$this->_db				=	$db;
		$this->_table			=	$table;
		$this->_currentTableAs	=	'a';						//TBD: CHECK IF I CAN ADD THIS HERE...
		$this->_pluginParams	=	$pluginParams;
	}

	/**
	 * Prepares query from a single element <data> with all needed children nodes (<orderby>, <rows>, <where> and if existing <groupby>)
	 *
	 * @param  SimpleXmlElement  $data
	 * @return void
	 */
	public function prepare_query( $data ) {
		$this->process_orderby( $data->getElementByPath( 'orderby') );			// <data><orderby><field> fields
		$this->process_fields(  $data->getElementByPath( 'rows') );				// <data><rows><field> fields
		$this->process_where(   $data->getElementByPath( 'where') );			// <data><where><column> fields
		$this->process_groupby( $data->getElementByPath( 'groupby' ) );			// <data><groupby><field> fields
	}

	/**
	 * Treats a <fields> node and its children
	 *
	 * @param SimpleXmlElement $fields
	 */
	public function process_fields( $fields ) {
		if ( $fields ) {
			foreach ( $fields->children() as $field ) {
				/** @var $field SimpleXmlElement */
				if ( $field->getName() == 'field' ) {
					$this->process_field( $field );
				} elseif ( $field->getName() == 'data' ) {
					$this->process_data( $field );
				} else {
					trigger_error( 'SQLXML::process_field: child type ' . $field->getName() . ' of fields is not implemented !', E_USER_NOTICE );
				}
			}
		}
	}

	/**
	 * Treats a <orderby> node and its children <field> nodes
	 *
	 * @param SimpleXmlElement $orderby
	 * @param string           $selectedOrderingGroup  The ordergroup to select and use for ordering
	 */
	public function process_orderby( $orderby, $selectedOrderingGroup = null ) {
		if ( $orderby ) {
			if ( $selectedOrderingGroup ) {
				$this->process_orderby( $orderby->getChildByNameAttr( 'ordergroup', 'name', $selectedOrderingGroup ) );
				return;
			}

			foreach ( $orderby->children() as $o ) {
				/** @var $o SimpleXmlElement */
				if ( $o->getName() == 'field' ) {
					list( $fieldsArray )	=	$this->get_field( $o );

					$this->processJoinsNeededForCount( $fieldsArray );

					$this->orderArray[]		=	array_pop( $fieldsArray ) . ( $o->attributes( 'ordering' ) === 'DESC' ? ' DESC' : '' );
				} elseif ( $o->getName() == 'ordergroup' ) {
					continue;
				} else {
					trigger_error( 'SQLXML::process_orderby: child type ' . $o->getName() . ' of orderby is not implemented !', E_USER_NOTICE );
				}
			}
		}
	}
	/**
	 * Adds an extra "group by $groupby"
	 * <groupby>
	 * <groupby withrollup="true">
	 *
	 * @param  SimpleXmlElement|string  $groupby
	 * @return void
	 */
	public function process_groupby( $groupby ) {
		if ( $groupby ) {
			if (is_string( $groupby ) ) {
				$this->groupByArray[]			=	$groupby;
			} elseif ( is_object( $groupby ) ) {

				// Look for <groupby withrollup="true">
				if ( $groupby->attributes( 'withrollup' ) == 'true' ) {
					$this->groupByArrayWithRollup	=	true;
				}

				// Treats each <field>
				foreach ( $groupby->children() as $o ) {
					/** @var $o SimpleXmlElement */
					if ( $o->getName() == 'field' ) {
						list( $fieldsArray )	=	$this->get_field( $o );

						$this->processJoinsNeededForCount( $fieldsArray );

						$groupByField			=	array_pop( $fieldsArray );

						// Treat case of GROUP BY WITH ROLLUP that does implicit ordering, but that MySQL does not allow ordering at same time! :		//FIXME: this breaks e.g. tax reports auxiliary query for filters:
						if ( $this->groupByArrayWithRollup && $o->attributes( 'ordering' ) ) {
							$groupByField		.=	( $o->attributes( 'ordering' ) === 'DESC' ? ' DESC' : '' );
						}

						$this->groupByArray[]	=	$groupByField;
					} else {
						trigger_error( 'SQLXML::process_groupby: child type ' . $o->getName() . ' of groupby is not implemented !', E_USER_NOTICE );
					}
				}
			} else {
				trigger_error( 'SQLXML::process_groupby does implement only strings and xml objects', E_USER_NOTICE );
			}
		}
	}
	/**
	 * Treats a <quicksearch> node and its children <field> nodes with a given $searchString
	 *
	 * @param  SimpleXmlElement  $quicksearch
	 * @param  string              $searchString
	 */
	public function process_search_string( $quicksearch, $searchString ) {
		if ( $searchString ) {
			$quicksearchfields			=	array();
			foreach ( $quicksearch->children() as $o ) {
				/** @var $o SimpleXmlElement */
				if ( $o->getName() == 'field' ) {
					list( $fieldsArray )				=	$this->get_field( $o );

					$this->processJoinsNeededForCount( $fieldsArray );

					$searchField						=	array_pop( $fieldsArray );

					$quicksearchfields[$searchField]	=	$o->attributes( 'valuetype' );
				} else {
					trigger_error( 'SQLXML::process_search_string: child type ' . $o->getName() . ' of quicksearchfields is not implemented !', E_USER_NOTICE );
				}
			}
			$qs							=	array();
			$cleanedSearch				=	$this->_db->getEscaped( trim( strtolower( $searchString ) ), true );
			foreach ( $quicksearchfields as $fieldName => $fieldType ) {
				if ( $fieldType ) {
					$cleanedValue		=	$this->sqlCleanQuote( $searchString, $fieldType );
					if ( $cleanedValue || ( ( $cleanedValue === 0 ) && ( $cleanedSearch === '0' ) ) ) {
						$qs[]			=	$fieldName . " = " . $cleanedValue;
					}
				} else {
					$qs[]				=	"( " . $fieldName . " LIKE '%" . $cleanedSearch . "%' )";
				}
			}
			if ( count( $qs ) > 0 ) {
				$this->where[]			=	implode( ' OR ', $qs );
			}

		}
	}

	/**
	 * Proccesses private $fieldsArray to toggle joinsNeededForCount
	 * For Quick Search, Order By, and Group By
	 *
	 * @param array $fieldsArray
	 */
	private function processJoinsNeededForCount( $fieldsArray ) {
		if ( count( $this->leftJoinArray ) > 0 ) {
			foreach( array_keys( $fieldsArray ) as $fieldName ) {
				if ( array_key_exists( $fieldName, $this->leftJoinedFieldsTable ) ) {
					if ( array_key_exists( $this->leftJoinedFieldsTable[$fieldName], $this->leftJoinArray ) ) {
						$this->joinsNeededForCount	=	true;
					}
				}
			}
		}
	}

	/**
	 * Adds a simple WHERE clause
	 *
	 * @param string $fieldName
	 * @param string $operator
	 * @param string $fieldValue
	 * @param string $type        ( 'sql:string' (default), 'sql:int', 'sql:float' )
	 */
	public function addWhere( $fieldName, $operator, $fieldValue, $type ) {
		if ( ( ! $type ) || ( $type == 'sql:field' ) ) {
			$value		=	$this->tableAs . '.' . $this->_db->NameQuote( $fieldValue );
		} elseif ( $type == 'sql:parentfield' ) {
			$value		=	$this->_currentTableAsStack[0] . '.' . $this->_db->NameQuote( $fieldValue );
		} else {
			$value		=	$this->sqlCleanQuote( $fieldValue, $type );
		}
		if ( substr( $operator, -8 ) == '||ISNULL' ) {
			$this->where[]	=	'('
				.	$this->_currentTableAs . '.`' . $this->_db->getEscaped( $fieldName ) . "` " . substr( $operator, 0, -8 ) . " " . $value		//BM maintableAs
				.	' OR '
				.	'ISNULL(' . $this->_currentTableAs . '.`' . $this->_db->getEscaped( $fieldName ) . '`)'
				.	')';
		} else {
			$this->where[]	=	( $this->_currentTableAs ? $this->_currentTableAs : $this->maintableAs ) . '.`' . $this->_db->getEscaped( $fieldName ) . "` " . $operator . " " . $value;		//BM maintableAs
		}
	}

	/**
	 * Adds a simple HAVING clause
	 *
	 * @param string $fieldName
	 * @param string $operator
	 * @param string $fieldValue
	 * @param string $type        ( 'sql:string' (default), 'sql:int', 'sql:float' )
	 */
	public function addHaving( $fieldName, $operator, $fieldValue, $type ) {
		$value		=	$this->sqlCleanQuote( $fieldValue, $type );
		$this->having[] = $this->_currentTableAs . '.`' . $this->_db->getEscaped( $fieldName ) . "` " . $operator . " " . $value;		//BM maintableAs
	}

	/**
	 * Executes the query to count total number of rows and returns count
	 *
	 * @return int|null   null if error
	 */
	public function queryCount() {
		/*
				$sql = "SELECT COUNT(*)"
					. " FROM `" . $this->_db->getEscaped( $this->_table ) . "` AS " . $this->maintableAs
					.		( ( count( $this->leftJoinsNeededForWhere ) > 0 ) ? "\n " 		  . implode( "\n ", $this->leftJoinsNeededForWhere ) : '' )
					.		( ( count( $this->where ) > 0 )		 			  ? "\n WHERE ( " . implode( ' ) AND ( ', $this->where ) . " )"		 : '' )
					//  .	( ( count( $this->groupByArray )  > 0 )			  ? "\n GROUP BY " . implode( ', ', $this->groupByArray )			 : '' )
					//	.	( ( count( $this->having ) > 0 )		 		  ? "\n HAVING ( " . implode( ' ) AND ( ', $this->having ) . " )"		 : '' )
					;
		*/

		if ( count( $this->groupByArray )  == 0 ) {
			$sql = "SELECT COUNT(*)";
		} else {
			$sql = "SELECT COUNT( DISTINCT " . implode( ', ', $this->groupByArray ) . " )";
		}

		$sql	.=	" FROM `" . $this->_db->getEscaped( $this->_table ) . "` AS " . $this->maintableAs
			.		( $this->joinsNeededForCount && ( count( $this->leftJoinArray ) > 0 ) ? "\n " 		   . implode( "\n ", $this->leftJoinArray )		: '' )
			.		( ( count( $this->where ) > 0 )		 								  ? "\n WHERE ( "  . implode( ' ) AND ( ', $this->where ) . " )"		 : '' )
			//		.		( ( count( $this->groupByArray )  > 0 )						  ? "\n GROUP BY " . implode( ', ', $this->groupByArray )			 : '' )
			.		( ( count( $this->having ) > 0 )		 							  ? "\n HAVING ( " . implode( ' ) AND ( ', $this->having ) . " )"		 : '' )
		;


		$this->_db->setQuery( $sql );

		$total = $this->_db->loadResult();
		if ( $total === null ) {
			trigger_error( 'SQLXML::queryCount error returned: ' . $this->_db->getErrorMsg(), E_USER_NOTICE );
		}
		return $total;
	}

	/**
	 * Executes the query to load the rows and returns them
	 *
	 * @param  SimpleXmlElement  $dataModel
	 * @param  int $limitstart
	 * @param  int $limit
	 * @return array of stdClass objects with ->_tbl set properly.
	 */
	public function & queryLoadObjectsList( $dataModel, $limitstart = 0, $limit = 0 ) {
		$sql								=	$this->_buildSQLquery();
		if ( $sql === null ) {
			return $sql;
		}
		$this->_db->setQuery( $sql, ( $limitstart ? (int) $limitstart : 0 ), ( $limit ? (int) $limit : 0 ) );

		$dataModelClass						=	$dataModel->attributes( 'class' );
		$dataModelKey						=	$dataModel->attributes( 'key' );
		$dataModelUseLoad					=	( $dataModel->attributes( 'useload' ) == 'true' );
		if ( ( ! $dataModelClass ) || ( $dataModelClass == 'stdClass' ) ) {
			$rows							=	$this->_db->loadObjectList();

			if ( $this->_db->getErrorNum() ) {
				trigger_error( 'SQLXML::queryObjectList: error: ' . $this->_db->getErrorMsg(), E_USER_NOTICE );
			} else {
				for ( $i = 0, $n = count( $rows ); $i < $n; $i++ ) {
					if ( ! ( $rows[$i] instanceof TableInterface ) ) {
						$rows[$i]->_tbl	=	$this->_table;
					}
				}
			}
		} else {
			$rowsArray						=	$this->_db->loadAssocList();

			if ( $this->_db->getErrorNum() ) {
				trigger_error( 'SQLXML::queryObjectList: error: ' . $this->_db->getErrorMsg(), E_USER_NOTICE );
			}

			if ( $rowsArray === null ) {
				$rows						=	null;
			} else {
				if ( strpos( $dataModelClass, '::' ) === false ) {
					$rows					=	array();
					foreach ( $rowsArray as $k => $rarr ) {
						$rows[$k]			=	new $dataModelClass( $this->_db );

						if ( $dataModelUseLoad && $dataModelKey && isset( $rarr[$dataModelKey] ) ) {
							if ( $rows[$k] instanceof TableInterface ) {
								/** @var TableInterface[] $rows */
								if ( $rows[$k]->getKeyName() == $dataModelKey ) {
									$rows[$k]->load( $rarr[$dataModelKey] );
								}
							}
						}

						foreach ( $rarr as $kk => $vv ) {
							$rows[$k]->$kk	=	$vv;
						}
					}
				} else {
					$dataModelSingleton		=	explode( '::', $dataModelClass );
					$rows					=	call_user_func_array( $dataModelSingleton, array( &$rowsArray ) );
				}
				unset( $rowsArray );
			}
		}
		return $rows;
	}
	/**
	 * Executes the query to load the rows and returns them
	 * If an object is passed to this function, the returned row is bound to the existing elements of <var>object</var>.
	 * If <var>object</var> has a value of null, then all of the returned query fields returned in the object.
	 *
	 * @param  \stdClass|null  $object  IN+OUT: The address of variable
	 * @return boolean                  True if the object got loaded
	 */
	public function & queryLoadObject( & $object ) {
		$sql	=	$this->_buildSQLquery();
		$this->_db->setQuery( $sql );

		$result	=	$this->_db->loadObject( $object );
		if ( $result ) {
			if ( ! ( $object instanceof TableInterface ) ) {
				$object->_tbl	=	$this->_table;
			}
		} else {
			if ( null != ( $errormsg = $this->_db->getErrorMsg() ) ) {
				trigger_error( 'SQLXML::queryLoadObject: error returned: ' . $errormsg, E_USER_NOTICE );
			}
		}
		return $result;
	}

	/**
	 * Loads the PHP object corresponding to the XML SQL request description
	 *
	 * @param  SimpleXmlElement  $dataModel
	 * @param  int $limitstart
	 * @param  int $limit
	 * @return \stdClass                         in fact it returns an object of the exact type/class
	 */
	public function loadObjectFromData( $dataModel, $limitstart = 0, $limit = 0 ) {
		$data	=	null;

		$dataModelClass			=	$dataModel->attributes( 'class' );
		$dataModelType			=	$dataModel->attributes( 'type' );
		switch ( $dataModelType ) {
			case 'sql:row':													// <data name="planrow" type="sql:row" table="#__plans" class="cbpaidPlan" key="id" value="parameter:tid" />
				$this->process_multiplerows( $dataModel );							// <data> datas
				$results		=	$this->queryLoadObjectsList( $dataModel, 0, 1 );
				if ( count( $results ) == 1 ) {
					$data		=	$results[0];
				} else {
					if ( strpos( $dataModelClass, '::' ) === false ) {
						$data				=	new $dataModelClass( $this->_db );
					} else {
						$dataModelSingleton	=	explode( '::', $dataModelClass );
						$rowsArray			=	array( array( ) );
						$rows				=	call_user_func_array( $dataModelSingleton, array( &$rowsArray ) );		// & needed for PHP 5.3.
						$data				=	$rows[0];
					}
				}
				break;
			case 'sql:multiplerows':										// <data name="subscriptionstable" type="sql:multiplerows" table="#s_subscriptions" class="cbpaidUsersubscriptionRecord" key="plan_id" value="parameter:pid">
				$this->process_multiplerows( $dataModel );					// <data> datas
				$data			=	$this->queryLoadObjectsList( $dataModel, $limitstart, $limit );
				break;
			case 'sql:field':												// <data name="params" type="sql:field" table="#_config" class="cbpaidConfig" key="id" value="1" valuetype="sql:int" />
				$this->process_data( $dataModel );
				$data			=	$this->queryloadResult();				// get the resulting field
				break;
			case 'parameters':												// <data name="pluginparams" type="parameters" />		//TBD make sure we have only 1 word for params
			case 'params':													// <data name="pluginparams" type="parameters" />
				$data			=	$this->_pluginParams;       			// object
				break;
			default:
				trigger_error( 'SQLXML::loadObjectFromData: Data model type ' . htmlspecialchars( $dataModelType ) . ' is not implemented !', E_USER_NOTICE );
				break;
		}
		/*
				// $this->setExternalDataTypeValues( 'modelofdata', $modelOfData );
				$this->process_orderby( $data->getElementByPath( 'orderby') );			// <data><orderby><field> fields
					// $this->process_fields( $data->getElementByPath( 'rows') );			// <data><rows><field> fields
					$this->process_where( $data->getElementByPath( 'where') );				// <data><where><column> fields
					// $this->process_groupby( 'value' );
		*/
		return $data;
	}
	/**
	 * process a <data type="sql:multiplerows" ...>
	 *
	 * @param  SimpleXmlElement  $dataModel
	 * @return \stdClass                         in fact it returns an object of the exact type/class
	 */
	protected function process_multiplerows( $dataModel ) {
		/* $formula			=	*/
		$this->process_sql_field( $dataModel );		// throw away the select formula, as we select *
		$this->fieldsArray	=	array( '*' );
	}

	/**
	 * This method loads the first field of the first row returned by the query.
	 *
	 * @return  string|null  The value returned in the query or null if the query failed.
	 */
	public function & queryloadResult( ) {
		$sql	=	$this->_buildSQLquery();
		$this->_db->setQuery( $sql );

		$result	=	$this->_db->loadResult();
		if ( ( $result === null ) && $this->_db->getErrorNum() ) {
			trigger_error( 'SQLXML::queryloadResult: error returned: ' . $this->_db->getErrorMsg(), E_USER_NOTICE );
		}
		return $result;
	}
	/**
	 * Updates object or array
	 *
	 * @param  object|array  $values   object( 'fieldname'->'content ) or array ( 'fieldName' => 'content' )
	 * @param  boolean       $updateNulls  TRUE: update all NULLs too, FALSE: don't update null values
	 * @return boolean
	 */
	public function queryUpdate( $values, $updateNulls = true ) {
		return $this->_queryUpdateInsert( 'UPDATE', $values, null, $updateNulls );
		//TBD LATER: add the logging in history
	}
	/**
	 * Inserts object or array
	 *
	 * @param  object|array  $values   object( 'fieldname'->'content ) or array ( 'fieldName' => 'content' )
	 * @param  string        $keyName  or NULL
	 * @return boolean
	 */
	public function queryInsert( $values, $keyName = null ) {
		return $this->_queryUpdateInsert( 'INSERT', $values, $keyName, false );
		//TBD LATER: add the logging in history
	}
	/**
	 * Inserts or updates object or array
	 * @access private
	 *
	 * @param  string        $command 'UPDATE' or 'INSERT'
	 * @param  object|array  $values   object( 'fieldname'->'content ) or array ( 'fieldName' => 'content' )
	 * @param  string        $keyName  or NULL
	 * @param  boolean       $updateNulls  TRUE: update all NULLs too, FALSE: don't update null values
	 * @return boolean
	 */
	protected function _queryUpdateInsert( $command, $values, $keyName = null, $updateNulls = true ) {
		$fields				=	array();
		if ( is_object( $values ) ) {
			foreach ( get_object_vars( $values ) as $k => $v ) {
				if ( is_array( $v ) || is_object( $v ) || ( $v === null ) ) {
					continue;
				}
				if ( $k[0] == '_' ) {		// private variable
					continue;
				}
				if ( ( $v === null ) && ! $updateNulls ) {
					continue;
				}
				$fields[$k]	=	$v;
			}
			$values			=	$fields;
		} elseif ( is_array( $values ) ) {
			foreach ( $values as $k => $v ) {
				if ( is_array( $v ) || is_object( $v ) || ( $v === null ) || ( $k[0] == '_' ) || ( ( $v === null ) && ! $updateNulls ) ) {
					continue;
				}
				$fields[$k]	=	$v;
			}
		} else {
			trigger_error( 'SQLXML::_queryUpdateInsert: Error queryInsert without object or array.', E_USER_NOTICE );
		}

		$sql	=	$this->_buildSQLqueryUpdateInsert( $command, $fields );
//ECHO "XMLSQL WRITE-QUERY: " . $sql;
//EXIT;
		$this->_db->setQuery( $sql );
		if ( $this->_db->query() ) {

			if ( substr( $command, 0, 6 ) == 'INSERT' ) {
				$id	=	$this->_db->insertid();
				if ($keyName && $id) {
					if ( is_object( $values ) ) {
						$values->$keyName	=	$id;
					} elseif ( is_array( $values ) ) {
						$values[$keyName]	=	$id;
					}
				}
			}
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Returns id of last INSERT
	 *
	 * @return mixed  autoincrement id of last INSERT
	 */
	public function insertid( ) {
		return $this->_db->insertid();
	}
	/**
	 * Builds the SQL query for the main content-getting query
	 * @access protected
	 * (for internal use of this class only)
	 *
	 * @return string  SQL query
	 */
	public function _buildSQLquery() {
		if ( count( $this->fieldsArray ) > 0 ) {
			$sql =	"SELECT " . implode( ', ', $this->fieldsArray )
				.		( $this->_table ? "\n FROM `" . $this->_db->getEscaped( $this->_table ) . "` AS " . $this->maintableAs	: '' )
				.		( ( count( $this->leftJoinArray ) > 0 )	? "\n " 		 . implode( "\n ", $this->leftJoinArray )		: '' )
				.		( ( count( $this->where ) > 0 )			? "\n WHERE ( "  . implode( ' ) AND ( ', $this->where ) . " )"	: '' )
				.		( ( count( $this->groupByArray )  > 0 ) ? "\n GROUP BY " . implode( ', ', $this->groupByArray )			. ( $this->groupByArrayWithRollup ? ' WITH ROLLUP' : '' )	: '' )
				.		( ( count( $this->having ) > 0 ) 		? "\n HAVING ( " . implode( ' ) AND ( ', $this->having ) . " )"	: '' )
				.		( ( count( $this->orderArray )  > 0 )	? "\n ORDER BY " . implode( ', ', $this->orderArray )			: '' );
		} else {
			$sql = null;
		}
		return $sql;
	}
	/**
	 * Builds the SQL query for UPDATE or INSERT
	 *
	 * @param  string  $command  'UPDATE' or 'INSERT'
	 * @param  array   $content  keyed array ( 'fieldName' => 'content' )
	 * @return string  SQL query
	 */
	protected function _buildSQLqueryUpdateInsert( $command, $content ) {
		$sql	=	null;
		if ( ( count( $this->leftJoinArray ) == 0 ) && ( count( $this->groupByArray )  == 0 ) ) {
			if ( count( $this->fieldsArray ) > 0 ) {
				$setFields					=	array();
				foreach ( $this->fieldsArray as $fieldName => $sqlStatement ) {
					if ( isset( $content[$fieldName] ) ) {
						// always sql:field type, which is wrong, but we are waiting for table descriptors implementation...
						/*
						if ( isset( $this->fieldsTypesArray[$fieldName] ) ) {
							$type			=	$this->fieldsTypesArray[$fieldName];
						} else {
							$type			=	'sql:string';
						}
						*/
						$type					=	'sql:string';
						$value					=	$this->sqlCleanQuote( $content[$fieldName], $type );
						$setFields[$fieldName]	=	$sqlStatement . ' = ' . $value;
					}
				}
				if ( count( $setFields ) > 0 ) {
					$sql		=	$command . " `" . $this->_db->getEscaped( $this->_table ) . "` AS " . $this->maintableAs
						.	 "\n SET " . implode( ', ', $setFields );
					if ( substr( $command, 0, 6 ) != 'INSERT' ) {
						$sql		.=	( ( count( $this->where ) > 0 )			? "\n WHERE ( "  . implode( ' ) AND ( ', $this->where ) . " )"	: '' )
							.	( ( count( $this->orderArray )  > 0 )	? "\n ORDER BY " . implode( ', ', $this->orderArray )			: '' );
					}
				}
			}
		} else {
			trigger_error( 'SQLXML::_buildSQLqueryUpdateInsert: SQL Update query with LEFT JOIN or GROUP BY is not supported.', E_USER_NOTICE );
		}
		return $sql;
	}
	/**
	 * Performs a merged query between this object and an additional object $addXmlSql :
	 * the fields are taken from $addXmlSql, while the joins, where and groupby are merged.
	 *
	 * @param  XmlQuery $addXmlSql
	 * @return object               with ->_tbl set properly.
	 */
	protected function & queryObjectMergedXmlSql( $addXmlSql ) {
		$sql	= "SELECT " . implode( ', ', $addXmlSql->fieldsArray )
			. " FROM `" . $this->_db->getEscaped( $this->_table ) . "` AS " . $this->maintableAs
			.		( ( ( count( $this->leftJoinArray ) + count( $addXmlSql->leftJoinArray ) )	> 0 ) ? "\n " 		   . implode( "\n ",	   array_merge( $this->leftJoinArray, $addXmlSql->leftJoinArray ) ) : '' )
			.		( ( ( count( $this->where )		    + count( $addXmlSql->where ) )			> 0 ) ? "\n WHERE ( "  . implode( ' ) AND ( ', array_merge( $this->where,		  $addXmlSql->where ) ) . " )"	: '' )
			.		( ( ( /*count( $this->groupByArray )  + */ count( $addXmlSql->groupByArray ) )	> 0 ) ? "\n GROUP BY " . implode( ', ',		 /*  array_merge( $this->groupByArray, */ $addXmlSql->groupByArray )	: '' )
			.		( ( ( count( $this->having )		+ count( $addXmlSql->having ) )			> 0 ) ? "\n HAVING ( " . implode( ' ) AND ( ', array_merge( $this->having,		  $addXmlSql->having ) ) . " )"	: '' )
		;
		$this->_db->setQuery( $sql );

		$array			=	$this->_db->loadAssoc();

		$statisticObj	=	new CheckedOrderedTable( $this->_db, $this->_table, 'id' );

		foreach ( $array as $k => $v ) {
			$statisticObj->$k	=	$v;
		}

		return $statisticObj;
	}

	/**
	 * Treats a <statistic> node and its children <where><column> and <model><data> nodes
	 *
	 * @param  SimpleXmlElement $statistic
	 * @return object                         with ->_tbl set properly.
	 */
	public function processQuery_statistic( $statistic ) {
		$result	=	null;
		//	$classname	=	get_class( $this );
		//	$addXmlSql	=	new $classname( $this->_db, $this->_table );
		$addXmlSql	=	new self( $this->_db, $this->_table, $this->_pluginParams );
		$addXmlSql->tableAs	=	$this->tableAs;

		// <statistic><where> ...
		$additionalWhere	=	$statistic->getElementByPath( 'where' );
		$addXmlSql->process_where( $additionalWhere );

		// <statistic><model> ...
		$model				=	$statistic->getElementByPath( 'model' );
		if ( $model ) {
			$addXmlSql->process_data( $model );
			$result			=	$this->queryObjectMergedXmlSql( $addXmlSql );
		}
		return $result;
	}
	/**
	 * Returns error message of query if any.
	 *
	 * @return string   The error message for the most recent query
	 */
	public function getErrorMsg( ) {
		return $this->_db->getErrorMsg();
	}
	/**
	 * Treats a <where> node and its children <column> nodes
	 *
	 * @param  SimpleXmlElement  $where
	 * @param  array               $filterValuesArray for reverse traversals and columns of type sql:formula: ( 'name' => colName (must match), 'internalvalue' => colValue (value to compare in where) )
	 */
	public function process_where( $where, $filterValuesArray = null ) {
		if ( $where ) {
			$doGroupby				=	( $where->attributes( 'dogroupby' ) != 'false' );
			foreach ( $where->children() as $column ) {
				/** @var $column SimpleXmlElement */
				if ( $column->getName() == 'column' ) {
					$this->process_column( $column, $filterValuesArray, false, $doGroupby );
				} else {
					trigger_error( 'SQLXML::process_where: child type ' . $column->getName() . ' of where xml tag is not implemented !', E_USER_NOTICE );
				}
			}
		}
	}

	/**
	 * Treats a <joinkeys> node and its children <column> nodes
	 *
	 * @param  SimpleXmlElement  $joinkeys
	 * @param  array               $filterValuesArray for reverse traversals and columns of type sql:formula: ( 'name' => colName (must match), 'internalvalue' => colValue (value to compare in where) )
	 * @return null|string
	 */
	public function process_joinkeys( $joinkeys, $filterValuesArray = null ) {
		if ( $joinkeys ) {
			$doGroupby				=	( $joinkeys->attributes( 'dogroupby' ) != 'false' );
			$expression				=	array();
			foreach ( $joinkeys->children() as $column ) {
				/** @var $column SimpleXmlElement */
				if ( $column->getName() == 'column' ) {
					$expression[]	=	$this->process_column( $column, $filterValuesArray, true, $doGroupby );
				} else {
					trigger_error( 'SQLXML:process_joinkeys: child type ' . $column->getName() . ' of where xml tag is not implemented !', E_USER_NOTICE );
				}
			}
			return implode( ' AND ', $expression );
		} else {
			return null;
		}
	}

	/**
	 * Treats a <filter> node and its children <data> nodes
	 *
	 * @param  SimpleXmlElement  $filter
	 * @param  array               $filterValuesArray for reverse traversals and columns of type sql:formula: ( 'name' => colName (must match), 'internalvalue' => colValue (value to compare in where) )
	 * @param  string              $valueType
	 * @return void
	 */
	public function process_filter( $filter, $filterValuesArray, $valueType )
	{
		if ( ! $filter ) {
			return;
		}

		// Process the filter data:
		$data								=	$filter->getElementByPath( 'data');

		if ( $data ) {
			$where							=	$data->getElementByPath( 'where');

			if ( $where ) {
				if ( cbStartOfStringMatch( $valueType, 'xml:' ) ) {
					// this is a quick fix to make the baskets plan filter still work, as it's very different
					$saveReverse			=	$this->setReverse( true );

					$this->process_where( $where, $filterValuesArray );
					$this->setReverse( $saveReverse );

					return;
				}
			}

			// Only parse data for joins if it hasn't been processed yet:
			if ( $data->attributes( 'dataprocessed' ) != 'true' ) {
				// Process the joins to ensure fields array is correct:
				$this->_addGetJoinAs( $data );

				// Check if the data has a join that needs to be a part of the count:
				$this->processJoinsNeededForCount( array( $data->attributes( 'name' ) => null ) );

				// Ensure this datas join is inner and not left:
				$this->_changeJoinType( $data->attributes( 'name' ) );
			}
		}

		// Process a single filter:
		if ( ! is_array( $filterValuesArray['valuefield'] ) ) {
			$saveAs							=	$this->_currentTableAs;

			if ( isset( $this->fieldsArray[$filterValuesArray['valuefield']] ) ) {
				if ( preg_match( '/^[a-z]\./i', $this->fieldsArray[$filterValuesArray['valuefield']] ) ) {
					$this->_currentTableAs	=	substr( $this->fieldsArray[$filterValuesArray['valuefield']], 0, 1 );
				} else {
					$this->_currentTableAs	=	null;
				}
			} elseif ( isset( $this->leftJoinedFieldsTable[$filterValuesArray['valuefield']] ) ) {
				// Field has already been joined; lets use its tableAs:
				$this->_currentTableAs		=	$this->leftJoinedFieldsTable[$filterValuesArray['valuefield']];
			}

			$this->addWhere( $filterValuesArray['valuefield'], $filterValuesArray['operator'], $filterValuesArray['internalvalue'], $valueType );

			$this->_currentTableAs			=	$saveAs;

			return;
		}

		// Process a repeat filter:
		for ( $i = 0, $n = count( $filterValuesArray['valuefield'] ); $i < $n; $i++ ) {
			$saveAs							=	$this->_currentTableAs;

			$this->_currentTableAs			=	$this->findTableAs( $filterValuesArray['table'][$i], $filterValuesArray['table_key'][$i], 'id', 'sql:field', 'sql:field' );

			if ( $this->_currentTableAs !== false ) {
				$this->addWhere( $filterValuesArray['valuefield'][$i], $filterValuesArray['operator'][$i], $filterValuesArray['internalvalue'][$i], 'const:string' );
			}

			$this->_currentTableAs			=	$saveAs;
		}
	}

	/**
	 * Changes the JOIN type from LEFT to INNER if required
	 *
	 * @param  string  $name  Name of the JOIN AS or column name
	 */
	protected function _changeJoinType( $name ) {
		if ( isset( $this->leftJoinedFieldsTable[$name] ) && isset( $this->leftJoinArray[$this->leftJoinedFieldsTable[$name]] ) ) {
			$previous						=	$this->leftJoinArray[$this->leftJoinedFieldsTable[$name]];

			if ( substr( $previous, 0, 10 ) == 'LEFT JOIN ' ) {
				$this->leftJoinArray[$this->leftJoinedFieldsTable[$name]]	=	'INNER JOIN ' . substr( $previous, 10 );

				$this->joinsNeededForCount	=	true;
			}
		}
	}

	/**
	 * Treats a <column> node and its children <data> and <where> nodes
	 *
	 * @param  SimpleXmlElement  $column
	 * @param  array    $filterValuesArray  for reverse traversals and columns of type sql:formula: ( 'name' => colName (must match), 'internalvalue' => colValue (value to compare in where) )
	 * @param  boolean  $returnExpression   returns the conditions expression instead of adding it to WHERE or HAVING arrays
	 * @param  boolean  $doGroupBy          returns the conditions expression instead of adding it to WHERE or HAVING arrays
	 * @return null|string|array
	 */
	protected function process_column( $column, $filterValuesArray = null, $returnExpression = false, $doGroupBy = true ) {
		if ( count( $column->children() ) == 0 ) {
			$expression			=	$this->_composeSQLformula( $column );
		} else /* if ( count( $column->children() ) == 1 ) */ {
			$data				=	$column->getElementByPath( 'data' );
			if ( $data ) {
				$expression		=	$this->process_column_data( $data );
			} else {
				trigger_error( 'SQLXML::process_column: child of column ' . $column->attributes( 'name' ) . 'is not data !', E_USER_NOTICE );
				return null;
			}
		}

		$colVal					=	$column->attributes( 'value' );
		$colValueType			=	$column->attributes( 'valuetype' );
		$colType				=	$column->attributes( 'type' );
		$addInBrackets			=	true;
		if ( $colValueType == 'sql:formula' ) {
			if ( $this->_reverse && isset( $filterValuesArray['name']) && ( $filterValuesArray['name'] == $colVal ) ) {
				$addInBrackets	=	( ! is_array( $filterValuesArray['internalvalue'] ) );
				$colVal			=	$this->sqlCleanQuote( $filterValuesArray['internalvalue'], isset( $filterValuesArray['specialvaluetype'] ) ? $filterValuesArray['specialvaluetype'] : $colType );
			} else {
				if ( count( $column->children() ) != 0 ) {
					$colVal		=	null;
				}
			}
		} elseif ( $colValueType == 'sql:field' ) {
			if ( $this->_reverse && isset( $filterValuesArray['name']) && ( $filterValuesArray['name'] == $colVal ) ) {
				$addInBrackets	=	( ! is_array( $filterValuesArray['internalvalue'] ) );
				$colVal			=	$this->sqlCleanQuote( $filterValuesArray['internalvalue'], $colType );
			} else {
				if ( $doGroupBy ) {
					$this->groupByArray[$this->_currentTableAs . '.' . $colVal]	=	$this->_currentTableAs . '.`' . $colVal . '`';		// this is useful for counts, TBD later: add it only for counts.
				}
				$colVal			=	$this->_currentTableAs . '.' . $this->_db->NameQuote( $colVal );
			}
		} elseif ( $colValueType == 'sql:parentfield' ) {
			if ( $this->_reverse && isset( $filterValuesArray['name']) && ( $filterValuesArray['name'] == $colVal ) ) {
				$addInBrackets	=	( ! is_array( $filterValuesArray['internalvalue'] ) );
				$colVal			=	$this->sqlCleanQuote( $filterValuesArray['internalvalue'], $colType );
			} else {
				$colVal			=	$this->_currentTableAsStack[0] . '.' . $this->_db->NameQuote( $colVal );
			}
		} else {
			// 'const:string', 'const:int', 'const:float' and much more:
			$addInBrackets		=	( ! is_array( $colVal ) );
			$colVal				=	$this->sqlCleanQuote( $colVal, $colValueType );
			if ($colVal === false ) {
				trigger_error( 'SQLXML::process_column: where column valuetype ' . $colValueType . ' not implemented !', E_USER_NOTICE );
			}
		}
		if ( ( $colVal !== false ) && ( ( $colValueType != 'sql:formula' ) || ( $colVal !== null ) ) ) {		// in case of formula children, the condition is embedded in joins, except for reverse join traversals.
			// Join expressions have e.g. '`id`=h.`plan_id`' in them. Here we want only the second field for the selector:
			$joinEqualInExpr	=	strpos( $expression, '=' );
			if ( $joinEqualInExpr !== false ) {
				$expression		=	substr( $expression, $joinEqualInExpr + 1 );
			}

			$operator			=	isset( $filterValuesArray['operator'] ) ? $filterValuesArray['operator'] : $column->attributes( 'operator' );

			if ( ! $addInBrackets ) {
				if ( $operator == '=') {
					$operator	=	'IN';
				} elseif ( $operator == '!=') {
					$operator	=	'NOT IN';
				}
			}

			$expression			=	$expression
								.	' ' . $operator . ' ';
			if ( in_array( strtolower( $operator ), array( 'in', 'not in' ) ) && $addInBrackets ) {
				$expression		.=	'(' . $colVal . ')';
			} else {
				$expression		.=	$colVal;
			}
			if ( $returnExpression ) {
				return $expression;
			} else{
				if ( $doGroupBy && ( $column->attributes( 'tablefield' ) == 'false' ) ) {
					$this->having[]	=	$expression;
					return null;
				}
				if ( substr( $expression, 0, 6 ) == 'COUNT(' ) {
					$this->having[]	=	$expression;
				} else {
					$this->where[]	=	$expression;
				}
			}
		}
		return null;
	}
	/**
	 * Treats a <data> node and its <data> and <where> children
	 *
	 * @param SimpleXmlElement $data
	 * @return array|null|string
	 */
	protected function process_column_data( $data ) {
		$childrenFormulas			=	$this->_composeSQLformula( $data );
		if ( $childrenFormulas != null && is_array( $childrenFormulas ) ) {
			if ( count( $childrenFormulas ) == 1 ) {
				$childrenFormulas	=	implode( '', $childrenFormulas );
			} else {
				trigger_error( 'SQLXML::process_column_data: more than one data in column ' . $data->attributes( 'name' ), E_USER_NOTICE );
			}
		}
		return $childrenFormulas;
	}

	/**
	 * Treats a <field> node and its <data> children and adds it to the fieldsarray
	 *
	 * @param  SimpleXmlElement $field
	 */
	public function process_field( $field ) {
		list( $fieldsArray, $fieldsTypesArray )		=	$this->get_field( $field );

		$this->fieldsArray							=	array_merge( $this->fieldsArray, $fieldsArray );
		$this->fieldsTypesArray						=	array_merge( $this->fieldsTypesArray, $fieldsTypesArray );
	}

	/**
	 * Treats a <field> node and its <data> children
	 *
	 * @param  SimpleXmlElement $field
	 * @return array                    The array of fields added by $field
	 */
	private function get_field( $field ) {
		$cnt_name			=	$field->attributes( 'name' );
		$cnt_type			=	$field->attributes( 'type' );
		$cnt_as				=	$field->attributes( 'as' );
		$fieldsArray		=	array();
		$fieldsTypesArray	=	array();

		if ( $cnt_name === '' ) {
			return array( $fieldsArray, $fieldsTypesArray );
		}

		if ( ! $this->_has_data_children( $field ) ) {
			if ( isset( $this->fieldsArray[$cnt_name] ) ) {
				if ( preg_match( '/^[a-z]\./i', $this->fieldsArray[$cnt_name] ) ) {
					$tableAs = substr( $this->fieldsArray[$cnt_name], 0, 1 );
				} elseif ( preg_match( '/^[a-z]*\(/i', $this->fieldsArray[$cnt_name] ) ) {
					// formulas start with '(' or with a 'FUNCTION(' and thus have no associated tables:
					$tableAs	=	null;
				} else {
					$tableAs	=	$this->_currentTableAs;
				}
			} elseif ( isset( $this->leftJoinedFieldsTable[$cnt_name] ) ) {
				// Field has already been joined; lets use its tableAs:
				$tableAs		=	$this->leftJoinedFieldsTable[$cnt_name];
			} else {
				// Field belongs to the current table; lets use its tableAs:
				$tableAs		=	$this->_currentTableAs;
			}

			$fieldsArray[( $cnt_as ? $cnt_as : $cnt_name )]			=	( $tableAs ? $this->_db->getEscaped( $tableAs ) . '.' : '' ) . '`' . $this->_db->getEscaped( $cnt_name ) . '`'
																	.	( $cnt_as ? ' AS `' . $cnt_as . '`' : '' );
			$fieldsTypesArray[( $cnt_as ? $cnt_as : $cnt_name )]	=	$cnt_type;
		} else {
			// special field type: instructions in child element <data>:
			foreach ( $field->children() as $data ) {
				/** @var $data SimpleXmlElement */
				if ( $data->getName() == 'data' ) {
					list( $dataFieldsArray, $dataFieldsTypesArray )		=	$this->get_data( $data );

					$fieldsArray			=	array_merge( $fieldsArray, $dataFieldsArray );
					$fieldsTypesArray		=	array_merge( $fieldsTypesArray, $dataFieldsTypesArray );

					// process <field paramvalues="field1 field2">
					$paramValues	=	$field->attributes( 'paramvalues' );				// not sure if it's even used !!!!
					if ( $paramValues ) {
						$paramValuesTypes	=	$field->attributes( 'paramvaluestypes' );
						$paramValues		=	explode( ' ', $paramValues );
						$paramValuesTypes	=	explode( ' ', $paramValuesTypes );
						foreach ( $paramValues as $k => $p ) {
							if ( ! ( isset( $paramValuesTypes[$k] ) && $paramValuesTypes[$k] ) ) {
								$paramValuesTypes[$k]			=	'sql:string';
							}
							$paramTypeArr	=	explode( ':', $paramValuesTypes[$k], 2 );
							if ( $paramTypeArr[0] == 'sql' ) {
								if ( ! ( isset( $fieldsArray[$p] ) || isset( $this->fieldsArray[$p] ) || isset( $this->leftJoinedFieldsTable[$p] ) ) ) {
									$fieldsArray[$p]		=	$this->_db->getEscaped( $this->_currentTableAs ) . '.`' . $this->_db->getEscaped( $p ) . '`';
									$fieldsTypesArray[$p]	=	( isset( $paramValuesTypes[$k] ) ? $paramValuesTypes[$k] : 'sql:string' );
								}
							}
						}
					}

				} elseif ( ! ( ( ( $field->attributes( 'type' ) == 'ordering' ) && ( $data->getName() == 'orderinggroup' ) ) || ( $data->getName() == 'attributes' ) || ( $data->getName() == 'option' ) ) ) {
					trigger_error( 'SQLXML::process_field: child ' . $data->getName() . ' of field is not implemented !', E_USER_NOTICE );
				}
			}
		}

		return array( $fieldsArray, $fieldsTypesArray );
	}
	/**
	 * Checks if a <...> node has <data> children
	 *
	 * @param  SimpleXmlElement  $field
	 * @return boolean             TRUE if at least one child is <data>, FALSE otherwise
	 */
	protected function _has_data_children( $field ) {
		$dataChilds		=	 ( count( $field->children() ) > 0 );
		if ( $dataChilds ) {
			$dataChilds	=	false;
			foreach ( $field->children() as $child ) {
				/** @var $child SimpleXmlElement */
				if ( $child->getName() == 'data' ) {
					$dataChilds	=	true;
					break;
				}
			}
		}
		return $dataChilds;
	}
	/**
	 * Treats a <data> node and its <data> and <where> children and adds it to the fieldsarray
	 *
	 * @param  SimpleXmlElement $data
	 */
	public function process_data( $data ) {
		list( $fieldsArray, $fieldsTypesArray )		=	$this->get_data( $data );

		$this->fieldsArray							=	array_merge( $this->fieldsArray, $fieldsArray );
		$this->fieldsTypesArray						=	array_merge( $this->fieldsTypesArray, $fieldsTypesArray );
	}
	/**
	 * Treats a <data> node and its <data> and <where> children
	 *
	 * @param  SimpleXmlElement $data
	 * @return array                   The array of fields added by $data
	 */
	private function get_data( $data ) {
//BB	if ( ! $this->_table ) {
//BB  		$this->_table	=	$data->attributes( 'table' );													//TBD: CHECK IF REALLY NOT NEEDED ! it breaks the COUNT(*) by adding a left join on main screen !
//BB	}
		$fieldsArray					=	array();
		$fieldsTypesArray				=	array();

		if ( $data->attributes( 'type' ) == 'sql:subquery' ) {
			$childrenFormulas			=	$this->process_subquery( $data, false );
		} else {
			$childrenFormulas			=	$this->_composeSQLformula( $data );
		}

		if ( $childrenFormulas != null ) {
			if ( ! is_array( $childrenFormulas ) ) {
				$cnt_name				=	$data->attributes( 'name' );
				$cnt_as					=	$data->attributes( 'as' );
				$cnt_type				=	$data->attributes( 'type' );
				$childrenFormulas		=	array( ( $cnt_as ? $cnt_as : $cnt_name ) => $childrenFormulas );
				$childrenFormulasType	=	array( ( $cnt_as ? $cnt_as : $cnt_name ) => $cnt_type );
				$fieldsTypesArray		=	array_merge( $fieldsTypesArray, $childrenFormulasType );		// otherwise done inside _composeSQLformula in process_sql_field.
			}

			$fieldsArray				=	array_merge( $fieldsArray, $childrenFormulas );
		}

		return array( $fieldsArray, $fieldsTypesArray );
	}
	/**
	 * Treats a <data type="sql:subquery"> node and its <data> and <where> children
	 *
	 * @param  SimpleXmlElement $data
	 * @param  boolean          $suppressAs  [optional] if true, do not output the AS statement (e.g. if used inside formulas it's not allowed)
	 * @return string
	 */
	protected function process_subquery( $data, $suppressAs = false ) {
		$subqueryData				=	$data->getChildByNameAttributes( 'data' );
		$subqueryTable				=	$subqueryData->attributes( 'table' );
		$subqueryName				=	$subqueryData->attributes( 'name' );
		$queryAs					=	$data->attributes( 'as' );
		$this->_levelPush();
		$this->incrementTableAs();
		$xmlsql						=	new self( $this->_db, $subqueryTable, $this->_pluginParams );
		$xmlsql->syncSubQueryTablesIndexes( $this );
//		$xmlsql->_currentTableAs	=	$this->tableAs;
		$xmlsql->maintableAs		=	$this->tableAs;
//		$xmlsql->_table				=	$subqueryTable;
		$xmlsql->process_data( $subqueryData );
		$childrenFormulas			=	'( ' . $xmlsql->_buildSQLquery() . ' )'
			.	( ( $queryAs || $subqueryName ) && ! $suppressAs ? ' AS ' . $this->_db->NameQuote( $queryAs ? $queryAs : $subqueryName ) : '' );
		$this->_levelPop();
		return $childrenFormulas;
	}
	/**
	 * Treats recursively a <data> or <field> node and its children
	 *
	 * @access private
	 * @param  SimpleXmlElement  $data
	 * @return array|null|string
	 */
	protected function _composeSQLformula( $data ) {
		$this->_levelPush();

		$dType				=	$data->attributes( 'type' );

		$formula			=	null;				// should not be used here
		$moreFormulas		=	null;

		if ( ! $this->_reverse ) {
			if ( $dType == 'sql:field' ) {
				$formula	=	$this->process_sql_field( $data );
			} elseif ( $dType == 'sql:count' ) {
				$formula	=	$this->process_sql_count( $data );
			}
		}
		$subFormula									=	array();
		if ( ( ! $this->_reverse ) || ( $data->attributes( 'table' ) != $this->_table ) ) {			//TBD LATER: shouldn't this be:  || ( $data->attributes( 'table' ) && ( $data->attributes( 'table' ) != $this->_table ) ) ) {

			if ( $this->_reverse && ( $data->attributes( 'table' ) != $this->_table ) ) {
				$this->joinsNeededForCount	=	true;
			}

			/** @var $child SimpleXmlElement */
			foreach ( $data->children() as $child ) {
				switch ( $child->getName() ) {
					case 'data':
						// recurse to process bottom-up
						if ( $child->attributes( 'type' ) == 'sql:subquery' ) {
							$childrenFormulas			=	$this->process_subquery( $child, true );
						} else {
							$childrenFormulas			=	$this->_composeSQLformula( $child );
						}
						if ( is_array( $childrenFormulas ) ) {
							$childrenFormulas	=	implode( ', ', $childrenFormulas );
						}
						$subFormula[]			=	$childrenFormulas;

						if ( ( ! $this->_reverse ) && $child->attributes( 'select' ) == 'true' ) {
							$moreFormulas		.=	', ' . $childrenFormulas;
						}
						break;
					case 'where':
						$this->_levelPush();
						$this->process_where( $child );
						$this->_levelPop();
						break;
					default:
						break;
				}
			}
		}
		switch ( $dType ) {
			case 'sql:count':						// count of related records in other table:
				//			if ( $this->_reverse ) {
				//				$formula	=	$this->process_sql_count( $data );
				//			}
				break;
			case 'sql:field':						// field value taken from related record in other table:
				if ( $this->_reverse ) {
					$formula	=	$this->process_reverse_sql_field( $data, $subFormula );
				}
				break;
			case 'sql:multiplerows':				// multiple field values taken from related record in other table:
				//			if ( $this->_reverse ) {
				//				$formula	=	$this->process_multiplerows( $data );
				//			}
				break;
			case 'sql:operator':					// any SQL operator between fields ( +, -, *, /, ...)
				$cnt_name	=	$this->_db->getEscaped( $data->attributes( 'name' ) );
				$operator	=	$data->attributes( 'operator' );
				$formula	=	'( ' . implode( ' ' . $operator . ' ', $subFormula ) . ' )' . ( $cnt_name ? ' AS `' . $cnt_name . '`' : '' );
				break;
			case 'sql:function':					// any SQL function of fields ( SUM( f1, f2 ), AVG(...) )
				$cnt_name	=	$this->_db->getEscaped( $data->attributes( 'name' ) );
				$operator	=	$data->attributes( 'operator' );
				$formula	=	$operator . '( ' . implode( ', ', $subFormula ) . ' ) ' . ( $cnt_name ? 'AS `' . $cnt_name . '`' : '' );
				if ( $operator == 'GROUP_CONCAT' ) {
					// Normally GROUP_CONCAT is only 1 kB, increase it to maximum value:
					$this->increaseGroupConcatMaxLen();
				}
				break;
			case 'sql:formula':					// any SQL formula of fields ( GROUP_CONCAT( f1 f2 f3 ), SUM(...) )
				$cnt_name	=	$this->_db->getEscaped( $data->attributes( 'name' ) );
				$operator	=	$data->attributes( 'operator' );
				$formula	=	$operator . '( ' . implode( ' ', $subFormula ) . ' ) ' . ( $cnt_name ? 'AS `' . $cnt_name . '`' : '' );
				if ( $operator == 'GROUP_CONCAT' ) {
					// Normally GROUP_CONCAT is only 1 kB, increase it to maximum value:
					$this->increaseGroupConcatMaxLen();
				}
				break;
			case null:								// top-level probably, no type...
				$formula	=	$subFormula;		// if not at top level, it will get imploded at level above.
				break;
			case 'param':							// a plugin parameter of type string
			case 'param:string':					// a plugin parameter of type string
			case 'param:int':						// a plugin parameter of type int
			case 'param:float':						// a plugin parameter of type float
			case 'param:datetime':					// a plugin parameter of type datetime
				$formula	=	$this->process_param( $data );
				break;
			default:
				// 'const:string', 'const:int', 'const:float' and much more:
				$name		=	$data->attributes( 'name' );
				if ( $data->attributes( 'translate' ) == 'yes' ) {
					$name	=	CBTxt::T( $name );
				}
				$formula	=	$this->sqlCleanQuote( $name, $dType );
				if ($formula === false ) {
					trigger_error( 'SQLXML::_composeSQLformula: data type ' . $dType . ' is not implemented !', E_USER_NOTICE );
				}
				break;
		}
		$this->_levelPop();

		if ( is_array( $formula ) ) {
			if ( $moreFormulas ) {
				$formula[]	=	$moreFormulas;
			}
		} else {
			$formula		.=	$moreFormulas;
		}

		return $formula;
	}

	/**
	 * Treats a <data type="sql:field"> node and its children:
	 * Field value taken from related record in other table
	 *
	 * @access private
	 * @param  SimpleXmlElement  $data
	 * @return string
	 */
	protected function process_sql_field( $data ) {
		$cnt_name			=	$data->attributes( 'name' );
		$cnt_as				=	$data->attributes( 'as' );
		$cnt_table			=	$data->attributes( 'table' );
		$cnt_key			=	$data->attributes( 'key' );
		$cnt_val			=	$data->attributes( 'value' );
		$cnt_valtype		=	$data->attributes( 'valuetype' );

		$tableAs			=	$this->_addGetJoinAs( $data );
		if ( $tableAs ) {

			$formula		=	$tableAs . '.`' . $cnt_name . '`'
				.	( $cnt_as ? ' AS `' . $cnt_as . '`' : '' );

		} else {
			if ( $cnt_table && ! $this->_table ) {
				$this->_table							=	$cnt_table;
				$this->_currentTableAs					=	$this->tableAs;		//TBD: CHECK IF I CAN ADD THIS HERE...
			}

			$formula		=	$this->_currentTableAs . '.`' . $cnt_name . '`'
				.	( $cnt_as ? ' AS `' . $cnt_as . '`' : '' );

			// collect field type for UPDATE or INSERT use:
			if ( $cnt_valtype ) {
				$this->fieldsTypesArray[$cnt_name]		=	$cnt_valtype;
			}

			// collect implicit where statement with <field name="xxx" ... key="id" value="1 valuetype="const:int"> :
			if ( $cnt_key && $cnt_val && $cnt_valtype ) {
				$this->addWhere( $cnt_key, '=', $cnt_val, $cnt_valtype );
			}

		}
		return $formula;
	}

	/**
	 * Treats a <data type="sql:count"> node and its children:
	 * Count of related records in other table.
	 *
	 * @access private
	 * @param  SimpleXmlElement  $data
	 * @return string
	 */
	protected function process_sql_count( $data ) {
		if ( $data->getElementByPath( 'joinkeys' ) ) {
			return $this->process_sql_count_join( $data );
		}
		$cnt_name				=	$data->attributes( 'name' );
		$cnt_table				=	$data->attributes( 'table' );
		$cnt_key				=	$data->attributes( 'key' );
		$cnt_val				=	$data->attributes( 'value' );
		$cnt_valtype			=	$data->attributes( 'valuetype' );
		$cnt_distinct			=	$data->attributes( 'distinct' );

		if ( ! $cnt_table ) {
			if ( $cnt_distinct ) {
				$formula		=	'COUNT( DISTINCT ' . $this->_currentTableAs . '.`' . $cnt_distinct . '` )';
			} else {
				$formula		=	'COUNT(*)';
			}
			if ( $cnt_name ) {
				$formula		.=	' AS `' . $cnt_name . '`';
			}
			if ( $cnt_key && $cnt_val && $cnt_valtype ) {
				$this->addWhere( $cnt_key, '=', $cnt_val, $cnt_valtype );
			}
		} elseif ( ( $cnt_table == $this->_table ) || ! $this->_table ) {
			if ( $cnt_distinct ) {
				$formula		=	'COUNT( DISTINCT ' . $this->_currentTableAs . '.`' . $cnt_distinct . '` )';
			} else {
				$formula		=	'COUNT(*)';
			}
			if ( $cnt_name ) {
				$formula		.=	' AS `' . $cnt_name . '`';
			}
			if ( $cnt_table && ! $this->_table ) {
				$this->_table	=	$cnt_table;
			}

			// collect implicit HAVING statement with <field name="xxx" ... key="id" value="1 valuetype="const:int"> :
			if ( ! $cnt_valtype ) {
				$cnt_valtype	=	'sql:parentfield';
			}
			if ( $cnt_key && $cnt_val ) {
				$this->addWhere( $cnt_key, '=', $cnt_val, $cnt_valtype );
			}
		} else {

			//		return $this->process_subquery( $data, false );
			$subqueryTable				=	$data->attributes( 'table' );
			$subqueryName				=	$data->attributes( 'name' );
			$this->_levelPush();
			$this->incrementTableAs();
			$xmlsql						=	new self( $this->_db, $subqueryTable, $this->_pluginParams );
			$xmlsql->syncSubQueryTablesIndexes( $this );
			$xmlsql->maintableAs		=	$this->tableAs;
			$xmlsql->process_data( $data );
			$formula					=	'( ' . $xmlsql->_buildSQLquery() . ' )'
				.	( $subqueryName ? ' AS ' . $this->_db->NameQuote( $subqueryName ) : '' );
			$this->_levelPop();
		}
		return $formula;
	}

	/**
	 * Treats a <data type="sql:count"> node and its children:
	 * Count of related records in other table.
	 *
	 * @access private
	 * @param  SimpleXmlElement  $data
	 * @return string
	 */
	protected function process_sql_count_join( $data ) {
		$cnt_name				=	$data->attributes( 'name' );
		$cnt_table				=	$data->attributes( 'table' );
		$cnt_key				=	$data->attributes( 'key' );
		$cnt_val				=	$data->attributes( 'value' );
		$cnt_valtype			=	$data->attributes( 'valuetype' );
		$cnt_distinct			=	$data->attributes( 'distinct' );
		/*
				if ( ( $cnt_table && $this->_table ) && ( $cnt_table != $this->_table ) ) {
					// count of related records in other table:
					$this->incrementTableAs();
		
					$this->leftJoinArray[$this->tableAs]	= 'LEFT JOIN `' . $cnt_table . '` AS ' . $this->tableAs
															. ' ON ' . $this->tableAs . '.`' . $cnt_key . '` = ' . $this->_currentTableAs . '.`' . $cnt_val . '`';
					$this->leftJoinedFieldsTable[$cnt_name]	=	$this->tableAs;
		*/
		$tableAs				=	$this->_addGetJoinAs( $data );
		if ( $tableAs ) {
			if ( $cnt_distinct ) {
				$formula		=	'COUNT( DISTINCT ' . $tableAs . '.`' . $cnt_distinct . '` )';
			} else {
				$formula		=	'COUNT( ' . $tableAs . '.`' . $cnt_key . '` )';
			}
			if ( $cnt_name ) {
				$formula		.=	' AS `' . $cnt_name . '`';
			}
			if ( $cnt_val ) {
				$this->groupByArray[$this->_currentTableAs . '.' . $cnt_val]	=	$this->_currentTableAs . '.`' . $cnt_val . '`';
			}
		} else {
			if ( $cnt_distinct ) {
				$formula		=	'COUNT( DISTINCT ' . $this->_currentTableAs . '.`' . $cnt_distinct . '` )';
			} else {
				$formula		=	'COUNT(*)';
			}
			if ( $cnt_name ) {
				$formula		.=	' AS `' . $cnt_name . '`';
			}
			if ( $cnt_table && ! $this->_table ) {
				$this->_table	=	$cnt_table;
			}

			// collect implicit HAVING statement with <field name="xxx" ... key="id" value="1 valuetype="const:int"> :
			if ( $cnt_key && $cnt_val && $cnt_valtype ) {
				$this->addWhere( $cnt_key, '=', $cnt_val, $cnt_valtype );
			}
		}
		return $formula;
	}

	/**
	 * Treats a <data type="sql:field"> node and its children:
	 * Field value taken from related record in other table
	 *
	 * @access private
	 * @param  SimpleXmlElement  $data
	 * @param  array               $subFormula
	 * @return null|string
	 */
	protected function process_reverse_sql_field( $data, $subFormula )
	{
		$formula			=	null;

		$cnt_name			=	$data->attributes( 'name' );
		$cnt_table			=	$data->attributes( 'table' );
		$cnt_key			=	$data->attributes( 'key' );
		$cnt_val			=	$data->attributes( 'value' );
		$cnt_valtype		=	$data->attributes( 'valuetype' );
		$cnt_tablefield		=	$data->attributes( 'tablefield' );

		if ( $cnt_table && $this->_table ) {
			if  ( $cnt_table == $this->_table ) {

				if ( ( ! $cnt_valtype ) || ( $cnt_valtype == 'sql:field' ) ) {

					$formula	=	/* $this->tableAs . '.' . */ '`' . $cnt_val . '`'											// small shortcut allowing only one level for now
						.	' = '
						.	$this->_currentTableAs . '.`' . $cnt_key . '`';
				}
			} else {
				$this->_addGetJoinAs( $data, $subFormula );

				$formula		=	'`' . $cnt_val . '`'
								.	' = '
								.	$this->tableAs . '.`' . $cnt_name . '`';
			}
		} else {
			$tableAs			=	$this->_addGetJoinAs( $data );
			if ( $tableAs ) {
				$formula		=	$tableAs . '.`' . $cnt_name . '`';
			} else {
				if ( $cnt_tablefield != 'false' ) {
					$formula	=	$this->tableAs . '.`' . $cnt_name . '`';
				} else {
					$formula	=	'`' . $cnt_name . '`';
				}
			}
		}
		return $formula;
	}

	/**
	 * @param  SimpleXMLElement|boolean  $joinkeys  <joinkeys> node (null means LEFT JOIN)
	 * @return string                            LEFT JOIN  or any other JOIN type
	 */
	protected function computeJoinKeyword( $joinkeys = null )
	{
		$joinKeyword		=	'LEFT JOIN';

		if ( is_object( $joinkeys ) ) {
			$joinType				=	$joinkeys->attributes( 'type' );
			if ( $joinType ) {
				if ( in_array( $joinType, array( 'inner', 'left', 'right', 'outer', 'left outer', 'right outer', 'cross', 'natural left', 'natural right', 'natural left outer', 'natural right outer', 'straight' ) ) ) {
					$joinKeyword		=	strtoupper( $joinType ) . ( $joinType == 'straight' ? '_' : ' ' ) . 'JOIN';
					if ( $joinType != 'left' ) {
						$this->joinsNeededForCount	=	true;
					}
				} else {
					trigger_error( sprintf( 'SQLXML::computeJoinKeyword joinkeys type="%s" unknown JOIN type: Using LEFT JOIN instead.', $joinType ), E_USER_NOTICE );
				}
			}
		}

		return $joinKeyword;
	}

	/**
	 * If a left join is needed by the <data> $data element:
	 * Adds if not yet added a JOIN statement for the <data> element $data
	 * and returns the table AS alias for accessing the corresponding table.
	 * Otherwise returns NULL.
	 *
	 * @param  SimpleXmlElement  $data
	 * @param  array             $subFormula  For process_reverse_sql_field ONLY (if we decide to use): SQL conditions in array which are imploded by AND for the merge
	 * @return string|null                    Name of table alias (a to z) if the <data> element required a left join.
	 */
	protected function _addGetJoinAs( $data, $subFormula = null ) {
		$tableAs			=	null;

		$cnt_name			=	$data->attributes( 'name' );
		$cnt_as				=	$data->attributes( 'as' );
		$cnt_table			=	$data->attributes( 'table' );
		$cnt_key			=	$data->attributes( 'key' );
		$cnt_val			=	$data->attributes( 'value' );
		$cnt_valtype		=	$data->attributes( 'valuetype' );
		$cnt_joinkeys		=	$data->getElementByPath( 'joinkeys' );

		if ( ( $cnt_table && $this->_table )
			&& ( ( $cnt_table != $this->_table ) || ( $cnt_key && $cnt_val ) || $cnt_joinkeys ) )
		{
			// Compute JOIN keyword, e.g. LEFT JOIN:
			$joinKeyword	=	$this->computeJoinKeyword( $cnt_joinkeys );

			if ( $cnt_joinkeys ) {
				if ( ( ! $cnt_key ) && ( ! $cnt_val ) ) {
					// compute the array-indexes for the leftJoindTableKeyValueArray:
					foreach ( $cnt_joinkeys->children() as $column ) {
						/** @var $column SimpleXmlElement */
						$cnt_key[]		=	$column->attributes( 'name' );
						$cnt_val[]		=	$column->attributes( 'value' );
						$cnt_valtype[]	=	$column->attributes( 'valuetype' );
						$subFormula[]	=	$column->attributes( 'operator' ) . $column->attributes( 'type' );
						// Could be this but that doesn't work for group-search in CB User Management, to check later why:
						// $subFormula[]	=	$this->_db->NameQuote( $column->attributes( 'name' )) . ' ' . $column->attributes( 'operator' ) . ' ' . $this->_currentTableAs . '.'
						// 	.	( in_array( $column->attributes( 'valuetype' ), array( 'sql:field' ) ) ? $this->_db->NameQuote( $column->attributes( 'value' ) ) : $column->attributes( 'value' ) );
					}
					$cnt_key			=	implode( '&', $cnt_key );
					$cnt_val			=	implode( '&', $cnt_val );
					$cnt_valtype		=	implode( '&', $cnt_valtype );			// Above change would make this look like WHERE statements: sql:field`user_id` = a.`id`
					// done below: $subFormulaArrayKey	=	implode( '&', $subFormula );
				} else {
					trigger_error( sprintf( 'SQLXML::addGetJoinAs notice: data %s has joinkeys and key="%s" and/or value="%s" at same time. Ignoring key and value.', $cnt_name, $cnt_key, $cnt_val ), E_USER_NOTICE );
				}
			}

			$subFormulaArrayKey		=	is_array( $subFormula ) ? implode( '&', $subFormula ) : $subFormula;

			// if different table or same table but a key and a value are specified as self-join,
			// field value is taken from related record in other or self-joined table:

			if ( isset( $this->leftJoindTableKeyValueArray[$cnt_table][$cnt_key][$cnt_val][$cnt_valtype . $subFormulaArrayKey] ) ) {
				$tableAs				=	$this->leftJoindTableKeyValueArray[$cnt_table][$cnt_key][$cnt_val][$cnt_valtype . $subFormulaArrayKey];
			} else {
				$this->incrementTableAs();

				if ( $cnt_joinkeys ) {
					$subFormulaReal		=	$this->process_joinkeys( $cnt_joinkeys );
					$this->leftJoinArray[$this->tableAs]	= $joinKeyword . ' ' . $this->_db->NameQuote( $cnt_table ) . ' AS ' . $this->tableAs
						. ' ON ' . $subFormulaReal;
				} elseif ( ( ! $cnt_valtype ) || ( $cnt_valtype == 'sql:field' ) || ( $cnt_valtype == 'sql:parentfield' ) ) {

					$this->leftJoinArray[$this->tableAs]	= $joinKeyword . ' ' . $this->_db->NameQuote( $cnt_table ) . ' AS ' . $this->tableAs
						. ' ON ' . $this->tableAs . '.'
						. ( ( $subFormula === null ) ? '`' . $cnt_key . '` = ' . ( $cnt_valtype == 'sql:parentfield' ? $this->_currentTableAsStack[0] : $this->_currentTableAs ) . '.`' . $cnt_val . '`'
							: implode( ' AND ', $subFormula ) );
					//TBD this $subFormula is a temporary simplification/hack for process_reverse_sql_field ONLY: not even sure if it's needed !!!	: check if really needed.

				} elseif ( $cnt_key && $cnt_val && $cnt_valtype )  {
					$value		=	$this->sqlCleanQuote( $cnt_val, $cnt_valtype );

					$this->leftJoinArray[$this->tableAs]	= $joinKeyword . ' ' . $this->_db->NameQuote( $cnt_table ) . ' AS ' . $this->tableAs
						. ' ON ' . $this->tableAs . '.`' . $cnt_key . '` = ' . $value;
				}

				$this->leftJoindTableKeyValueArray[$cnt_table][$cnt_key][$cnt_val][$cnt_valtype . $subFormulaArrayKey]	=	$this->tableAs;
				$tableAs	=	$this->tableAs;
			}

			$this->leftJoinedFieldsTable[( $cnt_as ? $cnt_as : $cnt_name )]		=	$tableAs;
		}
		return $tableAs;
	}
	/**
	 * Treats a <data type="param:..."> node and its children:
	 * Count of related records in other table.
	 *
	 * @access private
	 * @param  SimpleXmlElement  $data
	 * @return string
	 */
	protected function process_param( $data ) {
		$cnt_name		=	$data->attributes( 'name' );
		$cnt_val		=	$data->attributes( 'value' );
		$cnt_valtype	=	$data->attributes( 'valuetype' );

		$formula		=	$this->sqlCleanQuote( $cnt_val, $cnt_valtype );

		if ( $cnt_name ) {
			$formula	.=	' AS `' . $cnt_name . '`';
		}
		return $formula;
	}

	/**
	 * Treats a <data> node and its <data> and <where> children
	 * NOT USED !?
	 *
	 * @param SimpleXmlElement  $data
	 * @param string              $fieldName
	 * @param string              $operator
	 * @param string              $fieldValue
	 * @param string              $type        ( 'sql:string' (default), 'sql:int', 'sql:float' )
	 */
	protected function addReverseWhere( $data, $fieldName, $operator, $fieldValue, $type ) {
		$childrenFormulas			=	$this->_composeSQLformula( $data );
		if ( $childrenFormulas != null ) {
			if ( ! is_array( $childrenFormulas ) ) {
				$cnt_name			=	$data->attributes( 'name' );
				$cnt_as				=	$data->attributes( 'as' );
				$childrenFormulas	=	array( ( $cnt_as ? $cnt_as : $cnt_name ) => $childrenFormulas );
			}
			$this->fieldsArray		=	array_merge( $this->fieldsArray, $childrenFormulas );
		}
		$this->_levelPush();
		$this->addWhere( $fieldName, $operator, $fieldValue, $type );
		$this->_levelPop();
	}

	/**
	 * Increments the "b" table alias name of "AS b" for joined tables
	 *
	 * @access private
	 */
	protected function incrementTableAs( ) {
		$this->tableAs	= chr( ord( $this->tableAs ) + 1 );
		$this->_currentTableAsStack[$this->_currentTableAsStackIdx]		=	$this->tableAs;
	}
	/**
	 * Pushes stack level one level down
	 */
	protected function _levelPush( ) {
		$this->_currentTableAs			=	$this->_currentTableAsStack[$this->_currentTableAsStackIdx];
		$this->_currentTableAsStack[]	=	$this->_currentTableAs;
		++$this->_currentTableAsStackIdx;
	}
	/**
	 * Pops stack level one level up
	 */
	protected function _levelPop( ) {
		--$this->_currentTableAsStackIdx;
		array_pop( $this->_currentTableAsStack );
		if ( $this->_currentTableAsStackIdx ) {
			$this->_currentTableAs			=	$this->_currentTableAsStack[$this->_currentTableAsStackIdx - 1];
		} else {
			$this->_currentTableAs			=	$this->maintableAs;
		}
	}


	/**
	 * Finds a left-joined $tableName with $key of $keyType = $value with $valueType and returns its 'AS' table-alias
	 *
	 * @param  string  $tableName
	 * @param  string  $key
	 * @param  string  $value
	 * @param  string  $keyType
	 * @param  string  $valueType
	 * @return boolean|string
	 */
	protected function findTableAs( $tableName, $key, $value, $keyType, $valueType )
	{
		if ( $tableName == $this->_table ) {
			return $this->maintableAs;
		}

		if ( isset( $this->leftJoindTableKeyValueArray[$tableName] )
			&& isset( $this->leftJoindTableKeyValueArray[$tableName][$key] )
			&& isset( $this->leftJoindTableKeyValueArray[$tableName][$key][$value] )
			&& isset( $this->leftJoindTableKeyValueArray[$tableName][$key][$value][$keyType . '=' . $valueType] )
		)
		{
			return $this->leftJoindTableKeyValueArray[$tableName][$key][$value][$keyType . '=' . $valueType];
		}

		return false;
	}

	/**
	 * Makes the tableAs entries references to the main query $mainQuery
	 *
	 * @param  XmlQuery  $mainQuery
	 * @return void
	 */
	public function syncSubQueryTablesIndexes( $mainQuery ) {
		//	$this->maintableAs					=	$mainQuery->maintableAs;
		$this->tableAs						=	$mainQuery->tableAs;
		//	$this->_currentTableAs				=	$mainQuery->_currentTableAs;
		$this->_currentTableAsStack			=	$mainQuery->_currentTableAsStack;
		$this->_currentTableAsStackIdx		=	$mainQuery->_currentTableAsStackIdx;
		//	$this->fieldsArray					=	$mainQuery->fieldsArray;
		//	$this->fieldsTypesArray				=	$mainQuery->fieldsTypesArray;
		$this->leftJoinedFieldsTable		=	$mainQuery->leftJoinedFieldsTable;
		//	$this->_table						=	$mainQuery->_table;
		$this->_pluginParams				=	$mainQuery->_pluginParams;
		$this->_extDataModels				=	$mainQuery->_extDataModels;
		// not referenced:
		/** array of individual table-alias => "LEFT JOIN ... ON ... " expressions for SELECT
		 *  @access private
		 *  @var array of string */
//	private $leftJoinArray				=	array();
		/** array of individual [table][key][value] -alias => "LEFT JOIN ... ON ... " expressions for SELECT
		 *  @access private
		 *  @var array of string */
//	private $leftJoindTableKeyValueArray =	array();
		/** array of individual table-alias => "LEFT JOIN ... ON ... " expressions used for SELECT COUNT(*),
		 *  as they are needed by the "WHERE" statements (these are duplicated in the ->leftJoinArray.
		 *  @access private
		 *  @var array of string */
//	private $leftJoinsNeededForWhere	=	array();
		/** individual fields (or formula) expressions for "WHERE" (will be imploded with "AND")
		 *  @access private
		 *  @var array of string */
//	private $where						=	array();
		/** array of individual expressions used for "GROUP BY" (will be imploded with "AND")
		 *  @access private
		 *  @var array of string */
//	private $groupByArray				=	array();
		/** individual fields (or formula) expressions for "HAVING"
		 *  @access private
		 *  @var array of string */
//	private $having						=	array();
		/** individual fields (or formula) with "ASC/DESC" expressions for "ORDER BY"
		 *  @access private
		 *  @var array of string */
//	private $orderArray					=	array();
		/** database object
		 * @access private
		 * @var DatabaseDriverInterface */
//	private $_db;
		/** Internal state for XML->SQL traversal and query-generation mode
		 * @access private
		 * @var boolean */
//	private $_reverse					=	false;
	}
	/**
	 * Cleans the field value by type in a secure way for SQL
	 *
	 * @param  mixed  $fieldValue
	 * @param  string $type         const,sql,param : string,int,float,datetime,formula
	 * @return string or boolean FALSE in case of type error
	 */
	public function sqlCleanQuote( $fieldValue, $type ) {
		return XmlTypeCleanQuote::sqlCleanQuote( $fieldValue, $type, $this->_pluginParams, $this->_db, $this->_extDataModels );
	}
	/**
	 * Putes the XML to SQL conversion into reverse mode, for joining instead of selecting
	 * Useful for search/filtering functions.
	 *
	 * @param  boolean $reverse  New state of reverse (straight=FALSE)
	 * @return boolean           Previous state
	 */
	public function setReverse( $reverse = null ) {
		$oldReverse				=	$this->_reverse;
		if ( $reverse !== null ) {
			$this->_reverse		=	$reverse;
		}
		return $oldReverse;
	}
	/**
	 * Adds a reference to external data models (objects or arrays for "ext:datatype:xxx:yyy" valuetype)
	 *
	 * @param  string  $dataName  Name of the ext: datatype
	 * @param  mixed   $data      Reference to external datatype (type: object, array, or string, int, float)
	 */
	public function setExternalDataTypeValues( $dataName, $data ) {
		$this->_extDataModels[$dataName]	=	$data;
	}
	/**
	 * Fixes the default 1 kB GROUP_CONCAT result to 64 kB (which is MySQL 5.0's maximum)
	 */
	protected function increaseGroupConcatMaxLen( ) {
		if ( $this->_group_concat_max_len_todo ) {
			$this->_db->query( 'SET SESSION group_concat_max_len = 65536;' );
			$this->_group_concat_max_len_todo	=	false;
		}
	}
}
