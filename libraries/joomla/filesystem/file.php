<?php
/**
 * @package     Joomla.Platform
 * @subpackage  FileSystem
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * A File handling class
 *
 * @since  11.1
 */
class JFile
{
	/**
	 * Gets the extension of a file name
	 *
	 * @param   string  $file  The file name
	 *
	 * @return  string  The file extension
	 *
	 * @since   11.1
	 */
	public static function getExt($file)
	{
		$dot = strrpos($file, '.') + 1;

		return substr($file, $dot);
	}

	/**
	 * Strips the last extension off of a file name
	 *
	 * @param   string  $file  The file name
	 *
	 * @return  string  The file name without the extension
	 *
	 * @since   11.1
	 */
	public static function stripExt($file)
	{
		return preg_replace('#\.[^.]*$#', '', $file);
	}

	/**
	 * Makes file name safe to use
	 *
	 * @param   string  $file  The name of the file [not full path]
	 *
	 * @return  string  The sanitised string
	 *
	 * @since   11.1
	 */
	public static function makeSafe($file)
	{
		// Remove any trailing dots, as those aren't ever valid file names.
		$file = rtrim($file, '.');

		$regex = array('#(\.){2,}#', '#[^A-Za-z0-9\.\_\- ]#', '#^\.#');

		return trim(preg_replace($regex, '', $file));
	}

	/**
	 * Copies a file
	 *
	 * @param   string   $src          The path to the source file
	 * @param   string   $dest         The path to the destination file
	 * @param   string   $path         An optional base path to prefix to the file names
	 * @param   boolean  $use_streams  True to use streams
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public static function copy($src, $dest, $path = null, $use_streams = false)
	{
		$pathObject = new JFilesystemWrapperPath;

		// Prepend a base path if it exists
		if ($path)
		{
			$src = $pathObject->clean($path . '/' . $src);
			$dest = $pathObject->clean($path . '/' . $dest);
		}

		// Check src path
		if (!is_readable($src))
		{
			JLog::add(JText::sprintf('JLIB_FILESYSTEM_ERROR_JFILE_FIND_COPY', $src), JLog::WARNING, 'jerror');

			return false;
		}

		if ($use_streams)
		{
			$stream = JFactory::getStream();

			if (!$stream->copy($src, $dest))
			{
				JLog::add(JText::sprintf('JLIB_FILESYSTEM_ERROR_JFILE_STREAMS', $src, $dest, $stream->getError()), JLog::WARNING, 'jerror');

				return false;
			}

			return true;
		}
		else
		{
			$FTPOptions = JClientHelper::getCredentials('ftp');

			if ($FTPOptions['enabled'] == 1)
			{
				// Connect the FTP client
				$ftp = JClientFtp::getInstance($FTPOptions['host'], $FTPOptions['port'], array(), $FTPOptions['user'], $FTPOptions['pass']);

				// If the parent folder doesn't exist we must create it
				if (!file_exists(dirname($dest)))
				{
					$folderObject = new JFilesystemWrapperFolder;
					$folderObject->create(dirname($dest));
				}

				// Translate the destination path for the FTP account
				$dest = $pathObject->clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $dest), '/');

				if (!$ftp->store($src, $dest))
				{
					// FTP connector throws an error
					return false;
				}

				$ret = true;
			}
			else
			{
				if (!@ copy($src, $dest))
				{
					JLog::add(JText::sprintf('JLIB_FILESYSTEM_ERROR_COPY_FAILED_ERR01', $src, $dest), JLog::WARNING, 'jerror');

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
	 * @param   mixed  $file  The file name or an array of file names
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public static function delete($file)
	{
		$FTPOptions = JClientHelper::getCredentials('ftp');
		$pathObject = new JFilesystemWrapperPath;

		if (is_array($file))
		{
			$files = $file;
		}
		else
		{
			$files[] = $file;
		}

		// Do NOT use ftp if it is not enabled
		if ($FTPOptions['enabled'] == 1)
		{
			// Connect the FTP client
			$ftp = JClientFtp::getInstance($FTPOptions['host'], $FTPOptions['port'], array(), $FTPOptions['user'], $FTPOptions['pass']);
		}

		foreach ($files as $file)
		{
			$file = $pathObject->clean($file);

			if (!is_file($file))
			{
				continue;
			}

			// Try making the file writable first. If it's read-only, it can't be deleted
			// on Windows, even if the parent folder is writable
			@chmod($file, 0777);

			// In case of restricted permissions we zap it one way or the other
			// as long as the owner is either the webserver or the ftp
			if (@unlink($file))
			{
				// Do nothing
			}
			elseif ($FTPOptions['enabled'] == 1)
			{
				$file = $pathObject->clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $file), '/');

				if (!$ftp->delete($file))
				{
					// FTP connector throws an error

					return false;
				}
			}
			else
			{
				$filename = basename($file);
				JLog::add(JText::sprintf('JLIB_FILESYSTEM_DELETE_FAILED', $filename), JLog::WARNING, 'jerror');

				return false;
			}
		}

		return true;
	}

	/**
	 * Moves a file
	 *
	 * @param   string   $src          The path to the source file
	 * @param   string   $dest         The path to the destination file
	 * @param   string   $path         An optional base path to prefix to the file names
	 * @param   boolean  $use_streams  True to use streams
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public static function move($src, $dest, $path = '', $use_streams = false)
	{
		$pathObject = new JFilesystemWrapperPath;

		if ($path)
		{
			$src = $pathObject->clean($path . '/' . $src);
			$dest = $pathObject->clean($path . '/' . $dest);
		}

		// Check src path
		if (!is_readable($src))
		{
			JLog::add(JText::_('JLIB_FILESYSTEM_CANNOT_FIND_SOURCE_FILE'), JLog::WARNING, 'jerror');

			return false;
		}

		if ($use_streams)
		{
			$stream = JFactory::getStream();

			if (!$stream->move($src, $dest))
			{
				JLog::add(JText::sprintf('JLIB_FILESYSTEM_ERROR_JFILE_MOVE_STREAMS', $stream->getError()), JLog::WARNING, 'jerror');

				return false;
			}

			return true;
		}
		else
		{
			$FTPOptions = JClientHelper::getCredentials('ftp');

			if ($FTPOptions['enabled'] == 1)
			{
				// Connect the FTP client
				$ftp = JClientFtp::getInstance($FTPOptions['host'], $FTPOptions['port'], array(), $FTPOptions['user'], $FTPOptions['pass']);

				// Translate path for the FTP account
				$src = $pathObject->clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $src), '/');
				$dest = $pathObject->clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $dest), '/');

				// Use FTP rename to simulate move
				if (!$ftp->rename($src, $dest))
				{
					JLog::add(JText::_('JLIB_FILESYSTEM_ERROR_RENAME_FILE'), JLog::WARNING, 'jerror');

					return false;
				}
			}
			else
			{
				if (!@ rename($src, $dest))
				{
					JLog::add(JText::_('JLIB_FILESYSTEM_ERROR_RENAME_FILE'), JLog::WARNING, 'jerror');

					return false;
				}
			}

			return true;
		}
	}

	/**
	 * Read the contents of a file
	 *
	 * @param   string   $filename   The full file path
	 * @param   boolean  $incpath    Use include path
	 * @param   integer  $amount     Amount of file to read
	 * @param   integer  $chunksize  Size of chunks to read
	 * @param   integer  $offset     Offset of the file
	 *
	 * @return  mixed  Returns file contents or boolean False if failed
	 *
	 * @since   11.1
	 * @deprecated  13.3 (Platform) & 4.0 (CMS) - Use the native file_get_contents() instead.
	 */
	public static function read($filename, $incpath = false, $amount = 0, $chunksize = 8192, $offset = 0)
	{
		JLog::add(__METHOD__ . ' is deprecated. Use native file_get_contents() syntax.', JLog::WARNING, 'deprecated');

		$data = null;

		if ($amount && $chunksize > $amount)
		{
			$chunksize = $amount;
		}

		if (false === $fh = fopen($filename, 'rb', $incpath))
		{
			JLog::add(JText::sprintf('JLIB_FILESYSTEM_ERROR_READ_UNABLE_TO_OPEN_FILE', $filename), JLog::WARNING, 'jerror');

			return false;
		}

		clearstatcache();

		if ($offset)
		{
			fseek($fh, $offset);
		}

		if ($fsize = @ filesize($filename))
		{
			if ($amount && $fsize > $amount)
			{
				$data = fread($fh, $amount);
			}
			else
			{
				$data = fread($fh, $fsize);
			}
		}
		else
		{
			$data = '';

			/*
			 * While it's:
			 * 1: Not the end of the file AND
			 * 2a: No Max Amount set OR
			 * 2b: The length of the data is less than the max amount we want
			 */
			while (!feof($fh) && (!$amount || strlen($data) < $amount))
			{
				$data .= fread($fh, $chunksize);
			}
		}

		fclose($fh);

		return $data;
	}

	/**
	 * Write contents to a file
	 *
	 * @param   string   $file         The full file path
	 * @param   string   &$buffer      The buffer to write
	 * @param   boolean  $use_streams  Use streams
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public static function write($file, &$buffer, $use_streams = false)
	{
		@set_time_limit(ini_get('max_execution_time'));

		// If the destination directory doesn't exist we need to create it
		if (!file_exists(dirname($file)))
		{
			$folderObject = new JFilesystemWrapperFolder;

			if ($folderObject->create(dirname($file)) == false)
			{
				return false;
			}
		}

		if ($use_streams)
		{
			$stream = JFactory::getStream();

			// Beef up the chunk size to a meg
			$stream->set('chunksize', (1024 * 1024));

			if (!$stream->writeFile($file, $buffer))
			{
				JLog::add(JText::sprintf('JLIB_FILESYSTEM_ERROR_WRITE_STREAMS', $file, $stream->getError()), JLog::WARNING, 'jerror');

				return false;
			}

			return true;
		}
		else
		{
			$FTPOptions = JClientHelper::getCredentials('ftp');
			$pathObject = new JFilesystemWrapperPath;

			if ($FTPOptions['enabled'] == 1)
			{
				// Connect the FTP client
				$ftp = JClientFtp::getInstance($FTPOptions['host'], $FTPOptions['port'], array(), $FTPOptions['user'], $FTPOptions['pass']);

				// Translate path for the FTP account and use FTP write buffer to file
				$file = $pathObject->clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $file), '/');
				$ret = $ftp->write($file, $buffer);
			}
			else
			{
				$file = $pathObject->clean($file);
				$ret = is_int(file_put_contents($file, $buffer)) ? true : false;
			}

			return $ret;
		}
	}

	/**
	 * Append contents to a file
	 *
	 * @param   string   $file         The full file path
	 * @param   string   &$buffer      The buffer to write
	 * @param   boolean  $use_streams  Use streams
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.6.0
	 */
	public static function append($file, &$buffer, $use_streams = false)
	{
		@set_time_limit(ini_get('max_execution_time'));

		// If the file doesn't exist, just write instead of append
		if (!file_exists($file))
		{
			return self::write($file, $buffer, $use_streams);
		}

		if ($use_streams)
		{
			$stream = JFactory::getStream();

			// Beef up the chunk size to a meg
			$stream->set('chunksize', (1024 * 1024));

			if ($stream->open($file, 'ab') && $stream->write($buffer) && $stream->close())
			{
				return true;
			}

			JLog::add(JText::sprintf('JLIB_FILESYSTEM_ERROR_WRITE_STREAMS', $file, $stream->getError()), JLog::WARNING, 'jerror');

			return false;
		}
		else
		{
			// Initialise variables.
			$FTPOptions = JClientHelper::getCredentials('ftp');

			if ($FTPOptions['enabled'] == 1)
			{
				// Connect the FTP client
				jimport('joomla.client.ftp');
				$ftp = JFTP::getInstance($FTPOptions['host'], $FTPOptions['port'], null, $FTPOptions['user'], $FTPOptions['pass']);

				// Translate path for the FTP account and use FTP write buffer to file
				$file = JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $file), '/');
				$ret = $ftp->append($file, $buffer);
			}
			else
			{
				$file = JPath::clean($file);
				$ret = is_int(file_put_contents($file, $buffer, FILE_APPEND));
			}

			return $ret;
		}
	}

	/**
	 * Moves an uploaded file to a destination folder
	 *
	 * @param   string   $src              The name of the php (temporary) uploaded file
	 * @param   string   $dest             The path (including filename) to move the uploaded file to
	 * @param   boolean  $use_streams      True to use streams
	 * @param   boolean  $allow_unsafe     Allow the upload of unsafe files
	 * @param   boolean  $safeFileOptions  Options to JFilterInput::isSafeFile
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public static function upload($src, $dest, $use_streams = false, $allow_unsafe = false, $safeFileOptions = array())
	{
		if (!$allow_unsafe)
		{
			$descriptor = array(
				'tmp_name' => $src,
				'name'     => basename($dest),
				'type'     => '',
				'error'    => '',
				'size'     => '',
			);

			$isSafe = JFilterInput::isSafeFile($descriptor, $safeFileOptions);

			if (!$isSafe)
			{
				JLog::add(JText::sprintf('JLIB_FILESYSTEM_ERROR_WARNFS_ERR03', $dest), JLog::WARNING, 'jerror');

				return false;
			}
		}

		// Ensure that the path is valid and clean
		$pathObject = new JFilesystemWrapperPath;
		$dest = $pathObject->clean($dest);

		// Create the destination directory if it does not exist
		$baseDir = dirname($dest);

		if (!file_exists($baseDir))
		{
			$folderObject = new JFilesystemWrapperFolder;
			$folderObject->create($baseDir);
		}

		if ($use_streams)
		{
			$stream = JFactory::getStream();

			if (!$stream->upload($src, $dest))
			{
				JLog::add(JText::sprintf('JLIB_FILESYSTEM_ERROR_UPLOAD', $stream->getError()), JLog::WARNING, 'jerror');

				return false;
			}

			return true;
		}
		else
		{
			$FTPOptions = JClientHelper::getCredentials('ftp');
			$ret = false;

			if ($FTPOptions['enabled'] == 1)
			{
				// Connect the FTP client
				$ftp = JClientFtp::getInstance($FTPOptions['host'], $FTPOptions['port'], array(), $FTPOptions['user'], $FTPOptions['pass']);

				// Translate path for the FTP account
				$dest = $pathObject->clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $dest), '/');

				// Copy the file to the destination directory
				if (is_uploaded_file($src) && $ftp->store($src, $dest))
				{
					unlink($src);
					$ret = true;
				}
				else
				{
					JLog::add(JText::sprintf('JLIB_FILESYSTEM_ERROR_WARNFS_ERR04', $src, $dest), JLog::WARNING, 'jerror');
				}
			}
			else
			{
				if (is_writeable($baseDir) && move_uploaded_file($src, $dest))
				{
					// Short circuit to prevent file permission errors
					if ($pathObject->setPermissions($dest))
					{
						$ret = true;
					}
					else
					{
						JLog::add(JText::_('JLIB_FILESYSTEM_ERROR_WARNFS_ERR01'), JLog::WARNING, 'jerror');
					}
				}
				else
				{
					JLog::add(JText::sprintf('JLIB_FILESYSTEM_ERROR_WARNFS_ERR04', $src, $dest), JLog::WARNING, 'jerror');
				}
			}

			return $ret;
		}
	}

	/**
	 * Wrapper for the standard file_exists function
	 *
	 * @param   string  $file  File path
	 *
	 * @return  boolean  True if path is a file
	 *
	 * @since   11.1
	 */
	public static function exists($file)
	{
		$pathObject = new JFilesystemWrapperPath;

		return is_file($pathObject->clean($file));
	}

	/**
	 * Returns the name, without any path.
	 *
	 * @param   string  $file  File path
	 *
	 * @return  string  filename
	 *
	 * @since   11.1
	 * @deprecated  13.3 (Platform) & 4.0 (CMS) - Use basename() instead.
	 */
	public static function getName($file)
	{
		JLog::add(__METHOD__ . ' is deprecated. Use native basename() syntax.', JLog::WARNING, 'deprecated');

		// Convert back slashes to forward slashes
		$file = str_replace('\\', '/', $file);
		$slash = strrpos($file, '/');

		if ($slash !== false)
		{
			return substr($file, $slash + 1);
		}
		else
		{
			return $file;
		}
	}
}
