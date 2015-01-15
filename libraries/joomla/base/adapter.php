<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Base
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Adapter Class
 * Retains common adapter pattern functions
 * Class harvested from joomla.installer.installer
 *
 * @since  11.1
 */
class JAdapter extends JObject
{
	/**
	 * Associative array of adapters
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $_adapters = array();

	/**
	 * Adapter Folder
	 * @var    string
	 * @since  11.1
	 */
	protected $_adapterfolder = 'adapters';

	/**
	 * @var    string	Adapter Class Prefix
	 * @since  11.1
	 */
	protected $_classprefix = 'J';

	/**
	 * Base Path for the adapter instance
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_basepath = null;

	/**
	 * Database Connector Object
	 *
	 * @var    JDatabaseDriver
	 * @since  11.1
	 */
	protected $_db;

	/**
	 * Constructor
	 *
	 * @param   string  $basepath       Base Path of the adapters
	 * @param   string  $classprefix    Class prefix of adapters
	 * @param   string  $adapterfolder  Name of folder to append to base path
	 *
	 * @since   11.1
	 */
	public function __construct($basepath, $classprefix = null, $adapterfolder = null)
	{
		$this->_basepath = $basepath;
		$this->_classprefix = $classprefix ? $classprefix : 'J';
		$this->_adapterfolder = $adapterfolder ? $adapterfolder : 'adapters';

		$this->_db = JFactory::getDbo();
	}

	/**
	 * Get the database connector object
	 *
	 * @return  JDatabaseDriver  Database connector object
	 *
	 * @since   11.1
	 */
	public function getDBO()
	{
		return $this->_db;
	}

	/**
	 * Set an adapter by name
	 *
	 * @param   string  $name      Adapter name
	 * @param   object  &$adapter  Adapter object
	 * @param   array   $options   Adapter options
	 *
	 * @return  boolean  True if successful
	 *
	 * @since   11.1
	 */
	public function setAdapter($name, &$adapter = null, $options = array())
	{
		if (!is_object($adapter))
		{
			$fullpath = $this->_basepath . '/' . $this->_adapterfolder . '/' . strtolower($name) . '.php';

			if (!file_exists($fullpath))
			{
				return false;
			}

			// Try to load the adapter object
			require_once $fullpath;

			$class = $this->_classprefix . ucfirst($name);

			if (!class_exists($class))
			{
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
	 * @param   string  $name     Name of adapter to return
	 * @param   array   $options  Adapter options
	 *
	 * @return  object  Adapter of type 'name' or false
	 *
	 * @since   11.1
	 */
	public function getAdapter($name, $options = array())
	{
		if (!array_key_exists($name, $this->_adapters))
		{
			if (!$this->setAdapter($name, $options))
			{
				$false = false;

				return $false;
			}
		}

		return $this->_adapters[$name];
	}

	/**
	 * Loads all adapters.
	 *
	 * @param   array  $options  Adapter options
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function loadAllAdapters($options = array())
	{
		$files = new DirectoryIterator($this->_basepath . '/' . $this->_adapterfolder);

		/* @type  $file  DirectoryIterator */
		foreach ($files as $file)
		{
			$fileName = $file->getFilename();

			// Only load for php files.
			if (!$file->isFile() || $file->getExtension() != 'php')
			{
				continue;
			}

			// Try to load the adapter object
			require_once $this->_basepath . '/' . $this->_adapterfolder . '/' . $fileName;

			// Derive the class name from the filename.
			$name = str_ireplace('.php', '', ucfirst(trim($fileName)));
			$class = $this->_classprefix . ucfirst($name);

			if (!class_exists($class))
			{
				// Skip to next one
				continue;
			}

			$adapter = new $class($this, $this->_db, $options);
			$this->_adapters[$name] = clone $adapter;
		}
	}
}
