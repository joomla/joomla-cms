<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  FileSystem
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.path');

/**
 * A File handling class
 *
 * @static
 * @package		Joomla.Platform
 * @subpackage	FileSystem
 * @since		11.1
 */
class JFile
{
	/**
	 * Gets the extension of a file name
	 *
	 * @param	string	$file	The file name
	 *
	 * @return	string	The file extension
	 * @since 1.5
	 */
	public static function getExt($file)
	{
		$dot = strrpos($file, '.') + 1;
		return substr($file, $dot);
	}

	/**
	 * Strips the last extension off a file name
	 *
	 * @param	string	$file The file name
	 *
	 * @return	string	The file name without the extension
	 * @since 1.5
	 */
	public static function stripExt($file)
	{
		return preg_replace('#\.[^.]*$#', '', $file);
	}

	/**
	 * Makes file name safe to use
	 *
	 * @param	string	$file	The name of the file [not full path]
	 *
	 * @return	string	The sanitised string
	 * @since	11.1
	 */
	public static function makeSafe($file)
	{
		$regex = array('#(\.){2,}#', '#[^A-Za-z0-9\.\_\- ]#', '#^\.#');
		return preg_replace($regex, '', $file);
	}

	/**
	 * Copies a file
	 *
	 * @param	string	$src	The path to the source file
	 * @param	string	$dest	The path to the destination file
	 * @param	string	$path	An optional base path to prefix to the file names
	 *
	 * @return	boolean	True on success
	 * @since	11.1
	 */
	public static function copy($src, $dest, $path = null, $use_streams=false)
	{
		// Prepend a base path if it exists
		if ($path) {
			$src = JPath::clean($path.DS.$src);
			$dest = JPath::clean($path.DS.$dest);
		}

		//Check src path
		if (!is_readable($src)) {
			JError::raiseWarning(21, JText::sprintf('JLIB_FILESYSTEM_ERROR_JFILE_FIND_COPY', $src));
			return false;
		}

		if ($use_streams) {
			$stream = JFactory::getStream();

			if (!$stream->copy($src, $dest)) {
				JError::raiseWarning(21, JText::sprintf('JLIB_FILESYSTEM_ERROR_JFILE_STREAMS', $src, $dest, $stream->getError()));
				return false;
			}

			return true;
		} else {
			// Initialise variables.
			jimport('joomla.client.helper');
			$FTPOptions = JClientHelper::getCredentials('ftp');

			if ($FTPOptions['enabled'] == 1) {
				// Connect the FTP client
				jimport('joomla.client.ftp');
				$ftp = JFTP::getInstance($FTPOptions['host'], $FTPOptions['port'], null, $FTPOptions['user'], $FTPOptions['pass']);

				// If the parent folder doesn't exist we must create it
				if (!file_exists(dirname($dest))) {
					jimport('joomla.filesystem.folder');
					JFolder::create(dirname($dest));
				}

				//Translate the destination path for the FTP account
				$dest = JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $dest), '/');
				if (!$ftp->store($src, $dest)) {
					// FTP connector throws an error
					return false;
				}
				$ret = true;
			} else {
				if (!@ copy($src, $dest)) {
					JError::raiseWarning(21, JText::_('JLIB_FILESYSTEM_ERROR_COPY_FAILED'));
					return false;
				}
				$ret = true;
			}
			return $ret;
		}
	}

	/**
	 * Delete a file or array of files
	 *
	 * @param	mixed	$file The file name or an array of file names
	 *
	 * @return	boolean	True on success
	 * @since	11.1
	 */
	public static function delete($file)
	{
		// Initialise variables.
		jimport('joomla.client.helper');
		$FTPOptions = JClientHelper::getCredentials('ftp');

		if (is_array($file)) {
			$files = $file;
		} else {
			$files[] = $file;
		}

		// Do NOT use ftp if it is not enabled
		if ($FTPOptions['enabled'] == 1) {
			// Connect the FTP client
			jimport('joomla.client.ftp');
			$ftp = JFTP::getInstance($FTPOptions['host'], $FTPOptions['port'], null, $FTPOptions['user'], $FTPOptions['pass']);
		}

		foreach ($files as $file) {
			$file = JPath::clean($file);

			// Try making the file writeable first. If it's read-only, it can't be deleted
			// on Windows, even if the parent folder is writeable
			@chmod($file, 0777);

			// In case of restricted permissions we zap it one way or the other
			// as long as the owner is either the webserver or the ftp
			if (@unlink($file)) {
				// Do nothing
			} elseif ($FTPOptions['enabled'] == 1) {
				$file = JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $file), '/');
				if (!$ftp->delete($file)) {
					// FTP connector throws an error
					return false;
				}
			} else {
				$filename	= basename($file);
				JError::raiseWarning('SOME_ERROR_CODE', JText::sprintf('JLIB_FILESYSTEM_DELETE_FAILED', $filename));
				return false;
			}
		}

		return true;
	}

	/**
	 * Moves a file
	 *
	 * @param	string	$src	The path to the source file
	 * @param	string	$dest	The path to the destination file
	 * @param	string	$path	An optional base path to prefix to the file names
	 *
	 * @return	boolean	True on success
	 * @since	11.1
	 */
	public static function move($src, $dest, $path = '', $use_streams=false)
	{
		if ($path) {
			$src = JPath::clean($path.DS.$src);
			$dest = JPath::clean($path.DS.$dest);
		}

		//Check src path
		if (!is_readable($src)) { // && !is_writable($src)) { // file may not be writable by php if via ftp!
			return JText::_('JLIB_FILESYSTEM_CANNOT_FIND_SOURCE_FILE');
		}

		if($use_streams) {
			$stream = JFactory::getStream();

			if (!$stream->move($src, $dest)) {
				JError::raiseWarning(21, JText::sprintf('JLIB_FILESYSTEM_ERROR_JFILE_MOVE_STREAMS', $stream->getError()));
				return false;
			}

			return true;
		} else {
			// Initialise variables.
			jimport('joomla.client.helper');
			$FTPOptions = JClientHelper::getCredentials('ftp');

			if ($FTPOptions['enabled'] == 1) {
				// Connect the FTP client
				jimport('joomla.client.ftp');
				$ftp = JFTP::getInstance($FTPOptions['host'], $FTPOptions['port'], null, $FTPOptions['user'], $FTPOptions['pass']);

				//Translate path for the FTP account
				$src	= JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $src), '/');
				$dest	= JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $dest), '/');

				// Use FTP rename to simulate move
				if (!$ftp->rename($src, $dest)) {
					JError::raiseWarning(21, JText::_('JLIB_FILESYSTEM_ERROR_RENAME_FILE'));
					return false;
				}
			} else {
				if (!@ rename($src, $dest)) {
					JError::raiseWarning(21, JText::_('JLIB_FILESYSTEM_ERROR_RENAME_FILE'));
					return false;
				}
			}

			return true;
		}
	}

	/**
	 * Read the contents of a file
	 *
	 * @param	string	$filename	The full file path
	 * @param	boolean	$incpath	Use include path
	 * @param	int		$amount		Amount of file to read
	 * @param	int		$chunksize	Size of chunks to read
	 * @param	int		$offset		Offset of the file
	 *
	 * @return	mixed	Returns file contents or boolean False if failed
	 * @since	11.1
	 */
	public static function read($filename, $incpath = false, $amount = 0, $chunksize = 8192, $offset = 0)
	{
		// Initialise variables.
		$data = null;
		if ($amount && $chunksize > $amount) {
			$chunksize = $amount;
		}

		if (false === $fh = fopen($filename, 'rb', $incpath)) {
			JError::raiseWarning(21, JText::sprintf('JLIB_FILESYSTEM_ERROR_READ_UNABLE_TO_OPEN_FILE', $filename));
			return false;
		}

		clearstatcache();

		if ($offset) {
			fseek($fh, $offset);
		}

		if ($fsize = @ filesize($filename)) {
			if ($amount && $fsize > $amount) {
				$data = fread($fh, $amount);
			} else {
				$data = fread($fh, $fsize);
			}
		} else {
			$data = '';
			$x = 0;
			// While its:
			// 1: Not the end of the file AND
			// 2a: No Max Amount set OR
			// 2b: The length of the data is less than the max amount we want
			while (!feof($fh) && (!$amount || strlen($data) < $amount)) {
				$data .= fread($fh, $chunksize);
			}
		}
		fclose($fh);

		return $data;
	}

	/**
	 * Write contents to a file
	 *
	 * @param	string	$file The full file path
	 * @param	string	$buffer The buffer to write
	 *
	 * @return	boolean True on success
	 * @since	11.1
	 */
	public static function write($file, &$buffer, $use_streams=false)
	{

		// If the destination directory doesn't exist we need to create it
		if (!file_exists(dirname($file))) {
			jimport('joomla.filesystem.folder');
			JFolder::create(dirname($file));
		}

		if ($use_streams) {
			$stream = JFactory::getStream();
			$stream->set('chunksize', (1024 * 1024 * 1024)); // beef up the chunk size to a meg

			if (!$stream->writeFile($file, $buffer)) {
				JError::raiseWarning(21, JText::sprintf('JLIB_FILESYSTEM_ERROR_WRITE_STREAMS', $file, $stream->getError()));
				return false;
			}

			return true;
		} else {
			// Initialise variables.
			jimport('joomla.client.helper');
			$FTPOptions = JClientHelper::getCredentials('ftp');

			if ($FTPOptions['enabled'] == 1) {
				// Connect the FTP client
				jimport('joomla.client.ftp');
				$ftp = JFTP::getInstance($FTPOptions['host'], $FTPOptions['port'], null, $FTPOptions['user'], $FTPOptions['pass']);

				// Translate path for the FTP account and use FTP write buffer to file
				$file = JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $file), '/');
				$ret = $ftp->write($file, $buffer);
			} else {
				$file = JPath::clean($file);
				$ret = is_int(file_put_contents($file, $buffer)) ? true : false;
			}

			return $ret;
		}
	}

	/**
	 * Moves an uploaded file to a destination folder
	 *
	 * @param string $src The name of the php (temporary) uploaded file
	 * @param string $dest The path (including filename) to move the uploaded file to
	 *
	 * @return boolean True on success
	 * @since 1.5
	 */
	public static function upload($src, $dest, $use_streams=false)
	{
		// Ensure that the path is valid and clean
		$dest = JPath::clean($dest);

		// Create the destination directory if it does not exist
		$baseDir = dirname($dest);

		if (!file_exists($baseDir)) {
			jimport('joomla.filesystem.folder');
			JFolder::create($baseDir);
		}

		if($use_streams) {
			$stream = JFactory::getStream();

			if (!$stream->upload($src, $dest)) {
				JError::raiseWarning(21, JText::sprintf('JLIB_FILESYSTEM_ERROR_UPLOAD', $stream->getError()));
				return false;
			}

			return true;
		} else {
			// Initialise variables.
			jimport('joomla.client.helper');
			$FTPOptions = JClientHelper::getCredentials('ftp');
			$ret		= false;

			if ($FTPOptions['enabled'] == 1) {
				// Connect the FTP client
				jimport('joomla.client.ftp');
				$ftp = JFTP::getInstance($FTPOptions['host'], $FTPOptions['port'], null, $FTPOptions['user'], $FTPOptions['pass']);

				//Translate path for the FTP account
				$dest = JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $dest), '/');

				// Copy the file to the destination directory
				if ($ftp->store($src, $dest)) {
					$ftp->chmod($dest, 0777);
					$ret = true;
				} else {
					JError::raiseWarning(21, JText::_('JLIB_FILESYSTEM_ERROR_WARNFS_ERR02'));
				}
			} else {
				if (is_writeable($baseDir) && move_uploaded_file($src, $dest)) { // Short circuit to prevent file permission errors
					if (JPath::setPermissions($dest)) {
						$ret = true;
					} else {
						JError::raiseWarning(21, JText::_('JLIB_FILESYSTEM_ERROR_WARNFS_ERR01'));
					}
				} else {
					JError::raiseWarning(21, JText::_('JLIB_FILESYSTEM_ERROR_WARNFS_ERR02'));
				}
			}

			return $ret;
		}
	}

	/**
	 * Wrapper for the standard file_exists function
	 *
	 * @param	string	$file File path
	 *
	 * @return	boolean	True if path is a file
	 * @since	11.1
	 */
	public static function exists($file)
	{
		return is_file(JPath::clean($file));
	}

	/**
	 * Returns the name, without any path.
	 *
	 * @param	string	$file	File path
	 * @return	string	filename
	 * @since	11.1
	 */
	public static function getName($file)
	{
		$slash = strrpos($file, DS);
		if ($slash !== false) {
			return substr($file, $slash + 1);
		} else {
			return $file;
		}
	}
}
