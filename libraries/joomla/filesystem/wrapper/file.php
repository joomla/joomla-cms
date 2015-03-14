<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Filesystem
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.file');

/**
 * Wrapper class for JFile
 *
 * @package     Joomla.Platform
 * @subpackage  Filesystem
 * @since       3.4
 */
class JFilesystemWrapperFile
{
	/**
	 * Helper wrapper method for getExt
	 *
	 * @param   string  $file  The file name.
	 *
	 * @return  string  The file extension.
	 *
	 * @see     JFile::getExt()
	 * @since   3.4
	 */
	public function getExt($file)
	{
		return JFile::getExt($file);
	}

	/**
	 * Helper wrapper method for stripExt
	 *
	 * @param   string  $file  The file name.
	 *
	 * @return  string  The file name without the extension.
	 *
	 * @see     JFile::stripExt()
	 * @since   3.4
	 */
	public function stripExt($file)
	{
		return JFile::stripExt($file);
	}

	/**
	 * Helper wrapper method for makeSafe
	 *
	 * @param   string  $file  The name of the file [not full path].
	 *
	 * @return  string  The sanitised string.
	 *
	 * @see     JFile::makeSafe()
	 * @since   3.4
	 */
	public function makeSafe($file)
	{
		return JFile::makeSafe($file);
	}

	/**
	 * Helper wrapper method for copy
	 *
	 * @param   string   $src          The path to the source file.
	 * @param   string   $dest         The path to the destination file.
	 * @param   string   $path         An optional base path to prefix to the file names.
	 * @param   boolean  $use_streams  True to use streams.
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFile::copy()
	 * @since   3.4
	 */
	public function copy($src, $dest, $path = null, $use_streams = false)
	{
		return JFile::copy($src, $dest, $path, $use_streams);
	}

	/**
	 * Helper wrapper method for delete
	 *
	 * @param   mixed  $file  The file name or an array of file names
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFile::delete()
	 * @since   3.4
	 */
	public function delete($file)
	{
		return JFile::delete($file);
	}

	/**
	 * Helper wrapper method for move
	 *
	 * @param   string   $src          The path to the source file.
	 * @param   string   $dest         The path to the destination file.
	 * @param   string   $path         An optional base path to prefix to the file names.
	 * @param   boolean  $use_streams  True to use streams.
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFile::move()
	 * @since   3.4
	 */
	public function move($src, $dest, $path = '', $use_streams = false)
	{
		return JFile::move($src, $dest, $path, $use_streams);
	}

	/**
	 * Helper wrapper method for read
	 *
	 * @param   string   $filename   The full file path.
	 * @param   boolean  $incpath    Use include path.
	 * @param   integer  $amount     Amount of file to read.
	 * @param   integer  $chunksize  Size of chunks to read.
	 * @param   integer  $offset     Offset of the file.
	 *
	 * @return mixed  Returns file contents or boolean False if failed.
	 *
	 * @see     JFile::read()
	 * @since   3.4
	 */
	public function read($filename, $incpath = false, $amount = 0, $chunksize = 8192, $offset = 0)
	{
		return JFile::read($filename, $incpath, $amount, $chunksize, $offset);
	}

	/**
	 * Helper wrapper method for write
	 *
	 * @param   string   $file         The full file path.
	 * @param   string   &$buffer      The buffer to write.
	 * @param   boolean  $use_streams  Use streams.
	 *
	 * @return boolean  True on success.
	 *
	 * @see     JFile::write()
	 * @since   3.4
	 */
	public function write($file, &$buffer, $use_streams = false)
	{
		return JFile::write($file, $buffer, $use_streams);
	}

	/**
	 * Helper wrapper method for upload
	 *
	 * @param   string   $src          The name of the php (temporary) uploaded file.
	 * @param   string   $dest         The path (including filename) to move the uploaded file to.
	 * @param   boolean  $use_streams  True to use streams.
	 *
	 * @return boolean  True on success.
	 *
	 * @see     JFile::upload()
	 * @since   3.4
	 */
	public function upload($src, $dest, $use_streams = false)
	{
		return JFile::upload($src, $dest, $use_streams);
	}

	/**
	 * Helper wrapper method for exists
	 *
	 * @param   string  $file  File path.
	 *
	 * @return boolean  True if path is a file.
	 *
	 * @see     JFile::exists()
	 * @since   3.4
	 */
	public function exists($file)
	{
		return JFile::exists($file);
	}

	/**
	 * Helper wrapper method for getName
	 *
	 * @param   string  $file  File path.
	 *
	 * @return string  filename.
	 *
	 * @see     JFile::getName()
	 * @since   3.4
	 */
	public function getName($file)
	{
		return JFile::getName($file);
	}
}
