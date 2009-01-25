<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	FileSystem
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// No direct access
defined('JPATH_BASE') or die();

jimport('joomla.filesystem.path');

/**
 * A virtual filesystem class
 *
 * @abstract
 * @package 	Joomla.Framework
 * @subpackage	FileSystem
 * @since		1.5
 */
abstract class JFileSystem
{
	/* @static
	 * @var array filesystem instances
	 */
	private static $instances = array();
	/* @var array filesystem options
	 */
	protected $_options = array();

	/* Constructor
	 *
	 * @access protected
	 * @param array of options
	 */
	protected function __construct($options) {
		$this->_options = $options;
	}

	/* Returns instance of JFileSystem
	 *
	 * note, unless force is set, if $instance->check() fails, it will default to php as the type
	 *
	 * @static
	 * @access public
	 * @param string type of filesystem to instansiate
	 * @param array options for filesystem
	 * @param boolean force instansiation (even if it won't work
	 * @returns object JFileSystem
	 */
	public static function &getInstance($type = null, $options = array(), $force = false) {
		$config = JFactory::getConfig();
		if(empty($type)) {
			$types = $config->get('config.filesystem',array());
			$type = isset($types['_default']) ? $types['_default'] : 'php';
		}
		$type = strtolower(JFilterInput::clean($type, 'word'));
		if(!isset(JFileSystem::$instances[$type]) || $force) {
			$path = JPATH_LIBRARIES.DS.'joomla'.DS.'filesystem'.DS.'filesystem'.DS.$type.'.php';
			$class = 'JFileSystem'.ucfirst($type);
			if(!class_exists($class) && file_exists($path)) {
				require_once $path;
			}

			if(empty($options)) {
				$alloptions = $config->get('config.filesystem', array());
				if(isset($options[$type])) {
					$options = $alloptions[$type];
				}
			}

			$instance = new $class($options);
			if(!$force && !$instance->check() && $type != 'php') {
				JError::raiseNotice('SOME_ERROR_CODE', JText::sprintf('Unable to initialize filesystem %s', $type));
				$path = JPATH_LIBRARIES.DS.'joomla'.DS.'filesystem'.DS.'filesystem'.DS.'php.php';
				require_once $path;
				$instance = new JFileSystemPHP();
			}
			if(!$force) {
				JFileSystem::$instances[$type] = $instance;
			}
		} else {
			$instance = JFileSystem::$instances[$type];
		}
		return $instance;
	}

	/* Returns array of filesystems.  Force determines if to include the backend if the test() fails
	 *
	 * @static
	 * @access public
	 * @param boolean include all backends, or only working ones
	 * @returns array of backend names
	 */
	public static function getBackends($force = false) {
		$arr = array();
		$files = JFolder::files(JPATH_LIBRARIES.DS.'joomla'.DS.'filesystem'.DS.'filesystem', '.+\.php$');
		foreach($files AS $file) {
			list($name, $ext) = explode('.', $files, 2);
			$class = 'JFilesystem'.ucfirst($name);
			$path = JPATH_LIBRARIES.DS.'joomla'.DS.'filesystem'.DS.'filesystem'.DS.$file;
			if(!class_exists($class)) {
				require_once $path;
			}
			if($force || call_user_func(array($class, 'test'))) {
				$arr[] = $name;
			}
		}
		return $arr;
	}

	/*Get JParameter object for given filesystem
	 *
	 * @static
	 * @access public
	 * @param string type of file sysem
	 * @returns object JParameter instance
	 */
	public static function &getParams($type) {
		$type = strtolower(JFilterInput::clean($type, 'word'));
		$options = JFileSystem::getConfig($type);
		$xmlPath = JPATH_LIBRARIES.DS.'joomla'.DS.'filesystem'.DS.'filesystem'.DS.$type.'.xml';
		if(!file_exists($xmlPath)) {
			$xmlPath = '';
		}
		$params = new JParameter($options, $xmlPath);
		return $params;
	}

	abstract public static function test();

	abstract public function check();

	abstract public function copy($src, $dest);

	abstract public function delete($src);

	abstract public function rename($src, $dest);

	abstract public function read($path, $include_path=true, $length=0, $offset=0);

	abstract public function isReadable($path);

	abstract public function write($path, &$buffer);

	abstract public function isWritable($path);

	abstract public function chmod($path, $hex);

	abstract public function chgrp($path, $group);

	abstract public function chown($path, $owner);

	abstract public function mkdir($path);

	abstract public function rmdir($path);

	abstract public function perms($path);

	abstract public function owner($path);
}
