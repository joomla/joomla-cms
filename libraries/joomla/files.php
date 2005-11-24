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

/** boolean True if a Windows based host */
define( 'JPATH_ISWIN', (substr(PHP_OS, 0, 3) == 'WIN') );

if(!defined('DS')) {
	/** string Shortcut for the DIRECTORY_SEPERATOR define */
	define('DS', DIRECTORY_SEPERATOR);
}

if (!defined( 'JPATH_ROOT' )) {
	/** string The root directory of the file system in native format */
	define( 'JPATH_ROOT', JPath::clean( $mosConfig_absolute_path ) );
}
if (!defined( 'JPATH_FILEPEMS' )) {
	/** string The default directory permissions */
	define( 'JPATH_FILEPEMS',  !empty( $mosConfig_fileperms ) ? octdec( $mosConfig_fileperms ) : '0644' );
}
if (!defined( 'JPATH_DIRPEMS' )) {
	/** string The default directory permissions */
	define( 'JPATH_DIRPEMS',  !empty( $mosConfig_dirperms ) ? octdec( $mosConfig_dirperms ) : '0777' );
}

/**
 * A File handling class
 * 
 * @package Joomla
 * @static
 * @since 1.1
 */
class JFile 
{
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
	 * Makes file name safe to use
	 * @param string The name of the file (not full path)
	 * @return string The sanitised string
	 */
	function makeSafe( $file ) {
		$regex = '#\.\.[^A-Za-z0-9\.\_\- ]#';
		return preg_replace( $regex, '', $file );
	}
	
	/**
	 * Copies a file
	 * @param string The path to the source file
	 * @param string The path to the destination file
	 * @param string An optional base path to prefix to the file names
	 * @return mixed
	 */
	function copy( $src, $dest, $path = '' ) {

		if ($path) {
			$src = JPath::clean( $path . $src, false );
			$dest = JPath::clean( $path . $dest, false );
		}

		JPath::check( $src );
		JPath::check( $dest );

   		if (!file_exists( $src )) {
			return JText::_( 'Cannot find source file' );
		}
   		if (!is_writable( $dest )) {
   			if (!is_writable( dirname( $dest ) )) {
				return JText::_( 'Directory unwritable' );
   			}
		}
		if (!@copy( $src, $dest )) {
			return JText::_( 'Copy failed' );
		}
		return true;
	}
	
	/**
	 * Delete a file
	 * @param mixed The file name or an array of file names
	 * @return boolean  True on success
	 */
	function delete( $file ) {
		if (is_array( $file )) {
			$files = $file;
		} else {
			$files[] = $file;
		}

		$failed = 0;
		foreach ($files as $file) {
			$file = JPath::clean( $file, false );
			JPath::check( $file );
			$failed |= !unlink( $file );
		}
		return !$failed;
	}
	
	/**
	 * @param string The full file path
	 * @param string The buffer to read into
	 * @return boolean True on success
	 */
	function read( $file, &$buffer ) {
		JPath::check( $file );

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
		JPath::check( $file );

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
	function upload( $srcFile, $destFile, &$msg ) {
	
		$srcFile = JPath::clean( $srcFile, false );
		$destFile = JPath::clean( $destFile, false );
		JPath::check( $destFile );

		$baseDir = dirname( $destFile );

		if (file_exists( $baseDir )) {
			if (is_writable( $baseDir )) {
				if (move_uploaded_file( $srcFile, $destFile )) {
					if (JPath::setPermissions( $destFile )) {
						return true;
					} else {
						$msg = JText::_( 'WARNFS_ERR01' );
					}
				} else {
					$msg = JText::_( 'WARNFS_ERR02' );
				}
			} else {
				$msg = JText::_( 'WARNFS_ERR03' );
			}
		} else {
			$msg = JText::_( 'WARNFS_ERR04' );
		}
		return false;
	}
	
	/** Wrapper for the standard file_exists function
	 * @param string filename relative to installation dir
	 * @return boolean
	 */
	function exists( $file ) {
   		$file = JPath::clean( $file, false );
		return is_file( $file );
	}
}

/**
 * A Folder handling class
 * 
 * @package Joomla
 * @static
 * @since 1.1
 */
class JFolder
{
	/**
	* @param string A path to create from the base path
	* @param int Directory permissions
	* @return boolean True if successful
	*/
	function create( $path='', $mode=JPATH_DIRPEMS ) {
		JPath::check( $path );
		$path = JPath::clean( $path, false, true );

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
	 * Delete a folder
	 * @param mixed The folder name
	 * @return boolean True on success
	 */
	function delete( $path ) {
		$path = JPath::clean( $path, false );
		JPath::check( $path );

		// remove files in folder
		$files = JFolder::files( $path, '.', false, true );
		foreach ($files as $file) {
			JFile::delete( $file );
		}

		// remove sub-folders
		$folders = JFolder::folders( $path, '.', false, true );
		foreach ($folders as $folder) {
			JFolder::delete( $folder );
		}

		// remove the folders
		return rmdir( $path );
	}
	
	/** Wrapper for the standard file_exists function
	 * @param string filename relative to installation dir
	 * @return boolean
	 */
	function exists( $path ) {
   		$path = JPath::clean( $path, false );
		return is_dir( $path );
	}
	
	/**
	* Utility function to read the files in a directory
	* @param string The file system path
	* @param string A filter for the names
	* @param boolean Recurse search into sub-directories
	* @param boolean True if to prepend the full path to the file name
	* @return array
	*/
	function files( $path, $filter='.', $recurse=false, $fullpath=false  ) {
		$arr = array();
		$path = JPath::clean( $path, false );
		if (!is_dir( $path )) {
			return $arr;
		}

		// prevent snooping of the file system
		//JPath::check( $path );

		// read the source directory
		$handle = opendir( $path );
		$path .= DIRECTORY_SEPARATOR;
		while ($file = readdir( $handle )) {
			$dir = $path . $file;
			$isDir = is_dir( $dir );
			if ($file <> '.' && $file <> '..') {
				if ($isDir) {
					if ($recurse) {
						$arr2 = JFolder::files( $dir, $filter, $recurse, $fullpath );
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
	function folders( $path, $filter='.', $recurse=false, $fullpath=false  ) {
		$arr 	= array();
		$path 	= JPath::clean( $path, false );
		if (!is_dir( $path )) {
			return $arr;
		}

		// prevent snooping of the file system
		//mosFS::check( $path );

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
					$arr2 = JFolder::folders( $dir, $filter, $recurse, $fullpath );
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
			$GLOBALS['_JFolder_folder_tree_index'] = 0;
		}

		if ($level < $maxLevel) {
			JPath::check( $path );

			$folders = JFolder::folders( $path, $filter );

			// first path, index foldernames
			for ($i = 0, $n = count( $folders ); $i < $n; $i++) {
				$id = ++$GLOBALS['_JFolder_folder_tree_index'];
				$name = $folders[$i];
				$fullName = JPath::clean( $path . '/' . $name, false );
				$dirs[] = array(
					'id' => $id,
					'parent' => $parent,
					'name' => $name,
					'fullname' => $fullName,
					'relname' => str_replace( JPATH_ROOT, '', $fullName )
				);
				$dirs2 = JFolder::listFolderTree( $fullName, $filter, $maxLevel, $level+1, $id );
				$dirs = array_merge( $dirs, $dirs2 );
			}
		}

		return $dirs;
	}
}

/**
 * An Archive handling class
 * 
 * @package Joomla
 * @static
 * @since 1.1
 */
class JArchive
{
	/**
	 * @param string The name of the archive
	 * @param mixed The name of a single file or an array of files
	 * @param string The compression for the archive
	 * @param string Path to add within the archive
	 * @param string Path to remove within the archive
	 * @param boolean Automatically append the extension for the archive
	 * @param boolean Remove for source files
	 */
	function create( $archive, $files, $compress='tar', $addPath='', $removePath='', $autoExt=false, $cleanUp=false ) {
	
		jimport('archive.Tar');

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
			JFile::delete( $files );
		}
		return $tar;
	}
}

/**
 * A Path handling class
 * @package Joomla
 * @since 1.1
 */
class JPath {

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
	* Chmods files and directories recursivel to given permissions
	* @param path The starting file or directory (no trailing slash)
	* @param filemode Integer value to chmod files. NULL = dont chmod files.
	* @param dirmode Integer value to chmod directories. NULL = dont chmod directories.
	* @return TRUE=all succeeded FALSE=one or more chmods failed
	*/
	function setPermissions( $path, $filemode=JPATH_FILEPEMS, $dirmode=JPATH_DIRPEMS ) {
		//mosFS::check( $path );

		$ret = TRUE;
		if (is_dir($path)) {
			$dh = opendir($path);
			while ($file = readdir($dh)) {
				if ($file != '.' && $file != '..') {
					$fullpath = $path.'/'.$file;
					if (is_dir($fullpath)) {
						if (!JPath::setPermissions( $fullpath, $filemode, $dirmode )) {
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
	
	function getPermissions( $path ) {
		$path = JPath::clean( $path );
   		JPath::check( $path );
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
	 * Checks for snooping outside of the file system root
	 * @param string A file system path to check
	 */
	function check( $path ) {
		if (strpos( $path, '..' ) !== false) {
			mosBackTrace();
			die( 'JPath::check use of relative paths not permitted' ); // don't translate
		}
		if (strpos( JPath::clean($path), JPath::clean(JPATH_ROOT) ) !== 0) {
			mosBackTrace();
			die( 'JPath::check snooping out of bounds @ '.$path ); // don't translate
		}
	}

	/**
	 * Function to strip additional / or \ in a path name
	 * @param string The path
	 * @param boolean Add trailing slash
	 */
	function clean( $p_path, $p_addtrailingslash=true ) {
		$retval = '';
		$path = trim( $p_path );

		if (empty( $p_path )) {
			$retval = JPATH_ROOT;
		} else {
			if (JPATH_ISWIN)	{
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
}

?>
