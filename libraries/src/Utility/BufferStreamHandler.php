<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Utility;

defined('JPATH_PLATFORM') or die;

// Workaround for B/C. Will be removed with 4.0
BufferStreamHandler::stream_register();

/**
 * Generic Buffer stream handler
 *
 * This class provides a generic buffer stream.  It can be used to store/retrieve/manipulate
 * string buffers with the standard PHP filesystem I/O methods.
 *
 * @since  11.1
 */
class BufferStreamHandler
{
	/**
	 * Stream position
	 *
	 * @var    integer
	 * @since  11.1
	 */
	public $position = 0;

	/**
	 * Buffer name
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $name = null;

	/**
	 * Buffer hash
	 *
	 * @var    array
	 * @since  12.1
	 */
	public $buffers = array();

	/**
	 * Status of registering the wrapper
	 *
	 * @var    boolean
	 * @since  3.8.2
	 */
	static private $registered = false;

	/**
	 * Function to register the stream wrapper
	 *
	 * @return  void
	 *
	 * @since  3.8.2
	 */
	public static function stream_register()
	{
		if (!self::$registered)
		{
			stream_wrapper_register('buffer', '\\Joomla\\CMS\\Utility\\BufferStreamHandler');

			self::$registered = true;
		}

		return;
	}

	/**
	 * Function to open file or url
	 *
	 * @param   string   $path          The URL that was passed
	 * @param   string   $mode          Mode used to open the file @see fopen
	 * @param   integer  $options       Flags used by the API, may be STREAM_USE_PATH and
	 *                                  STREAM_REPORT_ERRORS
	 * @param   string   &$opened_path  Full path of the resource. Used with STREAN_USE_PATH option
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 * @see     streamWrapper::stream_open
	 */
	public function stream_open($path, $mode, $options, &$opened_path)
	{
		$url = parse_url($path);
		$this->name = $url['host'];
		$this->buffers[$this->name] = null;
		$this->position = 0;

		return true;
	}

	/**
	 * Read stream
	 *
	 * @param   integer  $count  How many bytes of data from the current position should be returned.
	 *
	 * @return  mixed    The data from the stream up to the specified number of bytes (all data if
	 *                   the total number of bytes in the stream is less than $count. Null if
	 *                   the stream is empty.
	 *
	 * @see     streamWrapper::stream_read
	 * @since   11.1
	 */
	public function stream_read($count)
	{
		$ret = substr($this->buffers[$this->name], $this->position, $count);
		$this->position += strlen($ret);

		return $ret;
	}

	/**
	 * Write stream
	 *
	 * @param   string  $data  The data to write to the stream.
	 *
	 * @return  integer
	 *
	 * @see     streamWrapper::stream_write
	 * @since   11.1
	 */
	public function stream_write($data)
	{
		$left = substr($this->buffers[$this->name], 0, $this->position);
		$right = substr($this->buffers[$this->name], $this->position + strlen($data));
		$this->buffers[$this->name] = $left . $data . $right;
		$this->position += strlen($data);

		return strlen($data);
	}

	/**
	 * Function to get the current position of the stream
	 *
	 * @return  integer
	 *
	 * @see     streamWrapper::stream_tell
	 * @since   11.1
	 */
	public function stream_tell()
	{
		return $this->position;
	}

	/**
	 * Function to test for end of file pointer
	 *
	 * @return  boolean  True if the pointer is at the end of the stream
	 *
	 * @see     streamWrapper::stream_eof
	 * @since   11.1
	 */
	public function stream_eof()
	{
		return $this->position >= strlen($this->buffers[$this->name]);
	}

	/**
	 * The read write position updates in response to $offset and $whence
	 *
	 * @param   integer  $offset  The offset in bytes
	 * @param   integer  $whence  Position the offset is added to
	 *                            Options are SEEK_SET, SEEK_CUR, and SEEK_END
	 *
	 * @return  boolean  True if updated
	 *
	 * @see     streamWrapper::stream_seek
	 * @since   11.1
	 */
	public function stream_seek($offset, $whence)
	{
		switch ($whence)
		{
			case SEEK_SET :
				return $this->seek_set($offset);

			case SEEK_CUR :

				return $this->seek_cur($offset);

			case SEEK_END :

				return $this->seek_end($offset);
		}

		return false;
	}

	/**
	 * Set the position to the offset
	 *
	 * @param   integer  $offset  The offset in bytes
	 *
	 * @return bool
	 */
	protected function seek_set($offset)
	{
		if ($offset < 0 || $offset > strlen($this->buffers[$this->name]))
		{
			return false;
		}

		$this->position = $offset;

		return true;
	}

	/**
	 * Adds the offset to current position
	 *
	 * @param   integer  $offset  The offset in bytes
	 *
	 * @return bool
	 */
	protected function seek_cur($offset)
	{
		if ($offset < 0)
		{
			return false;
		}

		$this->position += $offset;

		return true;
	}

	/**
	 * Sets the position to the end of the current buffer + offset
	 *
	 * @param   integer  $offset  The offset in bytes
	 *
	 * @return bool
	 */
	protected function seek_end($offset)
	{
		$offset += strlen($this->buffers[$this->name]);
		if ($offset < 0)
		{
			return false;
		}

		$this->position = $offset;

		return true;
	}
}
