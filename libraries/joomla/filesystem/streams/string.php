<?php
/**
 * @package     Joomla.Platform
 * @subpackage  FileSystem
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.support.stringcontroller');

/**
 * String Stream Wrapper
 *
 * This class allows you to use a PHP string in the same way that
 * you would normally use a regular stream wrapper
 *
 * @since  11.1
 */
class JStreamString
{
	/**
	 * The current string
	 *
	 * @var   string
	 * @since  12.1
	 */
	protected $currentString;

	/**
	 * The path
	 *
	 * @var   string
	 * @since  12.1
	 */
	protected $path;

	/**
	 * The mode
	 *
	 * @var   string
	 * @since  12.1
	 */
	protected $mode;

	/**
	 * Enter description here ...
	 *
	 * @var    string
	 * @since  12.1
	 */
	protected $options;

	/**
	 * Enter description here ...
	 *
	 * @var    string
	 * @since  12.1
	 */
	protected $openedPath;

	/**
	 * Current position
	 *
	 * @var    integer
	 * @since  12.1
	 */
	protected $pos;

	/**
	 * Length of the string
	 *
	 * @var    string
	 * @since  12.1
	 */
	protected $len;

	/**
	 * Statistics for a file
	 *
	 * @var    array
	 * @since  12.1
	 *
	 * @link   http://us.php.net/manual/en/function.stat.php
	 */
	protected $stat;

	/**
	 * Method to open a file or URL.
	 *
	 * @param   string   $path          The stream path.
	 * @param   string   $mode          Not used.
	 * @param   integer  $options       Not used.
	 * @param   string   &$opened_path  Not used.
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	public function stream_open($path, $mode, $options, &$opened_path)
	{
		$this->currentString = &JStringController::getRef(str_replace('string://', '', $path));

		if ($this->currentString)
		{
			$this->len = strlen($this->currentString);
			$this->pos = 0;
			$this->stat = $this->url_stat($path, 0);

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to retrieve information from a file resource
	 *
	 * @return  array
	 *
	 * @link    https://secure.php.net/manual/en/streamwrapper.stream-stat.php
	 * @since   11.1
	 */
	public function stream_stat()
	{
		return $this->stat;
	}

	/**
	 * Method to retrieve information about a file.
	 *
	 * @param   string   $path   File path or URL to stat
	 * @param   integer  $flags  Additional flags set by the streams API
	 *
	 * @return  array
	 *
	 * @link    https://secure.php.net/manual/en/streamwrapper.url-stat.php
	 * @since   11.1
	 */
	public function url_stat($path, $flags = 0)
	{
		$now = time();
		$string = &JStringController::getRef(str_replace('string://', '', $path));
		$stat = array(
			'dev' => 0,
			'ino' => 0,
			'mode' => 0,
			'nlink' => 1,
			'uid' => 0,
			'gid' => 0,
			'rdev' => 0,
			'size' => strlen($string),
			'atime' => $now,
			'mtime' => $now,
			'ctime' => $now,
			'blksize' => '512',
			'blocks' => ceil(strlen($string) / 512),
		);

		return $stat;
	}

	/**
	 * Method to read a given number of bytes starting at the current position
	 * and moving to the end of the string defined by the current position plus the
	 * given number.
	 *
	 * @param   integer  $count  Bytes of data from the current position should be returned.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 *
	 * @link    https://secure.php.net/manual/en/streamwrapper.stream-read.php
	 */
	public function stream_read($count)
	{
		$result = substr($this->currentString, $this->pos, $count);
		$this->pos += $count;

		return $result;
	}

	/**
	 * Stream write, always returning false.
	 *
	 * @param   string  $data  The data to write.
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 * @note    Updating the string is not supported.
	 */
	public function stream_write($data)
	{
		// We don't support updating the string.
		return false;
	}

	/**
	 * Method to get the current position
	 *
	 * @return  integer  The position
	 *
	 * @since   11.1
	 */
	public function stream_tell()
	{
		return $this->pos;
	}

	/**
	 * End of field check
	 *
	 * @return  boolean  True if at end of field.
	 *
	 * @since   11.1
	 */
	public function stream_eof()
	{
		if ($this->pos > $this->len)
		{
			return true;
		}

		return false;
	}

	/**
	 * Stream offset
	 *
	 * @param   integer  $offset  The starting offset.
	 * @param   integer  $whence  SEEK_SET, SEEK_CUR, SEEK_END
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function stream_seek($offset, $whence)
	{
		// $whence: SEEK_SET, SEEK_CUR, SEEK_END
		if ($offset > $this->len)
		{
			// We can't seek beyond our len.
			return false;
		}

		switch ($whence)
		{
			case SEEK_SET:
				$this->pos = $offset;
				break;

			case SEEK_CUR:
				if (($this->pos + $offset) < $this->len)
				{
					$this->pos += $offset;
				}
				else
				{
					return false;
				}
				break;

			case SEEK_END:
				$this->pos = $this->len - $offset;
				break;
		}

		return true;
	}

	/**
	 * Stream flush, always returns true.
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 * @note    Data storage is not supported
	 */
	public function stream_flush()
	{
		// We don't store data.
		return true;
	}
}

stream_wrapper_register('string', 'JStreamString') or die('JStreamString Wrapper Registration Failed');
