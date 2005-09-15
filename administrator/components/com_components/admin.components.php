<?php
/**
 * @version $Id: admin.components.php 137 2005-09-12 10:21:17Z eddieajau $
 * @package Mambo
 * @subpackage Components
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software and parts of it may contain or be derived from the
 * GNU General Public License or other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

// ensure user has access to this function
if (!$acl->acl_check( 'com_components', 'manage', 'users', $GLOBALS['my']->usertype )) {
	mosRedirect( 'index2.php', $_LANG->_('NOT_AUTH') );
}

mosFS::load( '@admin_html' );
mosFS::load( '@class' );

/**
 * @package Languages
 * @subpackage Languages
 */
class componentTasks extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function componentTasks() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'view' );

		// set task level access control
		$this->setAccessControl( 'com_components', 'manage' );

		$this->registerTask( 'refreshFiles', 'editXML' );
		$this->registerTask( 'addUninstallQuery', 'editXML' );
		$this->registerTask( 'refreshMenus', 'editXML' );
		$this->registerTask( 'addTable', 'editXML' );
		$this->registerTask( 'refreshTables', 'editXML' );

		$this->registerTask( 'installUpload', 'install' );
		$this->registerTask( 'installFromDir', 'install' );
	}

	/**
	 * Options for creating a new component
	 */
	function createOptions() {
		componentScreens::createOptions();
	}

	/**
	 * Create a new component
	 */
	function create() {
		global $_LANG, $mainframe;

		$name		= mosGetParam( $_POST, 'comname', '' );
		$client_id	= mosGetParam( $_POST, 'client_id', 0 );
		$meta		= mosGetParam( $_POST, 'meta', array() );
		$options	= mosGetParam( $_POST, 'options', array() );

		if (empty( $name )) {
			$this->setRedirect( 'index2.php?option=com_components&task=createOptions', $_LANG->_( 'Please enter a name' ) );
			return false;
		}

		$baseName = preg_replace( '#[^_A-Za-z0-9]#', '', $name );

		mosFS::load( 'administrator/components/com_components/components.builder.php' );

		$creator = new componentCreator( $client_id );
		$isErr = !$creator->make( $baseName, $meta, $options );

		echo $creator->error();

		//$this->setRedirect( 'index2.php?option=com_components&task=createOptions', $isErr ? $$creator->error() : 'Done' );
		print_r($_POST);
		return;

		$basePath = $mainframe->getBasePath( $client_id ) . 'components/com_' . $baseName;
		$basePath = mosFS::getNativePath( $basePath );
		$baseTmplPath = mosFS::getNativePath( $basePath . 'tmpl' );
		$baseHelpPath = mosFS::getNativePath( $basePath . 'help' );

		// create the base path
		if (!mosFS::autocreatePath( $basePath )) {
			$this->setRedirect( 'index2.php?option=com_components&task=createOptions', $_LANG->_( 'Failed to create base folder' ) );
			return false;
		}
		// create the tmpl path
		if (!mosFS::autocreatePath( $baseTmplPath )) {
			$this->setRedirect( 'index2.php?option=com_components&task=createOptions', $_LANG->_( 'Failed to create tmpl folder' ) );
			return false;
		}

		// create the help path
		if (!mosFS::autocreatePath( $baseHelpPath )) {
			$this->setRedirect( 'index2.php?option=com_components&task=createOptions', $_LANG->_( 'Failed to create hrlp folder' ) );
			return false;
		}

		// create the template object
		$tmpl =& componentCreator::createTemplate( array() );
		$tmpl->readTemplatesFromInput( 'php.html' );
		$tmpl->readTemplatesFromInput( 'html.html' );
		$tmpl->readTemplatesFromInput( 'xml.html' );
		$tmpl->addGlobalVars( $meta );
		$tmpl->addVars( 'options', $options );

	// ---- PHP FILES ----

		// blank index file
		$file = $basePath . 'index.html';
		$buffer = componentCreator::htmlIndex();
		mosFS::write( $file, $buffer );

		// main php file
		$mainPrefix = $client_id == 1 ? 'admin.' : '';

		$file = $basePath . $mainPrefix . $baseName . '.php';
		$buffer = componentCreator::phpMain( $tmpl );
		mosFS::write( $file, $buffer );

		// main html.php file
		$file = $basePath . $mainPrefix . $baseName . '.html.php';
		$buffer = componentCreator::phpMainHtml( $tmpl );
		mosFS::write( $file, $buffer );

		// toolbar php file
		$file = $basePath . 'toolbar.' . $baseName . '.php';
		$buffer = componentCreator::phpToolbar( $tmpl );
		mosFS::write( $file, $buffer );

		// class php file
		$file = $basePath . $baseName . '.class.php';
		$buffer = componentCreator::phpClass( $tmpl );
		mosFS::write( $file, $buffer );

	// ---- PHP HTML TEMPLATE FILES ----

		// blank index file
		$file = $baseTmplPath . 'index.html';
		$buffer = componentCreator::htmlIndex();
		mosFS::write( $file, $buffer );

		// class php file
		$file = $baseTmplPath . 'tree.html';
		$buffer = componentCreator::htmlTree( $tmpl );
		mosFS::write( $file, $buffer );

		// class php file
		$file = $baseTmplPath . 'relatedLinks.html';
		$buffer = componentCreator::htmlRelated( $tmpl );
		mosFS::write( $file, $buffer );

		// class php file
		$file = $baseTmplPath . 'view.html';
		$buffer = componentCreator::htmlView( $tmpl );
		mosFS::write( $file, $buffer );

		// class php file
		$file = $baseTmplPath . 'edit.html';
		$buffer = componentCreator::htmlEdit( $tmpl );
		mosFS::write( $file, $buffer );

	// ---- HELP FILES ----

		// blank index file
		$file = $baseHelpPath . 'index.html';
		$buffer = componentCreator::htmlIndex();
		mosFS::write( $file, $buffer );

	// ---- XML FILES ----

		// mosinstall
		$file = $basePath . $baseName . '.xml';
		$buffer = componentCreator::xmlMain( $tmpl );
		mosFS::write( $file, $buffer );

	// ---- DATABASE ----
		$query = "
			CREATE TABLE `mos_$baseName` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `title` varchar(255) NOT NULL default '',
			  `created_date` datetime NOT NULL default '0000-00-00 00:00:00',
			  `author_id` int(10) unsigned NOT NULL default '0',
			  `modified_id` int(10) unsigned NOT NULL default '0',
			  `checked_out` int(10) unsigned NOT NULL default '0',
			  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
			  `published` int(2) NOT NULL default '0',
			  `ordering` int(11) NOT NULL default '0',
			  PRIMARY KEY  (`id`)
			) TYPE=MyISAM";

	// ---- Menu Item ----

	}

	/**
	 * Generic install page
	 */
	function installOptions() {
		global $mosConfig_absolute_path;

		$vars = array(
			'siteWritable' => intval( is_writable( $mosConfig_absolute_path . '/components' ) ),
			'adminWritable' => intval( is_writable( $mosConfig_absolute_path . '/administrator/components' ) ),
			'mediaWritable' => intval( is_writable( $mosConfig_absolute_path . '/media' ) ),
		);
		componentScreens::installOptions( $vars );
	}

	/**
	 * Installs template
	 */
	function install() {
		$userfile = mosGetParam( $_FILES, 'userfile', null );

		$installer = mosComponentFactory::createInstaller();
		if ($this->getTask() == 'installUpload') {
			if (!$installer->uploadArchive( $userfile )) {
				$msg = $installer->error();
			}
			if (!$installer->extractArchive()) {
				$msg = $installer->error();
			}
		} else {
			$installer->installDir( $userfile );
		}
		if (!$installer->install()) {
			$installer->cleanupInstall();
			$msg = $installer->error();
		}
		$installer->cleanupInstall();

		componentScreens::installDone( $installer );
	}

	/**
	 * Options for packaging
	 */
	function packageOptions() {
		$cid = mosGetParam( $_REQUEST, 'cid', array(0) );

		$row = new mosComponent( $GLOBALS['database'] );
		if (!$row->load( intval( $cid[0] ) )) {
			$this->setRedirect( 'index2.php?option=com_components', $_LANG->_( 'errorElementNotFound' ) );
			return false;
		}

		componentScreens::packageOptions( $row );
	}

	/**
	 * Build the package
	 */
	function package() {
		global $_LANG, $mainframe;

		$redirect = 'index2.php?option=com_components';

		$compress = mosGetParam( $_POST, 'compress', 'gz' );
		$element = mosGetParam( $_POST, 'element', '' );
		$fileName = mosGetParam( $_POST, 'filename', $element );

		$cid = mosGetParam( $_REQUEST, 'cid', array(0) );
		$row = new mosComponent( $GLOBALS['database'] );
		if (!$row->load( intval( $cid[0] ) )) {
			$this->setRedirect( $redirect, $_LANG->_( 'errorElementNotFound' ) );
			return false;
		}

		mosFS::load( 'includes/mambo.files.xml.php' );

		$xmlFile = $mainframe->getPath( 'com_xml', $row->option );

		if (!mosXMLFS::read( $xmlFile, 'component', $vars )) {
			$this->setRedirect( $redirect, $_LANG->_( 'Failed to open XML file' ) );
			return false;
		}

		$archiveName = mosFS::getNativePath( dirname( __FILE__ ) . '/files/' . $fileName, false );

		$files = mosGetParam( $vars, 'siteFiles', array() );
		$basePath = mosComponent::getBasePath( 0 ) . $row->option . DIRECTORY_SEPARATOR;

		foreach ($files as $k => $v) {
			$files[$k] = $basePath . $files[$k]['file'];
		}
		$archive = mosFS::archive( $archiveName, $files, $compress, '', $basePath, true, false );

		// special handling for admin files
		$basePath = mosComponent::getBasePath( 1 ) . $row->option . DIRECTORY_SEPARATOR;

		$files = mosGetParam( $vars, 'adminFiles', array() );

		foreach ($files as $k => $v) {
			$files[$k] = mosFS::getNativePath( $basePath . $files[$k]['file'], false, true );
		}

		if (!$archive->addModify( $files, 'admin', $basePath )) {
			$msg = $_LANG->_( 'Failed to add administrator files to package' );
			$this->setRedirect( $redirect . '&task=listFiles', $msg );
		}

		$msg = $_LANG->_( 'Package Made' );
		$this->setRedirect( $redirect . '&task=listFiles', $msg );
	}

	/**
	 * List files in /files directory
	 */
	function listFiles() {
		$path = mosFS::getNativePath( dirname( __FILE__ ) . '/files' );

		$files = mosFS::listFiles( $path, '\.(tar|gz)$' );
		foreach ($files as $i=>$file) {
			$files[$i] = array(
				'file' 	=> $file,
				'fsize' => number_format( filesize( $path . $file ) ),
				'mtime' => date ( 'd-m-Y H:i:s', filemtime( $path . $file ) ),
				'perms' => mosFS::getPermissions( $path . $file )
			);
		}
		componentScreens::listFiles( $files );
	}

	/**
	 * Delete a set of language files
	 */
	function deleteFile() {
		global $_LANG;

		$cid = mosGetParam( $_POST, 'cid', array() );
		$path = mosFS::getNativePath( dirname( __FILE__ ) . '/files' );

		$redirect = 'index2.php?option=com_components&task=listFiles';
		if (count( $cid ) < 1) {
			$msg = $_LANG->_( 'errorNoFile' );
			mosRedirect( $redirect, $msg );
		}

		foreach ($cid as $file) {
			mosFS::deleteFile( $path . $file );
		}

		$msg = $_LANG->_( 'Deleted' );
		$this->setRedirect( $redirect, $msg );
	}

	/**
	 * Edit XML Setup File
	 */
	function editXML() {
		global $_LANG, $mainframe, $database;

		mosFS::load( '/includes/mambo.files.xml.php' );

		$xmlVars = mosGetParam( $_POST, 'vars', array() );
		varsStripSlashes( $xmlVars );
		$cid = mosGetParam( $_POST, 'cid', array(0) );

		$row = new mosComponent( $GLOBALS['database'] );
		if (!$row->load( intval( $cid[0] ) )) {
			$this->setRedirect( 'index2.php?option=com_components', $_LANG->_( 'errorElementNotFound' ) );
			return false;
		}

		switch ($this->getTask()) {
			case 'refreshMenus':
				$query = "SELECT id, name, admin_menu_link, admin_menu_alt, admin_menu_img"
				. "\n FROM #__components"
				. "\n WHERE id = $row->id"
				;
				$database->setQuery( $query );
				$temp = $database->loadAssocList();
				// re-index the id field
				foreach ($temp as $i => $v) {
					$temp[$i]['id'] = $i;
				}
				$xmlVars['adminMenus'] = $temp;

				$query = "SELECT id, name, admin_menu_link, admin_menu_alt, admin_menu_img"
				. "\n FROM #__components"
				. "\n WHERE parent = $row->id"
				;
				$database->setQuery( $query );
				$temp = $database->loadAssocList();
				// re-index the id field
				foreach ($temp as $i => $v) {
					$temp[$i]['id'] = $i;
				}
				$xmlVars['adminSubMenus'] = $temp;
				break;

			case 'addTable':
				$table = mosGetParam( $_POST, 'new_table', '' );
				if ($table) {
					$tc = $database->getTableCreate( array( $table ) );
					$xmlVars['installQueries'][] = array(
						'id' 	=> count( @$xmlVars['installQueries'] ),
						'table' => $table,
						'query' => $tc[$table]
					);
				}

				break;

			case 'refreshTables':
				$temp = array();
				foreach ($xmlVars['installQueries'] as $k => $v) {
					$query =& $xmlVars['installQueries'][$k];

					if (isset( $query['id'] )) {
						$tc = $database->getTableCreate( $query['table'] );
						$temp[] = array(
							'id' 	=> count( $temp ),
							'table' => $query['table'],
							'query' => $tc[$table]
						);
					}
				}
				$xmlVars['installQueries'] = $temp;
				break;

			case 'addUninstallQuery':
				$xmlVars['uninstallQueries'] = mosGetParam( $xmlVars, 'uninstallQueries', array() );
				$xmlVars['uninstallQueries'][] = array(
					'id' 	=> count( $xmlVars['uninstallQueries'] ),
					'query' => ''
				);
				break;

			case 'refreshFiles':
				global $mosConfig_absolute_path;

				// component files
				$exclude = mosGetParam( $_POST, 'exclude', '' );
				$_option = mosGetParam( $_POST, '_option', '' );

				// site files
				$dir = mosFS::getNativePath( $mosConfig_absolute_path . '/components/' . $_option, false );
				$files = mosFS::listFiles( $dir, '.', true, true );
				$temp = array();
				foreach ($files as $i => $v) {
					if (empty( $exclude ) || ($exclude && !eregi( $exclude, $v ) )) {
						$temp[] = array( 'file' => str_replace( '\\', '/', str_replace( $dir . DIRECTORY_SEPARATOR, '', $v ) ) );
					}
				}
				$xmlVars['siteFiles'] = $temp;

				// site files
				$dir = mosFS::getNativePath( $mosConfig_absolute_path . '/administrator/components/' . $_option, false );
				$files = mosFS::listFiles( $dir, '.', true, true );
				$temp = array();
				foreach ($files as $i => $v) {
					if (empty( $exclude ) || ($exclude && !eregi( $exclude, $v ) )) {
						$temp[] = array( 'file' => str_replace( '\\', '/', str_replace( $dir . DIRECTORY_SEPARATOR, '', $v ) ) );
					}
				}
				$xmlVars['adminFiles'] = $temp;

				break;

			default:
				$file = $mainframe->getPath( 'com_xml', $row->option );
				$file = mosFS::getNativePath( $file, false );

				if (file_exists( $file )) {
					mosXMLFS::read( $file, 'component', $xmlVars );
				} else {
					$xmlVars = array();
				}
				break;
		}

		if (count( @$xmlVars['siteFiles'] ) > 0 && !isset( $xmlVars['siteFiles'][0]['file'] )) {
			foreach ($xmlVars['siteFiles'] as $i => $v) {
				$xmlVars['siteFiles'][$i] = array( 'file' => $v );
			}
		}
		if (count( @$xmlVars['adminFiles'] ) > 0 && !isset( $xmlVars['adminFiles'][0]['file'] )) {
			foreach ($xmlVars['adminFiles'] as $i => $v) {
				$xmlVars['adminFiles'][$i] = array( 'file' => $v );
			}
		}

		if (!isset( $xmlVars['params'] )) {
			$xmlVars['params'] = '<params />';
		}
		$lists['tables'] = $database->getTableList();

		componentScreens::editXML( $xmlVars, $row, $lists );
	}

	/**
	 * Saves the xml setup file
	 */
	function saveXML() {
		global $_LANG, $mainframe;

		mosFS::load( '/includes/mambo.files.xml.php' );

		$client		= mosGetParam( $_REQUEST, 'client', '' );
		$client_id	= mosMainFrame::getClientID( $client );
		$element	= mosGetParam( $_POST, 'element', '' );
		$vars		= mosGetParam( $_POST, 'vars', array() );
		$option		= mosGetParam( $_POST, '_option', array() );

		$file = $mainframe->getPath( 'com_xml', $option );

		$msg = ( mosXMLFS::write( 'component', $vars, $file ) ? $_LANG->_( 'File Saved' ) : $_LANG->_( 'Error saving file' ) );

		$this->setRedirect( 'index2.php?option=com_components&client=' . $client, $msg );
	}

	/**
	 * List the templates
	 */
	function view() {
		global $database, $mainframe, $option;
		global $mosConfig_absolute_path, $mosConfig_list_limit;

		mosFS::load( 'includes/mambo.files.xml.php' );

		// form data
		$limit 		= $mainframe->getUserStateFromRequest( 'viewlistlimit', 'limit', $mosConfig_list_limit );
		$limitstart = $mainframe->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );

		$client		= mosGetParam( $_REQUEST, 'client', '' );
		$client 	= mosMainFrame::getClientID( $client );

		mosFS::load( '@pageNavigationAdmin' );

		$query = "SELECT *"
		. "\n FROM #__components"
		. "\n WHERE parent = '0'"
		. "\n AND iscore = '0'"
		. "\n ORDER BY name"
		;
		$database->setQuery( $query );
		$rows = $database->loadObjectList();
		$total 		= count( $rows );
		$pageNav 	= new mosPageNav( $total, $limitstart, $limit );
		$rows 		= array_slice( $rows, $pageNav->limitstart, $pageNav->limit );

		// pull name and description from module xml
		foreach ( $rows as $i => $row ) {
			$xml = ReadComponentXML( $row );
			$rows[$i]->xml_version 	= $xml[0];
			$rows[$i]->xml_date		= $xml[1];
			$rows[$i]->xml_author	= $xml[2];
			$rows[$i]->xml_url		= $xml[3];
			$rows[$i]->xml_email	= $xml[4];
		}

		componentScreens::view( $rows, $pageNav, $client );
	}

	/**
	 * Remove the selected template
	 */
	function remove() {
		global $database, $mainframe;
		global $_LANG;

		$cid 		= mosGetParam( $_REQUEST, 'cid', array(0) );
		$client 	= mosGetParam( $_REQUEST, 'client', '' );
		$client_id 	= $mainframe->getClientID( $client );

		$installer = mosComponentFactory::createInstaller();

		if ($installer->uninstall( $cid[0], $client_id )) {
			$msg = $_LANG->_( 'Success' );
		} else {
			$msg = $installer->error();
		}

		$this->setRedirect( 'index2.php?option=com_components&client=' . $client, $msg );
	}

	function listcomponents() {
		componentScreens::listComponents();
	}
}

$tasker = new componentTasks();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
$tasker->redirect();

function ReadComponentXML( $component ) {
	global $mosConfig_absolute_path;

	// XML library
	mosFS::load( '@domit' );

	$component->name = str_replace( 'com_', '', $component->option );

	$path = $mosConfig_absolute_path .'/administrator/components/'. $component->option .'/'. $component->name .'.xml';

	// xml file for module
	$xmlfile = $path;

	$xml[0]	= '';
	$xml[1] = '';
	$xml[2]	= '';
	$xml[3] = '';
	$xml[4]	= '';
	// check to see if xml file exists
	if ( file_exists( $xmlfile ) ) {
		$xmlDoc = new DOMIT_Lite_Document();
		$xmlDoc->resolveErrors( true );

		if ( $xmlDoc->loadXML( $xmlfile, false, true ) ) {
			$root = &$xmlDoc->documentElement;

			if ( ( $element->getTagName() == 'mosinstall' ) && ( $element->getAttribute( 'type' ) == 'component' ) ) {
				// version
				$element 	= &$root->getElementsByPath( 'version', 1 );
				$version		= ( $element ? trim( $element->getText() ) : '' );
				// date
				$element 	= &$root->getElementsByPath( 'creationDate', 1 );
				$date	 	= ( $element ? trim( $element->getText() ) : '' );
				// author
				$element 	= &$root->getElementsByPath( 'author', 1 );
				$author 	= ( $element ? trim( $element->getText() ) : '' );
				// url
				$element 	= &$root->getElementsByPath( 'authorUrl', 1 );
				$url	 	= ( $element ? trim( $element->getText() ) : '' );
				// email
				$element 	= &$root->getElementsByPath( 'authorEmail', 1 );
				$email	 	= ( $element ? trim( $element->getText() ) : '' );
			}
		}

		$xml[0]	= $version;
		$xml[1] = $date;
		$xml[2]	= $author;
		$xml[3] = $url;
		$xml[4]	= $email;
	}

	return $xml;
}
?>