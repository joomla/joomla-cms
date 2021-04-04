<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Filesystem;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Filesystem\Wrapper\PathWrapper;
use Joomla\CMS\Filesystem\Wrapper\FolderWrapper;
use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Client\FtpClient;

/**
 * A File handling class
 *
 * @since  1.7.0
 */
class File
{
	/**
	 * @var    boolean  true if OPCache enabled, and we have permission to invalidate files
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $canFlushFileCache;

	/**
	 * Gets the extension of a file name
	 *
	 * @param   string  $file  The file name
	 *
	 * @return  string  The file extension
	 *
	 * @since   1.7.0
	 */
	public static function getExt($file)
	{
		// String manipulation should be faster than pathinfo() on newer PHP versions.
		$dot = strrpos($file, '.');

		if ($dot === false)
		{
			return '';
		}

		$ext = substr($file, $dot + 1);

		// Extension cannot contain slashes.
		if (strpos($ext, '/') !== false || (DIRECTORY_SEPARATOR === '\\' && strpos($ext, '\\') !== false))
		{
			return '';
		}

		return $ext;
	}

	/**
	 * Strips the last extension off of a file name
	 *
	 * @param   string  $file  The file name
	 *
	 * @return  string  The file name without the extension
	 *
	 * @since   1.7.0
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
	 * @since   1.7.0
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
	 * @param   string   $src         The path to the source file
	 * @param   string   $dest        The path to the destination file
	 * @param   string   $path        An optional base path to prefix to the file names
	 * @param   boolean  $useStreams  True to use streams
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.7.0
	 */
	public static function copy($src, $dest, $path = null, $useStreams = false)
	{
		$pathObject = new PathWrapper;

		// Prepend a base path if it exists
		if ($path)
		{
			$src = $pathObject->clean($path . '/' . $src);
			$dest = $pathObject->clean($path . '/' . $dest);
		}

		// Check src path
		if (!is_readable($src))
		{
			Log::add(Text::sprintf('JLIB_FILESYSTEM_ERROR_JFILE_FIND_COPY', $src), Log::WARNING, 'jerror');

			return false;
		}

		if ($useStreams)
		{
			$stream = Factory::getStream();

			if (!$stream->copy($src, $dest))
			{
				Log::add(Text::sprintf('JLIB_FILESYSTEM_ERROR_JFILE_STREAMS', $src, $dest, $stream->getError()), Log::WARNING, 'jerror');

				return false;
			}

			self::invalidateFileCache($dest);

			return true;
		}
		else
		{
			$FTPOptions = ClientHelper::getCredentials('ftp');

			if ($FTPOptions['enabled'] == 1)
			{
				// Connect the FTP client
				$ftp = FtpClient::getInstance($FTPOptions['host'], $FTPOptions['port'], array(), $FTPOptions['user'], $FTPOptions['pass']);

				// If the parent folder doesn't exist we must create it
				if (!file_exists(dirname($dest)))
				{
					$folderObject = new FolderWrapper;
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
					Log::add(Text::sprintf('JLIB_FILESYSTEM_ERROR_COPY_FAILED_ERR01', $src, $dest), Log::WARNING, 'jerror');

					return false;
				}

				$ret = true;
			}

			self::invalidateFileCache($dest);

			return $ret;
		}
	}

	/**
	 * Invalidate opcache for a newly written/deleted file immediately, if opcache* functions exist and if this was a PHP file.
	 *
	 * @param   string   $filepath  The path to the file just written to, to flush from opcache
	 * @param   boolean  $force     If set to true, the script will be invalidated regardless of whether invalidation is necessary
	 *
	 * @return boolean TRUE if the opcode cache for script was invalidated/nothing to invalidate,
	 *                 or FALSE if the opcode cache is disabled or other conditions returning
	 *                 FALSE from opcache_invalidate (like file not found).
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function invalidateFileCache($filepath, $force = true)
	{
		if (self::canFlushFileCache() && '.php' === strtolower(substr($filepath, -4)))
		{
			return opcache_invalidate($filepath, $force);
		}

		return false;
	}

	/**
	 * `opcache_invalidate` is only available from PHP 5.5.0 onwards.
	 *
	 * First we check if opcache is enabled
	 * Then we check if the opcache_invalidate function is available
	 * Lastly we check if the host has restricted which scripts can use opcache_invalidate using opcache.restrict_api.
	 *
	 * `$_SERVER['SCRIPT_FILENAME']` approximates the origin file's path, but `realpath()`
	 * is necessary because `SCRIPT_FILENAME` can be a relative path when run from CLI.
	 * If the host has this set, check whether the path in `opcache.restrict_api` matches
	 * the beginning of the path of the origin file.
	 *
	 * @return boolean TRUE if we can proceed to use opcache_invalidate to flush a file from the OPCache
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function canFlushFileCache()
	{
		if (isset(static::$canFlushFileCache))
		{
			return static::$canFlushFileCache;
		}

		if (function_exists('opcache_invalidate')
			&& ini_get('opcache.enable')
			&& (!ini_get('opcache.restrict_api') || stripos(realpath($_SERVER['SCRIPT_FILENAME']), ini_get('opcache.restrict_api')) === 0))
		{
			static::$canFlushFileCache = true;
		}
		else
		{
			static::$canFlushFileCache = false;
		}

		return static::$canFlushFileCache;
	}

	/**
	 * Delete a file or array of files
	 *
	 * @param   mixed  $file  The file name or an array of file names
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.7.0
	 */
	public static function delete($file)
	{
		$FTPOptions = ClientHelper::getCredentials('ftp');
		$pathObject = new PathWrapper;

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
			$ftp = FtpClient::getInstance($FTPOptions['host'], $FTPOptions['port'], array(), $FTPOptions['user'], $FTPOptions['pass']);
		}

		foreach ($files as $file)
		{
			$file = $pathObject->clean($file);

			if (!is_file($file))
			{
				continue;
			}

			/**
			 * Try making the file writable first. If it's read-only, it can't be deleted
			 * on Windows, even if the parent folder is writable
			 */
			@chmod($file, 0777);

			/**
			 * Invalidate the OPCache for the file before actually deleting it
			 * @see https://github.com/joomla/joomla-cms/pull/32915#issuecomment-812865635
			 * @see https://www.php.net/manual/en/function.opcache-invalidate.php#116372
			 */
			self::invalidateFileCache($file);

			/**
			 * In case of restricted permissions we delete it one way or the other
			 * as long as the owner is either the webserver or the ftp
			 */
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
				Log::add(Text::sprintf('JLIB_FILESYSTEM_DELETE_FAILED', $filename), Log::WARNING, 'jerror');

				return false;
			}
		}

		return true;
	}

	/**
	 * Moves a file
	 *
	 * @param   string   $src         The path to the source file
	 * @param   string   $dest        The path to the destination file
	 * @param   string   $path        An optional base path to prefix to the file names
	 * @param   boolean  $useStreams  True to use streams
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.7.0
	 */
	public static function move($src, $dest, $path = '', $useStreams = false)
	{
		$pathObject = new PathWrapper;

		if ($path)
		{
			$src = $pathObject->clean($path . '/' . $src);
			$dest = $pathObject->clean($path . '/' . $dest);
		}

		// Check src path
		if (!is_readable($src))
		{
			Log::add(Text::_('JLIB_FILESYSTEM_CANNOT_FIND_SOURCE_FILE'), Log::WARNING, 'jerror');

			return false;
		}

		if ($useStreams)
		{
			$stream = Factory::getStream();

			if (!$stream->move($src, $dest))
			{
				Log::add(Text::sprintf('JLIB_FILESYSTEM_ERROR_JFILE_MOVE_STREAMS', $stream->getError()), Log::WARNING, 'jerror');

				return false;
			}

			return true;
		}
		else
		{
			$FTPOptions = ClientHelper::getCredentials('ftp');

			if ($FTPOptions['enabled'] == 1)
			{
				// Connect the FTP client
				$ftp = FtpClient::getInstance($FTPOptions['host'], $FTPOptions['port'], array(), $FTPOptions['user'], $FTPOptions['pass']);

				// Translate path for the FTP account
				$src = $pathObject->clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $src), '/');
				$dest = $pathObject->clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $dest), '/');

				// Use FTP rename to simulate move
				if (!$ftp->rename($src, $dest))
				{
					Log::add(Text::_('JLIB_FILESYSTEM_ERROR_RENAME_FILE'), Log::WARNING, 'jerror');

					return false;
				}
			}
			else
			{
				if (!@ rename($src, $dest))
				{
					Log::add(Text::_('JLIB_FILESYSTEM_ERROR_RENAME_FILE'), Log::WARNING, 'jerror');

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
	 * @since   1.7.0
	 * @deprecated  4.0 - Use the native file_get_contents() instead.
	 */
	public static function read($filename, $incpath = false, $amount = 0, $chunksize = 8192, $offset = 0)
	{
		Log::add(__METHOD__ . ' is deprecated. Use native file_get_contents() syntax.', Log::WARNING, 'deprecated');

		$data = null;

		if ($amount && $chunksize > $amount)
		{
			$chunksize = $amount;
		}

		if (false === $fh = fopen($filename, 'rb', $incpath))
		{
			Log::add(Text::sprintf('JLIB_FILESYSTEM_ERROR_READ_UNABLE_TO_OPEN_FILE', $filename), Log::WARNING, 'jerror');

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
	 * @param   string   $file        The full file path
	 * @param   string   $buffer      The buffer to write
	 * @param   boolean  $useStreams  Use streams
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.7.0
	 */
	public static function write($file, $buffer, $useStreams = false)
	{
		@set_time_limit(ini_get('max_execution_time'));

		// If the destination directory doesn't exist we need to create it
		if (!file_exists(dirname($file)))
		{
			$folderObject = new FolderWrapper;

			if ($folderObject->create(dirname($file)) == false)
			{
				return false;
			}
		}

		if ($useStreams)
		{
			$stream = Factory::getStream();

			// Beef up the chunk size to a meg
			$stream->set('chunksize', (1024 * 1024));

			if (!$stream->writeFile($file, $buffer))
			{
				Log::add(Text::sprintf('JLIB_FILESYSTEM_ERROR_WRITE_STREAMS', $file, $stream->getError()), Log::WARNING, 'jerror');

				return false;
			}

			return true;
		}
		else
		{
			$FTPOptions = ClientHelper::getCredentials('ftp');
			$pathObject = new PathWrapper;

			if ($FTPOptions['enabled'] == 1)
			{
				// Connect the FTP client
				$ftp = FtpClient::getInstance($FTPOptions['host'], $FTPOptions['port'], array(), $FTPOptions['user'], $FTPOptions['pass']);

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
	 * @param   string   $file        The full file path
	 * @param   string   $buffer      The buffer to write
	 * @param   boolean  $useStreams  Use streams
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.6.0
	 */
	public static function append($file, $buffer, $useStreams = false)
	{
		@set_time_limit(ini_get('max_execution_time'));

		// If the file doesn't exist, just write instead of append
		if (!file_exists($file))
		{
			return self::write($file, $buffer, $useStreams);
		}

		if ($useStreams)
		{
			$stream = Factory::getStream();

			// Beef up the chunk size to a meg
			$stream->set('chunksize', (1024 * 1024));

			if ($stream->open($file, 'ab') && $stream->write($buffer) && $stream->close())
			{
				return true;
			}

			Log::add(Text::sprintf('JLIB_FILESYSTEM_ERROR_WRITE_STREAMS', $file, $stream->getError()), Log::WARNING, 'jerror');

			return false;
		}
		else
		{
			// Initialise variables.
			$FTPOptions = ClientHelper::getCredentials('ftp');

			if ($FTPOptions['enabled'] == 1)
			{
				// Connect the FTP client
				$ftp = FtpClient::getInstance($FTPOptions['host'], $FTPOptions['port'], array(), $FTPOptions['user'], $FTPOptions['pass']);

				// Translate path for the FTP account and use FTP write buffer to file
				$file = Path::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $file), '/');
				$ret = $ftp->append($file, $buffer);
			}
			else
			{
				$file = Path::clean($file);
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
	 * @param   boolean  $useStreams       True to use streams
	 * @param   boolean  $allowUnsafe      Allow the upload of unsafe files
	 * @param   boolean  $safeFileOptions  Options to \JFilterInput::isSafeFile
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.7.0
	 */
	public static function upload($src, $dest, $useStreams = false, $allowUnsafe = false, $safeFileOptions = array())
	{
		if (!$allowUnsafe)
		{
			$descriptor = array(
				'tmp_name' => $src,
				'name'     => basename($dest),
				'type'     => '',
				'error'    => '',
				'size'     => '',
			);

			$isSafe = \JFilterInput::isSafeFile($descriptor, $safeFileOptions);

			if (!$isSafe)
			{
				Log::add(Text::sprintf('JLIB_FILESYSTEM_ERROR_WARNFS_ERR03', $dest), Log::WARNING, 'jerror');

				return false;
			}
		}

		// Ensure that the path is valid and clean
		$pathObject = new PathWrapper;
		$dest = $pathObject->clean($dest);

		// Create the destination directory if it does not exist
		$baseDir = dirname($dest);

		if (!file_exists($baseDir))
		{
			$folderObject = new FolderWrapper;
			$folderObject->create($baseDir);
		}

		if ($useStreams)
		{
			$stream = Factory::getStream();

			if (!$stream->upload($src, $dest))
			{
				Log::add(Text::sprintf('JLIB_FILESYSTEM_ERROR_UPLOAD', $stream->getError()), Log::WARNING, 'jerror');

				return false;
			}

			return true;
		}
		else
		{
			$FTPOptions = ClientHelper::getCredentials('ftp');
			$ret = false;

			if ($FTPOptions['enabled'] == 1)
			{
				// Connect the FTP client
				$ftp = FtpClient::getInstance($FTPOptions['host'], $FTPOptions['port'], array(), $FTPOptions['user'], $FTPOptions['pass']);

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
					Log::add(Text::sprintf('JLIB_FILESYSTEM_ERROR_WARNFS_ERR04', $src, $dest), Log::WARNING, 'jerror');
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
						Log::add(Text::_('JLIB_FILESYSTEM_ERROR_WARNFS_ERR01'), Log::WARNING, 'jerror');
					}
				}
				else
				{
					Log::add(Text::sprintf('JLIB_FILESYSTEM_ERROR_WARNFS_ERR04', $src, $dest), Log::WARNING, 'jerror');
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
	 * @since   1.7.0
	 */
	public static function exists($file)
	{
		$pathObject = new PathWrapper;

		return is_file($pathObject->clean($file));
	}

	/**
	 * Returns the name, without any path.
	 *
	 * @param   string  $file  File path
	 *
	 * @return  string  filename
	 *
	 * @since   1.7.0
	 * @deprecated  4.0 - Use basename() instead.
	 */
	public static function getName($file)
	{
		Log::add(__METHOD__ . ' is deprecated. Use native basename() syntax.', Log::WARNING, 'deprecated');

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
