<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * A File System utility class
 * @package Joomla
 * @since 1.1
 */
class mosFS {
	/**
	 * @param string The name of the archive
	 * @param mixed The name of a single file or an array of files
	 * @param string The compression for the archive
	 * @param string Path to add within the archive
	 * @param string Path to remove within the archive
	 * @param boolean Automatically append the extension for the archive
	 * @param boolean Remove for source files
	 */
	function archive( $archive, $files, $compress='tar', $addPath='', $removePath='', $autoExt=false, $cleanUp=false ) {
		global $mosConfig_absolute_path;

		mosFS::load( '@Tar' );

		if (is_string( $files )) {
			$files = array( $files );
		}
		if ($autoExt) {
			$archive .= '.' . $compress;
		}

		$tar = new Archive_Tar( $archive, $compress );
		$tar->setErrorHandling( PEAR_ERROR_PRINT );
		$tar->createModify( $files, $addPath, $removePath );

		if ($cleanUp) {
			// TODO: remove source files
			//die( 'mosFS::archive todo cleanup option' );
			mosFS::deleteFile( $files );
		}
		return $tar;
	}

	/**
	* @param string A path to create from the base path
	* @param int Directory permissions
	* @return boolean True if successful
	*/
	function autocreatePath( $path='', $mode='0777' ) {
		mosFS::check( $path );
		$path = mosFS::getNativePath( $path, false, true );

		// check if dir exists
		if (file_exists( $path )) {
			return true;
		}

		// set mode
		$origmask = @umask(0);
		$mode = octdec( $mode );

		$parts = explode( DIRECTORY_SEPARATOR, $path );
		$n = count( $parts );
		$ret = true;
		if ($n < 1) {
			$ret = false;
		} else {
			$path = $parts[0];
			for ($i = 1; $i < $n; $i++) {
				$path .= '/' . $parts[$i];
				if (!file_exists( $path )) {
					if (!mkdir( $path, $mode )) {
						$ret = false;
						break;
					}
				}
			}
		}
		@umask( $origmask );

		return $ret;
	}

	/**
	 * Gets the extension of a file name
	 * @param string The file name
	 * @return string
	 */
	function getExt( $file ) {
		$dot = strrpos( $file, '.' ) + 1;
		return substr( $file, $dot );
	}

	/**
	/**
	 * Strips the last extension off a file name
	 * @param string The file name
	 * @return string
	 */
	function stripExt( $file ) {
		return preg_replace( '#\.[^.]*$#', '', $file );
	}

	/**
	 * Checks if a files permissions can be changed
	 * @param string The file path
	 * @return boolean
	 */
	function canCHMOD( $file ) {
		$perms = fileperms( $file );
		if ($perms !== false)
			if (@chmod( $file, $perms ^ 0001 ) ) {
				@chmod( $file, $perms );
				return true;
			}
		return false;
	}

	/**
	 * Checks for snooping outside of the file system root
	 * @param string A file system path to check
	 */
	function check( $path ) {
		if (strpos( $path, '..' ) !== false) {
			mosBackTrace();
			die( 'mosFS::check use of relative paths not permitted' ); // don't translate
		}
		if (strpos( mosFS::getNativePath($path), MOSFS_ROOT ) !== 0) {
			mosBackTrace();
			die( 'mosFS::check snooping out of bounds @ '.$path ); // don't translate
		}
	}

	/**
	* Chmods files and directories recursivel to given permissions
	* @param path The starting file or directory (no trailing slash)
	* @param filemode Integer value to chmod files. NULL = dont chmod files.
	* @param dirmode Integer value to chmod directories. NULL = dont chmod directories.
	* @return TRUE=all succeeded FALSE=one or more chmods failed
	*/
	function CHMOD( $path, $filemode=MOSFS_FILEPEMS, $dirmode=MOSFS_DIRPEMS ) {
		mosFS::check( $path );

		$ret = TRUE;
		if (is_dir($path)) {
			$dh = opendir($path);
			while ($file = readdir($dh)) {
				if ($file != '.' && $file != '..') {
					$fullpath = $path.'/'.$file;
					if (is_dir($fullpath)) {
						if (!mosFS::CHMOD( $fullpath, $filemode, $dirmode )) {
							$ret = FALSE;
						}
					} else {
						if (isset($filemode)) {
							if (!@chmod( $fullpath, $filemode )) {
								$ret = FALSE;
							}
						}
					} // if
				} // if
			} // while
			closedir($dh);
			if (isset($dirmode))
				if (!@chmod($path, $dirmode)) {
					$ret = FALSE;
				}
		} else {
			if (isset($filemode))
				$ret = @chmod($path, $filemode);
		} // if
		return $ret;
	}

	/**
	 * Copies a file
	 * @param string The path to the source file
	 * @param string The path to the destination file
	 * @param string An optional base path to prefix to the file names
	 * @return mixed
	 */
	function copy( $src, $dest, $path = '' ) {
		global $_LANG;

		if ($path) {
			$src = mosFS::getNativePath( $path . $src, false );
			$dest = mosFS::getNativePath( $path . $dest, false );
		}

		mosFS::check( $src );
		mosFS::check( $dest );

   		if (!file_exists( $src )) {
			return $_LANG->_( 'Cannot find source file' );
		}
   		if (!is_writable( $dest )) {
   			if (!is_writable( dirname( $dest ) )) {
				return $_LANG->_( 'Directory unwritable' );
   			}
		}
		if (!@copy( $src, $dest )) {
			return $_LANG->_( 'Copy failed' );
		}
		return true;
	}
	/**
	 * Delete a file
	 * @param mixed The file name or an array of file names
	 * @return boolean  True on success
	 */
	function deleteFile( $file ) {
		if (is_array( $file )) {
			$files = $file;
		} else {
			$files[] = $file;
		}

		$failed = 0;
		foreach ($files as $file) {
			$file = mosFS::getNativePath( $file, false );
			mosFS::check( $file );
			$failed |= !unlink( $file );
		}
		return !$failed;
	}

	/**
	 * Delete a folder
	 * @param mixed The folder name
	 * @return boolean True on success
	 */
	function deleteFolder( $path ) {
		$path = mosFS::getNativePath( $path, false );
		mosFS::check( $path );

		// remove files in folder
		$files = mosFS::listFiles( $path, '.', false, true );
		foreach ($files as $file) {
			mosFS::deleteFile( $file );
		}

		// remove sub-folders
		$folders = mosFS::listFolders( $path, '.', false, true );
		foreach ($folders as $folder) {
			mosFS::deleteFolder( $folder );
		}

		// remove the folders
		return rmdir( $path );
	}

	/** Wrapper for the standard file_exists function
	 * @param string filename relative to installation dir
	 * @return boolean
	 */
	function file_exists( $file ) {
   		$file = mosFS::getNativePath( $file, false );
		return file_exists( $file );
	}
	
	/**
	 * Function to strip additional / or \ in a path name
	 * @param string The path
	 * @param boolean Add trailing slash
	 */
	function getNativePath( $p_path, $p_addtrailingslash=true ) {
		$retval = '';
		$path = trim( $p_path );

		if (empty( $p_path )) {
			$retval = MOSFS_ROOT;
		} else {
			if (MOSFS_ISWIN)	{
				$retval = str_replace( '/', DIRECTORY_SEPARATOR, $p_path );
				// Remove double \\
				$retval = str_replace( '\\\\', DIRECTORY_SEPARATOR, $retval );
			} else {
				$retval = str_replace( '\\', DIRECTORY_SEPARATOR, $p_path );
				// Remove double //
				$retval = str_replace('//',DIRECTORY_SEPARATOR,$retval);
			}
		}
		if ($p_addtrailingslash) {
			if (substr( $retval, -1 ) != DIRECTORY_SEPARATOR) {
				$retval .= DIRECTORY_SEPARATOR;
			}
		}

		return $retval;
	}

	function getPermissions( $path ) {
		$path = mosFS::getNativePath( $path );
   		mosFS::check( $path );
 		$mode = @decoct( @fileperms( $path ) & 0777 );

		if (strlen( $mode ) < 3) {
			return '---------';
		}
		$parsed_mode='';
		for ($i = 0; $i < 3; $i++) {
			// read
			$parsed_mode .= ($mode{$i} & 04) ? "r" : "-";
			// write
			$parsed_mode .= ($mode{$i} & 02) ? "w" : "-";
			// execute
			$parsed_mode .= ($mode{$i} & 01) ? "x" : "-";
		}
		return $parsed_mode;
	}
	/**
	* Utility function to read the files in a directory
	* @param string The file system path
	* @param string A filter for the names
	* @param boolean Recurse search into sub-directories
	* @param boolean True if to prepend the full path to the file name
	* @return array
	*/
	function listFiles( $path, $filter='.', $recurse=false, $fullpath=false  ) {
		$arr = array();
		$path = mosFS::getNativePath( $path, false );
		if (!is_dir( $path )) {
			return $arr;
		}

		// prevent snooping of the file system
		mosFS::check( $path );

		// read the source directory
		$handle = opendir( $path );
		$path .= DIRECTORY_SEPARATOR;
		while ($file = readdir( $handle )) {
			$dir = $path . $file;
			$isDir = is_dir( $dir );
			if ($file <> '.' && $file <> '..') {
				if ($isDir) {
					if ($recurse) {
						$arr2 = mosFS::listFiles( $dir, $filter, $recurse, $fullpath );
						$arr = array_merge( $arr, $arr2 );
					}
				} else {
					if (preg_match( "/$filter/", $file )) {
						if ($fullpath) {
							$arr[] = $path . $file;
						} else {
							$arr[] = $file;
						}
					}
				}
			}
		}
		closedir( $handle );
		asort( $arr );
		return $arr;
	}
	/**
	* Utility function to read the folders in a directory
	* @param string The file system path
	* @param string A filter for the names
	* @param boolean Recurse search into sub-directories
	* @param boolean True if to prepend the full path to the file name
	* @return array
	*/
	function listFolders( $path, $filter='.', $recurse=false, $fullpath=false  ) {
		$arr 	= array();
		$path 	= mosFS::getNativePath( $path, false );
		if (!is_dir( $path )) {
			return $arr;
		}

		// prevent snooping of the file system
		mosFS::check( $path );

		// read the source directory
		$handle = opendir( $path );
		$path 	.= DIRECTORY_SEPARATOR;
		while ( $file = readdir( $handle ) ) {
			$dir 	= $path . $file;
			$isDir 	= is_dir( $dir );
			if ( ( $file <> '.' ) && ( $file <> '..' ) && $isDir ) {
				// removes CVS directores from list
				if ( preg_match( "/$filter/", $file ) && !( preg_match( "/CVS/", $file ) ) ) {
					if ( $fullpath ) {
						$arr[] = $dir;
					} else {
						$arr[] = $file;
					}
				}
				if ( $recurse ) {
					$arr2 = mosFS::listFolders( $dir, $filter, $recurse, $fullpath );
					$arr = array_merge( $arr, $arr2 );
				}
			}
		}
		closedir( $handle );
		asort( $arr );
		return $arr;
	}

	/**
	 * Lists folder in format suitable for tree display
	 */
	function listFolderTree( $path, $filter, $maxLevel=3, $level=0, $parent=0 ) {
		$dirs = array();
		if ($level == 0) {
			$GLOBALS['_mosFS_folder_tree_index'] = 0;
		}

		if ($level < $maxLevel) {
			mosFS::check( $path );

			$folders = mosFS::listFolders( $path, $filter );

			// first path, index foldernames
			for ($i = 0, $n = count( $folders ); $i < $n; $i++) {
				$id = ++$GLOBALS['_mosFS_folder_tree_index'];
				$name = $folders[$i];
				$fullName = mosFS::getNativePath( $path . '/' . $name, false );
				$dirs[] = array(
					'id' => $id,
					'parent' => $parent,
					'name' => $name,
					'fullname' => $fullName,
					'relname' => str_replace( MOSFS_ROOT, '', $fullName )
				);
				$dirs2 = mosFS::listFolderTree( $fullName, $filter, $maxLevel, $level+1, $id );
				$dirs = array_merge( $dirs, $dirs2 );
			}
		}

		return $dirs;
	}

	/**
	 * Makes file name safe to use
	 * @param string The name of the file (not full path)
	 * @return string The sanitised string
	 */
	function makeSafe( $file ) {
		$regex = '#\.\.|[^A-Za-z0-9\.\_\- ]#';
		return preg_replace( $regex, '', $file );
	}
	/**
	 * @param string The full file path
	 * @param string The buffer to read into
	 * @return boolean True on success
	 */
	function read( $file, &$buffer ) {
		mosFS::check( $file );

		if (file_exists( $file )) {
			$buffer = file_get_contents( $file );
			return true;
		}
		return false;
	}

	/**
	 * @param string The full file path
	 * @param string The buffer to write
	 * @return mixed The number of bytes on success, false otherwise
	 */
	function write( $file, $buffer ) {
		mosFS::check( $file );

		if (!is_writable( $file )) {
			if (!is_writable( dirname( $file ))) {
				return false;
			}
		}
		return file_put_contents( $file, $buffer );
	}
	/**
	 * @param string The name of the php (temporary) uploaded file
	 * @param string The name of the file to put in the temp directory
	 * @param string The message to return
	 */
	function uploadFile( $srcFile, $destFile, &$msg ) {
		global $_LANG;

		$srcFile = mosFS::getNativePath( $srcFile, false );
		$destFile = mosFS::getNativePath( $destFile, false );
		mosFS::check( $destFile );

		$baseDir = dirname( $destFile );

		if (file_exists( $baseDir )) {
			if (is_writable( $baseDir )) {
				if (move_uploaded_file( $srcFile, $destFile )) {
					if (mosFS::CHMOD( $destFile )) {
						return true;
					} else {
						$msg = $_LANG->_( 'WARNFS_ERR01' );
					}
				} else {
					$msg = $_LANG->_( 'WARNFS_ERR02' );
				}
			} else {
				$msg = $_LANG->_( 'WARNFS_ERR03' );
			}
		} else {
			$msg = $_LANG->_( 'WARNFS_ERR04' );
		}
		return false;
	}
}

/** boolean True if a Windows based host */
define( 'MOSFS_ISWIN', (substr(PHP_OS, 0, 3) == 'WIN') );

if (!defined( 'MOSFS_ROOT' )) {
	/** string The root directory of the file system in native format */
	define( 'MOSFS_ROOT', mosFS::getNativePath( $mosConfig_absolute_path ) );
}
if (!defined( 'MOSFS_FILEPEMS' )) {
	/** string The default directory permissions */
	define( 'MOSFS_FILEPEMS',  !empty( $mosConfig_fileperms ) ? octdec( $mosConfig_fileperms ) : null );
}
if (!defined( 'MOSFS_DIRPEMS' )) {
	/** string The default directory permissions */
	define( 'MOSFS_DIRPEMS',  !empty( $mosConfig_dirperms ) ? octdec( $mosConfig_dirperms ) : null );
}

/**
* @param string An existing base path
* @param string A path to create from the base path
* @param int Directory permissions
* @return boolean True if successful
*/
function mosMakePath($base, $path='', $mode = NULL)
{
	global $mosConfig_dirperms;

	// convert windows paths
	$path = str_replace( '\\', '/', $path );
	$path = str_replace( '//', '/', $path );

	// check if dir exists
	if (file_exists( $base . $path )) return true;

	// set mode
	$origmask = NULL;
	if (isset($mode)) {
		$origmask = @umask(0);
	} else {
		if ($mosConfig_dirperms=='') {
			// rely on umask
			$mode = 0777;
		} else {
			$origmask = @umask(0);
			$mode = octdec($mosConfig_dirperms);
		} // if
	} // if

	$parts = explode( '/', $path );
	$n = count( $parts );
	$ret = true;
	if ($n < 1) {
		$ret = @mkdir($base, $mode);
	} else {
		$path = $base;
		for ($i = 0; $i < $n; $i++) {
			$path .= $parts[$i] . '/';
			if (!file_exists( $path )) {
				if (!@mkdir( $path, $mode )) {
					$ret = false;
					break;
				}
			}
		}
	}
	if (isset($origmask)) @umask($origmask);
	return $ret;
}

?>
