<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Filesystem;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Language\Text;

/**
 * Joomla! Stream Interface
 *
 * The Joomla! stream interface is designed to handle files as streams
 * where as the legacy File static class treated files in a rather
 * atomic manner.
 *
 * @note   This class adheres to the stream wrapper operations:
 * @link   https://www.php.net/manual/en/function.stream-get-wrappers.php
 * @link   https://www.php.net/manual/en/intro.stream.php PHP Stream Manual
 * @link   https://www.php.net/manual/en/wrappers.php Stream Wrappers
 * @link   https://www.php.net/manual/en/filters.php Stream Filters
 * @link   https://www.php.net/manual/en/transports.php Socket Transports (used by some options, particularly HTTP proxy)
 * @since  1.7.0
 */
class Stream extends CMSObject
{
	/**
	 * File Mode
	 *
	 * @var    integer
	 * @since  1.7.0
	 */
	protected $filemode = 0644;

	/**
	 * Directory Mode
	 *
	 * @var    integer
	 * @since  1.7.0
	 */
	protected $dirmode = 0755;

	/**
	 * Default Chunk Size
	 *
	 * @var    integer
	 * @since  1.7.0
	 */
	protected $chunksize = 8192;

	/**
	 * Filename
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $filename;

	/**
	 * Prefix of the connection for writing
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $writeprefix;

	/**
	 * Prefix of the connection for reading
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $readprefix;

	/**
	 * Read Processing method
	 * @var    string  gz, bz, f
	 * If a scheme is detected, fopen will be defaulted
	 * To use compression with a network stream use a filter
	 * @since  1.7.0
	 */
	protected $processingmethod = 'f';

	/**
	 * Filters applied to the current stream
	 *
	 * @var    array
	 * @since  1.7.0
	 */
	protected $filters = array();

	/**
	 * File Handle
	 *
	 * @var    resource
	 * @since  3.0.0
	 */
	protected $fh;

	/**
	 * File size
	 *
	 * @var    integer
	 * @since  3.0.0
	 */
	protected $filesize;

	/**
	 * Context to use when opening the connection
	 *
	 * @var    resource
	 * @since  3.0.0
	 */
	protected $context = null;

	/**
	 * Context options; used to rebuild the context
	 *
	 * @var    array
	 * @since  3.0.0
	 */
	protected $contextOptions;

	/**
	 * The mode under which the file was opened
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	protected $openmode;

	/**
	 * Constructor
	 *
	 * @param   string  $writeprefix  Prefix of the stream (optional). Unlike the JPATH_*, this has a final path separator!
	 * @param   string  $readprefix   The read prefix (optional).
	 * @param   array   $context      The context options (optional).
	 *
	 * @since   1.7.0
	 */
	public function __construct($writeprefix = '', $readprefix = '', $context = array())
	{
		$this->writeprefix = $writeprefix;
		$this->readprefix = $readprefix;
		$this->contextOptions = $context;
		$this->_buildContext();
	}

	/**
	 * Destructor
	 *
	 * @since   1.7.0
	 */
	public function __destruct()
	{
		// Attempt to close on destruction if there is a file handle
		if ($this->fh)
		{
			@$this->close();
		}
	}

	/**
	 * Generic File Operations
	 *
	 * Open a stream with some lazy loading smarts
	 *
	 * @param   string    $filename              Filename
	 * @param   string    $mode                  Mode string to use
	 * @param   boolean   $use_include_path      Use the PHP include path
	 * @param   resource  $context               Context to use when opening
	 * @param   boolean   $use_prefix            Use a prefix to open the file
	 * @param   boolean   $relative              Filename is a relative path (if false, strips JPATH_ROOT to make it relative)
	 * @param   boolean   $detectprocessingmode  Detect the processing method for the file and use the appropriate function
	 *                                           to handle output automatically
	 *
	 * @return  boolean
	 *
	 * @since   1.7.0
	 */
	public function open($filename, $mode = 'r', $use_include_path = false, $context = null,
		$use_prefix = false, $relative = false, $detectprocessingmode = false)
	{
		$filename = $this->_getFilename($filename, $mode, $use_prefix, $relative);

		if (!$filename)
		{
			$this->setError(Text::_('JLIB_FILESYSTEM_ERROR_STREAMS_FILENAME'));

			return false;
		}

		$this->filename = $filename;
		$this->openmode = $mode;

		$url = parse_url($filename);
		$retval = false;

		if (isset($url['scheme']))
		{
			// If we're dealing with a Joomla! stream, load it
			if (FilesystemHelper::isJoomlaStream($url['scheme']))
			{
				require_once __DIR__ . '/streams/' . $url['scheme'] . '.php';
			}

			// We have a scheme! force the method to be f
			$this->processingmethod = 'f';
		}
		elseif ($detectprocessingmode)
		{
			$ext = strtolower(File::getExt($this->filename));

			switch ($ext)
			{
				case 'tgz':
				case 'gz':
				case 'gzip':
					$this->processingmethod = 'gz';
					break;

				case 'tbz2':
				case 'bz2':
				case 'bzip2':
					$this->processingmethod = 'bz';
					break;

				default:
					$this->processingmethod = 'f';
					break;
			}
		}

		// Capture PHP errors
		$php_errormsg = 'Error Unknown whilst opening a file';
		$track_errors = ini_get('track_errors');
		ini_set('track_errors', true);

		// Decide which context to use:
		switch ($this->processingmethod)
		{
			// Gzip doesn't support contexts or streams
			case 'gz':
				$this->fh = gzopen($filename, $mode, $use_include_path);
				break;

			// Bzip2 is much like gzip except it doesn't use the include path
			case 'bz':
				$this->fh = bzopen($filename, $mode);
				break;

			// Fopen can handle streams
			case 'f':
			default:
				// One supplied at open; overrides everything
				if ($context)
				{
					$this->fh = fopen($filename, $mode, $use_include_path, $context);
				}
				// One provided at initialisation
				elseif ($this->context)
				{
					$this->fh = fopen($filename, $mode, $use_include_path, $this->context);
				}
				// No context; all defaults
				else
				{
					$this->fh = fopen($filename, $mode, $use_include_path);
				}

				break;
		}

		if (!$this->fh)
		{
			$this->setError($php_errormsg);
		}
		else
		{
			$retval = true;
		}

		// Restore error tracking to what it was before
		ini_set('track_errors', $track_errors);

		// Return the result
		return $retval;
	}

	/**
	 * Attempt to close a file handle
	 *
	 * Will return false if it failed and true on success
	 * If the file is not open the system will return true, this function destroys the file handle as well
	 *
	 * @return  boolean
	 *
	 * @since   1.7.0
	 */
	public function close()
	{
		if (!$this->fh)
		{
			$this->setError(Text::_('JLIB_FILESYSTEM_ERROR_STREAMS_FILE_NOT_OPEN'));

			return true;
		}

		$retval = false;

		// Capture PHP errors
		$php_errormsg = 'Error Unknown';
		$track_errors = ini_get('track_errors');
		ini_set('track_errors', true);

		switch ($this->processingmethod)
		{
			case 'gz':
				$res = gzclose($this->fh);
				break;

			case 'bz':
				$res = bzclose($this->fh);
				break;

			case 'f':
			default:
				$res = fclose($this->fh);
				break;
		}

		if (!$res)
		{
			$this->setError($php_errormsg);
		}
		else
		{
			// Reset this
			$this->fh = null;
			$retval = true;
		}

		// If we wrote, chmod the file after it's closed
		if ($this->openmode[0] == 'w')
		{
			$this->chmod();
		}

		// Restore error tracking to what it was before
		ini_set('track_errors', $track_errors);

		// Return the result
		return $retval;
	}

	/**
	 * Work out if we're at the end of the file for a stream
	 *
	 * @return  boolean
	 *
	 * @since   1.7.0
	 */
	public function eof()
	{
		if (!$this->fh)
		{
			$this->setError(Text::_('JLIB_FILESYSTEM_ERROR_STREAMS_FILE_NOT_OPEN'));

			return false;
		}

		// Capture PHP errors
		$php_errormsg = '';
		$track_errors = ini_get('track_errors');
		ini_set('track_errors', true);

		switch ($this->processingmethod)
		{
			case 'gz':
				$res = gzeof($this->fh);
				break;

			case 'bz':
			case 'f':
			default:
				$res = feof($this->fh);
				break;
		}

		if ($php_errormsg)
		{
			$this->setError($php_errormsg);
		}

		// Restore error tracking to what it was before
		ini_set('track_errors', $track_errors);

		// Return the result
		return $res;
	}

	/**
	 * Retrieve the file size of the path
	 *
	 * @return  mixed
	 *
	 * @since   1.7.0
	 */
	public function filesize()
	{
		if (!$this->filename)
		{
			$this->setError(Text::_('JLIB_FILESYSTEM_ERROR_STREAMS_FILE_NOT_OPEN'));

			return false;
		}

		$retval = false;

		// Capture PHP errors
		$php_errormsg = '';
		$track_errors = ini_get('track_errors');
		ini_set('track_errors', true);
		$res = @filesize($this->filename);

		if (!$res)
		{
			$tmp_error = '';

			if ($php_errormsg)
			{
				// Something went wrong.
				// Store the error in case we need it.
				$tmp_error = $php_errormsg;
			}

			$res = FilesystemHelper::remotefsize($this->filename);

			if (!$res)
			{
				if ($tmp_error)
				{
					// Use the php_errormsg from before
					$this->setError($tmp_error);
				}
				else
				{
					// Error but nothing from php? How strange! Create our own
					$this->setError(Text::_('JLIB_FILESYSTEM_ERROR_STREAMS_FILE_SIZE'));
				}
			}
			else
			{
				$this->filesize = $res;
				$retval = $res;
			}
		}
		else
		{
			$this->filesize = $res;
			$retval = $res;
		}

		// Restore error tracking to what it was before.
		ini_set('track_errors', $track_errors);

		// Return the result
		return $retval;
	}

	/**
	 * Get a line from the stream source.
	 *
	 * @param   integer  $length  The number of bytes (optional) to read.
	 *
	 * @return  mixed
	 *
	 * @since   1.7.0
	 */
	public function gets($length = 0)
	{
		if (!$this->fh)
		{
			$this->setError(Text::_('JLIB_FILESYSTEM_ERROR_STREAMS_FILE_NOT_OPEN'));

			return false;
		}

		$retval = false;

		// Capture PHP errors
		$php_errormsg = 'Error Unknown';
		$track_errors = ini_get('track_errors');
		ini_set('track_errors', true);

		switch ($this->processingmethod)
		{
			case 'gz':
				$res = $length ? gzgets($this->fh, $length) : gzgets($this->fh);
				break;

			case 'bz':
			case 'f':
			default:
				$res = $length ? fgets($this->fh, $length) : fgets($this->fh);
				break;
		}

		if (!$res)
		{
			$this->setError($php_errormsg);
		}
		else
		{
			$retval = $res;
		}

		// Restore error tracking to what it was before
		ini_set('track_errors', $track_errors);

		// Return the result
		return $retval;
	}

	/**
	 * Read a file
	 *
	 * Handles user space streams appropriately otherwise any read will return 8192
	 *
	 * @param   integer  $length  Length of data to read
	 *
	 * @return  mixed
	 *
	 * @link    https://www.php.net/manual/en/function.fread.php
	 * @since   1.7.0
	 */
	public function read($length = 0)
	{
		if (!$this->filesize && !$length)
		{
			// Get the filesize
			$this->filesize();

			if (!$this->filesize)
			{
				// Set it to the biggest and then wait until eof
				$length = -1;
			}
			else
			{
				$length = $this->filesize;
			}
		}

		if (!$this->fh)
		{
			$this->setError(Text::_('JLIB_FILESYSTEM_ERROR_STREAMS_FILE_NOT_OPEN'));

			return false;
		}

		$retval = false;

		// Capture PHP errors
		$php_errormsg = 'Error Unknown';
		$track_errors = ini_get('track_errors');
		ini_set('track_errors', true);
		$remaining = $length;

		do
		{
			// Do chunked reads where relevant
			switch ($this->processingmethod)
			{
				case 'bz':
					$res = ($remaining > 0) ? bzread($this->fh, $remaining) : bzread($this->fh, $this->chunksize);
					break;

				case 'gz':
					$res = ($remaining > 0) ? gzread($this->fh, $remaining) : gzread($this->fh, $this->chunksize);
					break;

				case 'f':
				default:
					$res = ($remaining > 0) ? fread($this->fh, $remaining) : fread($this->fh, $this->chunksize);
					break;
			}

			if (!$res)
			{
				$this->setError($php_errormsg);

				// Jump from the loop
				$remaining = 0;
			}
			else
			{
				if (!$retval)
				{
					$retval = '';
				}

				$retval .= $res;

				if (!$this->eof())
				{
					$len = strlen($res);
					$remaining -= $len;
				}
				else
				{
					// If it's the end of the file then we've nothing left to read; reset remaining and len
					$remaining = 0;
					$length = strlen($retval);
				}
			}
		}
		while ($remaining || !$length);

		// Restore error tracking to what it was before
		ini_set('track_errors', $track_errors);

		// Return the result
		return $retval;
	}

	/**
	 * Seek the file
	 *
	 * Note: the return value is different to that of fseek
	 *
	 * @param   integer  $offset  Offset to use when seeking.
	 * @param   integer  $whence  Seek mode to use.
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @link    https://www.php.net/manual/en/function.fseek.php
	 * @since   1.7.0
	 */
	public function seek($offset, $whence = SEEK_SET)
	{
		if (!$this->fh)
		{
			$this->setError(Text::_('JLIB_FILESYSTEM_ERROR_STREAMS_FILE_NOT_OPEN'));

			return false;
		}

		$retval = false;

		// Capture PHP errors
		$php_errormsg = '';
		$track_errors = ini_get('track_errors');
		ini_set('track_errors', true);

		switch ($this->processingmethod)
		{
			case 'gz':
				$res = gzseek($this->fh, $offset, $whence);
				break;

			case 'bz':
			case 'f':
			default:
				$res = fseek($this->fh, $offset, $whence);
				break;
		}

		// Seek, interestingly, returns 0 on success or -1 on failure.
		if ($res == -1)
		{
			$this->setError($php_errormsg);
		}
		else
		{
			$retval = true;
		}

		// Restore error tracking to what it was before
		ini_set('track_errors', $track_errors);

		// Return the result
		return $retval;
	}

	/**
	 * Returns the current position of the file read/write pointer.
	 *
	 * @return  mixed
	 *
	 * @since   1.7.0
	 */
	public function tell()
	{
		if (!$this->fh)
		{
			$this->setError(Text::_('JLIB_FILESYSTEM_ERROR_STREAMS_FILE_NOT_OPEN'));

			return false;
		}

		// Capture PHP errors
		$php_errormsg = '';
		$track_errors = ini_get('track_errors');
		ini_set('track_errors', true);

		switch ($this->processingmethod)
		{
			case 'gz':
				$res = gztell($this->fh);
				break;

			case 'bz':
			case 'f':
			default:
				$res = ftell($this->fh);
				break;
		}

		// May return 0 so check if it's really false
		if ($res === false)
		{
			$this->setError($php_errormsg);
		}

		// Restore error tracking to what it was before
		ini_set('track_errors', $track_errors);

		// Return the result
		return $res;
	}

	/**
	 * File write
	 *
	 * Whilst this function accepts a reference, the underlying fwrite
	 * will do a copy! This will roughly double the memory allocation for
	 * any write you do. Specifying chunked will get around this by only
	 * writing in specific chunk sizes. This defaults to 8192 which is a
	 * sane number to use most of the time (change the default with
	 * JStream::set('chunksize', newsize);)
	 * Note: This doesn't support gzip/bzip2 writing like reading does
	 *
	 * @param   string   &$string  Reference to the string to write.
	 * @param   integer  $length   Length of the string to write.
	 * @param   integer  $chunk    Size of chunks to write in.
	 *
	 * @return  boolean
	 *
	 * @link    https://www.php.net/manual/en/function.fwrite.php
	 * @since   1.7.0
	 */
	public function write(&$string, $length = 0, $chunk = 0)
	{
		if (!$this->fh)
		{
			$this->setError(Text::_('JLIB_FILESYSTEM_ERROR_STREAMS_FILE_NOT_OPEN'));

			return false;
		}

		// If the length isn't set, set it to the length of the string.
		if (!$length)
		{
			$length = strlen($string);
		}

		// If the chunk isn't set, set it to the default.
		if (!$chunk)
		{
			$chunk = $this->chunksize;
		}

		$retval = true;

		// Capture PHP errors
		$php_errormsg = '';
		$track_errors = ini_get('track_errors');
		ini_set('track_errors', true);
		$remaining = $length;
		$start = 0;

		do
		{
			// If the amount remaining is greater than the chunk size, then use the chunk
			$amount = ($remaining > $chunk) ? $chunk : $remaining;
			$res = fwrite($this->fh, substr($string, $start), $amount);

			// Returns false on error or the number of bytes written
			if ($res === false)
			{
				// Returned error
				$this->setError($php_errormsg);
				$retval = false;
				$remaining = 0;
			}
			elseif ($res === 0)
			{
				// Wrote nothing?
				$remaining = 0;
				$this->setError(Text::_('JLIB_FILESYSTEM_ERROR_NO_DATA_WRITTEN'));
			}
			else
			{
				// Wrote something
				$start += $amount;
				$remaining -= $res;
			}
		}
		while ($remaining);

		// Restore error tracking to what it was before.
		ini_set('track_errors', $track_errors);

		// Return the result
		return $retval;
	}

	/**
	 * Chmod wrapper
	 *
	 * @param   string  $filename  File name.
	 * @param   mixed   $mode      Mode to use.
	 *
	 * @return  boolean
	 *
	 * @since   1.7.0
	 */
	public function chmod($filename = '', $mode = 0)
	{
		if (!$filename)
		{
			if (!isset($this->filename) || !$this->filename)
			{
				$this->setError(Text::_('JLIB_FILESYSTEM_ERROR_STREAMS_FILENAME'));

				return false;
			}

			$filename = $this->filename;
		}

		// If no mode is set use the default
		if (!$mode)
		{
			$mode = $this->filemode;
		}

		$retval = false;

		// Capture PHP errors
		$php_errormsg = '';
		$track_errors = ini_get('track_errors');
		ini_set('track_errors', true);
		$sch = parse_url($filename, PHP_URL_SCHEME);

		// Scheme specific options; ftp's chmod support is fun.
		switch ($sch)
		{
			case 'ftp':
			case 'ftps':
				$res = FilesystemHelper::ftpChmod($filename, $mode);
				break;

			default:
				$res = chmod($filename, $mode);
				break;
		}

		// Seek, interestingly, returns 0 on success or -1 on failure
		if (!$res)
		{
			$this->setError($php_errormsg);
		}
		else
		{
			$retval = true;
		}

		// Restore error tracking to what it was before.
		ini_set('track_errors', $track_errors);

		// Return the result
		return $retval;
	}

	/**
	 * Get the stream metadata
	 *
	 * @return  array  header/metadata
	 *
	 * @link    https://www.php.net/manual/en/function.stream-get-meta-data.php
	 * @since   1.7.0
	 */
	public function get_meta_data()
	{
		if (!$this->fh)
		{
			$this->setError(Text::_('JLIB_FILESYSTEM_ERROR_STREAMS_FILE_NOT_OPEN'));

			return false;
		}

		return stream_get_meta_data($this->fh);
	}

	/**
	 * Stream contexts
	 * Builds the context from the array
	 *
	 * @return  mixed
	 *
	 * @since   1.7.0
	 */
	public function _buildContext()
	{
		// According to the manual this always works!
		if (count($this->contextOptions))
		{
			$this->context = @stream_context_create($this->contextOptions);
		}
		else
		{
			$this->context = null;
		}
	}

	/**
	 * Updates the context to the array
	 *
	 * Format is the same as the options for stream_context_create
	 *
	 * @param   array  $context  Options to create the context with
	 *
	 * @return  void
	 *
	 * @link    https://www.php.net/stream_context_create
	 * @since   1.7.0
	 */
	public function setContextOptions($context)
	{
		$this->contextOptions = $context;
		$this->_buildContext();
	}

	/**
	 * Adds a particular options to the context
	 *
	 * @param   string  $wrapper  The wrapper to use
	 * @param   string  $name     The option to set
	 * @param   string  $value    The value of the option
	 *
	 * @return  void
	 *
	 * @link    https://www.php.net/stream_context_create Stream Context Creation
	 * @link    https://www.php.net/manual/en/context.php Context Options for various streams
	 * @since   1.7.0
	 */
	public function addContextEntry($wrapper, $name, $value)
	{
		$this->contextOptions[$wrapper][$name] = $value;
		$this->_buildContext();
	}

	/**
	 * Deletes a particular setting from a context
	 *
	 * @param   string  $wrapper  The wrapper to use
	 * @param   string  $name     The option to unset
	 *
	 * @return  void
	 *
	 * @link    https://www.php.net/stream_context_create
	 * @since   1.7.0
	 */
	public function deleteContextEntry($wrapper, $name)
	{
		// Check whether the wrapper is set
		if (isset($this->contextOptions[$wrapper]))
		{
			// Check that entry is set for that wrapper
			if (isset($this->contextOptions[$wrapper][$name]))
			{
				// Unset the item
				unset($this->contextOptions[$wrapper][$name]);

				// Check that there are still items there
				if (!count($this->contextOptions[$wrapper]))
				{
					// Clean up an empty wrapper context option
					unset($this->contextOptions[$wrapper]);
				}
			}
		}

		// Rebuild the context and apply it to the stream
		$this->_buildContext();
	}

	/**
	 * Applies the current context to the stream
	 *
	 * Use this to change the values of the context after you've opened a stream
	 *
	 * @return  mixed
	 *
	 * @since   1.7.0
	 */
	public function applyContextToStream()
	{
		$retval = false;

		if ($this->fh)
		{
			// Capture PHP errors
			$php_errormsg = 'Unknown error setting context option';
			$track_errors = ini_get('track_errors');
			ini_set('track_errors', true);
			$retval = @stream_context_set_option($this->fh, $this->contextOptions);

			if (!$retval)
			{
				$this->setError($php_errormsg);
			}

			// Restore error tracking to what it was before
			ini_set('track_errors', $track_errors);
		}

		return $retval;
	}

	/**
	 * Stream filters
	 * Append a filter to the chain
	 *
	 * @param   string   $filtername  The key name of the filter.
	 * @param   integer  $read_write  Optional. Defaults to STREAM_FILTER_READ.
	 * @param   array    $params      An array of params for the stream_filter_append call.
	 *
	 * @return  mixed
	 *
	 * @link    https://www.php.net/manual/en/function.stream-filter-append.php
	 * @since   1.7.0
	 */
	public function appendFilter($filtername, $read_write = STREAM_FILTER_READ, $params = array())
	{
		$res = false;

		if ($this->fh)
		{
			// Capture PHP errors
			$php_errormsg = '';
			$track_errors = ini_get('track_errors');
			ini_set('track_errors', true);

			$res = @stream_filter_append($this->fh, $filtername, $read_write, $params);

			if (!$res && $php_errormsg)
			{
				$this->setError($php_errormsg);
			}
			else
			{
				$this->filters[] = &$res;
			}

			// Restore error tracking to what it was before.
			ini_set('track_errors', $track_errors);
		}

		return $res;
	}

	/**
	 * Prepend a filter to the chain
	 *
	 * @param   string   $filtername  The key name of the filter.
	 * @param   integer  $read_write  Optional. Defaults to STREAM_FILTER_READ.
	 * @param   array    $params      An array of params for the stream_filter_prepend call.
	 *
	 * @return  mixed
	 *
	 * @link    https://www.php.net/manual/en/function.stream-filter-prepend.php
	 * @since   1.7.0
	 */
	public function prependFilter($filtername, $read_write = STREAM_FILTER_READ, $params = array())
	{
		$res = false;

		if ($this->fh)
		{
			// Capture PHP errors
			$php_errormsg = '';
			$track_errors = ini_get('track_errors');
			ini_set('track_errors', true);
			$res = @stream_filter_prepend($this->fh, $filtername, $read_write, $params);

			if (!$res && $php_errormsg)
			{
				// Set the error msg
				$this->setError($php_errormsg);
			}
			else
			{
				array_unshift($res, '');
				$res[0] = &$this->filters;
			}

			// Restore error tracking to what it was before.
			ini_set('track_errors', $track_errors);
		}

		return $res;
	}

	/**
	 * Remove a filter, either by resource (handed out from the append or prepend function)
	 * or via getting the filter list)
	 *
	 * @param   resource  &$resource  The resource.
	 * @param   boolean   $byindex    The index of the filter.
	 *
	 * @return  boolean   Result of operation
	 *
	 * @since   1.7.0
	 */
	public function removeFilter(&$resource, $byindex = false)
	{
		// Capture PHP errors
		$php_errormsg = '';
		$track_errors = ini_get('track_errors');
		ini_set('track_errors', true);

		if ($byindex)
		{
			$res = stream_filter_remove($this->filters[$resource]);
		}
		else
		{
			$res = stream_filter_remove($resource);
		}

		if ($res && $php_errormsg)
		{
			$this->setError($php_errormsg);
		}

		// Restore error tracking to what it was before.
		ini_set('track_errors', $track_errors);

		return $res;
	}

	/**
	 * Copy a file from src to dest
	 *
	 * @param   string    $src         The file path to copy from.
	 * @param   string    $dest        The file path to copy to.
	 * @param   resource  $context     A valid context resource (optional) created with stream_context_create.
	 * @param   boolean   $use_prefix  Controls the use of a prefix (optional).
	 * @param   boolean   $relative    Determines if the filename given is relative. Relative paths do not have JPATH_ROOT stripped.
	 *
	 * @return  mixed
	 *
	 * @since   1.7.0
	 */
	public function copy($src, $dest, $context = null, $use_prefix = true, $relative = false)
	{
		// Capture PHP errors
		$php_errormsg = '';
		$track_errors = ini_get('track_errors');
		ini_set('track_errors', true);

		$chmodDest = $this->_getFilename($dest, 'w', $use_prefix, $relative);

		// Since we're going to open the file directly we need to get the filename.
		// We need to use the same prefix so force everything to write.
		$src = $this->_getFilename($src, 'w', $use_prefix, $relative);
		$dest = $this->_getFilename($dest, 'w', $use_prefix, $relative);

		if ($context)
		{
			// Use the provided context
			$res = @copy($src, $dest, $context);
		}
		elseif ($this->context)
		{
			// Use the objects context
			$res = @copy($src, $dest, $this->context);
		}
		else
		{
			// Don't use any context
			$res = @copy($src, $dest);
		}

		if (!$res && $php_errormsg)
		{
			$this->setError($php_errormsg);
		}
		else
		{
			$this->chmod($chmodDest);
		}

		// Restore error tracking to what it was before
		ini_set('track_errors', $track_errors);

		return $res;
	}

	/**
	 * Moves a file
	 *
	 * @param   string    $src         The file path to move from.
	 * @param   string    $dest        The file path to move to.
	 * @param   resource  $context     A valid context resource (optional) created with stream_context_create.
	 * @param   boolean   $use_prefix  Controls the use of a prefix (optional).
	 * @param   boolean   $relative    Determines if the filename given is relative. Relative paths do not have JPATH_ROOT stripped.
	 *
	 * @return  mixed
	 *
	 * @since   1.7.0
	 */
	public function move($src, $dest, $context = null, $use_prefix = true, $relative = false)
	{
		// Capture PHP errors
		$php_errormsg = '';
		$track_errors = ini_get('track_errors');
		ini_set('track_errors', true);

		$src = $this->_getFilename($src, 'w', $use_prefix, $relative);
		$dest = $this->_getFilename($dest, 'w', $use_prefix, $relative);

		if ($context)
		{
			// Use the provided context
			$res = @rename($src, $dest, $context);
		}
		elseif ($this->context)
		{
			// Use the object's context
			$res = @rename($src, $dest, $this->context);
		}
		else
		{
			// Don't use any context
			$res = @rename($src, $dest);
		}

		if (!$res && $php_errormsg)
		{
			$this->setError($php_errormsg());
		}

		$this->chmod($dest);

		// Restore error tracking to what it was before
		ini_set('track_errors', $track_errors);

		return $res;
	}

	/**
	 * Delete a file
	 *
	 * @param   string    $filename    The file path to delete.
	 * @param   resource  $context     A valid context resource (optional) created with stream_context_create.
	 * @param   boolean   $use_prefix  Controls the use of a prefix (optional).
	 * @param   boolean   $relative    Determines if the filename given is relative. Relative paths do not have JPATH_ROOT stripped.
	 *
	 * @return  mixed
	 *
	 * @since   1.7.0
	 */
	public function delete($filename, $context = null, $use_prefix = true, $relative = false)
	{
		// Capture PHP errors
		$php_errormsg = '';
		$track_errors = ini_get('track_errors');
		ini_set('track_errors', true);

		$filename = $this->_getFilename($filename, 'w', $use_prefix, $relative);

		if ($context)
		{
			// Use the provided context
			$res = @unlink($filename, $context);
		}
		elseif ($this->context)
		{
			// Use the object's context
			$res = @unlink($filename, $this->context);
		}
		else
		{
			// Don't use any context
			$res = @unlink($filename);
		}

		if (!$res && $php_errormsg)
		{
			$this->setError($php_errormsg());
		}

		// Restore error tracking to what it was before.
		ini_set('track_errors', $track_errors);

		return $res;
	}

	/**
	 * Upload a file
	 *
	 * @param   string    $src         The file path to copy from (usually a temp folder).
	 * @param   string    $dest        The file path to copy to.
	 * @param   resource  $context     A valid context resource (optional) created with stream_context_create.
	 * @param   boolean   $use_prefix  Controls the use of a prefix (optional).
	 * @param   boolean   $relative    Determines if the filename given is relative. Relative paths do not have JPATH_ROOT stripped.
	 *
	 * @return  mixed
	 *
	 * @since   1.7.0
	 */
	public function upload($src, $dest, $context = null, $use_prefix = true, $relative = false)
	{
		if (is_uploaded_file($src))
		{
			// Make sure it's an uploaded file
			return $this->copy($src, $dest, $context, $use_prefix, $relative);
		}
		else
		{
			$this->setError(Text::_('JLIB_FILESYSTEM_ERROR_STREAMS_NOT_UPLOADED_FILE'));

			return false;
		}
	}

	/**
	 * Writes a chunk of data to a file.
	 *
	 * @param   string  $filename  The file name.
	 * @param   string  &$buffer   The data to write to the file.
	 *
	 * @return  boolean
	 *
	 * @since   1.7.0
	 */
	public function writeFile($filename, &$buffer)
	{
		if ($this->open($filename, 'w'))
		{
			$result = $this->write($buffer);
			$this->chmod();
			$this->close();

			return $result;
		}

		return false;
	}

	/**
	 * Determine the appropriate 'filename' of a file
	 *
	 * @param   string   $filename    Original filename of the file
	 * @param   string   $mode        Mode string to retrieve the filename
	 * @param   boolean  $use_prefix  Controls the use of a prefix
	 * @param   boolean  $relative    Determines if the filename given is relative. Relative paths do not have JPATH_ROOT stripped.
	 *
	 * @return  string
	 *
	 * @since   1.7.0
	 */
	public function _getFilename($filename, $mode, $use_prefix, $relative)
	{
		if ($use_prefix)
		{
			// Get rid of binary or t, should be at the end of the string
			$tmode = trim($mode, 'btf123456789');

			// Check if it's a write mode then add the appropriate prefix
			// Get rid of JPATH_ROOT (legacy compat) along the way
			if (in_array($tmode, FilesystemHelper::getWriteModes()))
			{
				if (!$relative && $this->writeprefix)
				{
					$filename = str_replace(JPATH_ROOT, '', $filename);
				}

				$filename = $this->writeprefix . $filename;
			}
			else
			{
				if (!$relative && $this->readprefix)
				{
					$filename = str_replace(JPATH_ROOT, '', $filename);
				}

				$filename = $this->readprefix . $filename;
			}
		}

		return $filename;
	}

	/**
	 * Return the internal file handle
	 *
	 * @return  resource  File handler
	 *
	 * @since   1.7.0
	 */
	public function getFileHandle()
	{
		return $this->fh;
	}
}
