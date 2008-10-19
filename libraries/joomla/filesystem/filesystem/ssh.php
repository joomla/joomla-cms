<?php
/**
 * @version		$Id: ssh.php 9764 2007-12-30 07:48:11Z ircmaxell $
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
 * SSH Filesystem backend class
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	FileSystem
 * @since	1.6
 */
class JFilesystemSSH extends JFilesystem
{
	// FTP Object
	protected $_ftp = null;

	protected function __construct($options) {
		parent::__construct($options);
	}

	public static function test() {
		return extension_loaded('ssh2');
	}

	public function check() {
		$ret = JFileSystemSSH::test();
		if($ret) {
			$config =& JFactory::getConfig();
			$path = $config->get('config.tmp_path');
			$file = md5(rand(0,10000)).'_tmp';
			if($this->write($path . DS . $file, 'write test')) {
				$this->delete($path . DS . $file);
				$ret = true;
			} else {
				$ret = false;
			}
		}
		return $ret;
	}

	public function copy($src, $dest) {
		$con =& $this->_getSSH();
		return ssh2_scp_send($this->_makePath($src), $this->_makePath($dest));
	}

	public function delete($src) {
		$con =& $this->_getSFTP();
		return ssh2_sftp_unlink($con, $this->_makePath($src));
	}

	public function rename($src, $dest) {
		$con =& $this->_getSFTP();
		return ssh2_sftp_rename($con, $this->_makePath($src), $this->_makePath($dest));
	}

	public function read($src) {
		$config =& JFactory::getConfig();
		$path = $config->get('config.tmp_path');
		$file = md5(rand(0,10000)).'_tmp';
		$con =& $this->_getSSH();
		$buffer = false;
		if(ssh2_scp_recv($con, $this->_makePath($src), $path . DS . $file)) {
			$buffer = @file_get_contents($path. DS . $file);
			@unlink($path . DS . $file);
		}

		return $buffer;
	}

	public function write($file, $buffer) {
		//write to temp file
		$config =& JFactory::getConfig();
		$path = $config->get('config.tmp_path');
		$file = md5(rand(0,10000)).'_tmp';
		$ret = false;
		if(@file_put_contents($path . DS . $file, $buffer)) {
			$con =& $this->_getSSH();
			$ret = ssh2_scp_send($con, $path . DS . $file, $this->_makePath($src));
			@unlink($path . DS . $file);
		}
		return $ret;
	}

	public function isWritable($path) {
		return false;
	}

	public function isReadable($path) {
		return false;
	}

	public function chmod($path, $hex) {
		return false;
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
		$con =& $this->_getSFTP();
		return ssh2_sftp_mkdir($con, $this->_makePath($path));
	}

	public function rmdir($path) {
		$con =& $this->_getSFTP();
		return ssh2_sftp_rmdir($con, $this->_makePath($path));
	}

	public function perms($path) {
		return fileperms($path);
	}

	public function owner($path) {
		return fileowner($path);
	}

	protected function &_getSSH() {
		static $obj = null;
		if(!is_resource($obj)) {
			$obj = ssh2_connect($this->_options['host'], $this->_options['port']);
			if($obj) {
				ssh2_auth_password($obj, $this->_options['user'], $this->_options['pass']);
			}
		}
		return $obj;
	}

	protected function &_getSFTP() {
		static $obj = null;
		if(!is_resource($obj)) {
			$obj = false;
			$con =& $this->_getSSH;
			if($con) {
				$obj = ssh2_sftp($con);
			}
		}
	}

	protected function _makePath($path) {
		return JPath::clean(str_replace(JPATH_ROOT, $this->_options['root'], $path), '/');
	}

}
