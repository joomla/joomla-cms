<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 5/14/14 1:54 PM $
* @package CBLib\Database
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Database;

use CBLib\Application\Application;
use CBLib\Xml\SimpleXMLElement;

defined('CBLIB') or die();

/**
 * CBLib\Database\DatabaseUpgrade Class implementation
 * 
 */
class DatabaseUpgrade
{
	/**
	 * Database
	 * @var DatabaseDriverInterface
	 */
	protected  $_db				=	null;

	/**
	 * TRUE: Silent on successful tests
	 * @var boolean
	 */
	protected $_silentTestLogs	=	true;

	/**
	 * Logs records with details (details here are SQL queries ( ";\n"-separated )
	 * @var array
	 */
	protected $_logs			=	array();

	/**
	 * Error records with details (details here is SQL query)
	 * @var array
	 */
	protected $_errors			=	array();

	/**
	 * Current index in $_errors variable
	 * @var int
	 */
	protected $_logsIndex		=	0;

	/**
	 * SQL tables changing queries should not be run (for a dry-run)
	 * @var boolean
	 */
	protected $_dryRun			=	false;

	/**
	 * Enforce preferred column types
	 * @var boolean
	 */
	protected $_enforceTypes	=	false;
	/**
	 * For INSERTs if should process as batch: array(), otherwise boolean FALSE. If array(), contains:
	 * _batchProcess[tableName][sqlColumnsText][] = sqlColumnsValues
	 * @var boolean|array
	 */
	protected $_batchProcess	=	false;

	/**
	 * Constructor
	 *
	 * @param  DatabaseDriverInterface  $db              Database driver interface
	 * @param  boolean                  $silentTestLogs  TRUE: Silent on successful tests
	 */
	public function __construct( DatabaseDriverInterface $db = null, $silentTestLogs = true )
	{
		if ( $db === null ) {
			$db									=	Application::Database();
		}

		$this->_db								=	$db;
		$this->_silentTestLogs					=	$silentTestLogs;
	}

	/**
	 * Sets if SQL tables changing queries should not be run (for a dry-run)
	 *
	 * @param  boolean  $dryRun  FALSE (default): tables are changed, TRUE: Dryrunning
	 * @return self              For chaining
	 */
	public function setDryRun( $dryRun )
	{
		$this->_dryRun							=	$dryRun;

		return $this;
	}

	/**
	 * Sets if SQL tables changing queries should not be run (for a dry-run)
	 *
	 * @param  boolean  $doEnforce  FALSE (default): tables are changed, TRUE: Dryrunning
	 * @return boolean              Previous state
	 */
	public function setEnforcePreferredColumnType( $doEnforce )
	{
		$old									=	$this->_enforceTypes;
		$this->_enforceTypes					=	$doEnforce;

		return $old;
	}

	/**
	 * LOGS OF ACTIONS AND OF ERRORS:
	 */

	/**
	 * Records error with details (details here is SQL query)
	 *
	 * @param  string  $error  Error to display
	 * @param  string  $info   Additional sensitive information to display on request (e.g. SQL queries that gave an error)
	 * @return self            For chaining
	 */
	public function setError( $error, $info = null)
	{
		$this->_errors[++$this->_logsIndex]		=	array( $error, $info );

		return $this;
	}

	/**
	 * Returns all errors logged
	 *
	 * @param  string|boolean  $implode         False: returns full array, if string: imploding string
	 * @param  string|boolean  $detailsImplode  False: no details, otherwise imploding string
	 * @return string|array                     Errors logged with setError during execution
	 */
	public function getErrors( $implode = "\n", $detailsImplode = false )
	{
		if ( $implode === false) {
			return $this->_errors;
		} else {
			$errors								=	array();
			if ( $detailsImplode ) {
				foreach ( $this->_errors as $errInfo ) {
					$errors[]					=	implode( $detailsImplode, $errInfo );
				}
			} else {
				foreach ( $this->_errors as $errInfo ) {
					$errors[]					=	$errInfo[0];
				}
			}
			return implode( $implode, $errors );
		}
	}

	/**
	 * Records logs with details (details here are SQL queries ( ";\n"-separated )
	 *
	 * @param  string  $log   Log text
	 * @param  string  $info  Detailed sensitive information to display on request (e.g. SQL queries ran)
	 * @param  string  $type  'ok': successful check, 'change': successful change
	 * @return self           For chaining
	 */
	public function setLog( $log, $info = null, $type )
	{
		if ( ( $type != 'ok' ) || ! $this->_silentTestLogs ) {
			$this->_logs[++$this->_logsIndex]	=	array( $log, $info );
		}

		return $this;
	}

	/**
	 * Returns all logs logged
	 *
	 * @param  string|boolean  $implode         False: returns full array, if string: imploding string
	 * @param  string|boolean  $detailsImplode  False: no details, otherwise imploding string
	 * @return string|array                     Logs
	 */
	public function getLogs( $implode = "\n", $detailsImplode = false )
	{
		if ( $implode === false) {
			return $this->_logs;
		} else {
			$logs								=	array();
			if ( $detailsImplode ) {
				foreach ( $this->_logs as $logInfo ) {
					$logs[]						=	implode( $detailsImplode, $logInfo );
				}
			} else {
				foreach ( $this->_logs as $logInfo ) {
					$logs[]						=	$logInfo[0];
				}
			}
			return implode( $implode, $logs );
		}
	}

	/**
	 * MAIN FUNCTIONS:
	 */

	/**
	 * Checks if all columns of a xml description of all tables of a database matches the database
	 *
	 * Warning: if ( $change && $strictlyColumns ) it will DROP not described columns !!!
	 *
	 * 	<database version="1">
	 *		<table name="#__comprofiler" class="\CB\Database\Table\ComprofilerTable">
	 *			<columns>
	 *				<column name="_rate" nametype="namesuffix" type="sql:decimal(16,8)" unsigned="true" null="true" default="NULL" auto_increment="100" />
	 *		<table name="#__comprofiler_hf2_" nametype="nameprefix" class="myClass" strict="true">
	 *			<indexes>
	 *				<index name="primary" type="primary">
	 *					<column name="id"	/>
	 *				</index>
	 *				<index name="rate_chars">
	 *					<column name="rate" />
	 *					<column name="_mychars" nametype="namesuffix" size="8" ordering="ASC" />
	 *				</index>
	 *				<index name="chars_rate_id" type="unique" using="btree">
	 *
	 * @param  SimpleXMLElement           $db
	 * @param  string                     $colNamePrefix    Prefix to add to all column names
	 * @param  boolean|string             $change           FALSE: only check, TRUE: change database to match description (deleting non-matching columns if $strictlyColumns == true), 'drop': uninstalls columns/tables
	 * @param  boolean|null               $strictlyColumns  FALSE: allow for other columns, TRUE: doesn't allow for other columns
	 * @param  boolean|string|null        $strictlyEngine   FALSE: engine unchanged, TRUE: force engine change to type, updatewithtable: updates to match table, NULL: checks for attribute 'strict' in table
	 * @return boolean                    TRUE: matches, FALSE: don't match
	 */
	public function checkXmlDatabaseDescription( SimpleXMLElement $db, $colNamePrefix = '', $change = false, $strictlyColumns = false, $strictlyEngine = false )
	{
		$isMatching								=	false;
		if ( $db->getName() == 'database' ) {
			$isMatching							=	true;
			foreach ( $db->children() as $table ) {
				if ( $table->getName() == 'table' ) {
					if ( is_bool( $change ) ) {
						$isMatching				=	$this->checkXmlTableDescription( $table, $colNamePrefix, $change, $strictlyColumns, $strictlyEngine ) && $isMatching;
					} else {
						$isMatching				=	$this->dropXmlTableDescription( $table, $colNamePrefix, $change, $strictlyColumns, $strictlyEngine ) && $isMatching;
					}
				}
			}
		}
		return $isMatching;
	}

	/**
	 * Checks if all columns of a xml description of all tables of a database matches the database
	 *
	 * Warning: if ( $change && $strictlyColumns ) it will DROP not described columns !!!
	 *
	 * @param  SimpleXMLElement    $table
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @param  boolean             $change           FALSE: only check, TRUE: change database to match description (deleting columns if $strictlyColumns == true)
	 * @param  boolean|null        $strictlyColumns  FALSE: allow for other columns, TRUE: doesn't allow for other columns, NULL: checks for attribute 'strict' in table
	 * @param  boolean|null        $strictlyEngine   FALSE: engine unchanged, TRUE: force engine change to type, updatewithtable: updates to match table, NULL: checks for attribute 'strict' in table
	 * @return boolean             TRUE: matches, FALSE: don't match
	 */
	public function checkXmlTableDescription( SimpleXMLElement $table, $colNamePrefix = '', $change = false, $strictlyColumns = false, $strictlyEngine = false )
	{
		$isMatching								=	false;
		$directlyInsert							=	false;
		if ( $table->getName() == 'table' ) {
			$tableName							=	$this->prefixedName( $table, $colNamePrefix );
			$columns							=	$table->getElementByPath( 'columns' );
			$engine								=	$table->getElementByPath( 'engine' );
			if ( $tableName ) {
				$isMatching							=	true;
				if ( $columns !== false ) {
					if ( $strictlyColumns === null ) {
						$strictlyColumns			=	( $table->attributes( 'strict' ) == 'true' );
					}
					$allColumns						=	$this->getAllTableColumns( $tableName );
					if ( $allColumns === false ) {
						// table doesn't exist:
						if ( $change ) {
							if ( $this->createTable( $table, $colNamePrefix ) ) {
								$allColumns			=	$this->getAllTableColumns( $tableName );
								$directlyInsert		=	true;		// as we just created table, we can directly insert rows now
							} else {
								$isMatching			=	false;
							}
						} else {
							$this->setError( sprintf( 'Table %s does not exist', $tableName ), null );
							$isMatching				=	false;
						}
					} else {
						// Table exists:
						// 1) Check columns:
						if ( $strictlyColumns ) {
							$columnBefore			=	1;
						} else {
							$columnBefore			=	null;
						}
						foreach ( $columns->children() as $column ) {
							if ( $column->getName() == 'column' ) {
								if ( ! $this->checkColumnExistsType( $tableName, $allColumns, $column, $colNamePrefix, $change ) ) {
									if ( ( ! $change ) || ( ! $this->changeColumn( $tableName, $allColumns, $column, $colNamePrefix, $columnBefore ) ) ) {
										$isMatching	=	false;
									}
								}

								if ( array_key_exists( $column->attributes( 'name' ), $allColumns ) ) {
									// Column exists in current table, so next column in foreach can be after this $column
									$columnBefore	=	$column;
								}
							}
						}
						if ( $strictlyColumns && ( $columns->attributes( 'strict' ) !== 'false' ) && ! $this->checkOtherColumnsExist( $tableName, $allColumns, $columns, $colNamePrefix, $change ) ) {
							$isMatching				=	false;
						}

						// 2) Check indexes:
						$indexes					=	$table->getElementByPath( 'indexes' );
						if ( $indexes !== false ) {
							$allIndexes				=	$this->getAllTableIndexes( $tableName );
							foreach ( $indexes->children() as $index ) {
								if ( $index->getName() == 'index' ) {
									if ( ! $this->checkIndexExistsType( $tableName, $allIndexes, $index, $colNamePrefix, $change ) ) {
										if ( ( ! $change ) || ( ! $this->changeIndex( $tableName, $allIndexes, $index, $colNamePrefix ) ) ) {
											$isMatching	=	false;
										}
									}
								}
							}
							if ( $strictlyColumns && ( $indexes->attributes( 'strict' ) !== 'false' ) && ! $this->checkOtherIndexesExist( $tableName, $allIndexes, $indexes, $colNamePrefix, $change ) ) {
								$isMatching			=	false;
							}
						}
					}
					// 3) Now that indexed table is checked (exists or has been created), Check rows:
					if ( $allColumns !== false ) {
						$rows						=	$table->getElementByPath( 'rows' );
						if ( $rows !== false ) {
							// enable batch inserts to gain speed:
							$this->_batchProcess	=	array();
							foreach ( $rows->children() as $row ) {
								if ( $row->getName() == 'row' ) {
									// build the insert statements:
									if ( ! $this->checkOrChangeRow( $tableName, $row, $columns, $allColumns, $colNamePrefix, $change, $directlyInsert ) ) {
										$isMatching	=	false;
									}
								}
							}
							// now process the INSERTS:
							$this->processBatchInserts();
							if ( $strictlyColumns && ( $rows->attributes( 'strict' ) !== 'false' ) && ! $this->checkOtherRowsExist( $tableName, $rows, $colNamePrefix, $change ) ) {
								$isMatching			=	false;
							}
						}
					}
				}
				// 4) Check table engine against an existing tables engine if possible then update as needed:
				if ( $engine !== false ) {
					$engineType								=	$engine->attributes( 'type' );

					if ( $engineType !== null ) {
						if ( $strictlyEngine === null ) {
							$strictlyEngine					=	$engine->attributes( 'strict' );
						}

						if ( ! ( ( $strictlyEngine === null ) && ( $strictlyEngine === 'false' ) && ( $strictlyEngine === false ) ) ) {
							$currentEngine					=	$this->checkTableEngine( $tableName );

							if ( $currentEngine ) {
								if ( ( $strictlyEngine === 'true' ) || ( $strictlyEngine === true ) ) {
									if ( $engineType != $currentEngine ) {
										$this->setError( sprintf( 'Table %s Storage Engine is %s instead of %s', $tableName, $currentEngine, $engineType ) );

										if ( ( ! $change ) || ( ! $this->changeEngine( $tableName, $engineType ) ) ) {
											$isMatching		=	false;
										}
									}
								} elseif ( $strictlyEngine === 'updatewithtable' ) {
									$engineTable			=	$engine->attributes( 'sameastable' );

									if ( $engineTable !== null ) {
										$compareEngine		=	$this->checkTableEngine( $engineTable );
									} else {
										$compareEngine		=	null;
									}

									if ( $compareEngine && ( $compareEngine != $currentEngine ) ) {
										$this->setError( sprintf( 'Table %s Storage Engine is %s instead of %s', $tableName, $currentEngine, $compareEngine ) );

										if ( ( ! $change ) || ( ! $this->changeEngine( $tableName, $compareEngine ) ) ) {
											$isMatching		=	false;
										}
									}
								}
							}
						}
					}
				}
			}
		}
		return $isMatching;
	}

	/**
	 * Returns main table name (pre/post/fixed with $colNamePrefix)
	 *
	 * <database>
	 * 		<table name="xyz" nametype="nameprefix" maintable="true">
	 *
	 * @param  SimpleXMLElement    $db
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @param  string|null         $default          Default table result to return if table not found in xml
	 * @return string
	 */
	public function getMainTableName( SimpleXMLElement $db, $colNamePrefix = '', $default = null )
	{
		$mainTable								=	$db->getChildByNameAttr( 'table', 'maintable', 'true' );
		if ( $mainTable !== false ) {
			return $this->prefixedName( $mainTable, $colNamePrefix );
		}
		return $default;
	}

	/**
	 * Returns array of column names (pre/post/fixed with $colNamePrefix) of $table
	 *
	 * @param  SimpleXMLElement    $db
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @return array|boolean                         False if not found
	 */
	public function getMainTableColumnsNames( SimpleXMLElement $db, $colNamePrefix = '' )
	{
		$table									=	$db->getChildByNameAttr( 'table', 'maintable', 'true' );
		if ( $table !== false ) {
			$columns							=	$table->getElementByPath( 'columns' );
			if ( $columns !== false ) {
				$columnNamesArray				=	array();
				foreach ( $columns->children() as $column ) {
					if ( $column->getName() == 'column' ) {
						$columnNamesArray[]		=	$this->prefixedName( $column, $colNamePrefix );
					}
				}
				return $columnNamesArray;
			}
		}
		return false;
	}

	/**
	 * Dumps $tablesNames databases tables $withContent (true) or without content (false) into an XML SimpleXMLElement structure
	 *
	 * @param  string|array        $tablesNames      Name(s) of tables to dump
	 * @param  boolean             $withContent      FALSE: only structure, TRUE: also content
	 * @return SimpleXMLElement
	 */
	public function dumpTableToXml( $tablesNames, $withContent = true )
	{
		$db											=	new SimpleXMLElement( '<?xml version="1.0" encoding="UTF-8"?><database version="1" />' );

		foreach ( (array) $tablesNames as $tableName ) {

			$table									=	$db->addChild( 'table' );
			$table->addAttribute( 'name', $tableName );
			$table->addAttribute( 'class', '' );
			$table->addAttribute( 'strict', 'false' );
			$table->addAttribute( 'drop', 'never' );

			// Columns:

			$allColumns								=	$this->getAllTableColumns( $tableName );

			/** @var SimpleXMLElement $columns */
			$columns								=	$table->addChild( 'columns' );
			foreach ( $allColumns as $colEntry ) {
				$colTypeUnsigned					=	explode( ' ', $colEntry->Type );
				if ( count( $colTypeUnsigned ) == 1 ) {
					$colTypeUnsigned[1]				=	null;
				}
				$column								=	$columns->addChild( 'column' );
				$column->addAttribute( 'name', 		$colEntry->Field );
				$column->addAttribute( 'type',		'sql:' . $colTypeUnsigned[0] );
				if ( $colTypeUnsigned[1] === 'unsigned' ) {
					$column->addAttribute( 'unsigned',	( $colTypeUnsigned[1] === 'unsigned' ? 'true' : 'false' ) );
				}
				if ( $colEntry->Null === 'YES' ) {
					$column->addAttribute( 'null',		( $colEntry->Null === 'YES' ? 'true' : 'false' ) );
				}
				if ( $colEntry->Default !== null ) {
					if ( $colEntry->Null === 'YES' ) {
						$column->addAttribute( 'default', $colEntry->Default );
					} else {
						$defaultDefaultTypes		=	$this->defaultValuesOfTypes( $this->mysqlToXmlSql( 'sql:' . $colTypeUnsigned[0] ) );
						if ( ! in_array( $colEntry->Default, $defaultDefaultTypes ) ) {
							$column->addAttribute( 'default', $colEntry->Default );
						}
					}
				}
				if ( strpos( $colEntry->Extra, 'auto_increment' ) !== false ) {
					$tableStatus					=	$this->_db->getTableStatus( $tableName );
					if ( isset( $tableStatus[0]->Auto_increment ) ) {
						$lastAuto_increment			=	$tableStatus[0]->Auto_increment;
					} else {
						$lastAuto_increment			=	'100';
					}
					$column->addAttribute( 'auto_increment', $lastAuto_increment );
				}
			}

			// Indexes:

			$indexes								=	$table->addChild( 'indexes' );

			$primaryIndex							=	null;

			$allIndexes								=	$this->getAllTableIndexes( $tableName );

			foreach ( $allIndexes as $indexName => $sequenceInIndexArray ) {
				$type								=	$sequenceInIndexArray[1]['type'];
				$using								=	$sequenceInIndexArray[1]['using'];

				$index								=	$indexes->addChild( 'index' );
				$index->addAttribute( 'name',	$indexName );
				if ( $type != '' ) {
					$index->addAttribute( 'type',	$type );
				}
				if ( $using != 'btree' ) {
					$index->addAttribute( 'using',	$using );
				}
				foreach ( $sequenceInIndexArray as /* $sequenceInIndex => */ $indexAttributes ) {
					$column							=	$index->addChild( 'column' );
					$column->addAttribute( 'name', $indexAttributes['name'] );
					if ( $indexAttributes['size'] ) {
						$column->addAttribute( 'size', $indexAttributes['size'] );
					}
					if ( $indexAttributes['ordering'] != 'A' ) {
						$column->addAttribute( 'ordering', $indexAttributes['ordering'] );
					}
				}
				if ( $type == 'primary' ) {
					$primaryIndex					=	$index;
				}
			}

			// Content:

			if ( $withContent ) {
				$allRows							=	$this->loadRows( $tableName, null, null, null );
				if ( count( $allRows ) > 0 ) {
					$rows							=	$table->addChild( 'rows' );

					$primaryNames					=	null;
					if ( $primaryIndex !== null ) {
						foreach ( $primaryIndex->children() as $column ) {
							/** @var SimpleXMLElement $column */
							if ( $column->getName() == 'column' ) {
								$primaryNames[]		=	$column->attributes( 'name' );
							}
						}
					}

					foreach ( $allRows as $rowData ) {
						$row								=	$rows->addChild( 'row' );
						// missing primary key here:
						$rowIndexName						=	array();
						$rowIndexValue						=	array();
						$rowIndexValueType					=	array();
						foreach ( get_object_vars( $rowData ) as $fieldDataName => $fieldDataValueReferenceInObject ) {
							if ( $fieldDataName[0] != '_' ) {
								/** Workaround PHP bug https://bugs.php.net/bug.php?id=66961 : */
								$fieldDataValue				=	$fieldDataValueReferenceInObject;

								$typeColumn					=	$columns->getChildByNameAttributes( 'column', array( 'name' => $fieldDataName ) );
								$fieldDataValueType			=	'const:' . $this->mysqlToXmlSql( $typeColumn->attributes( 'type' ) );
								$field						=	$row->addChild( 'field' );
								if ( $fieldDataValue === null ) {
									$fieldDataValue			=	'NULL';
									$fieldDataValueType		=	'const:null';
								}
								$field->addAttribute( 'name', $fieldDataName );
								$field->addAttribute( 'value', $fieldDataValue );
								$field->addAttribute( 'valuetype', $fieldDataValueType );
								if ( in_array( $fieldDataName, $primaryNames ) ) {
									$field->addAttribute( 'strict', 'true' );
									$rowIndexName[]			=	$fieldDataName;
									$rowIndexValue[]		=	$fieldDataValue;
									$rowIndexValueType[]	=	$fieldDataValueType;
								}
							}
						}
						$row->addAttribute( 'index',	 implode( ' ', $rowIndexName ) );
						$row->addAttribute( 'value',	 implode( ' ', $rowIndexValue ) );
						$row->addAttribute( 'valuetype', implode( ' ', $rowIndexValueType ) );
					}
				}
			}
		}
		return $db;
	}

	/**
	 * SQL ACCESS FUNCTIONS:
	 */

	/**
	 * Checks if a given table exists in the database
	 *
	 * @param  string  $tableName  Name of table
	 * @return boolean
	 */
	protected function checkTableExists( $tableName )
	{
		$allTables								=	$this->_db->getTableList();
		return ( in_array( $tableName, $allTables ) );
	}

	/**
	 * Checks if table exists in database and returns all fields of table.
	 * Otherwise returns boolean false.
	 *
	 * @param  string  $tableName  Name of table
	 * @return array|boolean       Array of SHOW (FULL) COLUMNS FROM ... in SQL or boolean FALSE
	 */
	protected function getAllTableColumns( $tableName )
	{
		if ( $this->checkTableExists( $tableName ) ) {
			$fields								=	$this->_db->getTableFields( $tableName, false );
			if ( isset( $fields[$tableName] ) ) {
				return $fields[$tableName];
			}
		}
		return false;
	}

	/**
	 * Returns all indexes of the table
	 *
	 * @param  string  $tableName  Name of table
	 * @return array               Array of SHOW INDEX FROM ... in SQL
	 */
	protected function getAllTableIndexes( $tableName )
	{
		$sortedIndex							=	array();
		$idx									=	$this->_db->getTableIndex( $tableName );
		if ( is_array( $idx ) ) {
			foreach ( $idx as $n ) {
				$sortedIndex[$n->Key_name][$n->Seq_in_index]	=	array(
					'name'		=>	$n->Column_name,
					'size'		=>	$n->Sub_part,
					'ordering'	=>	$n->Collation,

					'type'		=>	( $n->Key_name == 'PRIMARY' ? 'primary' : ( $n->Non_unique == 0 ? 'unique' : '' ) ),
					'using'		=>	( array_key_exists( 'Index_type', $n ) ? strtolower( $n->Index_type ) : ( $n->Comment == 'FULLTEXT' ? 'fulltext' : '' ) )	// mysql <4.0.2 support
				);
			}
		}
		return $sortedIndex;
	}

	/**
	 * COLUMNS CHECKS:
	 */

	/**
	 * Checks if a column exists and has the type of the parameters below:
	 *
	 * @param  string              $tableName        Name of table (for engine type and error strings)
	 * @param  array               $allColumns       From $this->getAllTableColumns( $table )
	 * @param  SimpleXMLElement    $column           Column to check
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @param  boolean             $change           TRUE: only true/false check type, FALSE: logs success and if mismatch, error details
	 * @return boolean             TRUE: identical (no check on indexes), FALSE: errors are in $this->getErrors()
	 */
	protected function checkColumnExistsType( $tableName, $allColumns, SimpleXMLElement $column, $colNamePrefix, $change )
	{
		$colName								=	$this->prefixedName( $column, $colNamePrefix );
		if ( isset( $allColumns[$colName] ) )
		{
			if ( ! cbStartOfStringMatch( $column->attributes( 'type' ), 'sql:' ) ) {
				$this->setError( sprintf( 'Table %s Column %s type is %s instead of being prefixed by "sql:"', $tableName, $colName, $column->attributes( 'type' ) ) );
				return false;
			}
			if ( $column->attributes( 'strict' ) === 'false' ) {
				$this->setLog( sprintf( 'Table %s Column %s exists but is not of strict type, so not checked.', $tableName, $colName ), null, 'ok' );
				return true;
			}

			$type								=	$this->getMatchingColumnType( $allColumns[$colName]->Type, $column, $tableName );

			if ( $type === null ) {
				if ( $change === false ) {
					$this->setError( sprintf( 'Table %s Column %s type is %s instead of %s', $tableName, $colName, $allColumns[$colName]->Type, substr( $this->getPreferredColumnType( $column, $tableName ), 4 ) . ( $column->attributes( 'unsigned' ) === 'true' ? ' unsigned' : '' ) ) );
				}
				return false;
			}
			if ( ( $column->attributes( 'null' ) === 'true' ) !== ( $allColumns[$colName]->Null == 'YES' ) ) {		//if ( $column->attributes( 'null' ) !== null ): no attribute NULL means NOT NULL
				if ( $change === false ) {
					$this->setError( sprintf( 'Table %s Column %s NULL attribute is %s instead of %s', $tableName, $colName, $allColumns[$colName]->Null, ( $column->attributes( 'null' ) === 'true' ? 'YES' : 'NO') ) );
				}
				return false;
			}

			// BLOB and TEXT columns cannot have DEFAULT values. http://dev.mysql.com/doc/refman/5.0/en/blob.html
			$defaultValuePossible				=	! in_array( $type, array( 'text', 'blob', 'tinytext', 'mediumtext', 'longtext', 'tinyblob', 'mediumblob', 'longblob' ) );
			// auto-incremented columns don't care for default values:
			$autoIncrementedColumn				=	! in_array( $column->attributes( 'auto_increment' ), array( null, '', 'false' ), true );

			if ( $defaultValuePossible && ! $autoIncrementedColumn ) {
				if ( $column->attributes( 'default' ) === null ) {
					if ( $column->attributes( 'null' ) === 'true' ) {
						$shouldBeDefault			=	array( null );
					} else {
						$shouldBeDefault			=	$this->defaultValuesOfTypes( $this->mysqlToXmlSql( 'sql:' . $type ) );
					}
				} else {
					$shouldBeDefault				=	( $column->attributes( 'default' ) === 'NULL' ? array( null ) : array( $column->attributes( 'default' ) ) );
				}
				if ( ! in_array( $allColumns[$colName]->Default, $shouldBeDefault, true ) ) {
					if ( $change === false ) {
						$this->setError( sprintf( 'Table %s Column %s DEFAULT is %s instead of %s', $tableName, $colName, $this->displayNull( $allColumns[$colName]->Default ), $column->attributes( 'default' ) ) );
					}
					return false;
				}
			}
			$shouldBeExtra						=	( $autoIncrementedColumn ? 'auto_increment' : '' );
			if ( $allColumns[$colName]->Extra !== $shouldBeExtra ) {
				if ( $change === false ) {
					$this->setError( sprintf( 'Table %s Column %s AUTO_INCREMENT attribute is "%s" instead of "%s"', $tableName, $colName, $allColumns[$colName]->Extra, $shouldBeExtra ) );
				}
				return false;
			}
			$this->setLog( sprintf( 'Table %s Column %s structure is up-to-date.', $tableName, $colName ), null, 'ok' );
			return true;
		}
		else
		{
			if ( $column->attributes( 'mandatory' ) === 'false' ) {
				$this->setLog( sprintf( 'Table %s Column %s does not exist but is not mandatory, so is up-to-date.', $tableName, $colName ), null, 'ok' );
				return true;
			}
		}

		if ( $change === false ) {
			$this->setError( sprintf( 'Table %s Column %s does not exist', $tableName, $colName ), null );
		}
		return false;
	}

	/**
	 * Gets the matching column type if exists, otherwise returns null
	 *
	 * @param  string            $isType     Current type of column in database ( Type of the SQL schema)
	 * @param  SimpleXMLElement  $column     Column XML element
	 * @param  string            $tableName  Name of table (for engine type and error strings)
	 * @return string|null                   Matching type of $column type attribute
	 */
	protected function getMatchingColumnType( $isType, SimpleXMLElement $column, $tableName )
	{
		foreach ( $this->getAllPossibleColumnTypes( $column ) as $possibleType )
		{
			if ( ! cbStartOfStringMatch( $possibleType, 'sql:' ) ) {
				$this->setError( sprintf( 'Column %s type is %s instead of being prefixed by "sql:"', $column->attributes( 'name' ), $column->attributes( 'type' ) ) );
				continue;
			}

			$type					=	substr( $possibleType, 4 );
			$shouldBeType			=	$type . ( $column->attributes( 'unsigned' ) === 'true' ? ' unsigned' : '' );
			if ( $isType === $shouldBeType ) {

				if ( $this->_enforceTypes && ( $possibleType != $this->getPreferredColumnType( $column, $tableName ) ) ) {
					return null;
				}

				return $type;
			}

		}

		return null;
	}

	/**
	 * Get prefered column type
	 *
	 * @param  SimpleXMLElement  $column  Column
	 * @param  string              $tableName    Name of table (for determining engine for preferred type)
	 * @param  string              $tableEngine  Engine of table (if $tableName is not yet created, for preferred type)
	 * @return string|null                Prefered column type
	 */
	protected function getPreferredColumnType( SimpleXMLElement $column, $tableName, $tableEngine = null )
	{
		if ( $tableEngine === null ) {
			$tableEngine		=	$this->checkTableEngine( $tableName );
		}

		$types					=	$this->getAllPossibleColumnTypes( $column );

		if ( ( count( $types ) > 1 ) && ( $tableEngine == 'MyISAM' ) ) {
			return $types[1];
		}

		return $types[0];
	}

	/**
	 * Get all possible column types
	 *
	 * @param  SimpleXMLElement  $column  Column
	 * @return string[]|null[]            All possible column types
	 */
	protected function getAllPossibleColumnTypes( SimpleXMLElement $column )
	{
		return explode( '||', $column->attributes( 'type' ) );
	}

	/**
	 * Utility to display NULL for nulls and quotations.
	 *
	 * @param  string|null  $val  Numeric value, string or NULL
	 * @return string             Numeric unquoted string, Quoted string, or NULL as string
	 */
	protected function displayNull( $val )
	{
		if ( $val === null ) {
			return 'NULL';
		} elseif ( is_numeric( $val ) ) {
			return $val;
		} else {
			return "'" . $val . "'";
		}
	}

	/**
	 * Checks if a column exists and has the type of the parameters below:
	 *
	 * @param  string              $tableName       Name of table (for error strings)
	 * @param  array               $allColumns      From $this->getAllTableColumns( $table )
	 * @param  SimpleXMLElement    $columns         Columns to check array of string  Name of columns which are allowed to (should) exist
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @param  boolean             $drop            TRUE If drops unneeded columns or not
	 * @return boolean             TRUE: no other columns exist, FALSE: errors are in $this->getErrors()
	 */
	protected function checkOtherColumnsExist( $tableName, $allColumns, SimpleXMLElement $columns, $colNamePrefix, $drop = false )
	{
		$isMatching								=	false;
		if ( $columns->getName() == 'columns' ) {
			$isMatching							=	true;
			foreach ( array_keys( $allColumns ) as $existingColumnName ) {
				if ( ! $this->inXmlChildrenAttribute( $existingColumnName, $columns, 'column', 'name', $colNamePrefix ) ) {
					if ( $drop ) {
						if ( ! $this->dropColumn( $tableName, $existingColumnName ) ) {
							$isMatching			=	false;
						}
					} else {
						$isMatching				=	false;
						$this->setError( sprintf( 'Table %s Column %s exists but should not exist', $tableName, $existingColumnName ), null );
					}
				}
			}
			if ( $isMatching && ! $drop ) {
				$this->setLog( sprintf( 'Table %s has no unneeded columns.', $tableName ), null, 'ok' );
			}
		}
		return $isMatching;
	}

	/**
	 * INDEXES CHECKS:
	 */

	/**
	 * Checks if an index exists and has the type of the parameters below:
	 *
	 * @param  string              $tableName        Name of table (for error strings)
	 * @param  array               $allIndexes       From $this->getAllTableIndexes( $table )
	 * @param  SimpleXMLElement    $index            Index to check
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @param  boolean             $change           TRUE: only true/false check type, FALSE: logs success and if mismatch, error details
	 * @return boolean             TRUE: identical, FALSE: errors are in $this->getErrors()
	 */
	protected function checkIndexExistsType( $tableName, $allIndexes, SimpleXMLElement $index, $colNamePrefix, $change )
	{
		$indexName								=	$this->prefixedName( $index, $colNamePrefix );
		if ( isset( $allIndexes[$indexName] ) && isset( $allIndexes[$indexName][1] ) ) {
			$idxType							=	$allIndexes[$indexName][1]['type'];
			$idxUsing							=	$allIndexes[$indexName][1]['using'];

			if ( $idxType != $index->attributes( 'type' ) ) {
				if ( $change === false ) {
					$this->setError( sprintf( 'Table %s Index %s type is %s instead of %s', $tableName, $indexName, $idxType, $index->attributes( 'type' ) ) );
				}
				return false;
			}
			if ( $index->attributes( 'using' ) && ( $idxUsing != $index->attributes( 'using' ) ) ) {
				if ( $change === false ) {
					$indexShouldBeUsing			=	( $index->attributes( 'using' ) ? $index->attributes( 'using' ) : 'btree' );
					$this->setError( sprintf( 'Table %s Index %s is using %s instead of %s', $tableName, $indexName, $idxUsing, $indexShouldBeUsing ) );
				}
				return false;
			}
			$sequence							=	1;
			foreach ( $index->children() as $column ) {
				if ( $column->getName() == 'column' ) {
					$colName					=	$this->prefixedName( $column, $colNamePrefix );
					if ( ! isset( $allIndexes[$indexName][$sequence] ) ) {
						if ( $change === false ) {
							$this->setError( sprintf( 'Table %s Index %s Column %s is missing in index', $tableName, $indexName, $colName ) );
						}
						return false;
					}
					if ( $allIndexes[$indexName][$sequence]['name'] != $colName ) {
						if ( $change === false ) {
							$this->setError( sprintf( 'Table %s Index %s Column %s is not the intended column, but %s', $tableName, $indexName, $colName, $allIndexes[$indexName][$sequence]['name'] ) );
						}
						return false;
					}
					if ( $column->attributes( 'size' ) && ( $allIndexes[$indexName][$sequence]['size'] != $column->attributes( 'size' ) ) ) {
						if ( $change === false ) {
							$this->setError( sprintf( 'Table %s Index %s Column %s Size is %d instead of %s', $tableName, $indexName, $colName, $allIndexes[$indexName][$sequence]['size'], $column->attributes( 'size' ) ) );
						}
						return false;
					}
					// don't check ordering, as it can't be checked, and is probably irrelevant.
					++$sequence;
				}
			}
			$this->setLog( sprintf( 'Table %s Index %s is up-to-date.', $tableName, $indexName ), null, 'ok' );
			return true;
		}
		if ( $change === false ) {
			$this->setError( sprintf( 'Table %s Index %s does not exist', $tableName, $indexName ), null );
		}
		return false;
	}

	/**
	 * Checks if no surnumerous indexes exist
	 *
	 * @param  string              $tableName        Name of table (for error strings)
	 * @param  array               $allIndexes       From $this->getAllTableIndexes( $table )
	 * @param  SimpleXMLElement    $indexes          Indexes to check
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @param  boolean             $drop             TRUE If drops unneeded columns or not
	 * @return boolean             TRUE: no other columns exist, FALSE: errors are in $this->getErrors()
	 */
	protected function checkOtherIndexesExist( $tableName, $allIndexes, SimpleXMLElement $indexes, $colNamePrefix, $drop = false )
	{
		$isMatching								=	false;
		if ( $indexes->getName() == 'indexes' ) {
			$isMatching							=	true;
			foreach ( array_keys( $allIndexes ) as $existingIndexName ) {
				if ( ! $this->inXmlChildrenAttribute( $existingIndexName, $indexes, 'index', 'name', $colNamePrefix ) ) {
					if ( $drop ) {
						if ( ! $this->dropIndex( $tableName, $existingIndexName ) ) {
							$isMatching			=	false;
						}
					} else {
						$isMatching				=	false;
						$this->setError( sprintf( 'Table %s Index %s exists but should not exist', $tableName, $existingIndexName ), null );
					}
				}
			}
			if ( $isMatching && ! $drop ) {
				$this->setLog( sprintf( 'Table %s has no unneeded indexes.', $tableName ), null, 'ok' );
			}
		}
		return $isMatching;
	}

	/**
	 * ROWS CHECKS:
	 */

	/**
	 * Checks if no surnumerous indexes exist
	 *
	 * @param  string              $tableName        Name of table (for error strings)
	 * @param  SimpleXMLElement    $rows             <rows...>
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @param  boolean             $drop             TRUE If drops unneeded columns or not
	 * @return boolean             TRUE: no other columns exist, FALSE: errors are in $this->getErrors()
	 */
	protected function checkOtherRowsExist( $tableName, SimpleXMLElement $rows, $colNamePrefix, $drop = false )
	{
		$isMatching								=	false;
		if ( $rows->getName() == 'rows' ) {
			$isMatching							=	true;
			// $strictRows						=	( ( $rows->attributes( 'strict' ) === 'true' ) );
			if ( true /* $strictRows */ ) {

				// Build $strictRows index of indexes:
				$rowIndexes						=	array();
				foreach ( $rows->children() as $row ) {
					if ( $row->getName() == 'row' ) {
						$indexName				=	$this->prefixedName( $row, $colNamePrefix, 'index', 'indextype' );
						$indexValue				=	$row->attributes( 'value' );
						$indexValueType			=	$row->attributes( 'valuetype' );
						$rowIndexes[$indexName][$indexValue]	=	$indexValueType;
					}
				}

				// Count and if asked, drop rows which don't match:
				$otherRowsCount					=	$this->countRows( $tableName, $rowIndexes, false );
				$isMatching						=	( ( $otherRowsCount !== null ) && ( $otherRowsCount == 0 ) );
				if ( ! $isMatching ) {
					if ( $drop ) {
						$isMatching				=	$this->dropRows( $tableName, $rowIndexes, false );
					} else {
						$this->setError( sprintf( 'Table %s has %s rows which should not exist', $tableName, $otherRowsCount ), null );
					}
				}
			}

			if ( $isMatching && ! $drop ) {
				$this->setLog( sprintf( 'Table %s has no unneeded rows.', $tableName ), null, 'ok' );
			}
		}
		return $isMatching;
	}

	/**
	 * Drops $row from table $tableName
	 *
	 * @param  string   $tableName                   Name of table (for error strings)
	 * @param  SimpleXMLElement    $row              <row index="columnname" indextype="prefixname" value="123" valuetype="sql:int" /> to delete
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @return boolean
	 */
	protected function dropRow( $tableName, SimpleXMLElement $row, $colNamePrefix )
	{
		$indexName								=	$this->prefixedName( $row, $colNamePrefix, 'index', 'indextype' );
		$indexValue								=	$row->attributes( 'value' );
		$indexValueType							=	$row->attributes( 'valuetype' );
		$selection								=	array( $indexName => array( $indexValue => $indexValueType ) );
		return $this->dropRows( $tableName, $selection, true );
	}

	/**
	 * CHANGES OF TABLE STRUCTURE:
	 */

	/**
	 * Changes if a column exists or Creates a new column
	 *
	 * @param  string                     $tableName        Name of table (for error strings)
	 * @param  array                      $allColumns       [IN+OUT: MODIFIED] From $this->getAllTableColumns( $table )
	 * @param  SimpleXMLElement           $column           Column to check
	 * @param  string                     $colNamePrefix    Prefix to add to all column names
	 * @param  SimpleXMLElement|int|null  $columnNameAfter  The column which should be just before this one
	 * @return boolean                                      TRUE: identical (no check on indexes), FALSE: errors are in $this->getErrors()
	 */
	protected function changeColumn( $tableName, &$allColumns, SimpleXMLElement $column, $colNamePrefix, $columnNameAfter )
	{
		$colNamePrefixed							=	$this->prefixedName( $column, $colNamePrefix );
		$fullColumnType								=	$this->fullColumnType( $column, $tableName );
		if ( $fullColumnType !== false ) {
			$sqlUpdate								=	'';
			$updateResult							=	true;

			if ( $column->attributes( 'oldname' ) && array_key_exists( $this->prefixedName( $column, $colNamePrefix, 'oldname' ), $allColumns ) ) {
				$oldColName							=	$this->prefixedName( $column, $colNamePrefix, 'oldname' );
			} else {
				$oldColName							=	$colNamePrefixed;
			}
			if ( isset( $allColumns[$oldColName] ) && ( ( $colNamePrefixed == $oldColName ) || ! isset( $allColumns[$colNamePrefixed] ) ) ) {
				// column exists already, change it:
				if ( $column->attributes( 'initialvalue' ) && ( $column->attributes( 'null' ) !== 'true' ) ) {
					// we do need to treat the old NULL values specially:
					$sqlUpdate						=	'UPDATE ' . $this->_db->NameQuote( $tableName )
						.	"\n SET " . $this->_db->NameQuote( $oldColName )
						.	' = ' . $this->sqlCleanQuote( $column->attributes( 'initialvalue' ), $column->attributes( 'initialvaluetype' ) )
						.	"\n WHERE " . $this->_db->NameQuote( $oldColName ) . ' IS NULL'
					;
					$updateResult					=	$this->doQuery( $sqlUpdate );
				}

				$alteration							=	'CHANGE ' . $this->_db->NameQuote( $oldColName );
				$firstAfterSQL						=	'';
			} else {
				// column doesn't exist, create it:
				$alteration							=	'ADD';

				switch ( $columnNameAfter ) {
					case null:
						$firstAfterSQL				=	'';
						break;

					case 1:
						$firstAfterSQL				=	' FIRST';
						break;

					default:
						$colNameAfterPrefixed		=	$this->prefixedName( $columnNameAfter, $colNamePrefix );
						$firstAfterSQL				=	' AFTER ' . $this->_db->NameQuote( $colNameAfterPrefixed );
						break;
				}
			}
			$sql									=	'ALTER TABLE ' . $this->_db->NameQuote( $tableName )
				.	"\n " . $alteration
				.	' ' . $this->_db->NameQuote( $colNamePrefixed )
				.	' ' . $fullColumnType
				.	( $this->_db->versionCompare( '4.0' ) ? $firstAfterSQL : '' )
			;
			$alterationResult						=	$this->doQuery( $sql );

			if ( $alterationResult && ( $alteration == 'ADD' ) ) {
				if ( $column->attributes( 'initialvalue' ) ) {
					$sqlUpdate						=	'UPDATE ' . $this->_db->NameQuote( $tableName )
						.	"\n SET " . $this->_db->NameQuote( $colNamePrefixed )
						.	' = ' . $this->sqlCleanQuote( $column->attributes( 'initialvalue' ), $column->attributes( 'initialvaluetype' ) )
					;
					$updateResult					=	$this->doQuery( $sqlUpdate );
				}
			}
			if ( $alterationResult && ( $alteration != 'ADD' ) ) {
				if ( $colNamePrefixed != $oldColName ) {
					$allColumns[$colNamePrefixed]	=	$allColumns[$oldColName];
					unset( $allColumns[$oldColName] );
				}
			}
			if ( ! $alterationResult ) {
				$this->setError( sprintf( '%s::changeColumn (%s) of Table %s Column %s failed with SQL error: %s', get_class( $this ), $alteration, $tableName, $colNamePrefixed, $this->_db->getErrorMsg() ), $sql );
				return false;
			} elseif ( ! $updateResult ) {
				$this->setError( sprintf( '%s::changeColumn (UPDATE) of Table %s Column %s failed with SQL error: %s', get_class( $this ), $tableName, $colNamePrefixed, $this->_db->getErrorMsg() ), $sqlUpdate );
				return false;
			} else {
				$this->setLog( sprintf( 'Table %s Column %s %s successfully, type: %s', $tableName, $colNamePrefixed, ( $alteration == 'ADD' ? 'created' : 'changed' ), $this->fullColumnType( $column, $tableName ) ),
					( $alteration == 'ADD' ? $sql . ( $sqlUpdate ? ";\n" . $sqlUpdate : '' ) : ( $sqlUpdate ?  $sqlUpdate . ";\n" : '' ) . $sql ),
					'change' );
				return true;
			}
		} else {
			$this->setError( sprintf( '%s::changeColumn of Table %s Column %s failed because the column type %s could not be determined (not starting with sql:).', get_class( $this ), $tableName, $colNamePrefixed, $column->attributes( 'type' ) ) );
			return false;
		}
	}

	/**
	 * Changes if an index exists or Creates a new index
	 *
	 * @param  string              $tableName        Name of table (for error strings)
	 * @param  array               $allIndexes       From $this->getAllTableColumns( $table )
	 * @param  SimpleXMLElement    $index            Column to check
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @return boolean  TRUE: identical (no check on indexes), FALSE: errors are in $this->getErrors()
	 */
	protected function changeIndex( $tableName, $allIndexes, SimpleXMLElement $index, $colNamePrefix )
	{
		$indexName								=	$this->prefixedName( $index, $colNamePrefix );

		$queryParts								=	array();
		if ( isset( $allIndexes[$indexName] ) ) {
			// index exists already,drop it:
			if ( $indexName == 'PRIMARY') {
				$queryParts[]					=	'DROP PRIMARY KEY';
			} else {
				$queryParts[]					=	'DROP KEY ' . $this->_db->NameQuote( $indexName );
			}
			$alteration							=	'change';
		} else {
			$alteration							=	'new';
		}
		// Now create new index:
		$queryParts[]							=	'ADD ' . $this->fullIndexType( $index, $colNamePrefix );

		$sql									=	'ALTER TABLE ' . $this->_db->NameQuote( $tableName )
			.	"\n " . implode( ",\n ", $queryParts )
		;
		$alterationResult						=	$this->doQuery( $sql );

		if ( ! $alterationResult ) {
			$this->setError( sprintf( '%s::changeIndex (%s) of Table %s Index %s failed with SQL error: %s', get_class( $this ), $alteration, $tableName, $indexName, $this->_db->getErrorMsg() ), $sql );
			return false;
		} else {
			$this->setLog( sprintf( 'Table %s Index %s successfully %s', $tableName, $indexName, ( $alteration == 'new' ? 'created' : 'changed' ) ), $sql, 'change' );
			return true;
		}
	}

	/**
	 * Changes storage engine
	 *
	 * @param  string              $tableName        Name of table (for error strings)
	 * @param  string              $tableEngine      Engine to change the table to
	 * @return boolean  TRUE: identical, FALSE: errors are in $this->getErrors()
	 */
	protected function changeEngine( $tableName, $tableEngine )
	{
		$sql					=	'ALTER TABLE ' . $this->_db->NameQuote( $tableName ) . ' ENGINE = ' . $this->_db->Quote( $tableEngine );
		$alterationResult		=	$this->doQuery( $sql );

		if ( ! $alterationResult ) {
			$this->setError( sprintf( '%s::changeEngine (%s) of Table %s Storage Engine %s failed with SQL error: %s', get_class( $this ), 'change', $tableName, $tableEngine, $this->_db->getErrorMsg() ), $sql );
			return false;
		} else {
			$this->setLog( sprintf( 'Table %s Storage Engine %s successfully %s', $tableName, $tableEngine, 'changed' ), $sql, 'change' );
			return true;
		}
	}

	/**
	 * checks if a table exists and if it does what is its engine then returns the engine
	 *
	 * @param  string       $tableName  Table name
	 * @return string|null              "InnoDB" or "MyISAM" or ,,,
	 */
	protected function checkTableEngine( $tableName )
	{
		static $tableEngines				=	array();				//TODO: Cache needs to be cleared when tables are created or engines changed

		if ( ! isset( $tableEngines[$tableName] ) ) {
			try {
				$tableStatus				=	$this->_db->getTableStatus( $tableName );
			}
			catch ( \RuntimeException $e ) {
				$tableEngines[$tableName]	=	null;
				return null;
			}

			if ( isset( $tableStatus[0]->Engine ) ) {
				$tableEngines[$tableName]	=	$tableStatus[0]->Engine;
			} else {
				$tableEngines[$tableName]	=	null;
			}
		}

		return $tableEngines[$tableName];
	}

	/**
	 * Checks if an index exists and has the type of the parameters below:
	 *
	 * @param  string              $tableName        Name of table (for error strings)
	 * @param  SimpleXMLElement    $row              <row> to change
	 * @param  SimpleXMLElement    $columns           Corresponding <column> of table
	 * @param  array               $allColumns       From $this->getAllTableColumns( $table ) : columns which were existing before upgrading columns called before this function
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @param  boolean             $change           TRUE: changes row, FALSE: checks row
	 * @param  boolean             $directlyInsert   TRUE: does not test if row exists first, FALSE: checks first if row exists
	 * @return boolean             TRUE: identical, FALSE: errors are in $this->getErrors()
	 */
	protected function checkOrChangeRow( $tableName, SimpleXMLElement $row, SimpleXMLElement $columns, $allColumns, $colNamePrefix, $change = true, $directlyInsert = false )
	{
		$indexName								=	$this->prefixedName( $row, $colNamePrefix, 'index', 'indextype' );
		$indexValue								=	$row->attributes( 'value' );
		$indexValueType							=	$row->attributes( 'valuetype' );

		if ( $change && $directlyInsert ) {
			$rowsArray							=	array();
		} else {
			$rowsArray							=	$this->loadRows( $tableName, $indexName, $indexValue, $indexValueType );
		}

		$mismatchingFields						=	array();
		$mismatchingFieldsOldValues				=	array();

		if ( is_array( $rowsArray ) && ( count( $rowsArray ) > 0 ) ) {
			foreach ( $rowsArray as $rowData ) {
				foreach ( $row->children() as $field ) {
					if ( $field->getName() == 'field' ) {
						$strictField			=	$field->attributes( 'strict' );
						$fieldName				=	$this->prefixedName( $field, $colNamePrefix );
						if ( $strictField || ! isset( $allColumns[$fieldName] ) ) {
							// if field is strict, or if column has just been created: compare value to the should be one:
							$fieldValue			=	$field->attributes( 'value' );
							$fieldValueType		=	$field->attributes( 'valuetype' );

							$column				=	$columns->getChildByNameAttr( 'column', 'name', $field->attributes( 'name' ) );
							$columnMustExist	=	( $column && ( $column->attributes( 'mandatory' ) !== 'false' ) );

							if (	( $columnMustExist && ! isset( $allColumns[$fieldName] ) )
								||	( $columnMustExist && ! array_key_exists( $fieldName, $rowData ) )
								||	( ( $strictField === 'true' ) && ( $this->adjustToStrictType( $rowData->$fieldName, $fieldValueType ) !== $this->phpCleanQuote( $fieldValue, $fieldValueType ) ) )
								||	( ( $strictField === 'notnull' ) && ( $this->adjustToStrictType( $rowData->$fieldName, $fieldValueType ) === null ) && ( $this->phpCleanQuote( $fieldValue, $fieldValueType ) !== null ) )
								||	( ( $strictField === 'notzero' ) && ( ( ( $this->adjustToStrictType( $rowData->$fieldName, $fieldValueType ) === null ) || ( $this->adjustToStrictType( $rowData->$fieldName, $fieldValueType ) == 0 ) )
										&& ( ! ( ( $this->phpCleanQuote( $fieldValue, $fieldValueType ) === null ) || ( $this->phpCleanQuote( $fieldValue, $fieldValueType ) === 0 ) ) ) ) )
								||	( ( $strictField === 'notempty' ) && ( ( ( $this->adjustToStrictType( $rowData->$fieldName, $fieldValueType ) === null ) || ( $this->adjustToStrictType( $rowData->$fieldName, $fieldValueType ) == '' ) )
										&& ( ! ( ( $this->phpCleanQuote( $fieldValue, $fieldValueType ) === null ) || ( $this->phpCleanQuote( $fieldValue, $fieldValueType ) === '' ) ) ) ) )
							)
							{
								$mismatchingFields[$fieldName]				=	$this->sqlCleanQuote( $fieldValue, $fieldValueType );
								$mismatchingFieldsOldValues[$fieldName]		=	( array_key_exists( $fieldName, $rowData ) ? $rowData->$fieldName : '""' );
							}
						}
					}
				}
				foreach ( $row->children() as $field ) {
					if ( $field->getName() == 'field' ) {
						$strictField			=	$field->attributes( 'strict' );
						if ( $strictField == 'updatewithfield' ) {
							// if field should be updated same time than another field: check if the field is in the list to be upgraded:
							$strictSameAsField	=	$field->attributes( 'strictsameasfield' );
							if ( isset( $mismatchingFields[$strictSameAsField] ) ) {
								$fieldName		=	$this->prefixedName( $field, $colNamePrefix );
								$fieldValue		=	$field->attributes( 'value' );
								$fieldValueType	=	$field->attributes( 'valuetype' );
								if ( ( ! array_key_exists( $fieldName, $rowData ) )
									||	( $this->adjustToStrictType( $rowData->$fieldName, $fieldValueType ) !== $this->phpCleanQuote( $fieldValue, $fieldValueType ) )
								)
								{
									$mismatchingFields[$fieldName]	=	$this->sqlCleanQuote( $fieldValue, $fieldValueType );
								}
							}
						}
					}
				}
			}

			if ( count( $mismatchingFields ) > 0 ) {
				if ( $change === true ) {
					return $this->setFields( $tableName, $row, $mismatchingFields, $colNamePrefix );
				} else {
					$texts						=	array();
					foreach ($mismatchingFields as $name => $val ) {
						$texts[]				=	sprintf( 'Field %s = %s instead of %s', $name, $mismatchingFieldsOldValues[$name], $val );
					}
					$this->setError( sprintf( 'Table %s Rows %s = %s : %s', $tableName, $indexName, $indexValue, implode( ', ', $texts ) ) );
					return false;
				}
			} else {
				if ( $change === false ) {
					$this->setLog( sprintf( 'Table %s Rows %s = %s are up-to-date.', $tableName, $indexName, $this->sqlCleanQuote( $indexValue, $indexValueType ) ), null, 'ok' );
				}
				return true;
			}
		} else {
			if ( $change === true ) {
				return $this->insertRow( $tableName, $row, $colNamePrefix );
			} else {
				$this->setError( sprintf( 'Table %s Rows %s = %s do not exist', $tableName, $indexName, $this->sqlCleanQuote( $indexValue, $indexValueType ) ), null );
			}
			return false;
		}
	}

	/**
	 * Load rows from table $tableName
	 *
	 * @param  string   $tableName        Name of table (for error strings)
	 * @param  string   $indexName
	 * @param  string   $indexValue
	 * @param  string   $indexValueType
	 * @return \StdClass[]|boolean
	 */
	protected function loadRows( $tableName, $indexName, $indexValue, $indexValueType )
	{
		$sql									=	'SELECT * FROM ' . $this->_db->NameQuote( $tableName );
		if ( $indexName ) {
			$sql								.=	"\n WHERE " . $this->_db->NameQuote( $indexName )
				.	' = '
				.	$this->sqlCleanQuote( $indexValue, $indexValueType )
			;
		}
		$this->_db->setQuery( $sql );
		$result									=	$this->_db->loadObjectList();
		if ( $this->_db->getErrorMsg() ) {
			$this->setError( sprintf( '%s::loadRows of Table %s Rows %s = %s failed with SQL error: %s', get_class( $this ), $tableName, $indexName, $this->sqlCleanQuote( $indexValue, $indexValueType ), $this->_db->getErrorMsg() ), $sql );
			return false;
		} else {
			// $this->_setLog( sprintf( 'Table %s Rows %s = %s successfully loaded', $tableName, $columnName, $this->_sqlCleanQuote( $indexValue, $indexValueType ) ), $sql, 'change' );
			return $result;
		}
	}

	/**
	 * Drop rows from table $tableName matching $selection
	 *
	 * @param  string   $tableName
	 * @param  array    $selection        array( 'columnName' => array( 'columnValue' => 'columnValueType' ) )
	 * @param  boolean  $positiveSelect   TRUE: select corresponding to selection, FALSE: Select NOT the selection
	 * @return boolean                    TRUE: no error, FALSE: error (logged)
	 */
	protected function dropRows( $tableName, $selection, $positiveSelect )
	{
		$where									=	$this->sqlBuildSelectionWhere( $selection, $positiveSelect );
		$sql									=	'DELETE FROM ' . $this->_db->NameQuote( $tableName )
			.	"\n WHERE " . $where
		;
		if ( ! $this->doQuery( $sql ) ) {
			$this->setError( sprintf( '%s::dropRows of Table %s Row(s) %s failed with SQL error: %s', get_class( $this ), $tableName, $where, $this->_db->getErrorMsg() ), $sql );
			return false;
		} else {
			$this->setLog( sprintf( 'Table %s Row(s) %s successfully dropped', $tableName, $where ), $sql, 'change' );
			return true;
		}
	}

	/**
	 * Counts rows from table $tableName matching $selection
	 *
	 * @param  string   $tableName
	 * @param  array    $selection        array( 'columnName' => array( 'columnValue' => 'columnValueType' ) )
	 * @param  boolean  $positiveSelect   TRUE: select corresponding to selection, FALSE: Select NOT the selection
	 * @return boolean                    TRUE: no error, FALSE: error (logged)
	 */
	protected function countRows( $tableName, $selection, $positiveSelect )
	{
		$where									=	$this->sqlBuildSelectionWhere( $selection, $positiveSelect );
		$sql									=	'SELECT COUNT(*) FROM ' . $this->_db->NameQuote( $tableName )
			.	"\n WHERE " . $where
		;
		$this->_db->setQuery( $sql );
		$result									=	$this->_db->loadResult();
		if ( $result === null ) {
			$this->setError( sprintf( '%s::countRows of Table %s Row(s) %s failed with SQL error: %s', get_class( $this ), $tableName, $where, $this->_db->getErrorMsg() ), $sql );
		}
		return $result;
	}

	/**
	 * Counts rows from table $tableName matching $selection
	 *
	 * @param  string              $tableName
	 * @param  SimpleXMLElement    $row                <row index="columnname" indextype="prefixname" value="123" valuetype="sql:int" /> to delete
	 * @param  array               $mismatchingFields  array( 'columnName' => 'SQL-safe value' )
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @return boolean                                 TRUE: no error, FALSE: error (logged)
	 */
	protected function setFields( $tableName, SimpleXMLElement $row, $mismatchingFields, $colNamePrefix )
	{
		$indexName								=	$this->prefixedName( $row, $colNamePrefix, 'index', 'indextype' );
		$indexValue								=	$row->attributes( 'value' );
		$indexValueType							=	$row->attributes( 'valuetype' );

		$selection								=	array( $indexName => array( $indexValue => $indexValueType ) );
		$where									=	$this->sqlBuildSelectionWhere( $selection, true );

		$setFields								=	array();
		foreach ( $mismatchingFields as $name => $quotedValue ) {
			$setFields[]						=	$this->_db->NameQuote( $name ) . ' = ' . $quotedValue;
		}
		$setFieldsText							=	implode( ', ', $setFields );
		$sql									=	'UPDATE ' . $this->_db->NameQuote( $tableName )
			.	"\n SET " . $setFieldsText
			.	"\n WHERE " . $where
		;
		if ( ! $this->doQuery( $sql ) ) {
			$this->setError( sprintf( '%s::setFields of Table %s Row %s Fields %s failed with SQL error: %s', get_class( $this ), $tableName, $where, $setFieldsText, $this->_db->getErrorMsg() ), $sql );
			return false;
		} else {
			$this->setLog( sprintf( 'Table %s Row %s successfully updated', $tableName, $where ), $sql, 'change' );
			return true;
		}
	}

	/**
	 * Checks if an index exists and has the type of the parameters below:
	 *
	 * @param  string              $tableName        Name of table (for error strings)
	 * @param  SimpleXMLElement    $row              <row> to change
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @return boolean             TRUE: success, FALSE: errors are in $this->getErrors()
	 */
	protected function insertRow( $tableName, SimpleXMLElement $row, $colNamePrefix )
	{
		$indexName								=	$this->prefixedName( $row, $colNamePrefix, 'index', 'indextype' );
		$indexValue								=	$row->attributes( 'value' );
		$indexValueType							=	$row->attributes( 'valuetype' );

		if ( $row->getName() == 'row' ) {
			$sqlFieldNames						=	array();
			$sqlFieldValues						=	array();
			foreach ( $row->children() as $field ) {
				if ( $field->getName() == 'field' ) {
					$fieldName					=	$this->prefixedName( $field, $colNamePrefix );
					$fieldValue					=	$field->attributes( 'value' );
					$fieldValueType				=	$field->attributes( 'valuetype' );
					if ( ( $fieldName == $indexName ) && ( ( $fieldValue != $indexValue ) || ( $fieldValueType != $indexValueType ) ) ) {
						$this->setError( sprintf( '%s::insertRow Error in XML: Table %s Row %s = %s (type %s) trying to insert different Field Value or type: %s = %s (type %s)', get_class( $this ), $tableName, $indexName, $indexValue, $indexValueType, $fieldName, $fieldValue, $fieldValueType ), null );
						return false;
					}
					if ( isset( $sqlFieldNames[$fieldName] ) ) {
						$this->setError( sprintf( '%s::insertRow Error in XML: Table %s Row %s = %s : Field %s is defined twice in XML', get_class( $this ), $tableName, $indexName, $indexValue, $fieldName ), null );
						return false;
					}
					$sqlFieldNames[$fieldName]	=	$this->_db->NameQuote( $fieldName );
					$sqlFieldValues[$fieldName]	=	$this->sqlCleanQuote( $fieldValue, $fieldValueType );
				}
			}
			if ( ! isset( $sqlFieldNames[$indexName] ) ) {
				$sqlFieldNames[$indexName]		=	$this->_db->NameQuote( $indexName );
				$sqlFieldValues[$indexName]		=	$this->sqlCleanQuote( $indexValue, $indexValueType );
			}

			if ( count( $sqlFieldNames ) > 0 ) {
				$sqlColumnsText					=	'(' . implode( ',', $sqlFieldNames ) . ')';
				$sqlColumnsValues				=	'(' . implode( ',', $sqlFieldValues ) . ')';
			} elseif ( $indexName ) {
				$sqlColumnsText					=	'(' . $this->_db->NameQuote( $indexName ) . ')';
				$sqlColumnsValues				=	'(' . $this->sqlCleanQuote( $indexValue, $indexValueType ) . ')';
			} else {
				$sqlColumnsText					=	null;
				$sqlColumnsValues				=	null;
			}
			if ( $sqlColumnsText != null ) {
				$sql							=	'INSERT INTO ' . $this->_db->NameQuote( $tableName )
					.	"\n " . $sqlColumnsText
					.	"\n VALUES ";
				if ( $this->_batchProcess !== false ) {
					$this->_batchProcess[$tableName][$sqlColumnsText][]	=	$sqlColumnsValues;
				} else {
					// $sql						.=	implode( ",\n        ", $sqlColumnsValues );
					$sql						.=	$sqlColumnsValues;
					if ( ! $this->doQuery( $sql ) ) {
						$this->setError( sprintf( '%s::insertRow of Table %s Row %s = %s Fields %s = %s failed with SQL error: %s', get_class( $this ), $tableName, $indexName, $indexValue, $sqlColumnsText, $sqlColumnsValues, $this->_db->getErrorMsg() ), $sql );
						return false;
					} else {
						$this->setLog( sprintf( 'Table %s Row %s = %s successfully updated', $tableName, $indexName, $indexValue ), $sql, 'change' );
						return true;
					}
				}
			}
		}
		$this->setError( sprintf( '%s::insertRow : Error in SQL: No values to insert Row %s = %s (type %s)', $tableName, $indexName, $indexValue, $indexValueType ), null );
		return true;
	}

	/**
	 * Processes INSERT statements in batches
	 *
	 * @return boolean
	 */
	protected function processBatchInserts( )
	{
		$result									=	true;
		if ( $this->_batchProcess !== false ) {
			foreach ($this->_batchProcess as $tableName => $cv ) {
				foreach ($cv as $sqlColumnsText => $arrayOfSqlColumnsValues ) {
					$sql						=	'INSERT INTO ' . $this->_db->NameQuote( $tableName )
						.	"\n " . $sqlColumnsText
						.	"\n VALUES "
						.	implode( ",\n        ", $arrayOfSqlColumnsValues );
					if ( ! $this->doQuery( $sql ) ) {
						$this->setError( sprintf( '%s::_processBatchInserts of Table insert of Columns %s failed with SQL error: %s', get_class( $this ), $tableName, $sqlColumnsText, $this->_db->getErrorMsg() ), $sql );
						$result					=	false;
					} else {
						$this->setLog( sprintf( 'Table %s Columns %s successfully updated', $tableName, $sqlColumnsText ), $sql, 'change' );
					}
				}
			}
			$this->_batchProcess				=	false;
		}
		return $result;
	}

	/**
	 * Builds SQL WHERE statement (without WHERE) based on array $selection
	 *
	 * @param  array    $selection        array( 'columnName' => array( 'columnValue' => 'columnValueType' ) )
	 * @param  boolean  $positiveSelect   TRUE: select corresponding to selection, FALSE: Select NOT the selection
	 * @return boolean  True: no error, False: error (logged)
	 */
	protected function sqlBuildSelectionWhere( $selection, $positiveSelect )
	{
		$where									=	array();
		foreach ( $selection as $colName => $valuesArray ) {
			$values								=	array();
			foreach ( $valuesArray as $colValue => $colValueType ) {
				$values[]						=	$this->sqlCleanQuote( $colValue, $colValueType );
			}
			if ( count( $values ) > 0 ) {
				if ( count( $values ) > 1 ) {
					$where[]					=	$this->_db->NameQuote( $colName ) . ' IN (' .implode( ',', $values ) . ')';
				} else {
					$where[]					=	$this->_db->NameQuote( $colName ) . ' = ' . $values[0];
				}
			}
		}
		$positiveWhere							=	'(' . implode( ') OR (', $where ) . ')';
		if ( $positiveSelect ) {
			return $positiveWhere;
		} else {
			return 'NOT(' . $positiveWhere . ')';
		}
	}

	/**
	 * Drops column $ColumnName from table $tableName
	 *
	 * @param  string   $tableName        Name of table (for error strings)
	 * @param  string   $columnName       Old name of column to change
	 * @return boolean                    TRUE: no error, FALSE: errors are in $this->getErrors()
	 */
	protected function dropColumn( $tableName, $columnName )
	{
		$sql									=	'ALTER TABLE ' . $this->_db->NameQuote( $tableName )
			.	"\n DROP COLUMN " . $this->_db->NameQuote( $columnName )
		;
		if ( ! $this->doQuery( $sql ) ) {
			$this->setError( sprintf( '%s::dropColumn of Table %s Column %s failed with SQL error: %s', get_class( $this ), $tableName, $columnName, $this->_db->getErrorMsg() ), $sql );
			return false;
		} else {
			$this->setLog( sprintf( 'Table %s Column %s successfully dropped', $tableName, $columnName ), $sql, 'change' );
			return true;
		}
	}

	/**
	 * Drops INDEX $indexName from table $tableName
	 *
	 * @param  string   $tableName        Name of table (for error strings)
	 * @param  string   $indexName       Old name of column to change
	 * @return boolean                    TRUE: no error, FALSE: errors are in $this->getErrors()
	 */
	protected function dropIndex( $tableName, $indexName )
	{
		$sql									=	'ALTER TABLE ' . $this->_db->NameQuote( $tableName );
		if ( $indexName == 'PRIMARY' ) {
			$sql								.=	"\n DROP PRIMARY KEY";
		} else {
			$sql								.=	"\n DROP KEY " . $this->_db->NameQuote( $indexName );
		}
		if ( ! $this->doQuery( $sql ) ) {
			$this->setError( sprintf( '%s::dropIndex of Table %s Index %s failed with SQL error: %s', get_class( $this ), $tableName, $indexName, $this->_db->getErrorMsg() ), $sql );
			return false;
		} else {
			$this->setLog( sprintf( 'Table %s Index %s successfully dropped', $tableName, $indexName ), $sql, 'change' );
			return true;
		}
	}

	/**
	 * Drops table $tableName
	 *
	 * @param  string   $tableName        Name of table (for error strings)
	 * @return boolean                    TRUE: no error, FALSE: errors are in $this->getErrors()
	 */
	protected function dropTable( $tableName )
	{
		$sql									=	'DROP TABLE ' . $this->_db->NameQuote( $tableName )
		;
		if ( ! $this->doQuery( $sql ) ) {
			$this->setError( sprintf( '%s::dropTable of Table %s failed with SQL error: %s', get_class( $this ), $tableName, $this->_db->getErrorMsg() ), $sql );
			return false;
		} else {
			$this->setLog( sprintf( 'Table %s successfully dropped', $tableName ), $sql, 'change' );
			return true;
		}
	}

	/**
	 * Creates a new table
	 *
	 * @param  SimpleXMLElement    $table  Table
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @return boolean                               True: success, False: failure
	 */
	protected function createTable( SimpleXMLElement $table, $colNamePrefix )
	{
		if ( $table->getName() == 'table' ) {
			$tableName							=	$this->prefixedName( $table, $colNamePrefix );
			$columns							=	$table->getElementByPath( 'columns' );
			if ( $tableName && ( $columns !== false ) ) {

				$engine						=	$table->getElementByPath( 'engine' );
				$tableEngine				=	null;

				if ( $engine !== false ) {
					$engineType				=	$engine->attributes( 'type' );

					if ( $engineType !== null ) {
						$engineTable		=	$engine->attributes( 'sameastable' );

						if ( $engineTable !== null ) {
							$sameEngine		=	$this->checkTableEngine( $engineTable );
						} else {
							$sameEngine		=	null;
						}

						if ( $sameEngine ) {
							$tableEngine	=	$sameEngine;
						} else {
							$tableEngine	=	$engineType;
						}
					}
				}

				$sqlColumns						=	array();
				$tableOptions					=	array();

				foreach ( $columns->children() as $column ) {
					if ( $column->getName() == 'column' ) {
						$colNamePrefixed		=	$this->prefixedName( $column, $colNamePrefix );
						$sqlColumns[]			=	"\n " . $this->_db->NameQuote( $colNamePrefixed )
							.	' ' . $this->fullColumnType( $column, $tableName, $tableEngine )
						;
						if ( (int) $column->attributes( 'auto_increment' ) ) {
							$tableOptions[]		=	'AUTO_INCREMENT=' . (int) $column->attributes( 'auto_increment' );
						}
					}
				}

				$indexes						=	$table->getElementByPath( 'indexes' );
				if ( $indexes !== false ) {
					foreach ( $indexes->children() as $index ) {
						if ( $index->getName() == 'index' ) {
							$sqlIndexText		=	$this->fullIndexType( $index, $colNamePrefix );
							if ( $sqlIndexText ) {
								$sqlColumns[]	=	"\n " . $sqlIndexText;
							}
						}
					}
				}

				if ( ! $tableEngine ) {
					$cbEngine				=	$this->checkTableEngine( '#__comprofiler' );

					if ( $cbEngine ) {
						$tableEngine		=	$cbEngine;
					} else {
						$tableEngine		=	'InnoDB';
					}
				}

				$tableOptions[]				=	'ENGINE=' . $tableEngine;

				$collation						=	$table->attributes( 'collation' );
				if ( $collation && $this->_db->versionCompare( '4.1' ) ) {
					$charSet					=	substr( $collation, 0, strpos( $collation, '_' ) );
					if ( $charSet ) {
						$tableOptions[]			=	'CHARACTER SET = ' . preg_replace( '/[^a-z0-9_]/', '' , $charSet );
						$tableOptions[]			=	'COLLATE = ' . preg_replace( '/[^a-z0-9_]/', '' , $collation );
					}
				}
				$sql							=	'CREATE TABLE ' . $this->_db->NameQuote( $tableName )
					.	' ('
					.	implode( ',', $sqlColumns )
					.	"\n )"
					.	implode( ', ', $tableOptions )
				;
				if ( ! $this->doQuery( $sql ) ) {
					$this->setError( sprintf( '%s::createTableof Table %s failed with SQL error: %s', get_class( $this ), $tableName, $this->_db->getErrorMsg() ), $sql );
					return false;
				} else {
					$this->setLog( sprintf( 'Table %s successfully created', $tableName ), $sql, 'change' );
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * UTILITY FUNCTIONS:
	 */

	/**
	 * Sets modifying query and performs it, IF NOT in dry run mode.
	 * If in dry run mode, returns true
	 *
	 * @param  string  $sql
	 * @return boolean
	 */
	protected function doQuery( $sql )
	{
		if ( $this->_dryRun ) {
			return true;
		} else {
			$this->_db->SetQuery( $sql );
			return $this->_db->query();
		}
	}

	/**
	 * Utility: Checks if $needle is the $attribute of a child of $xml
	 *
	 * @param  string              $needle
	 * @param  SimpleXMLElement    $xml
	 * @param  string              $name
	 * @param  string              $attribute
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @return boolean
	 */
	protected function inXmlChildrenAttribute( $needle, SimpleXMLElement $xml, $name, $attribute, $colNamePrefix)
	{
		foreach ( $xml->children() as $chld ) {
			if ( $chld->getName() == $name ) {
				$colNamePrefixed				=	$this->prefixedName( $chld, $colNamePrefix, $attribute );
				if ( $needle == $colNamePrefixed ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Converts a XML description of a SQL column into a full SQL type
	 *
	 *	<column name="_rate" nametype="namesuffix" type="sql:decimal(16,8)" unsigned="true" null="true" default="NULL" auto_increment="100" />
	 *
	 * Returns: $fulltype: 'decimal(16,8) unsigned NULL DEFAULT NULL'
	 *
	 * @param  SimpleXMLElement    $column       Column to determine type
	 * @param  string              $tableName    Name of table (for determining engine for preferred type)
	 * @param  string              $tableEngine  Engine of table (if $tableName is not yet created, for preferred type)
	 * @return string|boolean                    Full SQL creation type or FALSE in case of error
	 */
	protected function fullColumnType( SimpleXMLElement $column, $tableName, $tableEngine = null )
	{
		$fullType					=	false;

		if ( $column->getName() == 'column' ) {
			// $colName				=	$column->attributes( 'name' );
			// $colNameType			=	$column->attributes( 'nametype' );
			// if ( $colNameType == 'namesuffix' ) {
			//	$colName			=	$colNamePrefix . $colName;
			// }
			$type					=	$this->getPreferredColumnType( $column, $tableName, $tableEngine );
			$unsigned				=	$column->attributes( 'unsigned' );
			$null					=	$column->attributes( 'null' );
			$default				=	$column->attributes( 'default' );
			$auto_increment			=	$column->attributes( 'auto_increment' );


			if ( cbStartOfStringMatch( $type, 'sql:' ) ) {
				$type				=	trim( substr( $type, 4 ) );		// remove 'sql:'
				if ( $type ) {
					$notQuoted		=	array( 'int', 'float', 'tinyint', 'bigint', 'decimal', 'boolean', 'bit', 'serial', 'smallint', 'mediumint', 'double', 'year' );
					$isInt			=	false;
					foreach ( $notQuoted as $n ) {
						if ( cbStartOfStringMatch( $type, $n ) ) {
							$isInt	=	true;
							break;
						}
					}
					$fullType		=	$type;
					if ( $unsigned == 'true' ) {
						$fullType	.=	' unsigned';
					}
					if ( $null !== 'true' ) {
						$fullType	.=	' NOT NULL';
					}
					if ( ! in_array( $type, array( 'text', 'blob', 'tinytext', 'mediumtext', 'longtext', 'tinyblob', 'mediumblob', 'longblob' ))) {
						// BLOB and TEXT columns cannot have DEFAULT values. http://dev.mysql.com/doc/refman/5.0/en/blob.html
						if ( $default !== null ) {
							$fullType	.=	' DEFAULT ' . ( ( $isInt || ( $default === 'NULL' ) ) ? $default : $this->_db->Quote( $default ) );
						} elseif ( ! $auto_increment ) {
							// MySQL 5.0.51a and b have a bug: they need a default value always to be able to return it correctly in SHOW COLUMNS FROM ...:
							if ( $null === 'true' ) {
								$default =	'NULL';
							} elseif ( $isInt ) {
								$default =	0;
							} elseif ( in_array( $type, array( 'datetime', 'date', 'time' ) ) ) {
								$default =	$this->_db->getNullDate( $type );
							} else {
								$default =	'';
							}
							$fullType	.=	' DEFAULT ' . ( ( $isInt || ( $default === 'NULL' ) ) ? $default : $this->_db->Quote( $default ) );
						}
					}
					if ( $auto_increment ) {
						$fullType	.=	' auto_increment';
					}
				}
			}
		}
		return $fullType;
	}

	/**
	 * Converts a mysql type with 'sql:' prefix to a xmlsql sql:/const: type (without prefix).
	 *
	 * @param  string  $type   MySql type, E.g.: 'sql:varchar(255)' (with 'sql:' prefix)
	 * @return string          Xmlsql type, E.g.: 'string' (without 'sql:' or 'const:' prefix)
	 */
	protected function mysqlToXmlSql( $type )
	{
		$mysqlTypes		=	array(	'varchar'		=>	'string',
									'character'		=>	'string',
									'char'			=>	'string',
									'binary'		=>	'string',
									'varbinary'		=>	'string',
									'tinyblob'		=>	'string',
									'blob'			=>	'string',
									'mediumblob'	=>	'string',
									'longblob'		=>	'string',
									'tinytext'		=>	'string',
									'mediumtext'	=>	'string',
									'longtext'		=>	'string',
									'text'			=>	'string',
									'tinyint'		=>	'int',
									'smallint'		=>	'int',
									'mediumint'		=>	'int',
									'bigint'		=>	'int',
									'integer'		=>	'int',
									'int'			=>	'int',
									'bit'			=>	'int',
									'boolean'		=>	'int',
									'year'			=>	'int',
									'float'			=>	'float',
									'double'		=>	'float',
									'decimal'		=>	'float',
									'date'			=>	'date',
									'datetime'		=>	'datetime',
									'timestamp'		=>	'datetime',
									'time'			=>	'time',
									'enum'			=>	'string'
			// missing since not in SQL standard: SET, and ENUM above is a little simplified since only partly supported.
		);
		$cleanedType	=	preg_replace( '/^sql:([^\\(]*)\\(?.*/', '$1', $type );
		if ( isset( $mysqlTypes[$cleanedType] ) ) {
			return $mysqlTypes[$cleanedType];
		} else {
			trigger_error( sprintf( 'mysqlToXmlsql: Unknown SQL type %s (i am extracting "%s" from type)', $type, $cleanedType ), E_USER_WARNING );
			return $type;
		}
	}

	/**
	 * Returns the possible default default values for that type
	 *
	 * @param  string $type
	 * @return array  of string
	 */
	protected function defaultValuesOfTypes( $type )
	{
		$defaultNulls	=	array(	'string'		=>	array( ''  ),
									'int'			=>	array( '', '0' ),
									'float'			=>	array( '', '0' ),
									'date'			=>	array( '', '0000-00-00' ),
									'datetime'		=>	array( '', '0000-00-00 00:00:00' ),
									'time'			=>	array( '', '00:00:00' ),
									'enum'			=>	array( '' )
		);
		if ( isset( $defaultNulls[$type] ) ) {
			return $defaultNulls[$type];
		} else {
			trigger_error( sprintf( 'defaultValuesOfTypes: Unknown SQL type %s', $type ), E_USER_WARNING );
			return array( '', 0 );
		}
	}

	/**
	 * Cleans and makes a value SQL safe depending on the type that is enforced.
	 *
	 * @param  mixed   $fieldValue
	 * @param  string  $type
	 * @return string
	 */
	protected function sqlCleanQuote( $fieldValue, $type )
	{
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
				$value		=	$this->_db->NameQuote( $fieldValue );
				break;
			case 'datetime':
				if ( preg_match( '/^[0-9]{4}-[01][0-9]-[0-3][0-9] [0-2][0-9](:[0-5][0-9]){2}$/', $fieldValue ) ) {
					$value	=	$this->_db->Quote( $fieldValue );
				} else {
					$value	=	"''";
				}
				break;
			case 'date':
				if ( preg_match( '/^[0-9]{4}-[01][0-9]-[0-3][0-9]$/', $fieldValue ) ) {
					$value	=	$this->_db->Quote( $fieldValue );
				} else {
					$value	=	"''";
				}
				break;
			case 'string':
				$value		=	$this->_db->Quote( $fieldValue );
				break;
			case 'null':
				if ( $fieldValue != 'NULL' ) {
					trigger_error( sprintf( 'CBSQLUpgrader::_sqlCleanQuote: ERROR: field type sql:null has not NULL value' ) );
				}
				$value		=	'NULL';
				break;

			default:
				trigger_error( 'CBSQLUpgrader::_sqlQuoteValueType: ERROR_UNKNOWN_TYPE: ' . htmlspecialchars( $type ), E_USER_NOTICE );
				$value		=	$this->_db->Quote( $fieldValue );	// false;
				break;
		}
		return (string) $value;
	}

	/**
	 * Cleans and makes a value comparable to the SQL stored value in a CBLib\Database\Table\TableInterface object, depending on the type that is enforced.
	 *
	 * @param  mixed   $fieldValue
	 * @param  string  $type
	 * @return string
	 */
	protected function phpCleanQuote( $fieldValue, $type )
	{
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
				$value		=	$fieldValue;	// this is temporarly done so
				break;
			case 'field':						// this is temporarly handled here
				$value		=	$fieldValue;
				break;
			case 'datetime':
				if ( preg_match( '/^[0-9]{4}-[01][0-9]-[0-3][0-9] [0-2][0-9](:[0-5][0-9]){2}$/', $fieldValue ) ) {
					$value	=	(string) $fieldValue;
				} else {
					$value	=	'';
				}
				break;
			case 'date':
				if ( preg_match( '/^[0-9]{4}-[01][0-9]-[0-3][0-9]$/', $fieldValue ) ) {
					$value	=	(string) $fieldValue;
				} else {
					$value	=	'';
				}
				break;
			case 'string':
				$value		=	(string) $fieldValue;
				break;
			case 'null':
				if ( $fieldValue != 'NULL' ) {
					trigger_error( sprintf( 'CBSQLUpgrader::_phpCleanQuote: ERROR: field type sql:null has not NULL value' ) );
				}
				$value		=	null;
				break;

			default:
				trigger_error( 'CBSQLUpgrader::_sqlQuoteValueType: ERROR_UNKNOWN_TYPE: ' . htmlspecialchars( $type ), E_USER_NOTICE );
				$value		=	(string) $fieldValue;	// false;
				break;
		}
		return $value;
	}

	/**
	 * Cleans and makes a value comparable to the SQL stored value in a TableInterface object, depending on the type that is enforced.
	 *
	 * @param  string|null  $fieldValue
	 * @param  string  $type
	 * @return mixed
	 */
	protected function adjustToStrictType( $fieldValue, $type )
	{
		$typeArray		=	explode( ':', $type, 3 );
		if ( count( $typeArray ) < 2 ) {
			$typeArray	=	array( 'const' , $type );
		}

		$value				=	$fieldValue;
		if ( $fieldValue !== null ) {
			switch ( $typeArray[1] ) {
				case 'int':
					if ( is_int( $fieldValue ) || preg_match( '/^\d++$/', $fieldValue ) ) {
						$value	=	(int) $fieldValue;
					}
					break;
				case 'float':
					if ( is_float( $fieldValue ) || ( preg_match( '/^(((+|-)?\d+(\.\d*)?([Ee](+|-)?\d+)?)|((+|-)?(\d*\.)?\d+([Ee](+|-)?\d+)?))$/', $fieldValue ) ) ) {
						$value	=	(float) $fieldValue;
					}
					break;
				case 'formula':
					$value		=	$fieldValue;	// this is temporarly done so
					break;
				case 'field':						// this is temporarly handled here
					$value		=	$fieldValue;
					break;
				case 'datetime':
					if ( preg_match( '/^[0-9]{4}-[01][0-9]-[0-3][0-9] [0-2][0-9](:[0-5][0-9]){2}$/', $fieldValue ) ) {
						$value	=	(string) $fieldValue;
					} else {
						$value	=	'';
					}
					break;
				case 'date':
					if ( preg_match( '/^[0-9]{4}-[01][0-9]-[0-3][0-9]$/', $fieldValue ) ) {
						$value	=	(string) $fieldValue;
					} else {
						$value	=	'';
					}
					break;
				case 'string':
					if ( is_string( $fieldValue ) ) {
						$value	=	(string) $fieldValue;
					}
					break;
				case 'null':
					if ( $fieldValue === null ) {
						$value	=	null;
					}
					break;

				default:
					trigger_error( 'CBSQLUpgrader::_sqlQuoteValueType: ERROR_UNKNOWN_TYPE: ' . htmlspecialchars( $type ), E_USER_NOTICE );
					$value		=	(string) $fieldValue;	// false;
					break;
			}
		}
		return $value;
	}

	/**
	 * Converts a XML description of a SQL index into a full SQL type
	 *
	 *	<index name="PRIMARY" type="primary">
	 *		<column name="id"	/>
	 *	</index>
	 *	<index name="rate_chars">
	 *		<column name="rate" />
	 *		<column name="_mychars" nametype="namesuffix" size="8" ordering="DESC" />
	 *	</index>
	 *	<index name="myrate" type="unique" using="btree">
	 *		<column name="rate" />
	 *	</index>
	 *
	 * Returns: $fulltype: 'decimal(16,8) unsigned NULL DEFAULT NULL'
	 *
	 * @param  SimpleXMLElement    $index
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @return string|boolean                        Full SQL creation type or NULL in case of no index/error
	 */
	protected function fullIndexType( SimpleXMLElement $index, $colNamePrefix )
	{
		$sqlIndexText							=	null;

		if ( $index->getName() == 'index' ) {
			// first collect all columns of this index:
			$indexColumns						=	array();
			foreach ( $index->children() as $column ) {
				if ( $column->getName() == 'column' ) {
					$colNamePrefixed			=	$this->prefixedName( $column, $colNamePrefix );
					$indexColText				=	$this->_db->NameQuote( $colNamePrefixed );
					if ( $column->attributes( 'size' ) ) {
						$indexColText			.=	' (' . (int) $column->attributes( 'size' ) . ')';
					}
					if ( $column->attributes( 'ordering' ) ) {
						$indexColText			.=	' ' . $this->_db->getEscaped( $column->attributes( 'ordering' ) );
					}

					$indexColumns[]				=	$indexColText;
				}
			}
			if ( count( $indexColumns ) > 0 ) {
				// then build the index creation SQL:
				if ( $index->attributes( 'type' ) ) {
					// PRIMARY, UNIQUE, FULLTEXT, SPATIAL:
					$sqlIndexText				.=	$this->_db->getEscaped( strtoupper( $index->attributes( 'type' ) ) ) . ' ';
				}
				$sqlIndexText					.=	'KEY ';
				if ( $index->attributes( 'type' ) !== 'primary' ) {
					$sqlIndexText				.=	$this->_db->NameQuote( $this->prefixedName( $index, $colNamePrefix ) ) . ' ';
				}
				if ( $index->attributes( 'using' ) ) {
					// BTREE, HASH, RTREE:
					$sqlIndexText				.=	'USING ' . $this->_db->getEscaped( $index->attributes( 'using' ) ) . ' ';
				}
				$sqlIndexText					.=	'(' . implode( ', ', $indexColumns ) . ')';
			}
		}
		return $sqlIndexText;
	}

	/**
	 * Prefixes the $attribute of $column (or table or other xml element) with
	 * $colNamePrefix if $column->attributes( 'nametype' ) == 'namesuffix' or 'nameprefix'
	 *
	 * @param  SimpleXMLElement    $column
	 * @param  string              $colNamePrefix
	 * @param  string              $attribute
	 * @param  string              $modifyingAttr
	 * @return string
	 */
	protected function prefixedName( SimpleXMLElement $column, $colNamePrefix, $attribute = 'name', $modifyingAttr = 'nametype' )
	{
		$colName								=	$column->attributes( $attribute );
		$colNameType							=	$column->attributes( $modifyingAttr );

		switch ( $colNameType ) {
			case 'nameprefix':
				$colName						.=	$colNamePrefix;

				break;
			case 'namesuffix':
				$colName						=	$colNamePrefix . $colName;
				break;

			default:
				break;
		}
		return $colName;
	}


	/**
	 * Checks if all columns of a xml description of all tables of a database matches the database
	 *
	 * Warning: removes columns tables and columns which would be added by the changes to XML !!!
	 *
	 * @param  SimpleXMLElement    $table
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @param  string              $change           'drop': uninstalls columns/tables
	 * @param  boolean|null        $strictlyColumns  FALSE: allow for other columns, TRUE: doesn't allow for other columns, NULL: checks for attribute 'strict' in table
	 * @return boolean             TRUE: matches, FALSE: don't match
	 */
	protected function dropXmlTableDescription( SimpleXMLElement $table, $colNamePrefix = '', $change = 'drop', $strictlyColumns = false )
	{
		$isMatching										=	false;
		if ( ( $change == 'drop' ) && ( $table->getName() == 'table' ) ) {
			$tableName									=	$this->prefixedName( $table, $colNamePrefix );
			$columns									=	$table->getElementByPath( 'columns' );
			if ( $tableName && ( $columns !== false ) ) {
				if ( $strictlyColumns === null ) {
					$strictlyColumns					=	( $table->attributes( 'strict' ) === 'true' );
				}
				$neverDropTable							=	( $table->attributes( 'drop' ) === 'never' );
				$isMatching								=	true;
				$allColumns								=	$this->getAllTableColumns( $tableName );
				if ( $allColumns === false ) {
					// table doesn't exist: do nothing
				} else {
					if ( $strictlyColumns && ( ! $neverDropTable ) ) {
						if ( in_array( $tableName, array( '#__comprofiler', '#_users', '#__comprofiler_fields' ) ) ) {
							// Safeguard against fatal error in XML file !
							$errorMsg					=	sprintf( 'Fatal error: Trying to delete core CB table %s not allowed.', $tableName );
							echo $errorMsg;
							trigger_error( $errorMsg, E_USER_ERROR );
							exit;
						}
						$this->dropTable( $tableName );
					} else {
						// 1) Drop rows:
						$rows								=	$table->getElementByPath( 'rows' );
						if ( $rows !== false ) {
							$neverDropRows					=	( $rows->attributes( 'drop' ) === 'never' );
							if ( ! $neverDropRows ) {
								$strictRows					=	( ( $rows->attributes( 'strict' ) === 'true' ) );
								foreach ( $rows->children() as $row ) {
									if ( $row->getName() == 'row' ) {
										$neverDropRow		=	( $row->attributes( 'drop' ) === 'never' );
										if ( ( $strictRows && ! $neverDropRow ) ) {
											if ( ! $this->dropRow( $tableName, $row, $colNamePrefix ) ) {
												$isMatching	=	false;
											}
										}
									}
								}
							}
						}
						// 2) Drop indexes:
						$indexes							=	$table->getElementByPath( 'indexes' );
						if ( $indexes !== false ) {
							$neverDropIndexes				=	( $indexes->attributes( 'drop' ) === 'never' );
							if ( ! $neverDropIndexes ) {
								$allIndexes					=	$this->getAllTableIndexes( $tableName );
								foreach ( $indexes->children() as $index ) {
									if ( $index->getName() == 'index' ) {
										$indexName			=	$this->prefixedName( $index, $colNamePrefix );
										if ( $indexName == 'PRIMARY' ) {
											$neverDropIndex	=	( $index->attributes( 'drop' ) !== 'always' );
										} else {
											$neverDropIndex	=	( $index->attributes( 'drop' ) === 'never' );
										}
										if ( isset( $allIndexes[$indexName] ) && ! $neverDropIndex ) {
											if ( ! $this->dropIndex( $tableName, $indexName ) ) {
												$isMatching	=	false;
											}
										}
									}
								}
							}
						}
						// 3) Drop columns:
						$neverDropColumns					=	( $columns->attributes( 'drop' ) === 'never' );
						if ( ! $neverDropColumns ) {
							foreach ( $columns->children() as $column ) {
								if ( $column->getName() == 'column' ) {
									$neverDropColumn		=	( $column->attributes( 'drop' ) === 'never' );
									$colNamePrefixed		=	$this->prefixedName( $column, $colNamePrefix );
									if ( isset( $allColumns[$colNamePrefixed] ) && ! $neverDropColumn ) {
										if ( ! $this->dropColumn( $tableName, $colNamePrefixed ) ) {
											$isMatching		=	false;
										}
									}
								}
							}
						}
					}
				}
			}
		}
		return $isMatching;
	}
}