<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	FileSystem
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

/**
 * FTP Filesystem backend class
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	FileSystem
 * @since	1.6
 */
class JFilesystemFTP extends JFilesystem
{
	// FTP Object
	protected $_ftp = null;

	protected function __construct($options) {
		parent::__construct($options);
		jimport('joomla.client.helper');
		if ($this->_options['enabled']) {
			$this->_ftp = $this->_getFTP();
		}
	}

	public static function test() {
		return true;
	}

	public function check() {
		$ret = false;
		if (!$this->_options['enabled']) {
			return $ret;
		}
		$config =& JFactory::getConfig();
		$path = $config->get('config.tmp_path');
		$file = md5(rand(0,10000)).'_tmp';
		if ($this->write($path . DS . $file, 'write test')) {
			$this->delete($path . DS . $file);
			$ret = true;
		}
		return $ret;
	}

	public function copy($src, $dest) {
		return $this->_ftp->store($this->_makePath($src), $this->_makePath($dest));
	}

	public function delete($src) {
		return $this->_ftp->delete($this->_makePath($src));
	}

	public function rename($src, $dest) {
		return $this->_ftp->rename($this->_makePath($src), $this->_makePath($dest));
	}

	public function read($src) {
		$buffer = null;
		if (!$this->_ftp->read($this->_makePath($src), $buffer)) {
			$buffer = false;
		}
		return $buffer;
	}

	public function write($file, &$buffer) {
		return $this->_ftp->write($this->_makePath($file), $buffer);
	}

	public function isWritable($path) {
		$buffer = null;
		$this->_ftp->read($this->_makePath($path), $buffer);
		return (bool) $this->write($path, $buffer);
	}

	public function isReadable($path) {
		$buffer = null;
		return (bool) $this->_ftp->read($this->_makePath($path), $buffer);
	}

	public function chmod($path, $hex) {
		return $this->_ftp->chmod($this->_makePath($path), $hex);
	}

	public function chgrp($file, $group) {
		return false;
	}

	public function chown($file, $owner) {
		return false;
	}

	public function exists($file) {
		return file_exists($file);
	}

	public function mkdir($path) {
		return $this->_ftp->mkdir($this->_makePath($path));
	}

	public function rmdir($path) {
		return $this->_ftp->delete($this->_makePath($path));
	}

	public function perms($path) {
		return fileperms($path);
	}

	public function owner($path) {
		return fileowner($path);
	}

	protected function &_getFTP() {
		static $obj = null;
		if (!is_object($obj)) {
			jimport('joomla.client.ftp');

			$obj =& JFTP::getInstance($this->_options['host'], $this->_options['port'], null, $this->_options['user'], $this->_options['pass']);

		}
		return $obj;
	}

	protected function _makePath($path) {
		return JPath::clean(str_replace(JPATH_ROOT, $this->_options['root'], $path), '/');
	}

}
