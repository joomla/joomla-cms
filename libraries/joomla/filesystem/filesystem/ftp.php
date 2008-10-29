<?php
/**
 * @version		$Id: ftp.php 9764 2007-12-30 07:48:11Z ircmaxell $
 * @package		Joomla.Framework
 * @subpackage	FileSystem
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
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
		if($this->_options['enabled']) {
			$this->_ftp = $this->_getFTP();
		}
	}

	public static function test() {
		return true;
	}

	public function check() {
		$ret = false;
		if(!$this->_options['enabled']) {
			return $ret;
		}
		$config =& JFactory::getConfig();
		$path = $config->get('config.tmp_path');
		$file = md5(rand(0,10000)).'_tmp';
		if($this->write($path . DS . $file, 'write test')) {
			$this->delete($path . DS . $file);
			$ret = true;
		}
		return $ret;
	}

	public function copy($src, $dest) {
		return $this->ftp->store($this->_makePath($src), $this->_makePath($dest));
	}

	public function delete($src) {
		return $this->ftp->delete($this->_makePath($src));
	}

	public function rename($src, $dest) {
		return $this->ftp->rename($this->_makePath($src), $this->_makePath($dest));
	}

	public function read($src) {
		if(!$this->ftp->read($this->_makePath($src), $buffer)) {
			$buffer = false;
		}
		return $buffer;
	}

	public function write($file, &$buffer) {
		return $this->ftp->write($this->_makePath($file), $buffer);
	}

	public function isWritable($path) {
		$this->ftp->read($this->_makePath($path), $buffer);
		return (bool) $this->write($path, $buffer);
	}

	public function isReadable($path) {
		return (bool) $this->ftp->read($this->_makePath($path), $buffer);
	}

	public function chmod($path, $hex) {
		return $this->ftp->chmod($this->_makePath($path), $hex);
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
		return $this->ftp->mkdir($this->_makePath($path));
	}

	public function rmdir($path) {
		return $this->ftp->delete($this->_makePath($path));
	}

	public function perms($path) {
		return fileperms($path);
	}

	public function owner($path) {
		return fileowner($path);
	}

	protected function &_getFTP() {
		static $obj = null;
		if(!is_object($obj)) {
			jimport('joomla.client.ftp');

			$obj =& JFTP::getInstance($this->_options['host'], $this->_options['port'], null, $this->_options['user'], $this->_options['pass']);

		}
		return $obj;
	}

	protected function _makePath($path) {
		return JPath::clean(str_replace(JPATH_ROOT, $this->_options['root'], $path), '/');
	}

}
