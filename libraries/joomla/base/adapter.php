<?php
/**
 * @version		$Id: object.php 9764 2007-12-30 07:48:11Z ircmaxell $
 * @package		Joomla.Framework
 * @subpackage	Base
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * Adapter Class
 * Retains common adapter pattern functions
 * Class harvested from joomla.installer.installer
 *
 * @package		Joomla.Framework
 * @subpackage	Base
 * @since		1.6
 */
class JAdapter extends JObject {
	/**
	 * Associative array of adapters
	 * @var array
	 */
	protected $_adapters = array();
	/**
	 * Adapter Folder
	 * @var string
	 */
	protected $_adapterfolder = 'adapters';
	/**
	 * Adapter Class Prefix
	 * @var string
	 */
	protected $_classprefix = 'J';
	
	/**
	 * Base Path for the adapter instance
	 * @var string
	 */
	protected $_basepath = null;
	/**
	 * Database Connector Object
	 * @var object
	 */
	protected $_db;
	
	public function __construct($basepath=null,$classprefix=null,$adapterfolder=null) {
		$this->_basepath = $basepath ? $basepath : dirname(__FILE__);
		$this->_classprefix = $classprefix ? $classprefix : 'J';
		$this->_adapterfolder = $adapterfolder ? $adapterfolder : $this->_adapterfolder;
		$this->_db =& JFactory::getDBO();
	}
	
	/**
	 * Get the database connector object
	 *
	 * @access	public
	 * @return	object	Database connector object
	 * @since	1.5
	 */
	public function &getDBO()
	{
		return $this->_db;
	}	
	
	/**
	 * Set an installer adapter by name
	 *
	 * @access	public
	 * @param	string	$name		Adapter name
	 * @param	object	$adapter	Installer adapter object
	 * @return	boolean True if successful
	 * @since	1.5
	 */
	public function setAdapter($name, &$adapter = null)
	{
		if (!is_object($adapter))
		{
			// Try to load the adapter object
			require_once($this->_basepath.DS.$this->_adapterfolder.DS.strtolower($name).'.php');
			$class = $this->_classprefix.ucfirst($name);
			if (!class_exists($class)) {
				return false;
			}
			$adapter = new $class($this);
			$adapter->parent =& $this;
		}
		$this->_adapters[$name] =& $adapter;
		return true;
	}	
		
	/**
	 * Loads all adapters
	 */
	public function loadAllAdapters() {
		$list = JFolder::files($this->_basepath.DS.$this->_adapterfolder);
		foreach($list as $filename) {
			if(JFile::getExt($filename) == 'php') {
				// Try to load the adapter object
				require_once($this->_basepath.DS.$this->_adapterfolder.DS.$filename);
				
				$name = JFile::stripExt($filename);
				$class = $this->_classprefix.ucfirst($name);
				if (!class_exists($class)) {
					return false;
				}
				$adapter = new $class($this);
				$this->_adapters[$name] = clone($adapter);
			}
		}
	}
}
