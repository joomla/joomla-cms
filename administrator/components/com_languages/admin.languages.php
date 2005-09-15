<?php
/**
* @version $Id: admin.languages.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Languages
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

mosFS::load( '@admin_html' );
mosFS::load( '@admin_functions' );

/**
 * @package Languages
 * @subpackage Languages
 */
class languageTasks extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function languageTasks() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'listLangs' );

		// set task level access control
		$this->setAccessControl( 'com_languages', 'manage' );

		// additional mappings
		$this->registerTask( 'refreshFiles', 'editXML' );
		$this->registerTask( 'remove', 'deleteFile' );
		$this->registerTask( 'deletePackage', 'deleteFile' );
		$this->registerTask( 'installUpload', 'install' );
		$this->registerTask( 'installFromDir', 'install' );
	}

	/**
	 * Generic install page
	 */
	function installOptions() {
		$tree = findLanguageFiles();
		languageScreens::installOptions( $tree );
	}

	/**
	 * Install from upload file
	 */
	function install() {
		$userfile = mosGetParam( $_FILES, 'userfile', null );

		$installer = mosLanguageFactory::createInstaller();
		if ($this->getTask() == 'installUpload') {
			if (!$installer->uploadArchive( $userfile )) {
				$msg = $installer->error();
				$this->setRedirect( 'index2.php?option=com_languages&task=installOptions', $msg );
			}
			if (!$installer->extractArchive()) {
				$msg = $installer->error();
				$this->setRedirect( 'index2.php?option=com_languages&task=installOptions', $msg );
			}
		} else {
			$installer->installDir( $userfile );
		}
		if (!$installer->install()) {
			$installer->cleanupInstall();
			$msg = $installer->error();
			$this->setRedirect( 'index2.php?option=com_languages&task=installOptions', $msg );
		}
		$installer->cleanupInstall();

		$tree = findLanguageFiles();
		languageScreens::installDone( $tree, $installer->elementName(), $installer->errno(), $installer->error() );
	}

	/**
	 * Options for packaging
	 */
	function packageOptions() {
		$cid = mosGetParam( $_POST, 'cid', array(0) );
		$client = mosGetParam( $_POST, 'client', '' );
		$client = mosMainFrame::getClientID( $client );
		$element = mosGetParam( $_POST, 'element', '' );

		mosFS::load( 'includes/mambo.files.xml.php' );
		$basePath = mosLanguage::getLanguagePath( $client, $element );

		$xmlFile = $basePath . $element . '.xml';

		if (!mosXMLFS::read( $xmlFile, 'language', $vars )) {
			$this->setRedirect( $redirect, $_LANG->_( 'Failed to open XML file' ) );
			return false;
		}

		$tree = findLanguageFiles();
		languageScreens::packageOptions( $tree, $vars, $element, $client );
	}

	/**
	 * Build the package
	 */
	function package() {
		global $_LANG;

		$compress = mosGetParam( $_POST, 'compress', 'gz' );
		$element = mosGetParam( $_POST, 'element', '' );
		$fileName = mosGetParam( $_POST, 'filename', $element );
		$client = mosGetParam( $_POST, 'client', '' );
		$client = mosMainFrame::getClientID( $client );

		$redirect = 'index2.php?option=com_languages&client='. $client;
		if (empty( $element )) {
			$this->setRedirect( $redirect, $_LANG->_( 'Language not supplied' ) );
			return false;
		}

		mosFS::load( 'includes/mambo.files.xml.php' );
		$basePath = mosLanguage::getLanguagePath( $client, $element );

		$xmlFile = $basePath . $element . '.xml';

		if (!mosXMLFS::read( $xmlFile, 'language', $vars )) {
			$this->setRedirect( $redirect, $_LANG->_( 'Failed to open XML file' ) );
			return false;
		}

		$archiveName = mosFS::getNativePath( dirname( __FILE__ ) . '/files/' . $fileName, false );

		$files = mosGetParam( $vars, 'siteFiles', array() );

		foreach ($files as $k => $v) {
			$files[$k] = $basePath . $files[$k]['file'];
		}
		$archive = mosFS::archive( $archiveName, $files, $compress, '', $basePath, true, false );

		$msg = $_LANG->_( 'Package Made' );
		$this->setRedirect( 'index2.php?option=com_languages&task=listFiles', $msg );
	}

	/**
	 * List files in /files directory
	 */
	function listFiles() {
		$path = mosFS::getNativePath( dirname( __FILE__ ) . '/files' );

		$files = mosFS::listFiles( $path, '\.(tar|gz)$' );
		foreach ($files as $i=>$file) {
			$files[$i] = array(
				'file' => $file,
				'fsize' => number_format( filesize( $path . $file ) ),
				'mtime' => date ("d-m-Y H:i:s", filemtime( $path . $file ) ),
				'perms' => mosFS::getPermissions( $path . $file )
			);
		}
		$tree = findLanguageFiles();
		languageScreens::listFiles( $tree, $files );
	}

	/**
	 * Delete a set of language files
	 */
	function deleteFile() {
		global $_LANG;

		$cid = mosGetParam( $_POST, 'cid', array() );
		$element = mosGetParam( $_POST, 'element', '' );
		$client = mosGetParam( $_POST, 'client', '' );
		$client = mosMainFrame::getClientID( $client );

		switch ($this->getTask()) {
			case 'remove':
				// deleting language file
				$path = mosLanguage::getLanguagePath( $client, $element );
				$redirect = 'index2.php?option=com_languages&element=' . $element . '&client=' . $client;
				break;
			case 'deleteFile':
			default:
				// deleting package
				$path = mosFS::getNativePath( dirname( __FILE__ ) . '/files' );
				$redirect = 'index2.php?option=com_languages&task=listFiles';
				break;
		}

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
		mosFS::load( '/includes/mambo.files.xml.php' );

		$element = mosGetParam( $_POST, 'element', 'english' );
		$client = intval( mosGetParam( $_POST, 'client', 0 ) );
		$dir = mosLanguage::getLanguagePath( $client, $element );

		if ($element == '' && $client > 2) {
			languageScreens::message( $tmpl, 'Panic. Need an object id.' );
		}

		$task = $this->getTask();
		switch ($task) {
			case 'refreshFiles':
				// site files
				$xmlFile = mosGetParam( $_POST, 'vars', array() );
				varsStripSlashes( $vars );

				$files = mosFS::listFiles( $dir, "^{$element}(\.[^\.]*)*\.(ini|xml|php)$" );

				$n = count( $files );
				for ($i = 0; $i < $n; $i++) {
					$files[$i] = array(
						'file' => $files[$i]
					);
				}
				$xmlFile['siteFiles'] = $files;
				break;

			default:
				$xml = $dir . $element . '.xml';
				if (file_exists( $xml )) {
					mosXMLFS::read( $xml, 'language', $xmlFile );
				} else {
					$xmlFile = array();
				}
				break;
		}

		languageScreens::editXML( $xmlFile, $element, $client );
	}

	/**
	 * Saves the xml setup file
	 */
	function saveXML() {
		global $_LANG;

		mosFS::load( '/includes/mambo.files.xml.php' );

		$element = mosGetParam( $_POST, 'element', 'english' );
		$client = intval( mosGetParam( $_POST, 'client', 0 ) );
		$vars = mosGetParam( $_POST, 'vars', array() );

		$vars['client'] = mosMainFrame::getClientName( $client_id );

		if ($element == '' && $client > 2) {
			languageScreens::message( 'Panic. "option" not defined to save xml setup file' );
			return;
		}

		$dir = mosLanguage::getLanguagePath( $client, $element );
		$xml = $dir . $element . '.xml';

		$msg = mosXMLFS::write( 'language', $vars, $xml )
			? $_LANG->_( 'XML File Saved' )
			: $_LANG->_( 'Error saving file' );

		$this->setRedirect( 'index2.php?option=com_languages&element=' . $element . '&client=' . $client, $msg );
	}

	/**
	 * cancel
	 */
	function cancel() {
		global $option;
		$element = mosGetParam( $_REQUEST, 'element', 'english' );
		$client = intval( mosGetParam( $_REQUEST, 'client', 0 ) );

		$this->setRedirect( 'index2.php?option=' . $option . '&element=' . $element . '&client=' . $client );
	}

	/**
	 * List languages
	 */
	function listLangs() {
		global $mosConfig_absolute_path;

		mosFS::load( 'includes/mambo.files.xml.php' );

		$element = mosGetParam( $_REQUEST, 'element', 'english' );
		$client = intval( mosGetParam( $_REQUEST, 'client', 0 ) );

		$dir = mosLanguage::getLanguagePath( $client, $element );
		$files = mosFS::listFiles( $dir, "^{$element}(\.[^\.]*)*\.(ini|xml)$" );

		foreach ($files as $i=>$file) {
			$files[$i] = array(
				'file' => $file,
				'fsize' => number_format( filesize( $dir . $file ) ),
				'mtime' => date( 'd-m-Y H:i:s', filemtime( $dir . $file ) ),
				'perms' => mosFS::getPermissions( $dir . $file )
			);
		}

		$xml = $dir . $element . '.xml';
		if (file_exists( $xml )) {
			$xmlFile = mosXMLFS::read( $xml, 'language', $xmlFile );
		} else {
			$xmlFile = null;
		}

		$vars = array(
			'writable' => intval( is_writable( $dir ) )
		);

		$tree = findLanguageFiles();
		languageScreens::listLangs( $tree, $files, $xmlFile, $vars, $element, $client );
	}

	/**
	 * Delete a set of language files
	 */
	function delete() {
		global $mosConfig_lang, $_LANG;

		$element = mosGetParam( $_POST, 'element', 'english' );
		$client = intval( mosGetParam( $_POST, 'client', 0 ) );

		if ($element == 'english') {
			$msg = $_LANG->_( 'errorDeleteEnglish' );
			mosRedirect( 'index2.php?option=com_languages&element=' . $element . '&client=' . $client, $msg );
		}
		if ($element == $mosConfig_lang) {
			$msg = $_LANG->_( 'errorDeleteCurrent' );
			mosRedirect( 'index2.php?option=com_languages&element=' . $element . '&client=' . $client, $msg );
		}

		$dir = mosLanguage::getLanguagePath( $client, $element );
		$files = mosFS::listFiles( $dir, "^{$element}(\.[^\.]*)*\.(ini|xml)$", false, true );

		foreach ($files as $file) {
			mosFS::deleteFile( $file );
		}

		$msg = $_LANG->_( 'Deleted' );
		$this->setRedirect( 'index2.php?option=com_languages', $msg );
	}

	/**
	 * Edit language file
	 */
	function edit() {
		global $mosConfig_absolute_path;

		$element = mosGetParam( $_POST, 'element', 'english' );
		$client = intval( mosGetParam( $_POST, 'client', 0 ) );
		$cid = mosGetParam( $_POST, 'cid', array() );
		$file = $cid[0];

		$englishDir = mosLanguage::getLanguagePath( $client, 'english' );
		$dir = mosLanguage::getLanguagePath( $client, $element );

		// load the english version as the 'keys' are taken from this file
		$ENGLISH = new mosLanguage();
		$engFile = str_replace( $element, 'english', $file );
		$ENGLISH->_load( $englishDir . $engFile );

		// if a translation, load the native langugae file
		$vars['isTranslation'] = ($element != 'english');
		if ($vars['isTranslation']) {
			$LANG = new mosLanguage();
			$LANG->_load( $dir . $file );
		} else {
			// just use a reference to the english
			$LANG =& $ENGLISH;
		}

		// the primary file in the site language.ini file
		// eg: english.ini, french.ini, etc
		$vars['isPrimary'] = mosLanguage::isPrimary( $element, $client, $file );
		if ($vars['isPrimary']) {
			// primary language file contains metadata
			$vars['name'] = $LANG->name();
			$vars['iso'] = $LANG->iso();
			$vars['isocode'] = $LANG->isoCode();
			$vars['locale'] = $LANG->locale();
			$vars['rtl'] = $LANG->rtl();
		}

		$rows = array();
		// the english 'keys' are the ones requiring translation
		foreach ($ENGLISH->_strings as $k=>$v) {
			// ignore the metadata variables
			if (substr( $k, 0, 2 ) != '__') {
				// leave a blank if the native file doesn't have a translation
				$text = $LANG->hasKey( $k ) ? $LANG->_( $k ) : '';
				$size = strlen( $text );

				$rows[] = array(
					'key' => $k,
					'value' => htmlspecialchars( $text ),
					'english' => ($vars['isTranslation'] ? $v : null),
					'same' => intval( $v == $text ),
					// use a textarea for long strings
					'type' => $size < 40 ? 'text' : 'textarea',
					// have a guess at the number of rows required
					'rows' => ceil( $size / 38 )
				);
			}
		}

		languageScreens::edit( $rows, $vars, $element, $client, $file );
	}

	/**
	 * Saves the language file
	 */
	function save() {
		global $mosConfig_absolute_path, $_LANG;

		mosFS::load( '@patTemplate' );

		//print_r($_POST);
		$element = mosGetParam( $_POST, 'element', 'english' );
		$client = intval( mosGetParam( $_POST, 'client', 0 ) );
		$file = mosGetParam( $_POST, 'file', 'english.ini' );

		$metadata = mosGetParam( $_POST, 'vars', array() );
		$strings = mosGetParam( $_POST, 'strings', array() );

		// sort by the alias
		ksort( $strings );

		// assemble the array to pass to the template
		$newStrings = array();
		$strip = get_magic_quotes_gpc();
		foreach ($strings as $k => $v) {
			if ($strip) {
				$v = stripslashes( $v );
			}
			$v = str_replace( "\r", '', $v );
			$v = str_replace( "\n", '\\n', $v );
			$newStrings[] = array(
				'alias' => $k,
				'text' => $v
			);
		}

		// write the ini file
		$tmpl = new patTemplate;
		$tmpl->setNamespace( 'mos' );

		// load the wrapper
		$tmpl->setRoot( dirname( __FILE__ ) . '/tmpl' );
		$tmpl->readTemplatesFromFile( 'save.html' );

		$tmpl->addVar( 'language-ini', 'version', strftime( '%Y-%m-%d %H:%M:%S' ) );
		$tmpl->addRows( 'strings', $newStrings );

		if (mosLanguage::isPrimary( $element, $client, $file )) {
			$tmpl->addVars( 'metadata', $metadata );
		}

		$buffer = $tmpl->getParsedTemplate( 'language-ini' );
		$dir = mosLanguage::getLanguagePath( $client, $element );

		if (!mosFS::write( $dir . $file, $buffer )) {
			die( 'TODO: Handle mosFS::write gracefully' );
		}
		$msg = $_LANG->_( 'File saved' );
		$this->setRedirect( 'index2.php?option=com_languages&element=' . $element . '&client=' . $client, $msg );
	}

	/**
	 * Create new language files
	 * @return boolean
	 */
	function create() {
		global $mosConfig_absolute_path, $_LANG;

		$name = mosGetParam( $_POST, 'newfile', '' );
		$copyFrom = mosGetParam( $_POST, 'copyfrom', '' );
		$element = mosGetParam( $_POST, 'element', 'english' );
		$client = intval( mosGetParam( $_POST, 'client', 0 ) );

		if ($name == '') {
			$msg = $_LANG->_( 'validName' );
			mosRedirect( 'index2.php?option=com_languages&element=' . $element . '&client=' . $client, $msg );
		}

		$langRootPath = mosLanguage::getLanguagePath( $client );

		$name = mosFS::makeSafe( $name );
		$copyToDir = mosLanguage::getLanguagePath( $client, $name );

		$copyFrom = mosFS::makeSafe( $copyFrom );
		$copyFromDir = mosLanguage::getLanguagePath( $client, $copyFrom );

		if (!is_writable( $langRootPath )) {
			$msg = $_LANG->_( 'validDirNotWritable' );
			mosRedirect( 'index2.php?option=com_languages&element=' . $element . '&client=' . $client, $msg );
		}

		if( !mosMakePath( '', $copyToDir ) ) {
			$msg = $_LANG->_( 'validDirNotWritable' );
			mosRedirect( 'index2.php?option=com_languages&element=' . $element . '&client=' . $client, $msg );
		}

		if ($copyFrom) {
			$files = mosFS::listFiles( $copyFromDir, "^{$copyFrom}(\.[^\.]*)*\.ini$" );
			foreach ($files as $file) {
				$newFile = preg_replace( "#^{$copyFrom}#", $name, $file );
				$msg = mosFS::copy( $copyFromDir . $file, $copyToDir . $newFile );
				if ($msg !== true ) {
					$this->setRedirect( 'index2.php?option=com_languages&element=' . $element . '&client=' . $client, $msg );
					return false;
				}
			}
			// modify and save xml file
			copy_language_xml( $copyFromDir, $copyFrom, $copyToDir, $name );
		} else {
			mosFS::load( '@patTemplate' );

			// write the ini file
			$tmpl = new patTemplate;
			$tmpl->setNamespace( 'mos' );

			// load the wrapper
			$tmpl->setRoot( dirname( __FILE__ ) . '/tmpl' );
			$tmpl->readTemplatesFromFile( 'save.html' );
			$tmpl->addGlobalVar( 'version', strftime( '%Y-%m-%d %H:%M:%S' ) );

			$dir = mosLanguage::getLanguagePath( $client, 'english' );

			$files = mosFS::listFiles( $dir, '^english(\.[^\.]*)*\.ini$' );
			foreach ($files as $file) {
				$file = preg_replace( '#^english#', $name, $file );

				if (mosLanguage::isPrimary( $name, $client, $file )) {
					$tmpl->addVar( 'metadata', 'name', $name );
				} else {
					//$tmpl->clearVar( 'metadata', 'name' );
				}

				$buffer = $tmpl->getParsedTemplate( 'language-ini' );
				if (!mosFS::write( $copyToDir . $file, $buffer )) {
					die( 'TODO: Handle mosFS::write gracefully' );
				}
			}
			// copy xml file
			// modify and save xml file
			copy_language_xml( $dir, 'english', $copyToDir, $name );
		}
		$msg = $_LANG->_( 'Language created' );
		$this->setRedirect( 'index2.php?option=com_languages&element=' . $element . '&client=' . $client, $msg );
	}

	/**
	 * Options for trawling
	 */
	function trawlOptions() {
		$tree = findLanguageFiles();
		languageScreens::trawlOptions( $tree );
	}

	/**
	* Trawl for untranslated strings
	* @param string The URL option
	*/
	function trawl() {
		global $mosConfig_absolute_path, $_LANG;

		$options = mosGetParam( $_POST, 'options', array() );

		$vars = trawlLanguages( $options );
		$tree = findLanguageFiles();
		languageScreens::trawl( $tree, $vars, $options );
	}
}

$tasker = new languageTasks();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
$tasker->redirect();
?>