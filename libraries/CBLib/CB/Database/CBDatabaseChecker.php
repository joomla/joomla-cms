<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 5/14/14 11:07 PM $
* @package CB\Database
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CB\Database;

use CBLib\Application\Application;
use CBLib\Database\DatabaseDriverInterface;
use CBLib\Database\DatabaseUpgrade;
use CBLib\Xml\SimpleXMLElement;
use CBLib\Language\CBTxt;
// Temporarily:
use cbFieldHandler;
use cbInstallerPlugin;

defined('CBLIB') or die();

/**
 * CB\Database\CBDatabaseChecker Class implementation
 * CB SQL versioning / upgrading functions
 *
 * WARNING:
 * This new library is experimental work in progress and should not be used directly by plugins and 3pds,
 * as it is subject to change without notice, and is not part of current CB API.
 * 
 */
class CBDatabaseChecker
{
	/**
	 * Should-be Mapping of Tab id => corresponding pluginClass
	 * @var array
	 */
	protected $_tabsShouldBe	=	array(
										6	=>	'getStatsTab',
										7	=>	'getCanvasTab',
										8	=>	'cbblogsTab',
										9	=>	'cbforumsTab',
										10	=>	'cbarticlesTab',
										11	=>	'getContactTab',
										15	=>	'getConnectionTab',
										17	=>	'getMenuTab',
										18	=>	'getConnectionPathsTab',
										19	=>	'getPageTitleTab',
										20	=>	'getPortraitTab',
										21	=>	'getStatusTab',
										22	=>	'getmypmsproTab',
	);

	/**
	 * Database
	 * @var DatabaseDriverInterface
	 */
	protected $_db					=	null;

	/**
	 * SQL upgrader
	 * @var DatabaseUpgrade
	 */
	protected $_sqlUpgrader			=	null;

	/**
	 * Be silent when all is ok
	 * @var boolean
	 */
	protected $_silentWhenOK		=	true;

	/**
	 * Constructor
	 *
	 * @param DatabaseDriverInterface  $db  Database driver
	 */
	public function __construct( DatabaseDriverInterface $db = null )
	{
		if ( $db === null ) {
			$db						=	Application::Database();
		}

		$this->_db					=	$db;
		$this->_silentWhenOK		=	false;
	}

	/**
	 * Returns all errors logged
	 *
	 * @param  string|boolean  $implode         False: returns full array, if string: imploding string
	 * @param  string|boolean  $detailsImplode  False: no details, otherwise imploding string
	 * @return string|array
	 */
	public function getErrors( $implode = "\n", $detailsImplode = false )
	{
		return $this->_sqlUpgrader->getErrors( $implode, $detailsImplode );
	}

	/**
	 * Returns all logs logged
	 *
	 * @param  string|boolean  $implode         False: returns full array, if string: imploding string
	 * @param  string|boolean  $detailsImplode  False: no details, otherwise imploding string
	 * @return string|array
	 */
	public function getLogs( $implode = "\n", $detailsImplode = false )
	{
		return $this->_sqlUpgrader->getLogs( $implode, $detailsImplode );
	}

	/**
	 * Checks the comprofiler_fields table and upgrades if needed
	 * Backend-use only.
	 * @access private
	 *
	 * @param  string          $tableName
	 * @param  boolean         $upgrade    False: only check table, True: upgrades table (depending on $dryRun)
	 * @param  boolean         $dryRun     True: doesn't do the modifying queries, but lists them, False: does the job
	 * @return string|boolean              Message to display
	 */
	public function checkTable( $tableName, $upgrade = false, $dryRun = false )
	{
		$xml							=	$this->_getCbDbXml();
		if ( $xml !== null ) {
			$db							=	$xml->getElementByPath( 'database' );

			if ( $db !== false ) {
				$table					=	$db->getChildByNameAttr( 'table', 'name', $tableName );
				if ( $table !== false ) {
					$this->_sqlUpgrader	=	new DatabaseUpgrade( $this->_db, $this->_silentWhenOK );
					$this->_sqlUpgrader->setDryRun( $dryRun );
					$success			=	$this->_sqlUpgrader->checkXmlTableDescription( $table, '', $upgrade, null );

					/*
					var_dump( $success );
					echo "<br>\nERRORS: " . $this->_sqlUpgrader->getErrors( "<br /><br />\n\n", "<br />\n" );
					echo "<br>\nLOGS: " . $this->_sqlUpgrader->getLogs( "<br /><br />\n\n", "<br />\n" );
					exit;
					*/
				} else {
					$success			=	array( sprintf( 'Error: could not find element table name="%s" in XML file', $tableName ), null );
				}
			} else {
				$success				=	array( 'Error: could not find element "database" in XML file', null );
			}
		} else {
			$success					=	array( 'Error: could not find XML file', null );
		}
		return $success;
	}

	/**
	 * Checks the all tables and upgrades if needed
	 * Backend-use only.
	 * @access private
	 *
	 * @param  boolean                $upgrade          FALSE: only check table, TRUE: upgrades table (depending on $dryRun)
	 * @param  boolean                $dryRun           TRUE: doesn't do the modifying queries, but lists them, FALSE: does the job
	 * @param  boolean|null           $strictlyColumns  FALSE: allow for other columns, TRUE: doesn't allow for other columns
	 * @param  boolean|string|null    $strictlyEngine   FALSE: engine unchanged, TRUE: force engine change to type, updatewithtable: updates to match table, NULL: checks for attribute 'strict' in table
	 * @return string                 Message to display
	 */
	public function checkDatabase( $upgrade = false, $dryRun = false, $strictlyColumns = true, $strictlyEngine = 'updatewithtable' )
	{
		$xml							=	$this->_getCbDbXml();
		if ( $xml !== null ) {
			$db							=	$xml->getElementByPath( 'database' );
			if ( $db ) {
				$this->_sqlUpgrader		=	new DatabaseUpgrade( $this->_db, $this->_silentWhenOK );
				$this->_sqlUpgrader->setDryRun( $dryRun );
				$success				=	$this->_sqlUpgrader->checkXmlDatabaseDescription( $db, '', $upgrade, $strictlyColumns, $strictlyEngine );
				/*
				var_dump( $success );
				echo "<br>\nERRORS: " . $this->_sqlUpgrader->getErrors( "<br /><br />\n\n", "<br />\n" );
				echo "<br>\nLOGS: " . $this->_sqlUpgrader->getLogs( "<br /><br />\n\n", "<br />\n" );
				exit;
				*/
			} else {
				$success				=	array( 'Error: could not find element database in XML file', null );
			}
		} else {
			$success					=	array( 'Error: could not find XML file', null );
		}
		return $success;
	}

	/**
	 * Handles SQL XML for the type of the field (backend use only!)
	 * e.g.: array( '#__comprofiler_fields' ), true
	 * array( '#__comprofiler', '#__comprofiler_field_values', '#__comprofiler_fields', '#__comprofiler_lists', '#__comprofiler_members', '#__comprofiler_plugin', '#__comprofiler_tabs', '#__comprofiler_userreports', '#__comprofiler_views' ), false
	 * Application::Database->getTableList(), false
	 *
	 * @param  array    $tablesArray  Array of tableNames:
	 * @param  boolean  $withContent  Dump with the content of tables
	 * @return string                 XML
	 */
	public function _dumpAll( $tablesArray, $withContent )
	{
		$sqlUpgrader					=	new DatabaseUpgrade( $this->_db );

		$sqlUpgrader->setDryRun( true );

		$tableXml						=	$sqlUpgrader->dumpTableToXml( $tablesArray, $withContent );
		if ( class_exists( 'DOMDocument' ) ) {
			$doc						=	new \DOMDocument( '1.0', 'UTF-8' );
			$doc->formatOutput			=	true;
			$domnode					=	dom_import_simplexml($tableXml);
			$domnode					=	$doc->importNode($domnode, true);
			$doc->appendChild($domnode);
			$text						=	str_replace( array( '/>', "\n\n" ), array( ' />', "\n" ), $doc->saveXML() );
		} else {
			$text						=	$tableXml->asXML();
		}
		return $text;
	}

	/**
	 * CB-specific stuff:
	 */

	/**
	 * Checks the all data content tables of comprofiler fields and upgrades if needed
	 * Backend-use only.
	 * @access private
	 *
	 * @param  boolean         $upgrade              False: only check table, True: upgrades table (depending on $dryRun)
	 * @param  boolean         $dryRun               True: doesn't do the modifying queries, but lists them, False: does the job
	 * @param  boolean         $preferredColumnType  Enforce preferred column type
	 * @return string                                Message to display
	 */
	public function checkAllCBfieldsDb( $upgrade = false, $dryRun = false, $preferredColumnType = false )
	{
		$this->_sqlUpgrader			=	new DatabaseUpgrade( $this->_db, $this->_silentWhenOK );
		$this->_sqlUpgrader->setDryRun( $dryRun );
		$this->_sqlUpgrader->setEnforcePreferredColumnType( $preferredColumnType );

		$this->_db->setQuery( "SELECT f.*"
//				f.fieldid, f.title, f.name, f.description, f.type, f.required, f.published, "
//			. "f.profile, f.ordering, f.registration, f.searchable, f.pluginid, f.sys, f.tablecolumns, "
//			. ", t.title AS 'tab', t.enabled AS 'tabenabled', t.pluginid AS 'tabpluginid', "
//			. "p.name AS pluginname, p.published AS pluginpublished, "
//			. "pf.name AS fieldpluginname, pf.published AS fieldpluginpublished "
			. "\n FROM "		. $this->_db->NameQuote( '#__comprofiler_fields' ) . ' AS f'
			. "\n INNER JOIN "	. $this->_db->NameQuote( '#__comprofiler_tabs' )   . ' AS t ON ( (f.' . $this->_db->NameQuote( 'tabid' ) . ' = t.' . $this->_db->NameQuote( 'tabid' ) . ') AND (t.' . $this->_db->NameQuote( 'fields' ) . ' = 1) ) '
			. "\n LEFT JOIN "	. $this->_db->NameQuote( '#__comprofiler_plugin' ) . ' AS p ON p.'    . $this->_db->NameQuote( 'id' )    . ' = t.' . $this->_db->NameQuote( 'pluginid' )
			. "\n LEFT JOIN "	. $this->_db->NameQuote( '#__comprofiler_plugin' ) . ' AS pf ON pf.'  . $this->_db->NameQuote( 'id' )    . ' = f.' . $this->_db->NameQuote( 'pluginid' )
			. "\n ORDER BY t. "	. $this->_db->NameQuote( 'ordering' ) . ', f.' . $this->_db->NameQuote( 'ordering' )
		);

		$rows = $this->_db->loadObjectList( 'fieldid', '\CB\Database\Table\FieldTable', array( &$this->_db ) );

		if ( $this->_db->getErrorNum() ) {
			echo $this->_db->getErrorMsg();
			return false;
		}
		$ret						=	true;
		foreach ( $rows as $field ) {
			$fieldHandler			=	new cbFieldHandler();
			$success				=	$fieldHandler->checkFixSQL( $this->_sqlUpgrader, $field, $upgrade );
			if ( ! $success ) {
				$ret				=	false;
				// echo $field->_error;
			}
		}
		return $ret;
	}

	/**
	 * Checks a few CB-specific stuff and upgrades if needed
	 * Backend-use only.
	 * @access private
	 *
	 * @param  boolean         $upgrade    False: only check table, True: upgrades table (depending on $dryRun)
	 * @param  boolean         $dryRun     True: doesn't do the modifying queries, but lists them, False: does the job
	 * @return string                      Message to display
	 */
	public function checkCBMandatoryDb( $upgrade = false, $dryRun = false )
	{
		$success				=	$this->_checkIfCBMandatoryOK();
		if ( $upgrade && ! $success ) {
			$success			=	$this->_fixCBmandatoryDb( $dryRun );
		}
		return $success;
	}

	/**
	 * Returns the core database SimpleXMLElement
	 *
	 * @return SimpleXMLElement
	 */
	protected function _getCbDbXml()
	{
		global $_CB_framework;

		static $_cb_db_xml			=	null;

		if ( $_cb_db_xml == null ) {
			$filename				=	$_CB_framework->getCfg( 'absolute_path' ) . '/administrator/components/com_comprofiler/database/database.cbcore.xml';
			if ( is_readable( $filename ) ) {
				$_cb_db_xml			=	new SimpleXMLElement( file_get_contents( $filename ) );
			}
		}
		return $_cb_db_xml;
	}

	/**
	 * Checks if mandatory CB Tabs tables are ok
	 *
	 * @return boolean  True: ok, False: not ok
	 */
	protected function _checkIfCBMandatoryOK( )
	{
		$success									=	false;

		$this->_sqlUpgrader		=	new DatabaseUpgrade( $this->_db, $this->_silentWhenOK );
		// fixing the tabid of installs before CB 1.0 RC 2:

		$sql			=	'SELECT * FROM ' . $this->_db->NameQuote( '#__comprofiler_tabs' )
						.	"\n ORDER BY "   . $this->_db->NameQuote( 'tabid' );		// `tabid`, `pluginclass`

		$this->_db->setQuery( $sql );
		$tabs			=	$this->_db->loadObjectList( 'tabid' );

		if ( $this->_db->getErrorNum() ) {
			$this->_sqlUpgrader->setError( 'Tabs selection query error: ' . $this->_db->getErrorMsg() );
		} else {
			// 0) check if all tabs are fine (as for new installs with CB 1.0 RC 2 included or more recent:
			//    so we avoid checking and messing with 3pd plugins which use CB pluginclasses:
			$success								=	true;
			foreach ( $tabs as $t ) {

				if ( isset( $this->_tabsShouldBe[$t->tabid] ) && ( $t->pluginclass == $this->_tabsShouldBe[$t->tabid] ) ) {
					// ok, cool, CORE tab: tabid and pluginclass match a core cb tab: no corrective action:
					continue;
				}
				if ( ( ! isset( $this->_tabsShouldBe[$t->tabid] ) ) && ( ! in_array( $t->pluginclass, $this->_tabsShouldBe ) ) ) {
					// ok, cool, NON-CORE tab: neither tabid nor pluginclass match a core cb tab: no corrective action:
					continue;
				}
				// well, we got a problem: either tabid XOR pluginclass of that tab are matching a CORE CB TAB:
				if ( isset( $this->_tabsShouldBe[$t->tabid] ) ) {
					$error							=	sprintf( 'This tab id is reserved for CB pluginclass "%s", so it needs to get another id.', $this->_tabsShouldBe[$t->tabid] );
				} else {
					$error							=	sprintf( 'This tab id is not right, this pluginclass is core CB and must have id %d, so it needs to change its id.', @implode( @array_keys( $this->_tabsShouldBe, $t->pluginclass ) ) );
				}

				$this->_sqlUpgrader->setError( sprintf( 'Error on tab id %d with pluginclass "%s": %s', $t->tabid, $t->pluginclass, $error ) );
				$success							=	false;
				// break;
			}
		}
		return $success;
	}

	/**
	 * fix mandatory CB tables for tabs and fields
	 *
	 * @param  boolean  $dryRun  TRUE: Just dry-runs to log actions that would be taken, FALSE: Run fixes for real
	 * @return bool
	 */
	protected function _fixCBmandatoryDb( $dryRun )
	{
		$this->_sqlUpgrader		=	new DatabaseUpgrade( $this->_db, $this->_silentWhenOK );
		$this->_sqlUpgrader->setDryRun( $dryRun );

		$sql			=	'SELECT * FROM ' . $this->_db->NameQuote( '#__comprofiler_tabs' )
						.	"\n ORDER BY "   . $this->_db->NameQuote( 'tabid' );		// `tabid`, `pluginclass`

		$this->_db->setQuery( $sql );

		$tabs			=	$this->_db->loadObjectList( 'tabid' );

		if ( $this->_db->getErrorNum() ) {
			$this->_sqlUpgrader->setError( 'Tabs selection query error: ' . $this->_db->getErrorMsg() );
			return false;
		}

		$sql			=	'SELECT '      . $this->_db->NameQuote( 'fieldid' ) . ', ' . $this->_db->NameQuote( 'tabid' )
						.	"\n FROM "     . $this->_db->NameQuote( '#__comprofiler_fields' )
						.	"\n ORDER BY " . $this->_db->NameQuote( 'tabid' );		// `tabid`, `pluginclass`

		$this->_db->setQuery( $sql );

		$fields			=	$this->_db->loadObjectList( 'fieldid' );

		if ( $this->_db->getErrorNum() ) {
			$this->_sqlUpgrader->setError( sprintf( 'Fields selection query error: ' . $this->_db->getErrorMsg() ), $sql );
			return false;
		}

		// 1) count and index tabs by core pluginclass and tabid holding array of fieldsids, so we can delete empty duplicate core tabs:
		$coreTabs			=	array();
		foreach ( $tabs as $t ) {
			if ( in_array( $t->pluginclass, $this->_tabsShouldBe ) ) {
				$coreTabs[$t->pluginclass][$t->tabid]	=	array();
			}
		}

		// 2) group fieldids by tabid
		// 3) add fields to $coreTabs[pluginclass][tabid][fieldid]
		$tabsFields			=	array();
		foreach ( $fields as $f ) {
			if ( isset( $tabs[$f->tabid] ) ) {
				$tabsFields[$f->tabid][$f->fieldid]		=	$f->fieldid;
				if ( $tabs[$f->tabid]->pluginclass != '' ) {
					$coreTabs[$tabs[$f->tabid]->pluginclass][$f->tabid][$f->fieldid]	=	$f->fieldid;
				}
			}
		}

		// 4) delete empty duplicate core tabs according to $coreTabs[pluginclass][tabid][fieldid]
		foreach ( $coreTabs as /* $pluginClass => */ $tabIds ) {
			if ( count( $tabIds ) > 1 ) {
				// there is more than one core tab for this core plugin class ! We need to decide which to keep:
				$tabidCandidatesToKeep					=	array();
				// 1st priority: keep tabs that are enabled AND have fields:
				foreach ( $tabIds as $tId => $tFields ) {
					if ( ( $tabs[$tId]->enabled == 1 ) && ( count( $tFields ) > 0 ) ) {
						$tabidCandidatesToKeep[]		=	$tId;
					}
				}
				// 2nd priority: keep tabs that have fields:
				if ( count( $tabidCandidatesToKeep ) == 0 ) {
					foreach ( $tabIds as $tId => $tFields ) {
						if ( count( $tFields ) > 0 ) {
							$tabidCandidatesToKeep[]	=	$tId;
						}
					}
				}
				// 3rd priority: keep tabs that are enabled:
				if ( count( $tabidCandidatesToKeep ) == 0 ) {
					foreach ( $tabIds as $tId => $tFields ) {
						if ( $tabs[$tId]->enabled == 1 ) {
							$tabidCandidatesToKeep[]	=	$tId;
						}
					}
				}
				// 4th priority: keep tab with the correct id:
				if ( count( $tabidCandidatesToKeep ) == 0 ) {
					foreach ( $tabIds as $tId => $tFields ) {
						if ( isset( $this->_tabsShouldBe[$tId] ) && ( $tabs[$tId]->pluginclass == $this->_tabsShouldBe[$tId] ) ) {
							$tabidCandidatesToKeep[]	=	$tId;
						}
					}
				}
				// 5th priority: well no more priorities to think of ! : just take first one !
				if ( count( $tabidCandidatesToKeep ) == 0 ) {
					foreach ( $tabIds as $tId => $tFields ) {
						$tabidCandidatesToKeep[]		=	$tId;
						break;
					}
				}
				// ok, by now we got at least one tab to keep: let's see which, in case we got more than one:
				if ( count( $tabidCandidatesToKeep ) == 1 ) {
					$tabToKeep							=	(int) $tabidCandidatesToKeep[0];
				} else {
					$tabToKeep							=	null;
					// a) has the right core id:
					foreach ( $tabidCandidatesToKeep as $tId ) {
						if ( isset( $this->_tabsShouldBe[$tId] ) && ( $tabs[$tId]->pluginclass == $this->_tabsShouldBe[$tId] ) ) {
							$tabToKeep					=	$tId;
							break;
						}
					}
					// b) first with fields:
					if ( $tabToKeep === null ) {
						foreach ( $tabidCandidatesToKeep as $tId ) {
							if ( count( $coreTabs[$tabs[$tId]->pluginclass][$tId] ) > 0 ) {
								$tabToKeep				=	$tId;
								break;
							}
						}
					}
					// c) first enabled one:
					if ( $tabToKeep === null ) {
						foreach ( $tabidCandidatesToKeep as $tId ) {
							if ( $tabs[$tId]->enabled == 1 ) {
								$tabToKeep				=	$tId;
								break;
							}
						}
					}
					// d) first one:
					if ( $tabToKeep === null ) {
						foreach ( $tabidCandidatesToKeep as $tId ) {
							$tabToKeep					=	$tId;
							break;
						}
					}
				}

				if ( $tabToKeep !== null ) {
					$tabsToDelete					=	array_diff( array_keys( $tabIds ), array( $tabToKeep ) );
					// first reassign the fields of the tabs to delete:
					$fieldsToReassign				=	array();
					foreach ( $tabIds as $tId => $tFields ) {
						if ( ( $tId != $tabToKeep ) && count( $tFields ) > 0 ) {
							$fieldsToReassign		=	array_merge( $fieldsToReassign, $tFields );
						}
					}
					if ( count( $fieldsToReassign ) > 0 ) {
						$sql	=	'UPDATE ' . $this->_db->NameQuote( '#__comprofiler_fields' )
								.	' SET '   . $this->_db->NameQuote( 'tabid' )   . ' = ' . (int) $tabToKeep
								. "\n WHERE " . $this->_db->NameQuote( 'fieldid' ) . ' IN ' . $this->_db->safeArrayOfIntegers( $fieldsToReassign );

						if ( ! ( $dryRun || $this->_db->query( $sql ) ) ) {
							$this->_sqlUpgrader->setError( 'Failed changing fieldids ' . $this->_db->safeArrayOfIntegers( $fieldsToReassign ) . ' from duplicates of kept core tabid: ' . $tabToKeep . ' because of error:' . $this->_db->getErrorMsg(), $sql );
							break;
						} else {
							$this->_sqlUpgrader->setLog( 'Changed fieldids ' . $this->_db->safeArrayOfIntegers( $fieldsToReassign ) . ' from duplicates of kept core tabid: ' . $tabToKeep, $sql, 'change' );
						}

					}
					// c) remove duplicate core tabs:
					$sql		=	'DELETE FROM ' . $this->_db->NameQuote( '#__comprofiler_tabs' )
								.	"\n WHERE "    . $this->_db->NameQuote( 'tabid' ) . ' IN ' . $this->_db->safeArrayOfIntegers( $tabsToDelete );

					if ( ! ( $dryRun || $this->_db->query( $sql ) ) ) {
						$this->_sqlUpgrader->setError( 'Failed deleting duplicates tabids ' . $this->_db->safeArrayOfIntegers( $tabsToDelete ) . ' of the used core tabid: ' . $tabToKeep . ' because of error:' . $this->_db->getErrorMsg(), $sql );
						break;
					} else {
						$this->_sqlUpgrader->setLog( 'Deleted duplicate core tabs tabids ' . $this->_db->safeArrayOfIntegers( $tabsToDelete ) . ' of the used core tabid: ' . $tabToKeep, $sql, 'change' );
					}

				}
			}
		}

		// 5) refetch tabs with now free space at reserved positions:
		$sql			=	'SELECT * FROM ' . $this->_db->NameQuote( '#__comprofiler_tabs' )
						.	"\n ORDER BY "   . $this->_db->NameQuote( 'tabid' );		// `tabid`, `pluginclass`
		$this->_db->setQuery( $sql );

		$tabs			=	$this->_db->loadObjectList( 'tabid' );

		if ( $this->_db->getErrorNum() ) {
			$this->_sqlUpgrader->setError( 'Tabs 2nd selection query error: ' . $this->_db->getErrorMsg(), $sql );
			return false;
		}
		unset( $coreTabs );		// this one is now invalid, and not needed anymore

		$sql			=	'SELECT '    . $this->_db->NameQuote( 'fieldid' ) . ', ' . $this->_db->NameQuote( 'tabid' )
						. "\n FROM "     . $this->_db->NameQuote( '#__comprofiler_fields' )
						. "\n ORDER BY " . $this->_db->NameQuote( 'tabid' );
		$this->_db->setQuery( $sql );

		$fields			=	$this->_db->loadObjectList( 'fieldid' );
		if ( $this->_db->getErrorNum() ) {
			$this->_sqlUpgrader->setError( 'Fields 3nd selection query error: ' . $this->_db->getErrorMsg(), $sql );
			return false;
		}
		// group fieldids by tabid
		$tabsFields			=	array();
		foreach ( $fields as $f ) {
			if ( isset( $tabs[$f->tabid] ) ) {
				$tabsFields[$f->tabid][$f->fieldid]		=	$f->fieldid;
			}
		}

		// 6) check tabs one by one, making room in reserved positions:
		foreach ( $tabs as $t ) {

			if ( isset( $this->_tabsShouldBe[$t->tabid] ) && ( $t->pluginclass == $this->_tabsShouldBe[$t->tabid] ) ) {
				// ok, cool, tabid and plugin matches: no corrective action:
				continue;
			}

			if ( isset( $this->_tabsShouldBe[$t->tabid] ) ) {
				// not ok: tabid is taken by another tab: we need to relocate this tab at last position:

				// a) insert same tab in another tabid
				$oldTabId	=	$t->tabid;
				if ( ! $dryRun ) {
					$t->tabid	=	null;
					if ( ! $this->_db->insertObject( '#__comprofiler_tabs', $t, 'tabid' ) ) {
						$this->_sqlUpgrader->setError( 'Failed moving (inserting) non-core tabid: ' . $oldTabId . ' because of error:' . $this->_db->getErrorMsg(), $sql );
						break;
					}
					$t->tabid	=	$this->_db->insertid();
				} else {
					$t->tabid	=	$t->tabid + 10000;		// just to fake the insert
				}
				$this->_sqlUpgrader->setLog( 'Inserted old tabid ' . $oldTabId . ' as new tabid ' . $t->tabid, ( $dryRun ? 'INSERT tabobject' : $this->_db->getQuery() ), 'change' );

				// b) change fields' tabid:
				if ( isset( $tabsFields[$oldTabId] ) && ( count( $tabsFields[$oldTabId] ) > 0 ) ) {
					$sql	=	'UPDATE '   . $this->_db->NameQuote( '#__comprofiler_fields' )
							.	' SET '     . $this->_db->NameQuote( 'tabid' ) . ' = ' . (int) $t->tabid
							.	"\n WHERE " . $this->_db->NameQuote( 'tabid' ) . ' = ' . (int) $oldTabId;

					if ( ! ( $dryRun || $this->_db->query( $sql ) ) ) {
						$this->_sqlUpgrader->setError( 'Failed changing fields from old non-core tab with core tabid: ' . $oldTabId . ' to new tabid: ' . $t->tabid . ' because of error:' . $this->_db->getErrorMsg(), $sql );
						break;
					} else {
						$this->_sqlUpgrader->setLog( 'Changed fields from old non-core tab with core tabid: ' . $oldTabId . ' (that must be for ' . $this->_tabsShouldBe[$oldTabId] . ') to new tabid: ' . $t->tabid, $sql, 'change' );
					}

				}

				// c) remove old tab:
				$sql		=	'DELETE FROM ' . $this->_db->NameQuote( '#__comprofiler_tabs' )
							.	"\n WHERE "    . $this->_db->NameQuote( 'tabid' ) . ' = ' . (int) $oldTabId;

				if ( ! ( $dryRun || $this->_db->query( $sql ) ) ) {
					$this->_sqlUpgrader->setError( 'Failed deleting old non-core tabid: ' . $oldTabId . ' which is already copied to new tabid: ' . $t->tabid . ' because of error:' . $this->_db->getErrorMsg(), $sql );
					break;
				} else {
					$this->_sqlUpgrader->setLog( 'Deleted old non-core tabid: ' . $oldTabId . ' which is already copied to new tabid: ' . $t->tabid, $sql, 'change' );
				}


			}
		}

		// 7) refetch tabs with now free space at reserved positions as well as fields and recompute $tabFields:
		$sql			=	'SELECT * FROM ' . $this->_db->NameQuote( '#__comprofiler_tabs' )
						.	"\n ORDER BY "   . $this->_db->NameQuote( 'tabid' );		// `tabid`, `pluginclass`

		$this->_db->setQuery( $sql );
		$tabs			=	$this->_db->loadObjectList( 'tabid' );

		if ( $this->_db->getErrorNum() ) {
			$this->_sqlUpgrader->setError( 'Tabs 3rd selection query error: ' . $this->_db->getErrorMsg(), $sql );
			return false;
		}

		$sql			=	'SELECT '      . $this->_db->NameQuote( 'fieldid' ) . ', '   . $this->_db->NameQuote( 'tabid' )
						.	"\n FROM "     . $this->_db->NameQuote( '#__comprofiler_fields' )
						.	"\n ORDER BY " . $this->_db->NameQuote( 'tabid' );

		$this->_db->setQuery( $sql );
		$fields			=	$this->_db->loadObjectList( 'fieldid' );

		if ( $this->_db->getErrorNum() ) {
			$this->_sqlUpgrader->setError( 'Fields 3nd selection query error: ' . $this->_db->getErrorMsg(), $sql );
			return false;
		}

		// group fieldids by tabid
		$tabsFields			=	array();
		foreach ( $fields as $f ) {
			if ( isset( $tabs[$f->tabid] ) ) {
				$tabsFields[$f->tabid][$f->fieldid]		=	$f->fieldid;
			}
		}

		// 8) check tabs one by one, moving tabs back to reserved positions if needed:
		foreach ( $tabs as $t ) {

			if ( isset( $this->_tabsShouldBe[$t->tabid] ) && ( $t->pluginclass == $this->_tabsShouldBe[$t->tabid] ) ) {
				// ok, cool, tabid and plugin matches: no corrective action:
				continue;
			}

			if ( ( ! isset( $this->_tabsShouldBe[$t->tabid] ) ) && in_array( $t->pluginclass, $this->_tabsShouldBe ) ) {
				// ok we found a core CB tab which doesn't have the right id: the right id is now free, so just update the tab:
				$newTabId	=	array_search( $t->pluginclass, $this->_tabsShouldBe );
				if ( $newTabId !== false ) {

					// a) move the core tab to the right tabid:

					$sql	=	'UPDATE '   . $this->_db->NameQuote( '#__comprofiler_tabs' )
							.	' SET '     . $this->_db->NameQuote( 'tabid' ) . ' = ' . (int) $newTabId
							.	"\n WHERE " . $this->_db->NameQuote( 'tabid' ) . ' = ' . (int) $t->tabid;

					if ( ! ( $dryRun || $this->_db->query( $sql ) ) ) {
						$this->_sqlUpgrader->setError( 'Failed moving core tab from old tabid: ' . $t->tabid . ' to new tabid: ' . $newTabId . ' because of error:' . $this->_db->getErrorMsg(), $sql );
						break;
					} else {
						$this->_sqlUpgrader->setLog( 'Moved core tab from old tabid: ' . $t->tabid . ' to new tabid: ' . $newTabId, $sql, 'change' );
					}

					// b) change fields' tabid:

					if ( isset( $tabsFields[$t->tabid] ) && ( count( $tabsFields[$t->tabid] ) > 0 ) ) {

						$sql	=	'UPDATE '   . $this->_db->NameQuote( '#__comprofiler_fields' )
								.	' SET '     . $this->_db->NameQuote( 'tabid' ) . ' = ' . (int) $newTabId
								.	"\n WHERE " . $this->_db->NameQuote( 'tabid' ) . ' = ' . (int) $t->tabid;

						if ( ! ( $dryRun || $this->_db->query( $sql ) ) ) {
							$this->_sqlUpgrader->setError( 'Failed changing fields from old core tabid: ' . $t->tabid . ' to new tabid: ' . $newTabId . ' because of error:' . $this->_db->getErrorMsg(), $sql );
							break;
						} else {
							$this->_sqlUpgrader->setLog( 'Changed fields from old core tabid: ' . $t->tabid . ' to new tabid: ' . $newTabId, $sql, 'change' );
						}

					}
				}
			}
		}
		// now missing core tabs will be inserted in the new 1.2 upgrader in next step.
		return true;
	}

	/**
	 * Shows result of database check or fix (with or without dryrun)
	 *
	 * @param  DatabaseUpgrade|CBDatabaseChecker|cbInstallerPlugin  $dbChecker
	 * @param  boolean                                              $upgrade
	 * @param  boolean                                              $dryRun
	 * @param  boolean                                              $result
	 * @param  array                                                $messagesBefore
	 * @param  array                                                $messagesAfter
	 * @param  string                                               $dbName
	 * @param  int                                                  $dbId
	 * @param  boolean                                              $showConclusion
	 */
	public static function renderDatabaseResults( &$dbChecker, $upgrade, $dryRun, $result, $messagesBefore, $messagesAfter, $dbName, $dbId, $showConclusion = true ) {
		global $_CB_framework;

		static $JS_LOADED			=	0;

		if ( ! $JS_LOADED++ ) {
			$js						=	"$( '.cbDbResultsLogShow' ).on( 'click', function() {"
									.		"$( this ).addClass( 'hidden' );"
									.		"$( this ).siblings( '.cbDbResultsLogHide' ).removeClass( 'hidden' );"
									.		"$( this ).siblings( '.cbDbResultsLogMsgs' ).slideDown();"
									.	"});"
									.	"$( '.cbDbResultsLogHide' ).on( 'click', function() {"
									.		"$( this ).addClass( 'hidden' );"
									.		"$( this ).siblings( '.cbDbResultsLogShow' ).removeClass( 'hidden' );"
									.		"$( this ).siblings( '.cbDbResultsLogMsgs' ).slideUp();"
									.	"});"
									.	"$( '.cbDbResultsLogMsgs' ).hide();";

			$_CB_framework->outputCbJQuery( $js );
		}

		$cbSpoofField				=	cbSpoofField();
		$cbSpoofString				=	cbSpoofString( null, 'plugin' );
		$return						=	'<div class="cbDbResults">';

		if ( $messagesBefore ) {
			$return					.=		'<div class="form-group cb_form_line clearfix cbDbResultsMsgs">';

			foreach ( $messagesBefore as $msg ) {
				if ( $msg ) {
					$return			.=			'<div class="cbDbResultsMsg">' . $msg . '</div>';
				}
			}

			$return					.=		'</div>';
		}

		if ( $dbChecker !== null ) {
			$return					.=		'<div class="form-group cb_form_line clearfix cbDbResultsCheck">';

			if ( $result == true ) {
				$return				.=			'<div class="text-success cbDbResultsSuccess">';

				if ( $upgrade ) {
					if ( $dryRun ) {
						$return		.=				CBTxt::T( 'NAME_DATABASE_ADJS_DRY_SUCCESS', '[name] database adjustments dryrun is successful. See results below.', array( '[name]' => $dbName ) );
					} else {
						$return		.=				CBTxt::T( 'NAME_DATABASE_ADJS_SUCCESS', '[name] database adjustments have been performed successfully.', array( '[name]' => $dbName ) );
					}
				} else {
					$return			.=				CBTxt::T( 'ALL_NAME_DATABASE_UPTODATE', 'All [name] database is up to date.', array( '[name]' => $dbName ) );
				}

				$return				.=			'</div>';
			} elseif ( is_string( $result ) ) {
				$return				.=			'<div class="text-danger">'
									.				$result
									.			'</div>';
			} else {
				$return				.=			'<div class="text-danger cbDbResultsErrors">';

				if ( $upgrade ) {
					$return			.=				CBTxt::T( 'NAME_DATABASE_ADJS_ERRORS', '[name] database adjustments errors:', array( '[name]' => $dbName ) );
				} else {
					$return			.=				CBTxt::T( 'NAME_DATABASE_STRUCT_DIFF', '[name] database structure differences:', array( '[name]' => $dbName ) );
				}

				foreach ( $dbChecker->getErrors( false ) as $error ) {
					$return			.=				'<div class="text-large cbDbResultsError">'
									.					htmlspecialchars( $error[0] )
									.					( $error[1] ? '<div class="text-small">' . htmlspecialchars( $error[1] ) . '</div>' : null )
									.				'</div>';
				}

				$return				.=			'</div>';

				if ( ! $upgrade ) {
					$return			.=			'<div class="text-danger cbDbResultsErrorsFix">'
									.				CBTxt::T( 'NAME_DATABASE_STRUCT_FIX', 'The [name] database structure differences can be fixed (adjusted) by clicking here:', array( '[name]' => $dbName ) )
									.				' <span class="alert alert-sm alert-danger text-large">'
									.					'<a href="' . $_CB_framework->backendUrl( "index.php?option=com_comprofiler&view=fixcbdb&dryrun=0&databaseid=$dbId&$cbSpoofField=$cbSpoofString" ) . '">'
									.						CBTxt::T( 'NAME_DATABASE_DIFF_FIX_CLICK_HERE', 'Click here to fix (adjust) all [name] database differences listed above', array( '[name]' => $dbName ) )
									.					'</a>'
									.				'</span> '
									.				CBTxt::T( 'DATABASE_STRUCT_DRY_CLICK_HERE', '(you can also <a href="[url]">Click here to preview fixing (adjusting) queries in a dry-run</a>), but <strong class="text-large text-underline">in all cases you need to backup database first</strong> as this adjustment is changing the database structure to match the needed structure for the installed version.', array( '[url]' => $_CB_framework->backendUrl( "index.php?option=com_comprofiler&view=fixcbdb&dryrun=1&databaseid=$dbId&$cbSpoofField=$cbSpoofString" ) ) )
									.			'</div>';
				}
			}

			$logs					=	$dbChecker->getLogs( false );

			if ( count( $logs ) > 0 ) {
				$return				.=			'<div class="cbDbResultsLog">'
									.				'<a href="javascript:void(0);" class="cbDbResultsLogShow">' . CBTxt::T( 'Click here to show details' ) . ' <span class="fa fa-caret-down"></span></a>'
									.				'<a href="javascript:void(0);" class="cbDbResultsLogHide hidden">' . CBTxt::T( 'Click here to hide details' ) . ' <span class="fa fa-caret-up"></span></a>'
									.				'<div class="text-success cbDbResultsLogMsgs" style="display: none;">';

				foreach ( $logs as $log ) {
					$return			.=					'<div class="text-large cbDbResultsLogMsg">'
									.						htmlspecialchars( $log[0] )
									.						( $log[1] ? '<div class="text-small">' . htmlspecialchars( $log[1] ) . '</div>' : null )
									.					'</div>';
				}

				$return				.=				'</div>'
									.			'</div>';
			}

			$return					.=		'</div>';
		}

		if ( $showConclusion ) {
			if ( $upgrade ) {
				if ( $dryRun ) {
					$return			.=		'<div class="form-group cb_form_line clearfix cbDbResultsConclusion">'
									.			CBTxt::T( 'NAME_DATABASE_ADJS_DRY', 'Dry-run of [name] database adjustments done. None of the queries listed in details have been performed.', array( '[name]' => $dbName ) )
									.			'<br />'
									.			CBTxt::T( 'NAME_DATABASE_ADJS_FIX', 'The [name] database adjustments listed above can be applied by clicking here:', array( '[name]' => $dbName ) )
									.			' <span class="alert alert-sm alert-danger text-large">'
									.				'<a href="' . $_CB_framework->backendUrl( "index.php?option=com_comprofiler&view=fixcbdb&dryrun=0&databaseid=$dbId&$cbSpoofField=$cbSpoofString" ) . '">'
									.					CBTxt::T( 'NAME_DATABASE_DIFF_FIX_CLICK_HERE', 'Click here to fix (adjust) all [name] database differences listed above', array( '[name]' => $dbName ) )
									.				'</a>'
									.			'</span> '
									.			CBTxt::T( 'DATABASE_FIX_BACKUP_FIRST', '<strong class="text-danger text-large text-underline">You need to backup database first</strong> as this fixing/adjusting is changing the database structure to match the needed structure for the installed version.' )
									.		'</div>';
				} else {
					$return			.=		'<div class="form-group cb_form_line clearfix cbDbResultsConclusion">'
									.			CBTxt::T( 'NAME_DATABASE_ADJS_DONE', 'The [name] database adjustments have been done. If all lines above are in green, database adjustments completed successfully. Otherwise, if some lines are red, please report exact errors and queries to authors forum, and try checking database again.', array( '[name]' => $dbName ) )
									.			'<br />'
									.			CBTxt::T( 'NAME_DATABASE_STRUCT_CHECK', 'The [name] database structure can be checked again by clicking here:', array( '[name]' => $dbName ) )
									.			' <span class="alert alert-sm alert-warning text-large">'
									.				'<a href="' . $_CB_framework->backendUrl( "index.php?option=com_comprofiler&view=checkcbdb&databaseid=$dbId&$cbSpoofField=$cbSpoofString" ) . '">'
									.					CBTxt::T( 'NAME_DATABASE_DIFF_CHECK_CLICK_HERE', 'Click here to check [name] database', array( '[name]' => $dbName ) )
									.				'</a>'
									.			'</span> '
									.		'</div>';
				}
			} else {
					$return			.=		'<div class="form-group cb_form_line clearfix cbDbResultsConclusion">'
									.			CBTxt::T( 'NAME_DATABASE_CHECKS_DONE', '[name] database checks done. If all lines above are in green, test completed successfully. Otherwise, please take corrective measures proposed in red.', array( '[name]' => $dbName ) )
									.		'</div>';
			}
		}

		if ( $messagesAfter ) {
			$return					.=		'<div class="form-group cb_form_line clearfix cbDbResultsMsgs">';

			foreach ( $messagesAfter as $msg ) {
				if ( $msg ) {
					$return			.=			'<div class="cbDbResultsMsg">' . $msg . '</div>';
				}
			}

			$return					.=		'</div>';
		}

		$return						.=	'</div>';

		echo $return;
	}
}
