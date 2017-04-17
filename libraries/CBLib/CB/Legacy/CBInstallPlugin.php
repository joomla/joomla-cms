<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/20/14 7:18 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Database\Table\Table;
use CBLib\Language\CBTxt;
use CBLib\Xml\SimpleXMLElement;

defined('CBLIB') or die();

/**
 * CBInstallPlugin Class implementation
 * 
 * Used for implementing the Model for CB Plugins installation screens of CB
 * for the store() method to install plugins.
 */
class CBInstallPlugin extends Table
{
	/**
	 * @var int
	 */
	public $id = null;
	/**
	 * @var string
	 */
	public $func;
	/**
	 * @var string
	 */
	public $localdirectory;
	/**
	 * @var string
	 */
	public $packageurl;
	/**
	 * @var string
	 */
	public $plgfile;

	/**
	 * @var string
	 */
	private $_resultMessage	=	null;

	/**
	 *	Binds an array/hash from database to this object
	 *
	 *	@param  int $oid  optional argument, if not specifed then the value of current key is used
	 *	@return mixed     any result from the database operation
	 */
	public function load( $oid = null )
	{
		return true;
	}

	/**
	 * If table key (id) is NULL : inserts a new row
	 * otherwise updates existing row in the database table
	 *
	 * Can be overridden or overloaded by the child class
	 *
	 * @param  boolean  $updateNulls  TRUE: null object variables are also updated, FALSE: not.
	 * @return boolean                TRUE if successful otherwise FALSE
	 *
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 */
	public function store( $updateNulls = false )
	{
		cbimport( 'cb.tabs' );
		cbimport( 'cb.imgtoolbox' );
		cbimport( 'cb.adminfilesystem' );
		cbimport( 'cb.installer' );
		cbimport( 'cb.params' );
		cbimport( 'cb.pagination' );

		cbSpoofCheck( 'plugin' );
		checkCanAdminPlugins( 'core.admin' );

		ob_start();

		switch ( $this->func ) {
			case 'installPluginUpload':
				$success	=	$this->installPluginUpload();
				break;
			case 'installPluginDir':
				$success	=	$this->installPluginDir( $this->localdirectory );
				break;
			case 'installPluginURL':
				$success	=	$this->installPluginURL( $this->packageurl );
				break;
			case 'installPluginDisc':
				$success	=	$this->installPluginDisc( $this->plgfile );
				break;
			default:
				throw new \InvalidArgumentException( CBTxt::T( 'INVALID_FUNCTION', 'Invalid function' ), 500 );
		}

		$html		=	ob_get_contents();
		ob_end_clean();

		$this->_resultMessage	=	$html;

		if ( ! $success ) {
			$this->setError( 'Installation error' );
		}

		return $success;
	}

	/**
	 * After store() this function may be called to get a result information message to display. Override if it is needed.
	 *
	 * @return string|null  STRING to display or NULL to not display any information message (Default: NULL)
	 */
	public function cbResultOfStore( )
	{
		return $this->_resultMessage;
	}

	/**
	 * returns html for maximum upload file size
	 * Used by Backend XML only
	 * @deprecated Do not use directly, only for XML tabs backend
	 *
	 * @return string
	 */
	public function displayUploadMaxFilesize( )
	{
		return ini_get( 'upload_max_filesize' )
		. ' <small>(upload_max_filesize in '
		. ( is_callable( 'php_ini_loaded_file' ) && php_ini_loaded_file() ? htmlspecialchars( php_ini_loaded_file() ) : 'php.ini' )
		. ')</small>';

	}

	/**
	 * Returns HTML for "install from discovery" tab
	 * Used by Backend XML only
	 * @deprecated Do not use directly, only for XML tabs backend
	 *
	 * @return string
	 */
	public function displayDiscoveries( )
	{
		global $_CB_framework, $_CB_database;

		// Prepare array of discovered plugins (not installed, but exists):
		$allPlgsFolders										=	array();
		$discoveredPlgs										=	array();
		$existingPlgList									=	array();
		$existingPlgFolders									=	array();
		$failingXmlFiles									=	array();

		// Discovers all installed plugins
		$query												=	'SELECT ' . $_CB_database->NameQuote( 'folder' )
			.	', ' . $_CB_database->NameQuote( 'type' )
			.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin' );
		$_CB_database->setQuery( $query );
		$existingPlgs										=	$_CB_database->loadAssocList();

		// Constructs list of installed plugins': 1) folders by type ($existingPlgList) and 2) list of installed folder paths ($existingPlgFolders)
		foreach ( $existingPlgs as $existingPlg ) {
			$plgType										=	$existingPlg['type'];

			$existingPlgList[$plgType][]					=	$existingPlg['folder'];

			$existingPlgFolders[]							=	$existingPlg['type'] . '/' . $existingPlg['folder'];
		}

		// Discovers inside each type all the directories:
		foreach ( $existingPlgList as $plgType => $existingPlgs ) {
			$plgFolders										=	array_filter(
																	cbReadDirectory( $_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/' . $plgType ),
																	function ( $subSubFolder )
																	{
																		return ! in_array( $subSubFolder, array( 'index.html', 'default' ) );
																	}
																);

			// Adds each directory of each type to the list of checks:
			foreach ( $plgFolders as $plgFolder ) {
				$plgFolderAndType							=	$plgType . '/' . $plgFolder;

				$allPlgsFolders[]							=	$plgFolderAndType;

				// Checks for sub-plugins, templates and known folders that might contain plugins:
				foreach ( array( 'plugin', 'templates', 'processors', 'products' ) as $subFolder ) {
					$subfolderPath							=	$_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/' . $plgFolderAndType . '/' . $subFolder;

					if ( file_exists( $subfolderPath ) ) {
						$subPluginsFolders					=	array_map(
							function ( $subSubFolder ) use ( $plgFolderAndType, $subFolder )
							{
								return $plgFolderAndType . '/' . $subFolder . '/' . $subSubFolder;
							},
							array_filter(
								cbReadDirectory( $subfolderPath ),
								function ( $subSubFolder )
								{
									return ! in_array( $subSubFolder, array( 'index.html', 'default' ) );
								}
							)
						);

						// Consolidates sub-folders:
						$allPlgsFolders							=	array_merge( $allPlgsFolders, $subPluginsFolders );
					}
				}
			}
		}

		// As discoveries above might lead to multiple entries depending on database of installed plugins, makes discoveries unique:
		$allPlgsFolders										=	array_unique( $allPlgsFolders );

		// Checks for each discovered folder if there are cbinstall-xml files, and if yes, if they are in the installed plugins list:
		foreach ( $allPlgsFolders as $plgFolderAndType ) {
			$plgFolderDir									=	$_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/' . $plgFolderAndType;

			if ( ( ! is_file( $plgFolderDir ) ) && ( ! in_array( $plgFolderAndType, $existingPlgFolders ) ) ) {
				$plgFiles									=	cbReadDirectory( $plgFolderDir );

				if ( $plgFiles ) foreach ( $plgFiles as $plgFile ) {
					if ( preg_match( '/^.+\.xml$/i', $plgFile ) ) {
						$plgPath							=	$plgFolderDir . ( substr( $plgFolderDir, -1, 1 ) == '/' ? '' : '/' ) . $plgFile;
						try {
							$plgXml							=	@new SimpleXMLElement( trim( file_get_contents( $plgPath ) ) );
							$elements						=	explode( '/', $plgFolderAndType );
							$lastFolder						=	array_pop( $elements );

							if ( ( $plgXml->getName() == 'cbinstall' ) && ( $lastFolder != 'default' ) && ( ! array_key_exists( $plgFolderAndType, $discoveredPlgs ) ) ) {
								$discoveredPlgs[$plgFolderAndType]	=	array( 'name' => ( isset( $plgXml->name ) ? (string) $plgXml->name : $plgFolderAndType ), 'file' => $plgFolderAndType );
							}
						} catch ( \Exception $e ) {
							$failingXmlFiles[]				=	$plgPath;
						}
					}
				}
			}
		}

		$return				=	'';

		if ( count( $failingXmlFiles ) > 0 ) {
			$return			.=	'<div class="col-sm-12">'
							.		'<div class="col-sm-12 alert alert-danger" role="alert">'
							.			'<h4>'
							.				CBTxt::Th( 'Malformed XML files discovered in CB plugin folders:' )
							.			'</h4>';

			foreach ( $failingXmlFiles as $failedFilePath ) {
				$return		.=			'<div class="cbft_text form-group cb_form_line clearfix">'
							.				CBTxt::Th( 'XML_FILE_FILE_IS_MALFORMED', 'XML file [FILE_PATH_AND_NAME] is malformed and should be replaced or fixed, or the plugin should be removed', array( '[FILE_PATH_AND_NAME]' => '<strong>' . htmlspecialchars( $failedFilePath ) . '</strong>' ) )
							.			'</div>';
			}

			$return			.=		'</div>'
							.	'</div>';

		}

		if ( $discoveredPlgs ) {
			foreach ( $discoveredPlgs as $discoveredPlg ) {
				$return		.=		'<div class="cbft_text form-group cb_form_line clearfix">'
							.			'<div class="control-label col-sm-3">'
							.				htmlspecialchars( $discoveredPlg['name'] )
							.			'</div>'
							.			'<div class="cb_field col-sm-9">'
							.				'<input type="button" class="btn btn-primary btn-sm" value="' . htmlspecialchars( CBTxt::T( 'Install Package' ) ) . '" onclick="submitbutton( \'act=apply&amp;func=installPluginDisc&amp;plgfile=' . addslashes( $discoveredPlg['file'] ) . '\' )" />'
							.			'</div>'
							.		'</div>';
			}
		} else {
			$return			.=		'<div class="col-sm-12">'
							.			CBTxt::Th( 'No plugins discovered.' )
							.		'</div>';
		}

		return $return;
	}

	/**
	 * Installs plugin by upload from URL
	 *
	 * @return boolean
	 */
	private	function installPluginUpload()
	{
		global $_FILES;

		// Try extending time, as unziping/ftping took already quite some... :
		@set_time_limit( 240 );

		_CBsecureAboveForm('showPlugins');

		outputCbTemplate( 2 );
		outputCbJs( 2 );
		initToolTip( 2 );

		$installer	=	new cbInstallerPlugin();

		// Check if file uploads are enabled
		if ( ! (bool) ini_get( 'file_uploads' ) ) {
			cbInstaller::showInstallMessage(
				CBTxt::T('The installer cannot continue before file uploads are enabled. Please use the install from directory method.'),
				CBTxt::T('Installer - Error'),
				false
			);
			return false;
		}

		// Check that the zlib is available
		if( ! extension_loaded( 'zlib' ) ) {
			cbInstaller::showInstallMessage(
				CBTxt::T('The installer cannot continue before zlib is installed'),
				CBTxt::T('Installer - Error'),
				false
			);
			return false;
		}

		$userfile				=	cbGetParam( $_FILES, 'uploadfile', null );

		if ( ! $userfile || ( $userfile == null ) ) {
			cbInstaller::showInstallMessage(
				CBTxt::T('No file selected'),
				CBTxt::T('Upload new plugin - error'),
				false
			);
			return false;
		}

//	$userfile['tmp_name']	=	stripslashes( $userfile['tmp_name'] );
//	$userfile['name']		=	stripslashes( $userfile['name'] );

		$msg		=	'';
		$localName	=	$_FILES['uploadfile']['name'];
		$resultdir	=	$this->uploadFile( $_FILES['uploadfile']['tmp_name'], $localName , $msg );		// $localName is updated here

		if ( $resultdir === false ) {
			cbInstaller::showInstallMessage(
				$msg,
				CBTxt::T( 'UPLOAD_ERROR', 'Upload Error' ),
				false
			);
			return false;
		}

		if ( ! $installer->upload( $localName ) ) {
			if ( $installer->unpackDir() ) {
				$installer->cleanupInstall( $localName, $installer->unpackDir() );
			}
			cbInstaller::showInstallMessage(
				$installer->getError(),
				CBTxt::T( 'UPLOAD_FAILED', 'Upload Failed'),
				false
			);
			return false;
		}

		$ret	=	$installer->install();

		$installer->cleanupInstall( $localName, $installer->unpackDir() );

		cbInstaller::showInstallMessage(
			$installer->getError(),
			( $ret ? CBTxt::T( 'UPLOAD_SUCCESS', 'Upload Success' ) : CBTxt::T( 'UPLOAD_FAILED', 'Upload Failed' ) ),
			$ret
		);

		$installer->cleanupInstall( $localName, $installer->unpackDir() );

		return $ret;
	}

	/**
	 * Changes the permissions of file
	 *
	 * @param  string   $filename  Filename with path
	 * @return boolean             Success
	 */
	private function _cbAdmin_chmod( $filename )
	{
		global $_CB_framework;

		cbimport( 'cb.adminfilesystem' );
		$adminFS			=	cbAdminFileSystem::getInstance();

		$origmask			=	null;
		if ( $_CB_framework->getCfg( 'dirperms' ) == '' ) {
			// rely on umask
			// $mode			=	0777;
			return true;
		} else {
			$origmask		=	@umask( 0 );
			$mode			=	octdec( $_CB_framework->getCfg( 'dirperms' ) );
		}

		$ret				=	$adminFS->chmod( $filename, $mode );

		if ( isset( $origmask ) ) {
			@umask( $origmask );
		}
		return $ret;
	}

	/**
	 * Uploads a file into the filesystem
	 *
	 * @param  string  $filename       Input filename for move_uploaded_file()
	 * @param  string  $userfile_name  INPUT+OUTPUT: Destination filesname
	 * @param  string  $msg            OUTPUT: Message for user
	 * @return boolean                 Success
	 */
	private function uploadFile( $filename, &$userfile_name, &$msg )
	{
		global $_CB_framework;

		cbimport( 'cb.adminfilesystem' );
		$adminFS			=	cbAdminFileSystem::getInstance();

		$baseDir			=	_cbPathName( $_CB_framework->getCfg('tmp_path') );
		$userfile_name		=	$baseDir . $userfile_name;		// WARNING: this parameter is returned !

		if ( $adminFS->file_exists( $baseDir ) ) {
			if ( $adminFS->is_writable( $baseDir ) ) {
				if ( move_uploaded_file( $filename, $userfile_name ) ) {
//			    if ( $this->_cbAdmin_chmod( $userfile_name ) ) {
					return true;
//				} else {
//					$msg = CBTxt::T('Failed to change the permissions of the uploaded file.');
//				}
				} else {
					$msg = sprintf( CBTxt::T('Failed to move uploaded file to %s directory.'), '<code>' . htmlspecialchars( $baseDir ) . '</code>' );
				}
			} else {
				$msg = sprintf( CBTxt::T('Upload failed as %s directory is not writable.'), '<code>' . htmlspecialchars( $baseDir ) . '</code>' );
			}
		} else {
			$msg = sprintf( CBTxt::T('Upload failed as %s directory does not exist.'), '<code>' . htmlspecialchars( $baseDir ) . '</code>' );
		}
		return false;
	}

	/**
	 * Installs the plugin From Directory
	 *
	 * @param  string   $userfile  Filename
	 * @return boolean             Success
	 */
	private function installPluginDir( $userfile )
	{
		// Try extending time, as unziping/ftping took already quite some... :
		@set_time_limit( 240 );

		_CBsecureAboveForm('showPlugins');

		outputCbTemplate( 2 );
		outputCbJs( 2 );
		initToolTip( 2 );

		$installer = new cbInstallerPlugin();

		// Check if file name exists
		if ( ! $userfile ) {
			cbInstaller::showInstallMessage(
				CBTxt::T('No file selected'),
				CBTxt::T('Install new plugin from directory - error'),
				false
			);
			return false;
		}

		$path = _cbPathName( $userfile );
		if (!is_dir( $path )) {
			$path = dirname( $path );
		}

		$ret = $installer->install( $path);

		cbInstaller::showInstallMessage(
			$installer->getError(),
			sprintf( CBTxt::T('Install new plugin from directory %s'), $userfile ) . ' - ' . ( $ret ? CBTxt::T('Success') : CBTxt::T('Failed') ),
			$ret
		);

		return $ret;
	}

	/**
	 * Installs the plugin From URL
	 *
	 * @param  string   $userfileURL  Url
	 * @return boolean                Success
	 */
	private function installPluginURL( $userfileURL )
	{
		global $_CB_framework;

		// Try extending time, as unziping/ftping took already quite some... :
		@set_time_limit( 240 );

		_CBsecureAboveForm('showPlugins');

		outputCbTemplate( 2 );
		outputCbJs( 2 );
		initToolTip( 2 );

		$installer = new cbInstallerPlugin();

		// Check that the zlib is available
		if( ! extension_loaded( 'zlib' ) ) {
			cbInstaller::showInstallMessage(
				CBTxt::T('The installer cannot continue before zlib is installed'),
				CBTxt::T('Installer - Error'),
				false
			);
			return false;
		}

		if ( ! $userfileURL ) {
			cbInstaller::showInstallMessage(
				CBTxt::T('No URL selected'),
				CBTxt::T('Upload new plugin - error'),
				false
			);
			return false;
		}


		cbimport( 'cb.adminfilesystem' );
		$adminFS			=	cbAdminFileSystem::getInstance();

		if ( $adminFS->isUsingStandardPHP() ) {
			$baseDir		=	_cbPathName( $_CB_framework->getCfg('tmp_path') );
		} else {
			$baseDir		=	$_CB_framework->getCfg( 'absolute_path' ) . '/tmp/';
		}
		$userfileName		=	$baseDir . 'comprofiler_temp.zip';


		$msg			=	'';

		$resultdir		=	$this->uploadFileURL( $userfileURL, $userfileName, $msg );

		if ( $resultdir === false ) {
			cbInstaller::showInstallMessage(
				$msg,
				sprintf(CBTxt::T('Download %s - Download Error'), $userfileURL),
				false
			);
			return false;
		}

		if ( ! $installer->upload( $userfileName ) ) {
			cbInstaller::showInstallMessage(
				$installer->getError(),
				sprintf(CBTxt::T('Download %s - Upload Failed'), $userfileURL),
				false
			);
			return false;
		}

		$ret = $installer->install();

		cbInstaller::showInstallMessage(
			$installer->getError(),
			sprintf( CBTxt::T('Download %s'), $userfileURL ) . ' - ' . ( $ret ? CBTxt::T('Success') : CBTxt::T('Failed') ),
			$ret
		);

		$installer->cleanupInstall( $userfileName, $installer->unpackDir() );

		return $ret;
	}

	/**
	 * Installs the plugin By in-place Discovery
	 *
	 * @param  string   $plgFile  Directory discovered
	 * @return boolean            Success
	 */
	private function installPluginDisc( $plgFile )
	{
		global $_CB_framework;

		// Try extending time, as unziping/ftping took already quite some... :
		@set_time_limit( 240 );

		_CBsecureAboveForm( 'showPlugins' );

		outputCbTemplate( 2 );
		outputCbJs( 2 );
		initToolTip( 2 );

		$installer	=	new cbInstallerPlugin();

		// Check if file xml exists
		if ( ! $plgFile ) {
			cbInstaller::showInstallMessage(
				CBTxt::T( 'No file selected' ),
				CBTxt::T( 'Install new plugin from discovery - error' ),
				false
			);
			return false;
		}

		$path		=	_cbPathName( $_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/' . $plgFile );

		if ( ! is_dir( $path ) ) {
			$path	=	dirname( $path );
		}

		if ( ! is_dir( $path ) ) {
			cbInstaller::showInstallMessage(
				CBTxt::T( 'FILE_DOES_NOT_EXIST_FILE', 'File does not exist - [file]', array( '[file]' => $path ) ),
				CBTxt::T( 'INSTALL_NEW_PLUGIN_FROM_DISCOVERY_ERROR', 'Install new plugin from discovery - error' ),
				false
			);
			return false;
		}

		$ret		=	$installer->install( $path, true );

		cbInstaller::showInstallMessage(
			$installer->getError(),
			CBTxt::T( 'INSTALL_NEW_PLUGIN_FROM_DISCOVERY_ERROR_FILE_STATUS', 'Install new plugin from discovery - [file] - [status]',
				array( '[file]' => $path, '[status]' => ( $ret ? CBTxt::T( 'Success' ) : CBTxt::T( 'Failed' ) ) )
			),
			$ret
		);

		return $ret;
	}

	/**
	 * Uploads a file from a Url into a file on the filesystem
	 *
	 * @param  string  $userfileURL    Url
	 * @param  string  $userfile_name  INPUT+OUTPUT: Destination filesname
	 * @param  string  $msg            OUTPUT: Message for user
	 * @return boolean                 Success
	 */
	private function uploadFileURL( $userfileURL, $userfile_name, &$msg )
	{
		global $_CB_framework;

		cbimport( 'cb.snoopy' );
		cbimport( 'cb.adminfilesystem' );
		$adminFS					=	cbAdminFileSystem::getInstance();

		if ( $adminFS->isUsingStandardPHP() ) {
			$baseDir				=	_cbPathName( $_CB_framework->getCfg('tmp_path') );
		} else {
			$baseDir				=	$_CB_framework->getCfg( 'absolute_path' ) . '/tmp';
		}

		if ( file_exists( $baseDir ) ) {
			if ( $adminFS->is_writable( $baseDir ) || ! $adminFS->isUsingStandardPHP() ) {

				$s					=	new CBSnoopy();
				$fetchResult		=	@$s->fetch( $userfileURL );

				if ( $fetchResult && ! $s->error && ( $s->status == 200 ) ) {
					cbimport( 'cb.adminfilesystem' );
					$adminFS		=	cbAdminFileSystem::getInstance();
					if ( $adminFS->file_put_contents( $baseDir . $userfile_name, $s->results ) ) {
						if ( $this->_cbAdmin_chmod( $baseDir . $userfile_name ) ) {
							return true;
						} else {
							$msg = sprintf(CBTxt::T('Failed to change the permissions of the uploaded file %s'), $baseDir.$userfile_name);
						}
					} else {
						$msg = sprintf(CBTxt::T('Failed to create and write uploaded file in %s'), $baseDir.$userfile_name);
					}
				} else {
					$msg = ( $s->error ? sprintf( CBTxt::T('Failed to download package file from <code>%s</code> to webserver due to following error: %s'),  $userfileURL, $s->error ) :
						sprintf( CBTxt::T('Failed to download package file from <code>%s</code> to webserver due to following status: %s'), $userfileURL, $s->status . ': ' . $s->response_code ) );
				}
			} else {
				$msg = sprintf( CBTxt::T('Upload failed as %s directory is not writable.'), '<code>' . htmlspecialchars( $baseDir ) . '</code>' );
			}
		} else {
			$msg = sprintf( CBTxt::T('Upload failed as %s directory does not exist.'), '<code>' . htmlspecialchars( $baseDir ) . '</code>' );
		}
		return false;
	}
}
