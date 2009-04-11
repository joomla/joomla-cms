<?php
/**
 * @version		$Id: stringstream.php 11476 2009-01-25 06:58:51Z eddieajau $
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

/**
 * String Stream Wrapper
 *
 * This file allows you to use a PHP string like
 * you would normally use a regular stream wrapper
 *
 * @package		Joomla.Administrator
 * @subpackage	Contact
 * @since		1.6
 * @todo		Integrate this with JStream
 */
class StringStreamController {

	function &_getArray() {
		static $strings = Array();
		return $strings;
	}

	function createRef($reference, &$string) {
		$ref =& StringStreamController::_getArray();
		$ref[$reference] =& $string;
	}


	function &getRef($reference) {
		$ref =& StringStreamController::_getArray();
		if (isset($ref[$reference])) {
			return $ref[$reference];
		} else {
			$false = false;
			return $false;
		}
	}
}


class StringStream {
	var $_currentstring;

	var $_path;
	var $_mode;
	var $_options;
	var $_opened_path;
	var $_pos;
	var $_len;

	function stream_open($path, $mode, $options, &$opened_path) {
		$this->_currentstring = StringStreamController::getRef(str_replace('string://','',$path));
		if ($this->_currentstring) {
			$this->_len = strlen($this->_currentstring);
			$this->_pos = 0;
			return true;
		} else {
			return false;
		}
	}

	function stream_stat() {
		return false;
	}

	function stream_read($count) {
		$result = substr($this->_currentstring, $this->_pos, $count);
		$this->_pos += $count;
		return $result;
	}

	function stream_write($data) {
		return strlen($data);
	}

	function stream_tell() {
		return $this->_pos;
	}

	function stream_eof() {
		if ($this->_pos > $this->_len) return true;
		return false;
	}

	function stream_seek($offset, $whence) {
		//$whence: SEEK_SET, SEEK_CUR, SEEK_END
		if ($offset > $this->_len) return false; // we can't seek beyond our len
		switch($whence) {
			case SEEK_SET:
				$this->_pos = $offset;
				break;
			case SEEK_CUR:
				if (($this->_pos + $offset) < $this->_len) {
					$this->_pos += $offset;
				} else return false;
				break;
			case SEEK_END:
				$this->_pos = $this->_len - $offset;
				break;
		}
		return true;
	}
}

stream_wrapper_register('string', 'StringStream') or die('Failed to register string stream');