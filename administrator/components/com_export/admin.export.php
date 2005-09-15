<?php
/**
 * @version $Id: admin.export.php 137 2005-09-12 10:21:17Z eddieajau $
 * @package Mambo
 * @subpackage Export
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

mosFS::load( '@class' );
mosFS::load( '@admin_html' );

/**
 * @package Languages
 * @subpackage Export
 */
class exportTasks extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function exportTasks() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'exportOptions' );

		// set task level access control
		$this->setAccessControl( 'com_checkin', 'manage' );
	}

	/**
	 * Loads formatter classes
	 */
	function _loadFormatters() {
		$path = dirname( __FILE__ ) . '/formatters';
		$files = mosFS::listFiles( $path, '\.php$', false, true );

		$exportFormatters = array();
		foreach ($files as $file) {
			require_once( $file );
		}
		return $exportFormatters;
	}

	/**
	* exportOptions
	*/
	function exportOptions() {
		global $database;

		$lists['tables'] = $database->getTableList();
		$lists['formatters'] = $this->_loadFormatters();

		exportScreens::exportOptions( $lists );
	}
		//$this->setRedirect( 'index2.php?option=com_checkin', $msg );

	/**
	* export
	*/
	function export() {
		global $database, $mainframe;

		$tmpl =& exportScreens::createTemplate();

		$tables = mosGetParam( $_POST, 'tables', array());
		$options = mosGetParam( $_POST, 'options', array());

		if (count( $tables ) < 1) {
			exportScreens::message( 'No tables selected' );
			return false;
		}

		$source = mosGetParam( $options, 'source', '' );
		$format = mosGetParam( $options, 'format', '' );
		$output = mosGetParam( $options, 'output', '' );
		$comment = mosGetParam( $options, 'comment', '' );

		$showVars = false;
		$table_creates = array();
		$table_fields = array();

		$sourceStructure = eregi( 's', $source );
		$sourceData = eregi( 'd', $source );

		// data format
		if ($sourceStructure) {
			// structure
			$table_creates = $database->getTableCreate( $tables );
		}
		$table_fields = $database->getTableFields( $tables );

		if ($sourceData) {
			// data
		}

		$buffer = '';
		// output format
		$formatters = $this->_loadFormatters();

		if (isset( $formatters[$format] )) {
			$formatter = new $formatters[$format];
			$buffer = $formatter->export( $tables, $table_fields, $table_creates, $options );
		}

/*
*/
		if ($format == 'sql') {
			$vars['date'] = date("M j, Y \a\\t H:i");
			$vars['db_version'] = $database->getVersion();
			$vars['php_version'] = phpversion();
			$vars['dbname'] = $GLOBALS['mosConfig_db'];
			$vars['dbhost'] = $GLOBALS['mosConfig_host'];
			$vars['comment'] = str_replace( "\n", "\n# ", $comment );
		} else {
			$vars = null;
		}

		// file format
		switch ($output) {
			case 'screen':
				exportScreens::exportToScreen( $buffer, $vars );
				break;
			case 'text':
			case 'tar':
			case 'gz':
			case 'bz2':
				$buffer = exportScreens::exportToFile( $buffer, $vars, true );

				$basePath = mosFS::getNativePath( dirname( __FILE__ ) . '/files' );
				$file = $basePath . $GLOBALS['mosConfig_db'] . '_' . date( "Ymd_His" ) . '.' .$format;
				//$archiveName = mosFS::getNativePath( dirname( __FILE__ ) . '/files/' . $row->option, false );
				if (mosFS::write( $file, $buffer )) {
					if ($output != 'text') {
						mosFS::archive( $file, $file, $output, '', $basePath, true, true );
					}
					exportScreens::message( 'Done.' );
				} else {
					exportScreens::message( 'Panic. Cannot write file.' );
				}
				break;
			default:
				exportScreens::message( 'Panic. Do not know about file type ' . $file_format );
				break;
		}
	}
	/**
	 *
	 */
	function deleteFiles() {
		$files = mosGetParam( $_POST, 'cid', array());

		if (count( $files ) < 1) {
			exportScreens::message( 'No files selected' );
			return false;
		}

		$basePath = mosFS::getNativePath( dirname( __FILE__ ) . '/files' );

		foreach ($files as $file) {
			if (file_exists( $basePath . $file )) {
				if (unlink( $basePath . $file )) {
					exportScreens::message( array( $file, 'deleted.' ) );
				} else {
					exportScreens::message( array( $file, 'failed to be deleted.' ) );
				}
			} else {
				exportScreens::message( array( $file, 'doesn\'t exist.' ) );
			}
		}
		die;
	}
	/**
	 *
	 */
	function restoreList() {
		global $mosConfig_absolute_path;

		$path = mosFS::getNativePath( $mosConfig_absolute_path . '/administrator/components/com_export/files' );

		$files = mosFS::listFiles( $path, '.' );
		foreach ($files as $i=>$file) {
			$files[$i] = array(
				'file' => $file,
				'fsize' => number_format( filesize( $path . $file ) ),
				'mtime' => date ("d-m-Y H:i:s", filemtime( $path . $file ) ),
				'perms' => mosFS::getPermissions( $path . '/' . $file )
			);
		}
		exportScreens::restoreList( $files );
	}

	/**
	 *
	 */
	function restore() {
		global $_LANG;
		$files = mosGetParam( $_POST, 'cid', array());

		if (count( $files ) < 1) {
			exportScreens::message( 'No files selected' );
			return false;
		}
		if (count( $files ) > 1) {
			exportScreens::message( 'Too many files selected' );
			return false;
		}
		$file = $files[0];

		if (!eregi( '\.sql$', $file )) {
			exportScreens::message( 'Can only support .sql files for now' );
			return false;
		}
		$errors = array();
		populateDatabase( $file, $errors );

		if (count( $errors )) {
			$newErrors = array();
			foreach ($errors as $error) {
				$newErrors[] = stripslashes( $error['msg'] );
			}
			exportScreens::message( $newErrors );
			return false;
		}
		$msg = $_LANG->_( 'Done' ) . ' ' . $file;
		$this->setRedirect( 'index2.php?option=com_export&task=restoreList', $msg );
	}
}

$tasker = new exportTasks();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
$tasker->redirect();

?>