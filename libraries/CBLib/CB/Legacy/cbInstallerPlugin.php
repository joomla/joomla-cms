<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/20/14 1:24 AM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\Database\DatabaseUpgrade;
use CBLib\Language\CBTxt;
use CBLib\Xml\SimpleXMLElement;
use CB\Database\Table\PluginTable;
use CB\Database\Table\TabTable;
use CB\Database\Table\FieldTable;
use CB\Database\Table\FieldValueTable;
use CB\Database\CBDatabaseChecker;

defined('CBLIB') or die();

/**
 * cbInstallerPlugin Class implementation
 * Implements installation of a CB plugin
 */
class cbInstallerPlugin extends cbInstaller
{
	/**
	 * Element type
	 * (needs to be public for backwards compatibility)
	 * @var string
	 */
	public $elementType				=	'plugin';
	/**
	 * Upgrade Errors from \CBLib\Database\DatabaseUpgrade class
	 * @var array
	 */
	protected $checkDatabaseErrors	=	null;
	/**
	 * Upgrade Logs from \CBLib\Database\DatabaseUpgrade class
	 * @var array
	 */
	protected $checkDatabaseLogs	=	null;

	/**
	 * Custom install method
	 *
	 * @param  null|string  $fromDirectory            Directory of plugin to install
	 * @param  boolean      $InstallIntoDatabaseOnly  Install plugin database only
	 * @return boolean
	 */
	function install( $fromDirectory = null, $InstallIntoDatabaseOnly = false )
	{
		global $_CB_framework, $_CB_database, $ueConfig, $_PLUGINS;

		if (!$this->preInstallCheck( $fromDirectory,$this->elementType )) {
			return false;
		}

		$cbInstallXML			=	$this->i_xmldocument;

		// Get name
		$e						=	$cbInstallXML->getElementByPath( 'name' );
		$this->elementName( $e->data() );
		$cleanedElementName		=	strtolower(str_replace(array(" ","."),array("","_"),$this->elementName()));

		// Get plugin filename
		$files_element			=	$cbInstallXML->getElementByPath( 'files' );
		$files_names			=	array();
		foreach ( $files_element->children() as $file ) {
			$files_names[]		=	$file->data();
			if ($file->attributes( "plugin" )) {
				$this->elementSpecial( $file->attributes( "plugin" ) );
			}
		}
		$fileNopathNoext		=	null;
		$matches			=	array();
		if ( preg_match("/^.*[\\/\\\\](.*)\\..*$/", $this->installFilename(), $matches ) ) {
			$fileNopathNoext	=	$matches[1];
		}
		if ( ! ( $fileNopathNoext && ( $this->elementSpecial() == $fileNopathNoext ) ) ) {
			$this->setError( 1, 'Installation filename `' . $fileNopathNoext . '` (with .xml) does not match main php file plugin attribute `'  . $this->elementSpecial() . '` in the plugin xml file<br />' );
			return false;
		}
		$cleanedMainFileName	=	strtolower(str_replace(array(" ","."),array("","_"),$this->elementSpecial()));

		// check version
		$v						=	$cbInstallXML->getElementByPath( 'version' );
		$version				=	$v->data();
		$THISCBVERSION			=	'2.0.11';
		if ( ( $version == $ueConfig['version'] ) || ( $version == $THISCBVERSION ) || ( version_compare( $version, $ueConfig['version'], '<=' ) && version_compare( $version, '1.0', '>=' ) ) ) {
			;
		} else {
			$this->setError( 1, 'Plugin version ('.$version.') different from Community Builder version ('.$ueConfig['version'].')' );
			return false;
		}

		$backendMenu			=	"";
		$adminmenusnode			=	$cbInstallXML->getElementByPath( 'adminmenus' );
		if ( $adminmenusnode !== false ) {
			$menusArr			=	array();
			//cycle through each menu
			foreach( $adminmenusnode->children() AS $menu ) {
				if ( $menu->getName() == "menu" ) {
					$action		=	$menu->attributes('action');
					$text		=	CBTxt::T( $menu->data() );
					$menusArr[]	=	$text . ":" . $action;
				}
			}
			$backendMenu		=	implode( ",", $menusArr );
		}

		$folder					=	strtolower($cbInstallXML->attributes( 'group' ));
		if ( cbStartOfStringMatch( $folder, '/' ) ) {
			$this->elementDir( $_CB_framework->getCfg('absolute_path') . $folder . '/' );
			$subFolder			=	$folder;
		} else {
			$subFolder			=	( ( $folder == 'user' ) ? 'plug_' : '' ) . $cleanedElementName;
			$this->elementDir( $_CB_framework->getCfg('absolute_path') . '/components/com_comprofiler/plugin/' . $folder . '/' . $subFolder . '/' );
		}

		$upgradeMethod			=	$this->installMethod( $cbInstallXML->attributes( 'method' ) );

		if (file_exists($this->elementDir()) && ! $upgradeMethod ) {
			$this->setError( 1, 'Another plugin is already using directory: "' . $this->elementDir() . '"' );
			return false;
		}

		$parentFolder			=	preg_replace( '/\/[^\/]*\/?$/', '/', $this->elementDir() );
		if ( ! file_exists( $parentFolder ) ) {
			$this->setError( 1, sprintf( 'The directory in which the plugin should install does not exist: probably the parent extension is not installed. Install parent extension first. Plugin parent directory missing: "%s" and plugin directory specified by installer for installation "%s"', $parentFolder, $this->elementDir() ) );
			return false;
		}

		if ( ! $InstallIntoDatabaseOnly ) {
			if(!file_exists($this->elementDir()) && !$this->createDirectoriesForPath($this->elementDir())) {
				$this->setError( 1, 'Failed to create directory' .' "' . $this->elementDir() . '"' );
				return false;
			}

			// Copy files from package:
			if ($this->parseFiles( 'files', 'plugin', 'No file is marked as plugin file' ) === false) {
				$this->cleanupInstall( null, $this->elementDir() );	// try removing directory and content just created successfully
				return false;
			}

			// Copy XML file from package (needed for creating fields of new types and so on):
			if ($this->copySetupFile() === false) {
				$this->cleanupInstall( null, $this->elementDir() );	// try removing directory and content just created successfully
				return false;
			}
		}

		// Check to see if plugin already exists in db
		$_CB_database->setQuery( "SELECT id FROM #__comprofiler_plugin WHERE element = '" . $this->elementSpecial() . "' AND folder = '" . $subFolder . "'" );
		if (!$_CB_database->query()) {
			$this->setError( 1, 'SQL error' .': ' . $_CB_database->getErrorMsg() );
			if ( ! $InstallIntoDatabaseOnly ) {
				$this->cleanupInstall( null, $this->elementDir() );	// try removing directory and content just created successfully
			}
			return false;
		}

		$pluginId 				=	$_CB_database->loadResult();

		$pluginRowWasNotExisting	=	( ! $pluginId );

		$row					=	new PluginTable();
		if ( $pluginId ) {
			$row->load( (int) $pluginId );
		}
		if ( ! $row->id ) {
			$row->name = $this->elementName();
			$row->ordering		=	99;
			$row->iscore		=	0;
			$row->viewaccesslevel	=	1;
			$row->client_id		=	0;
			$row->published		=	( $folder == 'language' ? 1 : 0 );
		}
		$row->type				=	$folder;
		$row->folder			=	$subFolder;
		$row->backend_menu		=	$backendMenu;
		$row->element			=	$this->elementSpecial();

		if (!$row->store()) {
			$this->setError( 1, 'SQL error' .': ' . $row->getError() );
			if ( ! $InstallIntoDatabaseOnly ) {
				$this->cleanupInstall( null, $this->elementDir() );	// try removing directory and content just created successfully
			}
			return false;
		}
		$pluginId				=	(int) $row->id;
		$savePreviousPluginId	=	$_PLUGINS->_setLoading( $row, true );

		$sqlUpgrader							=	new DatabaseUpgrade( null, false );
		$success								=	null;

		// Are there any Database statements ??
		$db										=	$cbInstallXML->getElementByPath( 'database' );
		if ( ( $db !== false ) && ( count( $db->children() ) > 0 ) ) {
//$sqlUpgrader->setDryRun( true );
			$success							=	$sqlUpgrader->checkXmlDatabaseDescription( $db, $cleanedElementName, true, null, null );
			/*
			var_dump( $success );
			echo "<br>\nERRORS: " . $sqlUpgrader->getErrors( "<br /><br />\n\n", "<br />\n" );
			echo "<br>\nLOGS: " . $sqlUpgrader->getLogs( "<br /><br />\n\n", "<br />\n" );
			exit;
			*/
			if ( ! $success ) {
				$this->setError( 1, "Plugin database XML SQL Error " . 	$sqlUpgrader->getErrors() );
				if ( $pluginRowWasNotExisting ) {
					$this->deleteTabAndFieldsOfPlugin( $row->id );	// delete tabs and private fields of plugin
					$row->delete();
				}
				if ( ! $InstallIntoDatabaseOnly ) {
					$this->cleanupInstall( null, $this->elementDir() );	// try removing directory and content just created successfully
				}
				$_PLUGINS->_setLoading( $row, $savePreviousPluginId );
				return false;
			}
		}

		$e											=	$cbInstallXML->getElementByPath( 'description' );
		$desc										=	$this->elementName();

		if ( $e !== false ) {
			$desc									.=	'<div>' . $e->data() . '</div>';

			$this->setError( 0, $desc );
		}
		//If type equals user then check for tabs and fields
		if ( $folder == 'user' ) {
			$tabsnode								=	$cbInstallXML->getElementByPath( 'tabs' );
			if( $tabsnode !== false ) {
				//cycle through each tab
				foreach( $tabsnode->children() AS $tab ) {
					if ( $tab->getName() == 'tab' ) {
						//install each tab
						$tabid						=	$this->installTab($pluginId,$tab);
						if ( $tabid ) {
							//get all fields in the tab
							$fieldsnode				=	$tab->getElementByPath( 'fields' );
							if ( $fieldsnode !== false ) {
								//cycle through each field
								foreach( $fieldsnode->children() AS $field ) {
									if ($field->getName() == "field") {
										//install each field
										//echo "installing field...";
										$fieldid	=	$this->installField($pluginId,$tabid,$field);
										//get all fieldvalues for the field
										//cycle through each fieldValue
										foreach( $field->children() AS $fieldValue) {
											if ( $fieldValue->getName() == "fieldvalue" ) {
												$this->installFieldValue($fieldid,$fieldValue);
											}
										}
									}
								}
							}
						} else {
							if ( $pluginRowWasNotExisting ) {
								if ( $db ) {
									$sqlUpgrader->checkXmlDatabaseDescription( $db, $cleanedElementName, 'drop', null, null );
								}
								$this->deleteTabAndFieldsOfPlugin( $row->id );	// delete tabs and private fields of plugin
								$row->delete();
							}
							if ( ! $InstallIntoDatabaseOnly ) {
								$this->cleanupInstall( null, $this->elementDir() );	// try removing directory and content just created successfully
							}
							$_PLUGINS->_setLoading( $row, $savePreviousPluginId );
							return false;
						}
					}
				}
			}
			// (re)install field types of plugin:
			$fieldtypes							=	$cbInstallXML->getElementByPath( 'fieldtypes' );
			if( $fieldtypes !== false ) {
				foreach ( $fieldtypes->children() as $typ ) {
					if ( $typ->getName() == 'field' ) {
						$this->installFieldType( $pluginId, $typ->attributes( 'type' ) );
					}
				}
			}
		}

		// Check if there are any files that need to be deleted:
		$deleteElements				=	$cbInstallXML->getElementByPath( 'delete' );

		if ( $deleteElements !== false ) {
			$adminFS				=	cbAdminFileSystem::getInstance();

			foreach ( $deleteElements->children() as $file ) {
				$filename			=	$file->data();

				if ( $adminFS->file_exists( $this->i_elementdir . $filename ) && ( ! in_array( $filename, $files_names ) ) ) {
					if ( $file->getName() == 'foldername' ) {
						$adminFS->deldir( _cbPathName( $this->i_elementdir . $filename . '/' ) );
					} elseif ( $file->getName() == 'filename' ) {
						$adminFS->unlink( _cbPathName( $this->i_elementdir . $filename, false ) );
					}
				}
			}
		}

		// Are there any SQL queries??
		$query_element							=	$cbInstallXML->getElementByPath( 'install/queries' );
		if ( $query_element !== false ) {
			foreach( $query_element->children() as $query ) {
				$_CB_database->setQuery( trim( $query->data() ) );
				if ( ! $_CB_database->query() )
				{
					$this->setError( 1, "SQL Error " . $_CB_database->getErrorMsg() );
					if ( $pluginRowWasNotExisting ) {
						if ( $db ) {
							$sqlUpgrader->checkXmlDatabaseDescription( $db, $cleanedElementName, 'drop', null, null );
						}
						$this->deleteTabAndFieldsOfPlugin( $row->id );	// delete tabs and private fields of plugin
						$row->delete();
					}
					if ( ! $InstallIntoDatabaseOnly ) {
						$this->cleanupInstall( null, $this->elementDir() );	// try removing directory and content just created successfully
					}
					$_PLUGINS->_setLoading( $row, $savePreviousPluginId );
					return false;
				}
			}
		}

		// Are there any CBLib libraries ?
		$libraries_element							=	$cbInstallXML->getElementByPath( 'libraries' );
		if ( $libraries_element !== false ) {
			foreach( $libraries_element->children() as $library ) {
				if ( $library->getName() != 'library' ) {
					continue;
				}

				// Copy files from library package:
				$savePackage						=	$this->i_xmldocument;
				$subFolder							=	$library->attributes( 'name' );
				$saveElement						=	$this->elementDir( $_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/libraries/' . ( $subFolder ? $subFolder . '/' : null ) );
				$this->i_xmldocument				=	$library;

				if ( ! file_exists( $this->elementDir() ) && ! $this->createDirectoriesForPath( $this->elementDir() ) ) {
					$this->setError( 1, 'Failed to create directory' .' "' . $this->elementDir() . '"' );

					$this->i_xmldocument				=	$savePackage;
					$this->elementDir( $saveElement );
					$this->cleanupInstall( null, $this->elementDir() );	// try removing directory and content just created successfully

					return false;
				}

				if ($this->parseFiles( 'files' ) === false) {
					$this->i_xmldocument				=	$savePackage;
					$this->elementDir( $saveElement );
					$this->cleanupInstall( null, $this->elementDir() );	// try removing directory and content just created successfully

					return false;
				}

				$this->i_xmldocument				=	$savePackage;
				$this->elementDir( $saveElement );
			}
		}


		// Is there an installfile
		$installfile_elemet						=	$cbInstallXML->getElementByPath( 'installfile' );

		if ( $installfile_elemet !== false ) {
			// check if parse files has already copied the install.component.php file (error in 3rd party xml's!)
			if ( ( ! $InstallIntoDatabaseOnly ) && ( ! file_exists( $this->elementDir() . $installfile_elemet->data() ) ) ) {
				if( ! $this->copyFiles( $this->installDir(), $this->elementDir(), array(), array( $installfile_elemet->data() ), $this->installMethod() ) ) {
					$this->setError( 1, 'Could not copy PHP install file.' );
					if ( $pluginRowWasNotExisting ) {
						if ( $db ) {
							$sqlUpgrader->checkXmlDatabaseDescription( $db, $cleanedElementName, 'drop', null, null );
						}
						$this->deleteTabAndFieldsOfPlugin( $row->id );	// delete tabs and private fields of plugin
						$row->delete();
					}
					$this->cleanupInstall( null, $this->elementDir() );	// try removing directory and content just created successfully
					$_PLUGINS->_setLoading( $row, $savePreviousPluginId );
					return false;
				}
			}
			$this->hasInstallFile( true );
			$this->installFile( $installfile_elemet->data() );
		}
		// Is there an uninstallfile
		$uninstallfile_elemet					=	$cbInstallXML->getElementByPath( 'uninstallfile' );
		if( $uninstallfile_elemet !== false ) {
			if ( ( ! $InstallIntoDatabaseOnly ) && ( ! file_exists( $this->elementDir() . $uninstallfile_elemet->data() ) ) ) {
				if( ! $this->copyFiles( $this->installDir(), $this->elementDir(), array(), array( $uninstallfile_elemet->data(), $this->installMethod() ) ) ) {
					$this->setError( 1, 'Could not copy PHP uninstall file' );
					if ( $pluginRowWasNotExisting ) {
						if ( $db ) {
							$sqlUpgrader->checkXmlDatabaseDescription( $db, $cleanedElementName, 'drop', null, null );
						}
						$this->deleteTabAndFieldsOfPlugin( $row->id );	// delete tabs and private fields of plugin
						$row->delete();
					}
					$this->cleanupInstall( null, $this->elementDir() );	// try removing directory and content just created successfully
					$_PLUGINS->_setLoading( $row, $savePreviousPluginId );
					return false;
				}
			}
		}

		self::cleanOpcodeCaches();

		if ( $this->hasInstallFile() ) {
			if ( is_file( $this->elementDir() . $this->installFile() ) ) {
				/** @noinspection PhpIncludeInspection */
				require_once( $this->elementDir() . $this->installFile() );
				$ret							=	call_user_func_array( 'plug_' . $cleanedMainFileName . '_install', array() );
				if ( $ret != '' ) {
					$this->setError( 0, $desc . $ret );
				}
			}
		}

		if ( ( $db !== false ) && ( count( $db->children() ) > 0 ) ) {
			CBDatabaseChecker::renderDatabaseResults( $sqlUpgrader, true, false, $success, array(), array(), $this->elementName(), 1, false );
		}
		$_PLUGINS->_setLoading( $row, $savePreviousPluginId );
		return true;
	}

	/**
	 * Installs a tab into database, finding already existing one if needed.
	 *
	 * @param  int               $pluginId  Plugin id
	 * @param  SimpleXMLElement  $tab       XML element of Tab
	 * @return int|boolean                  Id of tab or FALSE in case of error (error saved with $this->setError() ).
	 */
	function installTab( $pluginId, $tab )
	{
		global $_CB_database;

		// Check to see if plugin tab already exists in db
		if ( ! $tab->attributes( 'tabid' ) ) {
			if ( $tab->attributes( 'class' ) ) {
				$query		=	"SELECT tabid FROM #__comprofiler_tabs WHERE " /* . "pluginid = " . (int) $pluginid . " AND " */ . "pluginclass = " . $_CB_database->Quote( $tab->attributes('class') );
			} else {
				$query		=	"SELECT tabid FROM #__comprofiler_tabs WHERE pluginid = " . (int) $pluginId . " AND pluginclass = ''";
			}
			$_CB_database->setQuery( $query );
			$tabId			=	$_CB_database->loadResult();
		} else {
			$tabId			=	$tab->attributes( 'tabid' );
		}

		if ( $tab->attributes( 'type' ) == 'existingSytemTab' ) {
			if ( $tabId == null ) {
				$this->setError( 1, 'installTab error: existingSystemTab' . ': ' . $tab->attributes( 'class' ) . ' ' . 'not found' . '.' );
				return false;
			}
		} else {
			$row										=	new TabTable();
			if ( $tabId ) {
				$row->load( (int) $tabId );
			}
			if ( ! $row->tabid ) {
				$row->title								=	$tab->attributes('name');
				$row->description						=	trim( $tab->attributes('description') );
				$row->ordering							=	99;
				$row->position							=	$tab->attributes('position');
				$row->displaytype						=	$tab->attributes('displaytype');
				$row->ordering_register					=	$tab->attributes('ordering_register');
				$row->enabled							=	$tab->attributes('enabled');
				$row->viewaccesslevel					=	1;

				$viewAccessLevelName					=	$tab->attributes( 'viewaccesslevel' );

				if ( $viewAccessLevelName ) {
					$accessLevels						=	Application::CmsPermissions()->getAllViewAccessLevels();
					$viewAccessLevelId					=	array_search( $viewAccessLevelName, $accessLevels );
					if ( $viewAccessLevelId !== false ) {
						$row->viewaccesslevel			=	$viewAccessLevelId;
					}
				}
			}
			$row->width									=	$tab->attributes('width');
			$row->pluginclass							=	$tab->attributes('class');
			$row->pluginid								=	$pluginId;
			$row->fields								=	$tab->attributes('fields');
			$row->sys									=	$tab->attributes('sys');

			if ( ! $row->store() ) {
				$this->setError( 1, 'SQL error' .': ' . $row->getError() );
				return false;
			}
			$tabId										=	(int) $row->tabid;
		}
		return $tabId;
	}

	/**
	 * installs a field for plugin
	 *
	 * @param  int               $pluginId  Id of the plugin creating the field
	 * @param  int               $tabId     Id of tab into which to install the field
	 * @param  SimpleXMLElement  $field     XML element of the field to install
	 * @return int|false                   Field id or False on error
	 */
	function installField( $pluginId, $tabId, $field )
	{
		global $_CB_database, $_PLUGINS;

		// Check to see if plugin tab already exists in db
		if ( ! $field->attributes( 'fieldid' ) ) {
			$_CB_database->setQuery( "SELECT fieldid FROM #__comprofiler_fields WHERE name = '".$field->attributes('name')."'" );
			$fieldid			=	$_CB_database->loadResult();
		} else {
			$fieldid			=	$field->attributes('fieldid');
		}

		$row					=	new FieldTable();
		if ( $fieldid ) {
			$row->load( (int) $fieldid );
		}
		$row->name				=	$field->attributes('name');
		$row->pluginid			=	$pluginId;
		$row->tabid				=	$tabId;
		$row->type				=	$field->attributes('type');
		$row->calculated		=	(int) $field->attributes('calculated');
		if ( ! $row->fieldid ) {
			$row->title				=	$field->attributes('title');
			$row->description		=	trim( $field->attributes('description') );
			$row->ordering			=	99;
			$row->registration		=	$field->attributes('registration');
			$row->profile			=	$field->attributes('profile');
			$row->edit				=	$field->attributes('edit');
			$row->readonly			=	$field->attributes('readonly');
			$row->searchable		=	$field->attributes('searchable');
			$row->params			=	$field->attributes('params');
		}
		$dbTable				=	$field->getElementByPath( 'database/table' );
		if ( $dbTable !== false ) {
			$table				=	$dbTable->attributes( 'name' );
		} else {
			$table				=	$field->attributes('table');
		}
		if ( $table ) {
			$row->table			=	$table;
		} else {
			$row->table			=	'#__comprofiler';
		}

		// if the field type is unknown, suppose it's a field type of the plugin:
		$fieldTypePluginId		=	$_PLUGINS->getUserFieldPluginId( $row->type );
		if ( ! $fieldTypePluginId ) {
			// and register it so that the XML file for custom type can be found for store:
			$_PLUGINS->registerUserFieldTypes( array( $row->type => 'CBfield_' . $row->type ), $pluginId );
		}

		if (!$row->store()) {
			$this->setError( 1, 'SQL error on field store2' .': ' . $row->getError() );
			return false;
		}

		$fieldid				=	(int) $row->fieldid;

		return $fieldid;
	}

	/**
	 * Installs a field value
	 *
	 * @param  int               $fieldId
	 * @param  SimpleXMLElement  $fieldValue
	 * @return boolean                        True on success, False on failure
	 */
	function installFieldValue( $fieldId, $fieldValue )
	{
		global $_CB_database;

		$row					=	new FieldValueTable();
		$row->fieldid			=	(int) $fieldId;
		$row->fieldtitle		=	$fieldValue->attributes( 'title' );
		$row->ordering			=	$fieldValue->attributes( 'ordering' );
		$row->sys				=	$fieldValue->attributes( 'sys' );

		$_CB_database->setQuery("SELECT fieldvalueid FROM #__comprofiler_field_values WHERE fieldid = ". (int) $fieldId . " AND fieldtitle = '".$row->fieldtitle."'");
		$fieldValueId			=	$_CB_database->loadResult();

		if ( $fieldValueId ) {
			$row->fieldvalueid	=	$fieldValueId;
		}

		try {
			$row->store();
		}
		catch ( \RuntimeException $e ) {
			$this->setError( 1, 'SQL error on field store' .': ' . htmlspecialchars( $e->getMessage() ) );
			return false;
		}

		return true;
	}

	/**
	 * Installs field type (for now just updates pluginid of existing entries)
	 *
	 * @param int     $pluginId
	 * @param string  $fieldType
	 */
	function installFieldType( $pluginId, $fieldType )
	{
		global $_CB_database;

		// Update already existing fields of this type in db
		$_CB_database->setQuery( "UPDATE #__comprofiler_fields SET pluginid = " . ( $pluginId === null ? "NULL" : (int) $pluginId ) . " WHERE type = '" . $_CB_database->getEscaped( $fieldType ) . "'" );
		$_CB_database->query();
	}

	/**
	 * Gets XML of plugin
	 *
	 * @param  int               $pluginId
	 * @return SimpleXMLElement|string      XML element, or string if error
	 */
	function getXml( $pluginId )
	{
		global $_CB_framework;

		$row			=	new PluginTable();

		if ( ! $row->load( (int) $pluginId ) ) {
			return 'Invalid plugin id';
		}

		if ( trim( $row->folder ) == '' ) {
			return 'Folder field empty';
		} elseif ( cbStartOfStringMatch( $row->folder, '/' ) ) {
			$this->elementDir( $_CB_framework->getCfg('absolute_path') . $row->folder . '/' );
		} else {
			$this->elementDir( $_CB_framework->getCfg('absolute_path') . '/components/com_comprofiler/plugin/' . $row->type . '/' . $row->folder . '/' );
		}
		$this->installFilename( $this->elementDir() . $row->element . '.xml' );

		if ( ! ( file_exists( $this->installFilename() ) && is_readable( $this->installFilename() ) ) ) {
			return $row->name .' '. "has no readable xml file " . $this->i_installfilename;
		}

		return new SimpleXMLElement( trim( file_get_contents( $this->installFilename() ) ) );
	}

	/**
	 * Checks the plugin's database tables and upgrades if needed
	 * Backend-use only.
	 *
	 * Sets for $this->getErrors() $this->checkdbErrors and for $this->getLogs() $this->checkdbLogs
	 *
	 * @param  int                    $pluginId
	 * @param  boolean                $upgrade          FALSE: only check table, TRUE: upgrades table (depending on $dryRun)
	 * @param  boolean                $dryRun           TRUE: doesn't do the modifying queries, but lists them, FALSE: does the job
	 * @param  boolean|null           $strictlyColumns  FALSE: allow for other columns, TRUE: doesn't allow for other columns
	 * @param  boolean|string|null    $strictlyEngine   FALSE: engine unchanged, TRUE: force engine change to type, updatewithtable: updates to match table, NULL: checks for attribute 'strict' in table
	 * @return boolean|string         True: success: see logs, False: error, see errors, string: error
	 */
	function checkDatabase( $pluginId, $upgrade = false, $dryRun = false, $strictlyColumns = null, $strictlyEngine = null )
	{
		$success									=	null;

		$cbInstallXML								=	$this->getXml( $pluginId );
		if ( is_object( $cbInstallXML ) ) {
			$db										=	$cbInstallXML->getElementByPath( 'database' );
			if ( $db !== false ) {
				// get the element name:
				$e									=	$cbInstallXML->getElementByPath( 'name' );
				$this->elementName( $e->data() );
				$cleanedElementName					=	strtolower(str_replace(array(" ","."),array("","_"),$this->elementName()));

				$sqlUpgrader						=	new DatabaseUpgrade( null, false );
				$sqlUpgrader->setDryRun( $dryRun );
				$success							=	$sqlUpgrader->checkXmlDatabaseDescription( $db, $cleanedElementName, $upgrade, $strictlyColumns, $strictlyEngine );
				/*
				var_dump( $success );
				echo "<br>\nERRORS: " . $sqlUpgrader->getErrors( "<br /><br />\n\n", "<br />\n" );
				echo "<br>\nLOGS: " . $sqlUpgrader->getLogs( "<br /><br />\n\n", "<br />\n" );
				exit;
				*/
				$this->checkDatabaseErrors			=	$sqlUpgrader->getErrors( false );
				$this->checkDatabaseLogs			=	$sqlUpgrader->getLogs( false );
			}
		} else {
			$success								=	$cbInstallXML;
		}
		return $success;
	}

	/**
	 * Gets errors from the database installation/upgrade with \CBLib\Database\DatabaseUpgrade class
	 *
	 * @return array
	 */
	function getErrors( )
	{
		return $this->checkDatabaseErrors;
	}

	/**
	 * Gets logs from the database installation/upgrade with \CBLib\Database\DatabaseUpgrade class
	 *
	 * @return array
	 */
	function getLogs( )
	{
		return $this->checkDatabaseLogs;
	}

	/**
	 * Checks that plugin is properly installed and sets, if returned true:
	 * $this->i_elementdir   To the directory of the plugin (with final / )
	 * $this->i_xmldocument  To a SimpleXMLElement of the XML file
	 *
	 * @param  int     $pluginId
	 * @param  string  $option
	 * @param  string  $action
	 * @return boolean
	 */
	function checkPluginGetXml( $pluginId, $option, $action = 'Uninstall' )
	{
		global $_CB_framework;

		$row			=	new PluginTable();

		try {
			$loadResult	=	$row->load( (int) $pluginId );
		}
		catch ( \RuntimeException $e ) {
			self::renderInstallMessage( $e->getMessage(), $action . ' -  error' ,
				$this->returnTo( $option, 'showPlugins') );
			return false;
		}

		if ( ! $loadResult ) {
			self::renderInstallMessage(
				'Invalid plugin id',
				$action . ' -  error',
				$this->returnTo( $option, 'showPlugins')
			);
			return false;
		}

		if ( trim( $row->folder ) == '' ) {
			self::renderInstallMessage( 'Folder field empty, cannot remove files', $action . ' -  error',
				$this->returnTo( $option, 'showPlugins') );
			return false;
		}

		if ( $row->iscore ) {
			self::renderInstallMessage( $row->name .' '. "is a core element, and cannot be uninstalled.<br />You need to unpublish it if you don't want to use it" ,
				'Uninstall -  error', $this->returnTo( $option, 'showPlugins') );
			return false;
		}

		if ( trim( $row->folder ) == '' ) {
			return 'Folder field empty';
		} elseif ( cbStartOfStringMatch( $row->folder, '/' ) ) {
			$this->elementDir( $_CB_framework->getCfg('absolute_path') . $row->folder . '/' );
		} else {
			$this->elementDir( $_CB_framework->getCfg('absolute_path') . '/components/com_comprofiler/plugin/' . $row->type . '/' . $row->folder . '/' );
		}
		$this->installFilename( $this->elementDir() . $row->element . '.xml' );

		if ( ! ( file_exists( $this->i_installfilename ) && is_readable( $this->i_installfilename ) ) ) {
			self::renderInstallMessage( $row->name .' '. "has no readable xml file " . $this->i_installfilename . ", and might not be uninstalled completely." ,
				$action . ' -  warning', $this->returnTo( $option, 'showPlugins') );
		}

		// see if there is an xml install file, must be same name as element
		if ( file_exists( $this->i_installfilename ) && is_readable( $this->i_installfilename ) ) {
			$this->i_xmldocument	=	new SimpleXMLElement( trim( file_get_contents( $this->i_installfilename ) ) );
		} else {
			$this->i_xmldocument	=	null;
		}
		return true;
	}

	/**
	 * Plugin un-installer with best effort depending on what it finds.
	 *
	 * @param  int      $pluginId  Plugin id to uninstall
	 * @param  string   $option    Option request of component
	 * @return boolean             Success
	 */
	function uninstall( $pluginId, $option )
	{
		global $_CB_framework, $_CB_database;

		$db						=	false;
		$success				=	false;

		if ( ! $this->checkPluginGetXml( $pluginId, $option ) ) {
			return false;
		}

		if ( ( $this->i_xmldocument !== null ) && count( $this->i_xmldocument->children() ) > 0 ) {
			$cbInstallXML	=	$this->i_xmldocument;

			// get the element name:
			$e = $cbInstallXML->getElementByPath( 'name' );
			$this->elementName( $e->data() );
			// $cleanedElementName = strtolower(str_replace(array(" ","."),array("","_"),$this->elementName()));

			// get the files element
			$files_element = $cbInstallXML->getElementByPath( 'files' );
			if ( $files_element !== false ) {

				if ( count( $files_element->children() ) ) {
					foreach ( $files_element->children() as $file) {
						if ($file->attributes( "plugin" )) {
							$this->elementSpecial( $file->attributes( "plugin" ) );
							break;
						}
					}
				}

				$cleanedMainFileName	=	strtolower(str_replace(array(" ","."),array("","_"),$this->elementSpecial()));

				// Is there an uninstallfile
				$uninstallfile_elemet = $cbInstallXML->getElementByPath( 'uninstallfile' );
				if ( $uninstallfile_elemet !== false ) {
					if (is_file( $this->i_elementdir . $uninstallfile_elemet->data()))
					{
						global /** @noinspection PhpUnusedLocalVariableInspection */
						$_PLUGINS;		// needed for the require_once below !

						/** @noinspection PhpIncludeInspection */
						require_once( $this->i_elementdir . $uninstallfile_elemet->data());

						$ret = call_user_func_array("plug_".$cleanedMainFileName."_uninstall", array());

						if ($ret != '') {
							$this->setError( 0, $ret );
						}
					}
				}

				$adminFS					=	cbAdminFileSystem::getInstance();
				$installFileName			=	basename( $this->i_installfilename );

				$this->deleteFiles( $files_element, $adminFS, $installFileName );

				// Are there any CBLib libraries ?
				$libraries_element				=	$cbInstallXML->getElementByPath( 'libraries' );
				if ( $libraries_element !== false ) {
					foreach( $libraries_element->children() as $library ) {
						if ( $library->getName() != 'library' ) {
							continue;
						}

						// Delete files from library package:
						$savePackage			=	$this->i_xmldocument;
						$subFolder				=	$library->attributes( 'name' );
						$saveElement			=	$this->elementDir( $_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/libraries/' . ( $subFolder ? $subFolder . '/' : null ) );
						$this->i_xmldocument	=	$library;

						$this->deleteFiles( $library->getElementByPath( 'files' ), $adminFS, null );

						$this->i_xmldocument				=	$savePackage;
						$this->elementDir( $saveElement );
					}
				}

				// Are there any SQL queries??
				$query_element = $cbInstallXML->getElementByPath( 'uninstall/queries' );
				if ( $query_element !== false ) {
					foreach ( $query_element->children() as $query )
					{
						$_CB_database->setQuery( trim( $query->data() ) );
						if ( ! $_CB_database->query() )
						{
							$this->setError( 1, "SQL Error " . $_CB_database->getErrorMsg() );
							return false;
						}
					}
				}

				// Are there any Database statements ??
				$db										=	$cbInstallXML->getElementByPath( 'database' );
				if ( ( $db !== false ) && ( count( $db->children() ) > 0 ) ) {
					$sqlUpgrader						=	new DatabaseUpgrade( null, false );
//$sqlUpgrader->setDryRun( true );
					$success							=	$sqlUpgrader->checkXmlDatabaseDescription( $db, $cleanedMainFileName, 'drop', null, null );
					/*
					var_dump( $success );
					echo "<br>\nERRORS: " . $sqlUpgrader->getErrors( "<br /><br />\n\n", "<br />\n" );
					echo "<br>\nLOGS: " . $sqlUpgrader->getLogs( "<br /><br />\n\n", "<br />\n" );
					exit;
					*/
					if ( ! $success ) {
						$this->setError( 1, "Plugin database XML SQL Error " . 	$sqlUpgrader->getErrors() );
						return false;
					}
				}

				// Delete tabs and private fields of plugin:
				$this->deleteTabAndFieldsOfPlugin( $pluginId );

				// remove XML file from front
				$xmlRemoveResult	=	$adminFS->unlink(  _cbPathName( $this->i_installfilename, false ) );
				$filesRemoveResult	=	true;

				/*					// define folders that should not be removed
								$sysFolders = array(
								'content',
								'search'
								);
								if ( ! in_array( $row->folder, $sysFolders ) ) {
				*/						// delete the non-system folders if empty
				if ( count( cbReadDirectory( $this->i_elementdir ) ) < 1 ) {
					$filesRemoveResult	=	$adminFS->deldir( $this->i_elementdir );
				}
				/*					}
				*/
				if ( ! $xmlRemoveResult ) {
					self::renderInstallMessage( 'Could not delete XML file: ' . _cbPathName( $this->i_installfilename, false ) . ' due to permission error. Please remove manually.', 'Uninstall -  warning',
						$this->returnTo( $option, 'showPlugins') );
				}
				if ( ! $filesRemoveResult ) {
					self::renderInstallMessage( 'Could not delete directory: ' . $this->i_elementdir . ' due to permission error. Please remove manually.', 'Uninstall -  warning',
						$this->returnTo( $option, 'showPlugins') );
				}
			}
		}

		$_CB_database->setQuery( "DELETE FROM #__comprofiler_plugin WHERE id = " . (int) $pluginId );
		if (!$_CB_database->query()) {
			$msg = $_CB_database->getErrorMsg();
			self::renderInstallMessage( 'Cannot delete plugin database entry due to error: ' . $msg, 'Uninstall -  error',
				$this->returnTo( $option, 'showPlugins') );
			return false;
		}
		if ( ( $this->i_xmldocument !== null ) && ( $db !== false ) && ( count( $db->children() ) > 0 ) ) {
			CBDatabaseChecker::renderDatabaseResults( $sqlUpgrader, true, false, $success, array(), array(), $this->elementName(), 1, false );
		}
		return true;
	}

	/**
	 * Deletes <files>
	 *
	 * @param  SimpleXMLElement   $files_element
	 * @param  cbAdminFileSystem  $adminFS
	 * @param  string|null        $installFileName
	 * @return void
	 */
	protected function deleteFiles( SimpleXMLElement $files_element, cbAdminFileSystem $adminFS, $installFileName )
	{
		foreach ( $files_element->children() as $file ) {
			// delete the files
			$filename				=	$file->data();
			if ( $adminFS->file_exists( $this->i_elementdir . $filename ) ) {
				$parts				=	pathinfo( $filename );
				$subpath			=	$parts['dirname'];
				if ( $subpath <> '' && $subpath <> '.' && $subpath <> '..' ) {
					$adminFS->deldir( _cbPathName( $this->i_elementdir . $subpath . '/' ) );
				} else {
					if ( $file->getName() == 'foldername' ) {
						$adminFS->deldir( _cbPathName( $this->i_elementdir . $filename . '/' ) );
					} elseif ( $installFileName != $filename ) {
						$adminFS->unlink( _cbPathName( $this->i_elementdir . $filename, false ) );
					}
				}
			}
		}
	}

	/**
	 * Deletes tabs and private fields of plugin id
	 *
	 * @param int $id   id of plugin
	 */
	function deleteTabAndFieldsOfPlugin( $id )
	{
		global $_CB_database;

		//Find all tabs related to this plugin
		$_CB_database->setQuery( "SELECT `tabid`, `fields` FROM #__comprofiler_tabs WHERE pluginid=" . (int) $id );
		$tabs				=	$_CB_database->loadObjectList();
		if ( count( $tabs ) > 0 ) {
			$rowTab			=	new TabTable();
			foreach( $tabs AS $tab ) {
				//Find all fields related to the tab
				$_CB_database->setQuery( "SELECT `fieldid`, `name` FROM #__comprofiler_fields WHERE `tabid`=" . (int) $tab->tabid . " AND `pluginid`=" . (int) $id );
				$fields		=	$_CB_database->loadObjectList();
				$rowField	=	new FieldTable();

				//Delete fields and fieldValues, but not data content itself in the comprofilier table so they stay on reinstall
				if ( count( $fields ) > 0 ) {
					//delete each field related to a tab and all field value related to a field, but not the content
					foreach( $fields AS $field ) {
						//Now delete the field itself without deleting the user data, preserving it for reinstall
						//$rowField->deleteColumn('#__comprofiler',$field->name);	// this would delete the user data
						$rowField->delete( $field->fieldid );
					}
				}

				if( $tab->fields ) {
					$_CB_database->setQuery( "SELECT COUNT(*) FROM #__comprofiler_fields WHERE tabid=" . (int) $tab->tabid );
					$fieldsCount	=	$_CB_database->loadResult();
					if( $fieldsCount > 0 ) {
						$_CB_database->setQuery( "UPDATE #__comprofiler_tabs SET `pluginclass`=null, `pluginid`=null WHERE `tabid`=" . (int) $tab->tabid );
						$_CB_database->query();
					} else {
						//delete each tab
						$rowTab->delete( $tab->tabid );
					}
				} else {
					//delete each tab
					$rowTab->delete( $tab->tabid );
				}
			}
		}

		//Find all fields related to this plugin which are in other tabs, are calculated and delete them as they are of no use anymore:
		$_CB_database->setQuery( "SELECT `fieldid`, `name` FROM #__comprofiler_fields WHERE `calculated`=1 AND `sys`=0 AND `pluginid`=" . (int) $id );
		$fields		=	$_CB_database->loadObjectList();

		$rowField	=	new FieldTable();
		if ( count( $fields ) > 0 ) {
			foreach( $fields AS $field ) {
				//Now delete the field itself:
				$rowField->delete( $field->fieldid );
			}
		}
		//Find all fields related to this plugin and set to NULL the now uninstalled plugin.
		$_CB_database->setQuery( "SELECT COUNT(*) FROM #__comprofiler_fields WHERE pluginid=" . (int) $id );
		$fieldsNumber		=	$_CB_database->loadResult();
		if ( $fieldsNumber > 0 ) {
			$_CB_database->setQuery( "UPDATE #__comprofiler_fields SET pluginid = NULL WHERE pluginid=" . (int) $id );
			$_CB_database->query();
		}
	}
}
