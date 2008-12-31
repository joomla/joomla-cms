<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	FileSystem
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// No direct access
defined('JPATH_BASE') or die();

jimport('joomla.filesystem.path');
jimport('joomla.filesystem.filesystem');

/**
 * A File handling class
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	FileSystem
 * @since		1.5
 */
abstract class JFile
{
	private static $filesystem = null;

	protected static function &getFileSystem() {
		if(!is_object(JFile::$filesystem)) {
			JFile::$filesystem = JFileSystem::getInstance();
		}
		return JFile::$filesystem;
	}

	/**
	 * Gets the extension of a file name
	 *
	 * @param string $file The file name
	 * @return string The file extension
	 * @since 1.5
	 */
	public static function getExt($file) {
		$dot = strrpos($file, '.');
		return $dot === false ? '' : substr($file, $dot + 1);
	}

	/**
	 * Strips the last extension off a file name
	 *
	 * @param string $file The file name
	 * @return string The file name without the extension
	 * @since 1.5
	 */
	public static function stripExt($file) {
		return preg_replace('#\.[^.]*$#', '', $file);
	}

	/**
	 * Makes file name safe to use
	 *
	 * @param string $file The name of the file [not full path]
	 * @return string The sanitised string
	 * @since 1.5
	 */
	public static function makeSafe($file) {
		$regex = array('#(\.){2,}#', '#[^A-Za-z0-9\.\_\- ]#', '#^\.#');
		return preg_replace($regex, '', $file);
	}

	/**
	 * Copies a file
	 *
	 * @param string $src The path to the source file
	 * @param string $dest The path to the destination file
	 * @param string $path An optional base path to prefix to the file names
	 * @return boolean True on success
	 * @since 1.5
	 */
	public static function copy($src, $dest, $path = null)
	{
		$backend = JFile::getFileSystem();
		// Prepend a base path if it exists
		if ($path) {
			$src = JPath::clean($path.DS.$src);
			$dest = JPath::clean($path.DS.$dest);
		}

		//Check src path
		if (!$backend->isReadable($src)) {
			JError::raiseWarning(21, 'JFile::copy: '.JText::_('Cannot find or read file' . ": '$src'"));
			return false;
		}
		if (!$backend->copy($src, $dest)) {
			JError::raiseWarning(21, JText::_('Copy failed'));
			return false;
		}
		return true;
	}

	/**
	 * Delete a file or array of files
	 *
	 * @param mixed $file The file name or an array of file names
	 * @return boolean  True on success
	 * @since 1.5
	 */
	public static function delete($file)
	{
		$backend = JFile::getFileSystem();

		if (is_array($file)) {
			$files = $file;
		} else {
			$files[] = $file;
		}

		foreach ($files as $file)
		{
			$file = JPath::clean($file);

			// Try making the file writeable first. If it's read-only, it can't be deleted
			// on Windows, even if the parent folder is writeable
			$backend->chmod($file, 0777);

			// In case of restricted permissions we zap it one way or the other
			// as long as the owner is either the webserver or the ftp
			if(!$backend->delete($file)) {
				$filename	= basename($file);
				JError::raiseWarning('SOME_ERROR_CODE', JText::_('Delete failed') . ": '$filename'");
				return false;
			}
		}

		return true;
	}

	/**
	 * Moves a file
	 *
	 * @param string $src The path to the source file
	 * @param string $dest The path to the destination file
	 * @param string $path An optional base path to prefix to the file names
	 * @return boolean True on success
	 * @since 1.5
	 */
	public static function move($src, $dest, $path = '')
	{
		$backend = JFile::getFileSystem();

		if ($path) {
			$src = JPath::clean($path.DS.$src);
			$dest = JPath::clean($path.DS.$dest);
		}

		//Check src path
		if (!$backend->isReadable($src) && !$backend->isWritable($src)) {
			return JText::_('Cannot find source file');
		}

		if (!$backend->rename($src, $dest)) {
			JError::raiseWarning(21, JText::_('Rename failed'));
			return false;
		}
		return true;
	}

	/**
	 * Read the contents of a file
	 *
	 * @param string $filename The full file path
	 * @param boolean $incpath Use include path
	 * @param int $amount Amount of file to read
	 * @param int $chunksize Size of chunks to read
	 * @param int $offset Offset of the file
	 * @return mixed Returns file contents or boolean False if failed
	 * @since 1.5
	 */
	public static function read($filename, $incpath = false, $amount = 0, $chunksize = 8192, $offset = 0)
	{
		$backend = JFile::getFileSystem();
		return $backend->read($filename, $incpath, $amount, $offset);
	}

	/**
	 * Write contents to a file
	 *
	 * @param string $file The full file path
	 * @param string $buffer The buffer to write
	 * @return boolean True on success
	 * @since 1.5
	 */
	public static function write($file, &$buffer)
	{
		$backend = JFile::getFileSystem();

		// If the destination directory doesn't exist we need to create it
		if (!$backend->exists(dirname($file))) {
			jimport('joomla.filesystem.folder');
			JFolder::create(dirname($file));
		}

		$file = JPath::clean($file);
		$ret = $backend->write($file, $buffer);
		return $ret;
	}

	/**
	 * Moves an uploaded file to a destination folder
	 *
	 * @param string $src The name of the php (temporary) uploaded file
	 * @param string $dest The path (including filename) to move the uploaded file to
	 * @return boolean True on success
	 * @since 1.5
	 */
	public static function upload($src, $dest)
	{
		$backend = JFile::getFileSystem();
		$ret		= false;

		// Ensure that the path is valid and clean
		$dest = JPath::clean($dest);

		// Create the destination directory if it does not exist
		$baseDir = dirname($dest);
		if (!$backend->exists($baseDir)) {
			jimport('joomla.filesystem.folder');
			JFolder::create($baseDir);
		}

		// Copy the file to the destination directory
		if ($backend->rename($src, $dest)) {
			$backend->chmod($dest, 0777);
			$ret = true;
		} else {
			JError::raiseWarning(21, JText::_('WARNFS_ERR02'));
		}
		return $ret;
	}

	/**
	 * Wrapper for the standard file_exists function
	 *
	 * @param string $file File path
	 * @return boolean True if path is a file
	 * @since 1.5
	 */
	public static function exists($file)
	{
		$backend = JFile::getFileSystem();
		return $backend->exists(JPath::clean($file));
	}

	/**
	 * Returns the name, sans any path
	 *
	 * param string $file File path
	 * @return string filename
	 * @since 1.5
	 */
	public static function getName($file) {
		$slash = strrpos($file, DS) + 1;
		return substr($file, $slash);
	}
}
