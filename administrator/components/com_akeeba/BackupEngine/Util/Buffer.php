<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Util;

defined('AKEEBAENGINE') || die();

/**
 * Generic Buffer stream handler
 *
 * This class provides a generic buffer stream.  It can be used to store/retrieve/manipulate
 * string buffers with the standard PHP filesystem I/O methods.
 */
class Buffer
{

	/**
	 * Stream position
	 *
	 * @var    integer
	 */
	public $position = 0;

	/**
	 * Buffer name
	 *
	 * @var    string
	 */
	public $name = null;

	/**
	 * Buffer hash
	 *
	 * @var    array
	 */
	public $_buffers = [];

	/**
	 * Function to open file or url
	 *
	 * @param   string   $path          The URL that was passed
	 * @param   string   $mode          Mode used to open the file @see fopen
	 * @param   integer  $options       Flags used by the API, may be STREAM_USE_PATH and
	 *                                  STREAM_REPORT_ERRORS
	 * @param   string  &$opened_path   Full path of the resource. Used with STREAM_USE_PATH option
	 *
	 * @return  boolean
	 *
	 * @see     streamWrapper::stream_open
	 */
	public function stream_open($path, $mode, $options, &$opened_path)
	{
		$url                         = parse_url($path);
		$this->name                  = $url["host"];
		$this->_buffers[$this->name] = null;
		$this->position              = 0;

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
	 */
	public function stream_read($count)
	{
		$ret            = substr($this->_buffers[$this->name], $this->position, $count);
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
	 */
	public function stream_write($data)
	{
		$left                        = substr($this->_buffers[$this->name], 0, $this->position);
		$right                       = substr($this->_buffers[$this->name], $this->position + strlen($data));
		$this->_buffers[$this->name] = $left . $data . $right;
		$this->position              += strlen($data);

		return strlen($data);
	}

	/**
	 * Function to get the current position of the stream
	 *
	 * @return  integer
	 *
	 * @see     streamWrapper::stream_tell
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
	 */
	public function stream_eof()
	{
		return $this->position >= strlen($this->_buffers[$this->name]);
	}

	/**
	 * The read write position updates in response to $offset and $whence
	 *
	 * @param   integer  $offset   The offset in bytes
	 * @param   integer  $whence   Position the offset is added to
	 *                             Options are SEEK_SET, SEEK_CUR, and SEEK_END
	 *
	 * @return  boolean  True if updated
	 *
	 * @see     streamWrapper::stream_seek
	 */
	public function stream_seek($offset, $whence)
	{
		switch ($whence)
		{
			case SEEK_SET:
				if ($offset < strlen($this->_buffers[$this->name]) && $offset >= 0)
				{
					$this->position = $offset;

					return true;
				}
				else
				{
					return false;
				}
				break;

			case SEEK_CUR:
				if ($offset >= 0)
				{
					$this->position += $offset;

					return true;
				}
				else
				{
					return false;
				}
				break;

			case SEEK_END:
				if (strlen($this->_buffers[$this->name]) + $offset >= 0)
				{
					$this->position = strlen($this->_buffers[$this->name]) + $offset;

					return true;
				}
				else
				{
					return false;
				}
				break;

			default:
				return false;
		}
	}
}

// Register the stream
stream_wrapper_register("buffer", Buffer::class);
