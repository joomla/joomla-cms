<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Base
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Adapter Class
 * Retains common adapter pattern functions
 * Class harvested from joomla.installer.installer
 *
 * @package		Joomla.Framework
 * @subpackage	Base
 * @since		1.6
 */
class JAdapter extends JObject
{
	/**
	 * @var		array	Associative array of adapters
	 * @since	1.6
	 */
	protected $_adapters = array();

	/**
	 * @var		string	Adapter Folder
	 * @since	1.6
	 */
	protected $_adapterfolder = 'adapters';

	/**
	 * @var		string	Adapter Class Prefix
	 * @since	1.6
	 */
	protected $_classprefix = 'J';

	/**
	 * @var		string	Base Path for the adapter instance
	 * @since	1.6
	 */
	protected $_basepath = null;

	/**
	 * @var		object	Database Connector Object
	 * @since	1.6
	 */
	protected $_db;

	/**
	 * Constructor
	 *
	 * @param	string	$basepath		Base Path of the adapters
	 * @param	string	$classprefix	Class prefix of adapters
	 * @param	string	$adapterfolder	Name of folder to append to base path
	 *
	 * @return	JAdapter
	 * @since	1.6
	 */
	public function __construct($basepath, $classprefix = null, $adapterfolder = null)
	{
		$this->_basepath		= $basepath;
		$this->_classprefix		= $classprefix ? $classprefix : 'J';
		$this->_adapterfolder	= $adapterfolder ? $adapterfolder : 'adapters';

		$this->_db = JFactory::getDBO();
	}

	/**
	 * Get the database connector object
	 *
	 * @return	object	Database connector object
	 * @since	1.6
	 */
	public function getDBO()
	{
		return $this->_db;
	}

	/**
	 * Set an adapter by name
	 *
	 * @param	string	$name		Adapter name
	 * @param	object	$adapter	Adapter object
	 * @param	array	$options	Adapter options
	 *
	 * @return	boolean True if successful
	 * @since	1.6
	 */
	public function setAdapter($name, &$adapter = null, $options = Array())
	{
		if (!is_object($adapter)) {
			$fullpath = $this->_basepath.DS.$this->_adapterfolder.DS.strtolower($name).'.php';

			if (!file_exists($fullpath)) {
				return false;
			}

			// Try to load the adapter object
			require_once $fullpath;

			$class = $this->_classprefix.ucfirst($name);
			if (!class_exists($class)) {
				return false;
			}

			$adapter = new $class($this, $this->_db, $options);
		}

		$this->_adapters[$name] = &$adapter;

		return true;
	}

	/**
	 * Return an adapter.
	 *
	 * @param 	string 	$name		Name of adapter to return
	 * @param 	array 	$options	Adapter options
	 *
	 * @return 	object 	Adapter of type 'name' or false
	 * @since	1.6
	 */
	public function getAdapter($name, $options = Array())
	{
		if (!array_key_exists($name, $this->_adapters)) {
			if (!$this->setAdapter($name, $options)) {
				$false = false;

				return $false;
			}
		}

		return $this->_adapters[$name];
	}

	/**
	 * Loads all adapters.
	 *
	 * @params	array	$options	Adapter options
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function loadAllAdapters($options = array())
	{
		$list = JFolder::files($this->_basepath.DS.$this->_adapterfolder);

		foreach ($list as $filename)
		{
			if (JFile::getExt($filename) == 'php') {
				// Try to load the adapter object
				require_once $this->_basepath.DS.$this->_adapterfolder.DS.$filename;

				$name = JFile::stripExt($filename);
				$class = $this->_classprefix.ucfirst($name);

				if (!class_exists($class)) {
					continue; // skip to next one
				}

				$adapter = new $class($this, $this->_db, $options);
				$this->_adapters[$name] = clone $adapter;
			}
		}
	}
}