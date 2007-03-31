<?php
/**
 * @version		$Id:buffer.php 6961 2007-03-15 16:06:53Z tcp $
 * @package		Joomla.Framework
 * @subpackage	Utilities
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * Generic Buffer stream handler
 *
 * This class provides a generic buffer stream.  It can be used to store/retrieve/manipulate string buffers with the standard
 * PHP filesystem I/O methods.
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage	Utilities
 * @since		1.5
 */
class JBufferStream
{
	/**
	 * Stream position
	 * @var int
	 */
	var $position = 0;

	/**
	 * Buffer name
	 * @var string
	 */
	var $name = null;

	/**
	 * Buffer hash
	 * @var array
	 */
	var $_buffers = array ();

	function stream_open($path, $mode, $options, & $opened_path) {
		$url = parse_url($path);
		$this->name = $url["host"];
		$this->_buffers[$this->name] = null;
		$this->position = 0;

		return true;
	}

	function stream_read($count) {
		$ret = substr($this->_buffers[$this->name], $this->position, $count);
		$this->position += strlen($ret);
		return $ret;
	}

	function stream_write($data) {
		$left = substr($this->_buffers[$this->name], 0, $this->position);
		$right = substr($this->_buffers[$this->name], $this->position + strlen($data));
		$this->_buffers[$this->name] = $left . $data . $right;
		$this->position += strlen($data);
		return strlen($data);
	}

	function stream_tell() {
		return $this->position;
	}

	function stream_eof() {
		return $this->position >= strlen($this->_buffers[$this->name]);
	}

	function stream_seek($offset, $whence) {
		switch ($whence) {
			case SEEK_SET :
				if ($offset < strlen($this->_buffers[$this->name]) && $offset >= 0) {
					$this->position = $offset;
					return true;
				} else {
					return false;
				}
				break;

			case SEEK_CUR :
				if ($offset >= 0) {
					$this->position += $offset;
					return true;
				} else {
					return false;
				}
				break;

			case SEEK_END :
				if (strlen($this->_buffers[$this->name]) + $offset >= 0) {
					$this->position = strlen($this->_buffers[$this->name]) + $offset;
					return true;
				} else {
					return false;
				}
				break;

			default :
				return false;
		}
	}
}
// Register the stream
stream_wrapper_register("buffer", "JBufferStream");