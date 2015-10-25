<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/20/14 1:05 AM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

defined('CBLIB') or die();

/**
 * cbAdminFileSystem Class implementation
 * 
 */
class cbAdminFileSystem
{
	/**
	 * Functions of Cms that can be called instead of doing it in PHP (e.g. for FTP layer)
	 * @var array
	 */
	protected $functions			=	array();

	/**
	 * Constructor
	 *
	 * @param  array  $functions   array of functions
	 * @return cbAdminFileSystem
	 */
	protected function __construct( $functions )
	{
		if ( isset( $functions['_constructor'] ) ) {
			call_user_func_array( $functions['_constructor'], array( &$functions ) );
		}
		$this->functions					=&	$functions;
	}

	/**
	 * Gets a single instance of the cbAdminFileSystem class
	 *
	 * @param  boolean  $purePHP  TRUE: uses only PHP functions, FALSE: use CMS functions
	 * @return cbAdminFileSystem
	 */
	public static function & getInstance( $purePHP = false )
	{
		static $singleInstance				=	array( false => null, true => null );
		static $emptyArray					=	array();

		if ( ( ! isset( $singleInstance[$purePHP] ) ) || ( $singleInstance[$purePHP] === null ) ) {
			if ( $purePHP === true ) {
				$singleInstance[$purePHP]	=	new cbAdminFileSystem( $emptyArray );
			} else {
				global $CB_AdminFileFunctions;

				$singleInstance[$purePHP]	=	new cbAdminFileSystem( $CB_AdminFileFunctions );
			}
		}
		return $singleInstance[$purePHP];
	}

	/**
	 * Is the filesystem using only PHP functions ?
	 *
	 * @return boolean  TRUE: yes, FALSE: no
	 */
	function isUsingStandardPHP( )
	{
		return ( count( $this->functions ) == 0 );
	}

	/**
	 * DIRECTORY METHODS:
	 */

	/**
	 * creates a directory
	 * @see PHP  mkdir( $pathname, $mode, $recursive, $context )
	 *
	 * @param  string   $pathname    The directory path
	 * @param  int      $mode        Default is not 0777 like PHP function's default, but our default is relying on CMS's 'dirperm' configuration, and if not existant or not set, on Umask
	 * @param  boolean  $recursive   Recursively create
	 * @param  resource $context     Context: See streams
	 * @return boolean               Returns TRUE on success or FALSE on failure
	 */
	public function mkdir( $pathname, $mode = null, $recursive = null, $context = null )
	{
		if ( $mode === null ) {
			global $_CB_framework;
			if ( ( ! $_CB_framework ) || $_CB_framework->getCfg( 'dirperms' ) == '' ) {
				// rely on umask:
				$mode						=	0755;
			} else {
				$origmask					=	@umask( 0 );
				$mode						=	octdec( $_CB_framework->getCfg( 'dirperms' ) );
			}
		}
		if ( isset( $this->functions['mkdir'] ) ) {
			$return							=	call_user_func_array( $this->functions['mkdir'], array( $pathname, $mode, $recursive, $context ) );
		} else {
			$return							=	( is_null( $context ) ? mkdir( $pathname, $mode, $recursive ) : mkdir( $pathname, $mode, $recursive, $context ) );
		}
		if ( isset( $origmask ) ) {
			@umask( $origmask );
		}
		return $return;
	}

	/**
	 * Remove an empty directory
	 * @see PHP  rmdir( $dirname, $context )
	 *
	 * @param  string    $dirname  Directory path
	 * @param  resource  $context  [optional] Context
	 * @return bool|mixed
	 */
	public function rmdir( $dirname, $context = null )
	{
		if ( isset( $this->functions['rmdir'] ) ) {
			return call_user_func_array( $this->functions['rmdir'], array( $dirname, $context ) );
		}

		return ( is_null( $context ) ? rmdir( $dirname ) : rmdir( $dirname, $context ) );
	}

	/**
	 * Checks if $filename is a directory
	 * @see PHP  is_dir( $filename )
	 *
	 * @param  string  $filename
	 * @return boolean
	 */
	public function is_dir( $filename )
	{
		if ( isset( $this->functions['is_dir'] ) ) {
			return call_user_func_array( $this->functions['is_dir'], array( $filename ) );
		}

		return is_dir( $filename );
	}

	/**
	 * DIRECTORY LISTING METHODS:
	 */

	/**
	 * Opens directory for listing
	 * @see PHP  opendir( $path, $context )
	 *
	 * @param  string    $path     Path
	 * @param  resource  $context  [optional] Context
	 * @return resource            Directory handle
	 */
	public function opendir( $path, $context = null )
	{
		if ( isset( $this->functions['opendir'] ) ) {
			return call_user_func_array( $this->functions['opendir'], array( $path, $context ) );
		}

		return ( is_null( $context ) ? opendir( $path ) : opendir( $path, $context ) );
	}

	/**
	 * Reads next entry in directory
	 * @see PHP  readdir( $dir_handle )
	 *
	 * @param  resource        $dir_handle  Directory handle
	 * @return string|boolean
	 */
	public function readdir( $dir_handle )
	{
		if ( isset( $this->functions['readdir'] ) ) {
			return call_user_func_array( $this->functions['readdir'], array( $dir_handle ) );
		}

		return readdir( $dir_handle );
	}

	/**
	 * Closes directory opened for listing with opendir()
	 * @see PHP  closedir( $dir_handle )
	 *
	 * @param  resource  $dir_handle  Directory handle
	 * @return void
	 */
	public function closedir( $dir_handle )
	{
		if ( isset( $this->functions['closedir'] ) ) {
			call_user_func_array( $this->functions['closedir'], array( $dir_handle ) );
			return;
		}

		closedir( $dir_handle );
	}

	/**
	 * FILES/DIRECTORY METHODS:
	 */

	/**
	 * Renames file or directory
	 * @see PHP  rename( $old_name, $new_name, $context )
	 *
	 * @param  string    $old_name  Old name
	 * @param  string    $new_name  New name
	 * @param  resource  $context   Context
	 * @return boolean
	 */
	public function rename( $old_name, $new_name, $context = null )
	{
		if ( isset( $this->functions['rename'] ) ) {
			return call_user_func_array( $this->functions['rename'], array( $old_name, $new_name, $context ) );
		}

		return ( is_null( $context ) ? rename( $old_name, $new_name ) : rename( $old_name, $new_name, $context ) );
	}

	/**
	 * File or directory exists ?
	 * @see PHP  file_exists( $filename )
	 *
	 * @param  string  $filename  File path
	 * @return bool|mixed
	 */
	public function file_exists( $filename )
	{
		if ( isset( $this->functions['file_exists'] ) ) {
			return call_user_func_array( $this->functions['file_exists'], array( $filename ) );
		}

		return file_exists( $filename );
	}
	/**
	 * FILES METHODS:
	 */

	/**
	 * Checks if file is writeable
	 * @see PHP  is_writable( $filename )
	 *
	 * @param  string  $filename  Filename with path
	 * @return boolean            True: Yes
	 */
	public function is_writable( $filename )
	{
		if ( isset( $this->functions['is_writable'] ) ) {
			return call_user_func_array( $this->functions['is_writable'], array( $filename ) );
		}

		return is_writable( $filename );
	}

	/**
	 * Checks if it is a file
	 * @see PHP  is_file( $filename )
	 *
	 * @param  string  $filename  Filename with path
	 * @return boolean            True: Yes
	 */
	public function is_file( $filename )
	{
		if ( isset( $this->functions['is_file'] ) ) {
			return call_user_func_array( $this->functions['is_file'], array( $filename ) );
		}

		return is_file( $filename );
	}

	/**
	 * Change file attributes
	 * @see PHP  chmod( $pathname, $mode )
	 *
	 * @param  string  $pathname  Filename with path
	 * @param  int     $mode      3 octal digits
	 * @return boolean            Success
	 */
	public function chmod( $pathname, $mode )
	{
		if ( isset( $this->functions['chmod'] ) ) {
			return call_user_func_array( $this->functions['chmod'], array( $pathname, $mode ) );
		}

		return chmod( $pathname, $mode );
	}

	/**
	 * Recursively changes a directory and all sub-directories and files
	 * No PHP equivalent
	 *
	 * @param  string   $source      Directory name with path
	 * @param  int      $foldermode  Folders mode (3 octal digits)
	 * @param  int      $filemode    Files mode (3 octal digits)
	 * @return boolean
	 */
	public function chmoddir( $source, $foldermode, $filemode )
	{
		if ( ! $this->file_exists( $source ) ) {
			return false;
		}

		if ( is_dir( $source ) ) {
			$dir					=	$this->opendir( $source );

			if ( $dir === false ) {
				return false;
			}

			if ( ! $this->chmod( $source, $foldermode ) ) {
				return false;
			}

			while ( $file = $this->readdir( $dir ) ) {
				if ( ( $file != '.' ) && ( $file != '..' ) ) {
					$fileSource		=	$source . '/' . $file;

					if ( $this->is_dir( $fileSource ) ) {
						if ( ! $this->chmoddir( $fileSource, $foldermode, $filemode ) ) {
							return false;
						}
					} else {
						if ( ! $this->copy( $fileSource, $filemode ) ) {
							return false;
						}
					}
				}
			}

			$this->closedir( $dir );

			return true;
		}

		return $this->chmod( $source, $filemode );
	}

	/**
	 * Copy file
	 * PHP copy( $source, $dest, $context )
	 *
	 * @param  string    $source   Filename with path
	 * @param  string    $dest     New filename
	 * @param  resource  $context  Context
	 * @return boolean             Success
	 */
	public function copy( $source, $dest, $context = null )
	{
		if ( isset( $this->functions['copy'] ) ) {
			return call_user_func_array( $this->functions['copy'], array( $source, $dest, $context ) );
		}

		return ( is_null( $context ) ? copy( $source, $dest ) : copy( $source, $dest, $context ) );
	}

	/**
	 * Recursively copy directory
	 * No PHP equivalent
	 *
	 * @param  string   $source  Directory name with path
	 * @param  string   $dest    New destination directory
	 * @param  boolean  $force   Overwrite destionation ?
	 * @return boolean           Success
	 */
	public function copydir( $source, $dest, $force = false  )
	{
		if ( isset( $this->functions['copydir'] ) ) {
			return call_user_func_array( $this->functions['copydir'], array( $source, $dest, null, $force ) );
		}

		if ( ! $this->file_exists( $source ) ) {
			return false;
		}

		if ( is_dir( $source ) ) {
			$dir					=	$this->opendir( $source );

			if ( $dir === false ) {
				return false;
			}

			if ( $this->file_exists( $dest ) && ( ! $force ) ) {
				return false;
			}

			if ( ( ! $this->file_exists( $dest ) ) && ( ! $this->mkdir( $dest ) ) ) {
				return false;
			}

			while ( $file = $this->readdir( $dir ) ) {
				if ( ( $file != '.' ) && ( $file != '..' ) ) {
					$fileSource		=	$source . '/' . $file;
					$fileDest		=	$dest . '/' . $file;

					if ( $this->is_dir( $fileSource ) ) {
						if ( ! $this->copydir( $fileSource, $fileDest ) ) {
							return false;
						}
					} else {
						if ( ! $this->copy( $fileSource, $fileDest ) ) {
							return false;
						}
					}
				}
			}

			$this->closedir( $dir );

			return true;
		}

		return $this->copy( $source, $dest );
	}

	/**
	 * Remove file
	 * @see PHP  unlink( $filename, $context )
	 *
	 * @param  string    $filename  Filename with path
	 * @param  resource  $context   Context
	 * @return boolean              Success
	 */
	public function unlink( $filename, $context = null )
	{
		if ( isset( $this->functions['unlink'] ) ) {
			return call_user_func_array( $this->functions['unlink'], array( $filename, $context ) );
		}

		@chmod( $filename, 0777 );
		return ( is_null( $context ) ? unlink( $filename ) : unlink( $filename, $context ) );
	}

	/**
	 * Puts content $data into file
	 * @see PHP  file_put_contents( $file, $data, $flags, $context )
	 *
	 * @param  string       $file     Filename with path
	 * @param  string       $data     Data to put in file
	 * @param  int          $flags    FILE_USE_INCLUDE_PATH, FILE_APPEND, LOCK_EX
	 * @param  resource     $context  Context
	 * @return int|boolean            Success: Number of bytes written, Failure: FALSE
	 */
	public function file_put_contents( $file, $data, $flags = null, $context = null )
	{
		if ( isset( $this->functions['file_put_contents'] ) ) {
			$params				=	array( &$file, &$data, &$flags, &$context ) ;
			return call_user_func_array( $this->functions['file_put_contents'], $params );
		}

		return ( is_null( $context ) ? file_put_contents( $file, $data, $flags ) : file_put_contents( $file, $data, $flags, $context ) );
	}

	/**
	 * Moves an uploaded file
	 * @see PHP  move_uploaded_file( $path, $new_path )
	 *
	 * @param  string  $path       Path of uploaded file
	 * @param  string  $new_path   New path where to store it
	 * @return boolean             Success
	 */
	public function move_uploaded_file( $path, $new_path )
	{
		if ( isset( $this->functions['move_uploaded_file'] ) ) {
			if ( is_uploaded_file( $path ) ) {
				return call_user_func_array( $this->functions['move_uploaded_file'], array( $path, $new_path ) );
			}

			return false;
		}

		return move_uploaded_file( $path, $new_path );
	}

	/**
	 * UTILITY METHODS:
	 */

	/**
	 * Deletes directory, trailing slash needed:
	 *
	 * @param  string   $dir  With / at end.
	 * @return boolean
	 */
	public function deldir( $dir )
	{
		$current_dir		=	$this->opendir( $dir );
		if ( $current_dir !== false ) {
			while ( false !== ( $entryname = $this->readdir( $current_dir ) ) ) {
				if ( $entryname != '.' and $entryname != '..' ) {
					if ( is_dir( $dir . $entryname ) ) {
						$this->deldir( _cbPathName( $dir . $entryname ) );
					} else {
						$this->unlink( $dir . $entryname );
					}
				}
			}
			$this->closedir( $current_dir );
		}
		@chmod( $dir, 0777 );

		return $this->rmdir( $dir );
	}
}



if (is_callable("jimport")) {
	global $CB_AdminFileFunctions;
	/** @noinspection PhpUnusedParameterInspection */
	$CB_AdminFileFunctions	=	array(	'_constructor'		=>	function ( &$functions ) {
			jimport('joomla.filesystem.file');
			jimport('joomla.filesystem.folder');
			jimport('joomla.filesystem.archive');
			jimport('joomla.filesystem.path');
		},
											 'mkdir'				=>	array( 'JFolder', 'create' ),
											 'rmdir'				=>	array( 'JFolder', 'delete' ),
											 'is_dir'			=>	null,
											 'opendir'			=>	null,
											 'readdir'			=>	null,
											 'closedir'			=>	null,
											 'rename'			=>	function ( $old_name, $new_name ) {
													 if ( is_file( $old_name ) ) {
														 return JFile::move( $old_name, $new_name );
													 } elseif ( is_dir( $old_name ) ) {
														 return JFolder::move( $old_name, $new_name );
													 } else {
														 return false;
													 }
												 },
											 'file_exists'		=>	null,
											 'is_writable'		=>	null,
											 'is_file'			=>	null,
											 'chmod'				=>	function ( $pathname, $mode ) {
													 jimport( 'joomla.client.helper' );
													 $FTPOptions		=	JClientHelper::getCredentials( 'ftp' );
													 if ( $FTPOptions['enabled'] == 1 ) {
														 jimport( 'joomla.client.ftp' );
														 // JFTPClient is Joomla 3.x-only, not in 2.5.
														 $ftp		=	JFTP::getInstance($FTPOptions['host'], $FTPOptions['port'], array(), $FTPOptions['user'], $FTPOptions['pass']);

														 //Translate path to FTP account:
														 $dest		=	JPath::clean(str_replace( JPATH_ROOT, $FTPOptions['root'], $pathname), '/' );
														 return $ftp->chmod( $dest, $mode );
													 } else {
														 return @chmod( $pathname, $mode );
													 }
												 },
											 'chmoddir'			=>	null,
											 'copy'				=>	array( 'JFile', 'copy' ),
											 'copydir'			=>	array( 'JFolder', 'copy' ),
											 'unlink'			=>	array( 'JFile', 'delete' ),
											 'file_put_contents'	=>	array( 'JFile', 'write' ),
											 'move_uploaded_file'=>	array( 'JFile', 'upload' ),
	);
}
