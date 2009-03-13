<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	FileSystem
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

/**
 * PHP Filesystem backend class
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	FileSystem
 * @since		1.6
 */
class JFilesystemPHP extends JFilesystem
{

	protected function __construct($options) {
		parent::__construct($options);
	}

	public static function test() {
		return true;
	}

	public function check() {
		return true;
	}

	public function copy($src, $dest) {
		return @copy($src, $dest);
	}

	public function delete($src) {
		return @unlink($src);
	}

	public function rename($src, $dest) {
		return @rename($src, $dest);
	}

	public function read($src, $include_path=true, $length=-1, $offset=0) {
		if($length > 0) {
			return @file_get_contents($src, $include_path, null, $offset, $length);	
		} else {
			return @file_get_contents($src, $include_path, null, $offset);
		}
	}

	public function write($file, &$buffer) {
		return @file_put_contents($file, $buffer);
	}

	public function isWritable($path) {
		return is_writable($path);
	}

	public function isReadable($path) {
		return is_readable($path);
	}

	public function chmod($path, $hex) {
		return @chmod($path, $hex);
	}

	public function chgrp($file, $group) {
		return @chgrp($file, $group);
	}

	public function chown($file, $owner) {
		return @chown($file, $owner);
	}

	public function exists($file) {
		return file_exists($file);
	}

	public function mkdir($path) {
		return @mkdir($path);
	}

	public function rmdir($path) {
		return @rmdir($path);
	}

	public function perms($path) {
		return fileperms($path);
	}

	public function owner($path) {
		return fileowner($path);
	}
}
