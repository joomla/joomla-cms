<?php
/**
 * @package     Joomla.Platform
 * @subpackage  FileSystem
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.support.stringcontroller');

/**
 * String Stream Wrapper
 *
 * This class allows you to use a PHP string like
 * you would normally use a regular stream wrapper
 *
 * @package		Joomla.Platform
 * @subpackage	FileSystem
 * @since		11.1
 */
class JStreamString
{
	private $_currentstring;

	private $_path;

	private $_mode;

	private $_options;

	private $_opened_path;

	private $_pos;

	private $_len;

	private $_stat;

	/**
	 * @param	$path
	 * @param	$mode
	 * @param	$options
	 * @param	$opened_path
	 *
	 * @return	boolean
	 * @since	11.1
	 */
	function stream_open($path, $mode, $options, &$opened_path)
	{
		$this->_currentstring = &JStringController::getRef(str_replace('string://','',$path));

		if($this->_currentstring) {
			$this->_len = strlen($this->_currentstring);
			$this->_pos = 0;
			$this->_stat = $this->url_stat($path, 0);

			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * @return
	 * @since	11.1
	 */
	function stream_stat()
	{
		return $this->_stat;
	}

	/**
	 * @param	$path
	 * @param	$flags
	 *
	 * @return
	 * @since	11.1
	 */
	function url_stat($path, $flags = 0)
	{
		$now = time();
		$string = &JStringController::getRef(str_replace('string://','',$path));
		$stat = array(
			'dev'		=> 0,
			'ino'		=> 0,
			'mode'		=> 0,
			'nlink'		=> 1,
			'uid'		=> 0,
			'gid'		=> 0,
			'rdev'		=> 0,
			'size'		=> strlen($string),
			'atime'		=> $now,
			'mtime'		=> $now,
			'ctime'		=> $now,
			'blksize'	=> '512',
			'blocks'	=> ceil(strlen($string) / 512)
		);

		return $stat;
	}

	/**
	 * @param	$count
	 *
	 * @return
	 * @since	11.1
	 */
	function stream_read($count)
	{
		$result = substr($this->_currentstring, $this->_pos, $count);
		$this->_pos += $count;

		return $result;
	}

	/**
	 * @return	boolean
	 * @since	11.1
	 */
	function stream_write($data)
	{
		// We don't support updating the string.
		return false;
	}

	/**
	 * @return
	 * @since	11.1
	 */
	function stream_tell()
	{
		return $this->_pos;
	}

	/**
	 * @return	boolean
	 * @since	11.1
	 */
	function stream_eof()
	{
		if ($this->_pos > $this->_len) {
			return true;
		}

		return false;
	}

	/**
	 * @param	$offset
	 * @param	$whence
	 *
	 * @return
	 * @since	11.1
	 */
	function stream_seek($offset, $whence)
	{
		// $whence: SEEK_SET, SEEK_CUR, SEEK_END
		if ($offset > $this->_len) {
			// We can't seek beyond our len.
			return false;
		}

		switch($whence)
		{
			case SEEK_SET:
				$this->_pos = $offset;
				break;

			case SEEK_CUR:
				if (($this->_pos + $offset) < $this->_len) {
					$this->_pos += $offset;
				}
				else {
					return false;
				}
				break;

			case SEEK_END:
				$this->_pos = $this->_len - $offset;
				break;
		}

		return true;
	}

	/**
	 * @return	boolean
	 * @since	11.1
	 */
	function stream_flush()
	{
		// We don't store data.
		return true;
	}
}

stream_wrapper_register('string', 'JStreamString') or die(JText::_('JLIB_FILESYSTEM_STREAM_FAILED'));
