<?php
/**
 * String Stream Wrapper
 *
 * This file allows you to use a PHP string like
 * you would normally use a regular stream wrapper
 *
 * PHP5
 *
 * Created on Aug 7, 2008
 *
 * @package stringstream
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 * @version SVN: $Id$
 */

// No direct access.
defined('JPATH_BASE') or die();

jimport('joomla.filesystem.support.stringcontroller');

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

	function stream_open($path, $mode, $options, &$opened_path)
	{
		$this->_currentstring =& JStringController::getRef(str_replace('string://','',$path));
		if($this->_currentstring)
		{
			$this->_len = strlen($this->_currentstring);
			$this->_pos = 0;
			$this->_stat = $this->url_stat($path, 0);
			return true;
		}
		else {
			return false;
		}
	}

	function stream_stat()
	{
		return $this->_stat;
	}

	function url_stat($path, $flags=0)
	{
		$now = time();
		$string =& JStringController::getRef(str_replace('string://','',$path));
		$stat = Array(
			'dev'=> 0,
			'ino'=> 0,
			'mode'=>0,
			'nlink'=>1,
			'uid'=>0,
			'gid'=>0,
			'rdev'=>0,
			'size'=>strlen($string),
			'atime'=>$now,
			'mtime'=>$now,
			'ctime'=>$now,
			'blksize'=>'512',
			'blocks'=>ceil(strlen($string) / 512)
		);
		return $stat;
	}

	function stream_read($count)
	{
		$result = substr($this->_currentstring, $this->_pos, $count);
		$this->_pos += $count;
		return $result;
	}

	function stream_write($data)
	{
		// We don't support updating the string.
		return false;
	}

	function stream_tell()
	{
		return $this->_pos;
	}

	function stream_eof()
	{
		if ($this->_pos > $this->_len) {
			return true;
		}
		return false;
	}

	function stream_seek($offset, $whence) {
		//$whence: SEEK_SET, SEEK_CUR, SEEK_END
		if($offset > $this->_len) {
			return false; // we can't seek beyond our len
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

	function stream_flush()
	{
		return true; // we don't store data
	}
}

stream_wrapper_register('string', 'JStreamString') or die('Failed to register string stream');
