<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive');
jimport('joomla.filesystem.path');
jimport('joomla.base.adapter');

/**
 * Joomla base installer class
 *
 * @package     Joomla.Platform
 * @subpackage  Installer
 * @since       11.1
 */
class JInstaller extends JAdapter
{
	/**
	 * Array of paths needed by the installer
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $_paths = array();

	/**
	 * True if packakge is an upgrade
	 *
	 * @var    boolean
	 * @since  11.1
	 */
	protected $_upgrade = null;

	/**
	 * The manifest trigger class
	 *
	 * @var    object
	 * @since  11.1
	 */
	public $manifestClass = null;

	/**
	 * True if existing files can be overwritten
	 * @var    boolean
	 * @since  11.1
	 */
	protected $_overwrite = false;

	/**
	 * Stack of installation steps
	 * - Used for installation rollback
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $_stepStack = array();

	/**
	 * Extension Table Entry
	 *
	 * @var    JTableExtension
	 * @since  11.1
	 */
	public $extension = null;

	/**
	 * The output from the install/uninstall scripts
	 *
	 * @var    string
	 * @since  11.1
	 * */
	public $message = null;

	/**
	 * The installation manifest XML object
	 *
	 * @var    object
	 * @since  11.1
	 */
	public $manifest = null;

	/**
	 * The extension message that appears
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $extension_message = null;

	/**
	 * The redirect URL if this extension (can be null if no redirect)
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $redirect_url = null;

	/**
	 * Constructor
	 *
	 * @return  JInstaller
	 *
	 * @since   11.1
	 */
	public function __construct()
	{
		parent::__construct(dirname(__FILE__), 'JInstaller');
	}

	/**
	 * Returns the global Installer object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return  object  An installer object
	 *
	 * @since   11.1
	 */
	public static function getInstance()
	{
		static $instance;

		if (!isset($instance))
		{
			$instance = new JInstaller();
		}
		return $instance;
	}

	/**
	 * Get the allow overwrite switch
	 *
	 * @return  boolean  Allow overwrite switch
	 *
	 * @since   11.1
	 */
	public function getOverwrite()
	{
		return $this->_overwrite;
	}

	/**
	 * Set the allow overwrite switch
	 *
	 * @param   boolean  $state  Overwrite switch state
	 *
	 * @return  boolean  True it state is set, false if it is not
	 *
	 * @since   11.1
	 */
	public function setOverwrite($state = false)
	{
		$tmp = $this->_overwrite;

		if ($state)
		{
			$this->_overwrite = true;
		}
		else
		{
			$this->_overwrite = false;
		}

		return $tmp;
	}

	/**
	 * Get the redirect location
	 *
	 * @return  string  Redirect location (or null)
	 *
	 * @since   11.1
	 */
	public function getRedirectURL()
	{
		return $this->redirect_url;
	}

	/**
	 * Set the redirect location
	 *
	 * @param   string  $newurl  New redirect location
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function setRedirectURL($newurl)
	{
		$this->redirect_url = $newurl;
	}

	/**
	 * Get the upgrade switch
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	public function getUpgrade()
	{
		return $this->_upgrade;
	}

	/**
	 * Set the upgrade switch
	 *
	 * @param   boolean  $state  Upgrade switch state
	 *
	 * @return  boolean  True if upgrade, false otherwise
	 *
	 * @since   11.1
	 */
	public function setUpgrade($state = false)
	{
		$tmp = $this->_upgrade;

		if ($state)
		{
			$this->_upgrade = true;
		}
		else
		{
			$this->_upgrade = false;
		}

		return $tmp;
	}

	/**
	 * Get the installation manifest object
	 *
	 * @return  object  Manifest object
	 *
	 * @since   11.1
	 */
	public function getManifest()
	{
		if (!is_object($this->manifest))
		{
			$this->findManifest();
		}

		return $this->manifest;
	}

	/**
	 * Get an installer path by name
	 *
	 * @param   string  $name     Path name
	 * @param   string  $default  Default value
	 *
	 * @return  string  Path
	 *
	 * @since   11.1
	 */
	public function getPath($name, $default = null)
	{
		return (!empty($this->_paths[$name])) ? $this->_paths[$name] : $default;
	}

	/**
	 * Sets an installer path by name
	 *
	 * @param   string  $name   Path name
	 * @param   string  $value  Path
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function setPath($name, $value)
	{
		$this->_paths[$name] = $value;
	}

	/**
	 * Pushes a step onto the installer stack for rolling back steps
	 *
	 * @param   array  $step  Installer step
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function pushStep($step)
	{
		$this->_stepStack[] = $step;
	}

	/**
	 * Installation abort method
	 *
	 * @param   string  $msg   Abort message from the installer
	 * @param   string  $type  Package type if defined
	 *
	 * @return  boolean  True if successful
	 *
	 * @since   11.1
	 */
	public function abort($msg = null, $type = null)
	{
		// Initialise variables.
		$retval = true;
		$step = array_pop($this->_stepStack);

		// Raise abort warning
		if ($msg)
		{
			JError::raiseWarning(100, $msg);
		}

		while ($step != null)
		{
			switch ($step['type'])
			{
				case 'file':
					// Remove the file
					$stepval = JFile::delete($step['path']);
					break;

				case 'folder':
					// Remove the folder
					$stepval = JFolder::delete($step['path']);
					break;

				case 'query':
					// Placeholder in case this is necessary in the future
					// $stepval is always false because if this step was called it invariably failed
					$stepval = false;
					break;

				case 'extension':
					// Get database connector object
					$db = $this->getDBO();

					// Remove the entry from the #__extensions table
					$query = 'DELETE' . ' FROM `#__extensions`' . ' WHERE extension_id = ' . (int) $step['id'];
					$db->setQuery($query);
					$stepval = $db->Query();

					break;

				default:
					if ($type && is_object($this->_adapters[$type]))
					{
						// Build the name of the custom rollback method for the type
						$method = '_rollback_' . $step['type'];
						// Custom rollback method handler
						if (method_exists($this->_adapters[$type], $method))
						{
							$stepval = $this->_adapters[$type]->$method($step);
						}
					}
					else
					{
						$stepval = false; // set it to false
					}
					break;
			}

			// Only set the return value if it is false
			if ($stepval === false)
			{
				$retval = false;
			}

			// Get the next step and continue
			$step = array_pop($this->_stepStack);
		}

		$conf = JFactory::getConfig();
		$debug = $conf->get('debug');

		if ($debug)
		{
			JError::raiseError(500, JText::_('JLIB_INSTALLER_ABORT_DEBUG') . $msg);
		}

		return $retval;
	}

	// Adapter functions


	/**
	 * Package installation method
	 *
	 * @param   string  $path  Path to package source folder
	 *
	 * @return  boolean  True if successful
	 *
	 * @since   11.1
	 */
	public function install($path = null)
	{
		if ($path && JFolder::exists($path))
		{
			$this->setPath('source', $path);
		}
		else
		{
			$this->abort(JText::_('JLIB_INSTALLER_ABORT_NOINSTALLPATH'));
			return false;
		}

		if (!$this->setupInstall())
		{
			$this->abort(JText::_('JLIB_INSTALLER_ABORT_DETECTMANIFEST'));

			return false;
		}

		$type = (string) $this->manifest->attributes()->type;

		if (is_object($this->_adapters[$type]))
		{
			// Add the languages from the package itself
			if (method_exists($this->_adapters[$type], 'loadLanguage'))
			{
				$this->_adapters[$type]->loadLanguage($path);
			}

			// Fire the onExtensionBeforeInstall event.
			JPluginHelper::importPlugin('extension');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger(
				'onExtensionBeforeInstall',
				array('method' => 'install', 'type' => $type, 'manifest' => $this->manifest, 'extension' => 0)
			);

			// Run the install
			$result = $this->_adapters[$type]->install();

			// Fire the onExtensionAfterInstall
			$dispatcher->trigger(
				'onExtensionAfterInstall',
				array('installer' => clone $this, 'eid' => $result)
			);

			if ($result !== false)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		return false;
	}

	/**
	 * Discovered package installation method
	 *
	 * @param   integer  $eid  Extension ID
	 *
	 * @return  boolean  True if successful
	 *
	 * @since   11.1
	 */
	public function discover_install($eid = null)
	{
		if ($eid)
		{
			$this->extension = JTable::getInstance('extension');

			if (!$this->extension->load($eid))
			{
				$this->abort(JText::_('JLIB_INSTALLER_ABORT_LOAD_DETAILS'));

				return false;
			}

			if ($this->extension->state != -1)
			{
				$this->abort(JText::_('JLIB_INSTALLER_ABORT_ALREADYINSTALLED'));

				return false;
			}

			// Lazy load the adapter
			if (!isset($this->_adapters[$this->extension->type]) || !is_object($this->_adapters[$this->extension->type]))
			{
				if (!$this->setAdapter($this->extension->type))
				{
					return false;
				}
			}

			if (is_object($this->_adapters[$this->extension->type]))
			{
				if (method_exists($this->_adapters[$this->extension->type], 'discover_install'))
				{
					// Add the languages from the package itself
					if (method_exists($this->_adapters[$this->extension->type], 'loadLanguage'))
					{
						$this->_adapters[$this->extension->type]->loadLanguage();
					}

					// Fire the onExtensionBeforeInstall event.
					JPluginHelper::importPlugin('extension');
					$dispatcher = JDispatcher::getInstance();
					$dispatcher->trigger(
						'onExtensionBeforeInstall',
						array(
							'method' => 'discover_install',
							'type' => $this->extension->get('type'),
							'manifest' => null,
							'extension' => $this->extension->get('extension_id')
						)
					);

					// Run the install
					$result = $this->_adapters[$this->extension->type]->discover_install();
					// Fire the onExtensionAfterInstall
					$dispatcher->trigger(
						'onExtensionAfterInstall',
						array('installer' => clone $this, 'eid' => $result)
					);
					if ($result !== false)
					{
						return true;
					}
					else
					{
						return false;
					}
				}
				else
				{
					$this->abort(JText::_('JLIB_INSTALLER_ABORT_METHODNOTSUPPORTED'));

					return false;
				}
			}

			return false;
		}
		else
		{
			$this->abort(JText::_('JLIB_INSTALLER_ABORT_EXTENSIONNOTVALID'));

			return false;
		}
	}

	/**
	 * Extension discover method
	 * Asks each adapter to find extensions
	 *
	 * @return  array  JExtension
	 *
	 * @since   11.1
	 */
	public function discover()
	{
		$this->loadAllAdapters();
		$results = Array();

		foreach ($this->_adapters as $adapter)
		{
			// Joomla! 1.5 installation adapter legacy support
			if (method_exists($adapter, 'discover'))
			{
				$tmp = $adapter->discover();

				// if its an array and has entries
				if (is_array($tmp) && count($tmp))
				{
					// merge it into the system
					$results = array_merge($results, $tmp);
				}
			}
		}

		return $results;
	}

	/**
	 * Package update method
	 *
	 * @param   string  $path  Path to package source folder
	 *
	 * @return  boolean  True if successful
	 *
	 * @since   11.1
	 */
	public function update($path = null)
	{
		if ($path && JFolder::exists($path))
		{
			$this->setPath('source', $path);
		}
		else
		{
			$this->abort(JText::_('JLIB_INSTALLER_ABORT_NOUPDATEPATH'));
		}

		if (!$this->setupInstall())
		{
			return $this->abort(JText::_('JLIB_INSTALLER_ABORT_DETECTMANIFEST'));
		}

		$type = (string) $this->manifest->attributes()->type;

		if (is_object($this->_adapters[$type]))
		{
			// Add the languages from the package itself
			if (method_exists($this->_adapters[$type], 'loadLanguage'))
			{
				$this->_adapters[$type]->loadLanguage($path);
			}

			// Fire the onExtensionBeforeUpdate event.
			JPluginHelper::importPlugin('extension');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onExtensionBeforeUpdate', array('type' => $type, 'manifest' => $this->manifest));

			// Run the update
			$result = $this->_adapters[$type]->update();

			// Fire the onExtensionAfterUpdate
			$dispatcher->trigger(
				'onExtensionAfterUpdate',
				array('installer' => clone $this, 'eid' => $result)
			);

			if ($result !== false)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		return false;
	}

	/**
	 * Package uninstallation method
	 *
	 * @param   string   $type        Package type
	 * @param   mixed    $identifier  Package identifier for adapter
	 * @param   integer  $cid         Application ID; deprecated in 1.6
	 *
	 * @return  boolean  True if successful
	 *
	 * @since   11.1
	 */
	public function uninstall($type, $identifier, $cid = 0)
	{
		if (!isset($this->_adapters[$type]) || !is_object($this->_adapters[$type]))
		{
			if (!$this->setAdapter($type))
			{
				// We failed to get the right adapter
				return false;
			}
		}

		if (is_object($this->_adapters[$type]))
		{
			// We don't load languages here, we get the extension adapter to work it out
			// Fire the onExtensionBeforeUninstall event.
			JPluginHelper::importPlugin('extension');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onExtensionBeforeUninstall', array('eid' => $identifier));
			// Run the uninstall
			$result = $this->_adapters[$type]->uninstall($identifier);
			// Fire the onExtensionAfterInstall
			$dispatcher->trigger(
				'onExtensionAfterUninstall',
				array('installer' => clone $this, 'eid' => $identifier, 'result' => $result)
			);

			return $result;
		}

		return false;
	}

	/**
	 * Refreshes the manifest cache stored in #__extensions
	 *
	 * @param   integer  $eid  Extension ID
	 *
	 * @return  mixed  void on success, false on error @todo missing return value ?
	 *
	 * @since   11.1
	 */
	function refreshManifestCache($eid)
	{
		if ($eid)
		{
			$this->extension = JTable::getInstance('extension');

			if (!$this->extension->load($eid))
			{
				$this->abort(JText::_('JLIB_INSTALLER_ABORT_LOAD_DETAILS'));
				return false;
			}

			if ($this->extension->state == -1)
			{
				$this->abort(JText::_('JLIB_INSTALLER_ABORT_REFRESH_MANIFEST_CACHE'));
				return false;
			}

			// Lazy load the adapter
			if (!isset($this->_adapters[$this->extension->type]) || !is_object($this->_adapters[$this->extension->type]))
			{
				if (!$this->setAdapter($this->extension->type))
				{
					return false;
				}
			}

			if (is_object($this->_adapters[$this->extension->type]))
			{
				if (method_exists($this->_adapters[$this->extension->type], 'refreshManifestCache'))
				{
					$result = $this->_adapters[$this->extension->type]->refreshManifestCache();

					if ($result !== false)
					{
						return true;
					}
					else
					{
						return false;
					}
				}
				else
				{
					$this->abort(JText::sprintf('JLIB_INSTALLER_ABORT_METHODNOTSUPPORTED_TYPE', $this->extension->type));

					return false;
				}
			}

			return false;
		}
		else
		{
			$this->abort(JText::_('JLIB_INSTALLER_ABORT_REFRESH_MANIFEST_CACHE_VALID'));

			return false;
		}
	}

	// Utility functions


	/**
	 * Prepare for installation: this method sets the installation directory, finds
	 * and checks the installation file and verifies the installation type.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public function setupInstall()
	{
		// We need to find the installation manifest file
		if (!$this->findManifest())
		{
			return false;
		}

		// Load the adapter(s) for the install manifest
		$type = (string) $this->manifest->attributes()->type;

		// Lazy load the adapter
		if (!isset($this->_adapters[$type]) || !is_object($this->_adapters[$type]))
		{
			if (!$this->setAdapter($type))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Backward compatible method to parse through a queries element of the
	 * installation manifest file and take appropriate action.
	 *
	 * @param   JXMLElement  $element  The XML node to process
	 *
	 * @return  mixed  Number of queries processed or False on error
	 *
	 * @since   11.1
	 */
	public function parseQueries($element)
	{
		// Get the database connector object
		$db = & $this->_db;

		if (!$element || !count($element->children()))
		{
			// Either the tag does not exist or has no children therefore we return zero files processed.
			return 0;
		}

		// Get the array of query nodes to process
		$queries = $element->children();

		if (count($queries) == 0)
		{
			// No queries to process
			return 0;
		}

		// Process each query in the $queries array (children of $tagName).
		foreach ($queries as $query)
		{
			$db->setQuery($query->data());

			if (!$db->query())
			{
				JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));

				return false;
			}
		}

		return (int) count($queries);
	}

	/**
	 * Method to extract the name of a discreet installation sql file from the installation manifest file.
	 *
	 * @param   object  $element  The XML node to process
	 *
	 * @return  mixed  Number of queries processed or False on error
	 *
	 * @since   11.1
	 */
	public function parseSQLFiles($element)
	{
		if (!$element || !count($element->children()))
		{
			// The tag does not exist.
			return 0;
		}

		// Initialise variables.
		$queries = array();
		$db = & $this->_db;
		$dbDriver = strtolower($db->name);

		if ($dbDriver == 'mysqli')
		{
			$dbDriver = 'mysql';
		}

		$dbCharset = ($db->hasUTF()) ? 'utf8' : '';

		// Get the name of the sql file to process
		$sqlfile = '';

		foreach ($element->children() as $file)
		{
			$fCharset = (strtolower($file->attributes()->charset) == 'utf8') ? 'utf8' : '';
			$fDriver = strtolower($file->attributes()->driver);

			if ($fDriver == 'mysqli')
			{
				$fDriver = 'mysql';
			}

			if ($fCharset == $dbCharset && $fDriver == $dbDriver)
			{
				$sqlfile = $this->getPath('extension_root') . '/' . $file;

				// Check that sql files exists before reading. Otherwise raise error for rollback
				if (!file_exists($sqlfile))
				{
					JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_FILENOTFOUND', $sqlfile));

					return false;
				}

				$buffer = file_get_contents($sqlfile);

				// Graceful exit and rollback if read not successful
				if ($buffer === false)
				{
					JError::raiseWarning(1, JText::_('JLIB_INSTALLER_ERROR_SQL_READBUFFER'));

					return false;
				}

				// Create an array of queries from the sql file
				jimport('joomla.installer.helper');
				$queries = JInstallerHelper::splitSql($buffer);

				if (count($queries) == 0)
				{
					// No queries to process
					return 0;
				}

				// Process each query in the $queries array (split out of sql file).
				foreach ($queries as $query)
				{
					$query = trim($query);

					if ($query != '' && $query{0} != '#')
					{
						$db->setQuery($query);

						if (!$db->query())
						{
							JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));

							return false;
						}
					}
				}
			}
		}

		return (int) count($queries);
	}

	/**
	 * Set the schema version for an extension by looking at its latest update
	 *
	 * @param   JXMLElement  $schema  Schema Tag
	 * @param   integer      $eid     Extension ID
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function setSchemaVersion($schema, $eid)
	{
		if ($eid && $schema)
		{
			$db = JFactory::getDBO();
			$schemapaths = $schema->children();

			if (!$schemapaths)
			{
				return;
			}

			if (count($schemapaths))
			{
				$dbDriver = strtolower($db->name);
				if ($dbDriver == 'mysqli')
				{
					$dbDriver = 'mysql';
				}

				$schemapath = '';

				foreach ($schemapaths as $entry)
				{
					$attrs = $entry->attributes();
					if ($attrs['type'] == $dbDriver)
					{
						$schemapath = $entry;
						break;
					}
				}

				if (strlen($schemapath))
				{
					$files = str_replace('.sql', '', JFolder::files($this->getPath('extension_root') . '/' . $schemapath, '\.sql$'));
					usort($files, 'version_compare');
					// Update the database
					$query = $db->getQuery(true);
					$query->delete()
						->from('#__schemas')
						->where('extension_id = ' . $eid);
					$db->setQuery($query);

					if ($db->query())
					{
						$query->clear();
						$query->insert('#__schemas')
							->set('extension_id = ' . $eid)
							->set('version_id = ' . $db->quote(end($files)));
						$db->setQuery($query);
						$db->query();
					}
				}
			}
		}
	}

	/**
	 * Method to process the updates for an item
	 *
	 * @param   JXMLElement  $schema  The XML node to process
	 * @param   integer      $eid     Extension Identifier
	 *
	 * @return  boolean      Result of the operations
	 *
	 * @since   11.1
	 */
	public function parseSchemaUpdates($schema, $eid)
	{
		$files = Array();
		$update_count = 0;

		// Ensure we have an XML element and a valid extension id
		if ($eid && $schema)
		{
			$db = JFactory::getDBO();
			$schemapaths = $schema->children();

			if (count($schemapaths))
			{
				$dbDriver = strtolower($db->name);

				if ($dbDriver == 'mysqli')
				{
					$dbDriver = 'mysql';
				}

				$schemapath = '';
				foreach ($schemapaths as $entry)
				{
					$attrs = $entry->attributes();
					if ($attrs['type'] == $dbDriver)
					{
						$schemapath = $entry;
						break;
					}
				}

				if (strlen($schemapath))
				{
					$files = str_replace('.sql', '', JFolder::files($this->getPath('extension_root') . '/' . $schemapath, '\.sql$'));
					usort($files, 'version_compare');

					if (!count($files))
					{
						return false;
					}

					$query = $db->getQuery(true);
					$query->select('version_id')
						->from('#__schemas')
						->where('extension_id = ' . $eid);
					$db->setQuery($query);
					$version = $db->loadResult();

					if ($version)
					{
						// We have a version!
						foreach ($files as $file)
						{
							if (version_compare($file, $version) > 0)
							{
								$buffer = file_get_contents($this->getPath('extension_root') . '/' . $schemapath . '/' . $file . '.sql');

								// Graceful exit and rollback if read not successful
								if ($buffer === false)
								{
									JError::raiseWarning(1, JText::_('JLIB_INSTALLER_ERROR_SQL_READBUFFER'));

									return false;
								}

								// Create an array of queries from the sql file
								jimport('joomla.installer.helper');
								$queries = JInstallerHelper::splitSql($buffer);

								if (count($queries) == 0)
								{
									// No queries to process
									continue;
								}

								// Process each query in the $queries array (split out of sql file).
								foreach ($queries as $query)
								{
									$query = trim($query);
									if ($query != '' && $query{0} != '#')
									{
										$db->setQuery($query);

										if (!$db->query())
										{
											JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));

											return false;
										}

										$update_count++;
									}
								}
							}
						}
					}

					// Update the database
					$query = $db->getQuery(true);
					$query->delete()
						->from('#__schemas')
						->where('extension_id = ' . $eid);
					$db->setQuery($query);

					if ($db->Query())
					{
						$query->clear();
						$query->insert('#__schemas')
							->set('extension_id = ' . $eid)
							->set('version_id = ' . $db->quote(end($files)));
						$db->setQuery($query);
						$db->Query();
					}
				}
			}
		}

		return $update_count;
	}

	/**
	 * Method to parse through a files element of the installation manifest and take appropriate
	 * action.
	 *
	 * @param   JXMLElement  $element   The XML node to process
	 * @param   integer      $cid       Application ID of application to install to
	 * @param   array        $oldFiles  List of old files (JXMLElement's)
	 * @param   array        $oldMD5    List of old MD5 sums (indexed by filename with value as MD5)
	 *
	 * @return  boolean      True on success
	 *
	 * @since   11.1
	 */
	public function parseFiles($element, $cid = 0, $oldFiles = null, $oldMD5 = null)
	{
		// Get the array of file nodes to process; we checked whether this had children above.
		if (!$element || !count($element->children()))
		{
			// Either the tag does not exist or has no children (hence no files to process) therefore we return zero files processed.
			return 0;
		}

		// Initialise variables.
		$copyfiles = array();

		// Get the client info
		jimport('joomla.application.helper');
		$client = JApplicationHelper::getClientInfo($cid);

		/*
		 * Here we set the folder we are going to remove the files from.
		 */
		if ($client)
		{
			$pathname = 'extension_' . $client->name;
			$destination = $this->getPath($pathname);
		}
		else
		{
			$pathname = 'extension_root';
			$destination = $this->getPath($pathname);
		}

		// Here we set the folder we are going to copy the files from.


		// Does the element have a folder attribute?
		//
		// If so this indicates that the files are in a subdirectory of the source
		// folder and we should append the folder attribute to the source path when
		// copying files.


		$folder = (string) $element->attributes()->folder;

		if ($folder && file_exists($this->getPath('source') . '/' . $folder))
		{
			$source = $this->getPath('source') . '/' . $folder;
		}
		else
		{
			$source = $this->getPath('source');
		}

		// Work out what files have been deleted
		if ($oldFiles && ($oldFiles instanceof JXMLElement))
		{
			$oldEntries = $oldFiles->children();

			if (count($oldEntries))
			{
				$deletions = $this->findDeletedFiles($oldEntries, $element->children());

				foreach ($deletions['folders'] as $deleted_folder)
				{
					JFolder::delete($destination . '/' . $deleted_folder);
				}

				foreach ($deletions['files'] as $deleted_file)
				{
					JFile::delete($destination . '/' . $deleted_file);
				}
			}
		}

		// Copy the MD5SUMS file if it exists
		if (file_exists($source . '/MD5SUMS'))
		{
			$path['src'] = $source . '/MD5SUMS';
			$path['dest'] = $destination . '/MD5SUMS';
			$path['type'] = 'file';
			$copyfiles[] = $path;
		}

		// Process each file in the $files array (children of $tagName).
		foreach ($element->children() as $file)
		{
			$path['src'] = $source . '/' . $file;
			$path['dest'] = $destination . '/' . $file;

			// Is this path a file or folder?
			$path['type'] = ($file->getName() == 'folder') ? 'folder' : 'file';

			// Before we can add a file to the copyfiles array we need to ensure
			// that the folder we are copying our file to exits and if it doesn't,
			// we need to create it.


			if (basename($path['dest']) != $path['dest'])
			{
				$newdir = dirname($path['dest']);

				if (!JFolder::create($newdir))
				{
					JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_CREATE_DIRECTORY', $newdir));
					return false;
				}
			}

			// Add the file to the copyfiles array
			$copyfiles[] = $path;
		}

		return $this->copyFiles($copyfiles);
	}

	/**
	 * Method to parse through a languages element of the installation manifest and take appropriate
	 * action.
	 *
	 * @param   JXMLElement  $element  The XML node to process
	 * @param   integer      $cid      Application ID of application to install to
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public function parseLanguages($element, $cid = 0)
	{
		// TODO: work out why the below line triggers 'node no longer exists' errors with files
		if (!$element || !count($element->children()))
		{
			// Either the tag does not exist or has no children therefore we return zero files processed.
			return 0;
		}

		// Initialise variables.
		$copyfiles = array();

		// Get the client info
		jimport('joomla.application.helper');
		$client = JApplicationHelper::getClientInfo($cid);

		// Here we set the folder we are going to copy the files to.
		// 'languages' Files are copied to JPATH_BASE/language/ folder


		$destination = $client->path . '/language';

		// Here we set the folder we are going to copy the files from.


		// Does the element have a folder attribute?


		// If so this indicates that the files are in a subdirectory of the source
		// folder and we should append the folder attribute to the source path when
		// copying files.


		$folder = (string) $element->attributes()->folder;

		if ($folder && file_exists($this->getPath('source') . '/' . $folder))
		{
			$source = $this->getPath('source') . '/' . $folder;
		}
		else
		{
			$source = $this->getPath('source');
		}

		// Process each file in the $files array (children of $tagName).
		foreach ($element->children() as $file)
		{
			// Language files go in a subfolder based on the language code, ie.
			// <language tag="en-US">en-US.mycomponent.ini</language>
			// would go in the en-US subdirectory of the language folder.


			// We will only install language files where a core language pack
			// already exists.


			if ((string) $file->attributes()->tag != '')
			{
				$path['src'] = $source . '/' . $file;

				if ((string) $file->attributes()->client != '')
				{
					// Override the client
					$langclient = JApplicationHelper::getClientInfo((string) $file->attributes()->client, true);
					$path['dest'] = $langclient->path . '/language/' . $file->attributes()->tag . '/' . basename((string) $file);
				}
				else
				{
					// Use the default client
					$path['dest'] = $destination . '/' . $file->attributes()->tag . '/' . basename((string) $file);
				}

				// If the language folder is not present, then the core pack hasn't been installed... ignore
				if (!JFolder::exists(dirname($path['dest'])))
				{
					continue;
				}
			}
			else
			{
				$path['src'] = $source . '/' . $file;
				$path['dest'] = $destination . '/' . $file;
			}

			//
			// Before we can add a file to the copyfiles array we need to ensure
			// that the folder we are copying our file to exits and if it doesn't,
			// we need to create it.


			if (basename($path['dest']) != $path['dest'])
			{
				$newdir = dirname($path['dest']);

				if (!JFolder::create($newdir))
				{
					JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_CREATE_DIRECTORY', $newdir));

					return false;
				}
			}

			// Add the file to the copyfiles array
			$copyfiles[] = $path;
		}

		return $this->copyFiles($copyfiles);
	}

	/**
	 * Method to parse through a media element of the installation manifest and take appropriate
	 * action.
	 *
	 * @param   JXMLElement  $element  The XML node to process
	 * @param   integer      $cid      Application ID of application to install to
	 *
	 * @return  boolean     True on success
	 *
	 * @since   11.1
	 */
	public function parseMedia($element, $cid = 0)
	{
		if (!$element || !count($element->children()))
		{
			// Either the tag does not exist or has no children therefore we return zero files processed.
			return 0;
		}

		// Initialise variables.
		$copyfiles = array();

		// Get the client info
		jimport('joomla.application.helper');
		$client = JApplicationHelper::getClientInfo($cid);

		// Here we set the folder we are going to copy the files to.
		//	Default 'media' Files are copied to the JPATH_BASE/media folder


		$folder = ((string) $element->attributes()->destination) ? '/' . $element->attributes()->destination : null;
		$destination = JPath::clean(JPATH_ROOT . '/media' . $folder);

		// Here we set the folder we are going to copy the files from.


		// Does the element have a folder attribute?
		// If so this indicates that the files are in a subdirectory of the source
		// folder and we should append the folder attribute to the source path when
		// copying files.


		$folder = (string) $element->attributes()->folder;

		if ($folder && file_exists($this->getPath('source') . '/' . $folder))
		{
			$source = $this->getPath('source') . '/' . $folder;
		}
		else
		{
			$source = $this->getPath('source');
		}

		// Process each file in the $files array (children of $tagName).
		foreach ($element->children() as $file)
		{
			$path['src'] = $source . '/' . $file;
			$path['dest'] = $destination . '/' . $file;

			// Is this path a file or folder?
			$path['type'] = ($file->getName() == 'folder') ? 'folder' : 'file';

			// Before we can add a file to the copyfiles array we need to ensure
			// that the folder we are copying our file to exits and if it doesn't,
			// we need to create it.


			if (basename($path['dest']) != $path['dest'])
			{
				$newdir = dirname($path['dest']);

				if (!JFolder::create($newdir))
				{
					JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_CREATE_DIRECTORY', $newdir));

					return false;
				}
			}

			// Add the file to the copyfiles array
			$copyfiles[] = $path;
		}

		return $this->copyFiles($copyfiles);
	}

	/**
	 * Method to parse the parameters of an extension, build the INI
	 * string for its default parameters, and return the INI string.
	 *
	 * @return  string   INI string of parameter values
	 *
	 * @since   11.1
	 */
	public function getParams()
	{
		// Validate that we have a fieldset to use
		if (!isset($this->manifest->config->fields->fieldset))
		{
			return '{}';
		}
		// Getting the fieldset tags
		$fieldsets = $this->manifest->config->fields->fieldset;

		// Creating the data collection variable:
		$ini = array();

		// Iterating through the fieldsets:
		foreach ($fieldsets as $fieldset)
		{
			if (!count($fieldset->children()))
			{
				// Either the tag does not exist or has no children therefore we return zero files processed.
				return null;
			}

			// Iterating through the fields and collecting the name/default values:
			foreach ($fieldset as $field)
			{
				// Check against the null value since otherwise default values like "0"
				// cause entire parameters to be skipped.


				if (($name = $field->attributes()->name) === null)
				{
					continue;
				}

				if (($value = $field->attributes()->default) === null)
				{
					continue;
				}

				$ini[(string) $name] = (string) $value;
			}
		}

		return json_encode($ini);
	}

	/**
	 * Copyfiles
	 *
	 * Copy files from source directory to the target directory
	 *
	 * @param   array    $files      Array with filenames
	 * @param   boolean  $overwrite  True if existing files can be replaced
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public function copyFiles($files, $overwrite = null)
	{
		// To allow for manual override on the overwriting flag, we check to see if
		// the $overwrite flag was set and is a boolean value.  If not, use the object
		// allowOverwrite flag.


		if (is_null($overwrite) || !is_bool($overwrite))
		{
			$overwrite = $this->_overwrite;
		}

		/*
		 * $files must be an array of filenames.  Verify that it is an array with
		 * at least one file to copy.
		 */
		if (is_array($files) && count($files) > 0)
		{

			foreach ($files as $file)
			{
				// Get the source and destination paths
				$filesource = JPath::clean($file['src']);
				$filedest = JPath::clean($file['dest']);
				$filetype = array_key_exists('type', $file) ? $file['type'] : 'file';

				if (!file_exists($filesource))
				{
					/*
					 * The source file does not exist.  Nothing to copy so set an error
					 * and return false.
					 */
					JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_NO_FILE', $filesource));

					return false;
				}
				else if (($exists = file_exists($filedest)) && !$overwrite)
				{

					// It's okay if the manifest already exists
					if ($this->getPath('manifest') == $filesource)
					{
						continue;
					}

					// The destination file already exists and the overwrite flag is false.
					// Set an error and return false.


					JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_FILE_EXISTS', $filedest));

					return false;
				}
				else
				{
					// Copy the folder or file to the new location.
					if ($filetype == 'folder')
					{
						if (!(JFolder::copy($filesource, $filedest, null, $overwrite)))
						{
							JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_FAIL_COPY_FOLDER', $filesource, $filedest));
							return false;
						}

						$step = array('type' => 'folder', 'path' => $filedest);
					}
					else
					{
						if (!(JFile::copy($filesource, $filedest, null)))
						{
							JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_FAIL_COPY_FILE', $filesource, $filedest));

							return false;
						}

						$step = array('type' => 'file', 'path' => $filedest);
					}

					/*
					 * Since we copied a file/folder, we want to add it to the installation step stack so that
					 * in case we have to roll back the installation we can remove the files copied.
					 */
					if (!$exists)
					{
						$this->_stepStack[] = $step;
					}
				}
			}
		}
		else
		{
			// The $files variable was either not an array or an empty array
			return false;
		}

		return count($files);
	}

	/**
	 * Method to parse through a files element of the installation manifest and remove
	 * the files that were installed
	 *
	 * @param   object   $element  The XML node to process
	 * @param   integer  $cid      Application ID of application to remove from
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public function removeFiles($element, $cid = 0)
	{
		if (!$element || !count($element->children()))
		{
			// Either the tag does not exist or has no children therefore we return zero files processed.
			return true;
		}

		// Initialise variables.
		$removefiles = array();
		$retval = true;

		$debug = false;
		if (isset($GLOBALS['installerdebug']) && $GLOBALS['installerdebug'])
		{
			$debug = true;
		}

		// Get the client info if we're using a specific client
		jimport('joomla.application.helper');

		if ($cid > -1)
		{
			$client = JApplicationHelper::getClientInfo($cid);
		}
		else
		{
			$client = null;
		}

		// Get the array of file nodes to process
		$files = $element->children();

		if (count($files) == 0)
		{
			// No files to process
			return true;
		}

		$folder = '';

		/*
		 * Here we set the folder we are going to remove the files from.  There are a few
		 * special cases that need to be considered for certain reserved tags.
		 */
		switch ($element->getName())
		{
			case 'media':
				if ((string) $element->attributes()->destination)
				{
					$folder = (string) $element->attributes()->destination;
				}
				else
				{
					$folder = '';
				}

				$source = $client->path . '/media/' . $folder;

				break;

			case 'languages':
				$lang_client = (string) $element->attributes()->client;

				if ($lang_client)
				{
					$client = JApplicationHelper::getClientInfo($lang_client, true);
					$source = $client->path . '/language';
				}
				else
				{
					if ($client)
					{
						$source = $client->path . '/language';
					}
					else
					{
						$source = '';
					}
				}

				break;

			default:
				if ($client)
				{
					$pathname = 'extension_' . $client->name;
					$source = $this->getPath($pathname);
				}
				else
				{
					$pathname = 'extension_root';
					$source = $this->getPath($pathname);
				}

				break;
		}

		// Process each file in the $files array (children of $tagName).
		foreach ($files as $file)
		{
			// If the file is a language, we must handle it differently.  Language files
			// go in a subdirectory based on the language code, ie.
			//		<language tag="en_US">en_US.mycomponent.ini</language>
			// would go in the en_US subdirectory of the languages directory.


			if ($file->getName() == 'language' && (string) $file->attributes()->tag != '')
			{
				if ($source)
				{
					$path = $source . '/' . $file->attributes()->tag . '/' . basename((string) $file);
				}
				else
				{
					$target_client = JApplicationHelper::getClientInfo((string) $file->attributes()->client, true);
					$path = $target_client->path . '/language/' . $file->attributes()->tag . '/' . basename((string) $file);
				}

				// If the language folder is not present, then the core pack hasn't been installed... ignore
				if (!JFolder::exists(dirname($path)))
				{
					continue;
				}
			}
			else
			{
				$path = $source . '/' . $file;
			}

			// Actually delete the files/folders


			if (is_dir($path))
			{
				$val = JFolder::delete($path);
			}
			else
			{
				$val = JFile::delete($path);
			}

			if ($val === false)
			{
				JError::raiseWarning(43, 'Failed to delete ' . $path);
				$retval = false;
			}
		}

		if (!empty($folder))
		{
			$val = JFolder::delete($source);
		}

		return $retval;
	}

	/**
	 * Copies the installation manifest file to the extension folder in the given client
	 *
	 * @param   integer  $cid  Where to copy the installfile [optional: defaults to 1 (admin)]
	 *
	 * @return  boolean  True on success, False on error
	 *
	 * @since   11.1
	 */
	public function copyManifest($cid = 1)
	{
		// Get the client info
		jimport('joomla.application.helper');
		$client = JApplicationHelper::getClientInfo($cid);

		$path['src'] = $this->getPath('manifest');

		if ($client)
		{
			$pathname = 'extension_' . $client->name;
			$path['dest'] = $this->getPath($pathname) . '/' . basename($this->getPath('manifest'));
		}
		else
		{
			$pathname = 'extension_root';
			$path['dest'] = $this->getPath($pathname) . '/' . basename($this->getPath('manifest'));
		}

		return $this->copyFiles(array($path), true);
	}

	/**
	 * Tries to find the package manifest file
	 *
	 * @return  boolean  True on success, False on error
	 *
	 * @since 1.0
	 */
	public function findManifest()
	{
		// Get an array of all the XML files from the installation directory
		$xmlfiles = JFolder::files($this->getPath('source'), '.xml$', 1, true);
		// If at least one XML file exists
		if (!empty($xmlfiles))
		{

			foreach ($xmlfiles as $file)
			{
				// Is it a valid Joomla installation manifest file?
				$manifest = $this->isManifest($file);

				if (!is_null($manifest))
				{
					// If the root method attribute is set to upgrade, allow file overwrite
					if ((string) $manifest->attributes()->method == 'upgrade')
					{
						$this->_upgrade = true;
						$this->_overwrite = true;
					}

					// If the overwrite option is set, allow file overwriting
					if ((string) $manifest->attributes()->overwrite == 'true')
					{
						$this->_overwrite = true;
					}

					// Set the manifest object and path
					$this->manifest = $manifest;
					$this->setPath('manifest', $file);

					// Set the installation source path to that of the manifest file
					$this->setPath('source', dirname($file));

					return true;
				}
			}

			// None of the XML files found were valid install files
			JError::raiseWarning(1, JText::_('JLIB_INSTALLER_ERROR_NOTFINDJOOMLAXMLSETUPFILE'));

			return false;
		}
		else
		{
			// No XML files were found in the install folder
			JError::raiseWarning(1, JText::_('JLIB_INSTALLER_ERROR_NOTFINDXMLSETUPFILE'));
			return false;
		}
	}

	/**
	 * Is the XML file a valid Joomla installation manifest file.
	 *
	 * @param   string  $file  An xmlfile path to check
	 *
	 * @return  mixed  A JXMLElement, or null if the file failed to parse
	 *
	 * @since   11.1
	 */
	public function isManifest($file)
	{
		// Initialise variables.
		$xml = JFactory::getXML($file);

		// If we cannot load the XML file return null
		if (!$xml)
		{
			return null;
		}

		// Check for a valid XML root tag.
		// @todo: Remove backwards compatability in a future version
		// Should be 'extension', but for backward compatability we will accept 'extension' or 'install'.


		// 1.5 uses 'install'
		// 1.6 uses 'extension'
		if ($xml->getName() != 'install' && $xml->getName() != 'extension')
		{
			return null;
		}

		// Valid manifest file return the object
		return $xml;
	}

	/**
	 * Generates a manifest cache
	 *
	 * @return string serialised manifest data
	 *
	 * @since   11.1
	 */
	public function generateManifestCache()
	{
		return json_encode(JApplicationHelper::parseXMLInstallFile($this->getPath('manifest')));
	}

	/**
	 * Cleans up discovered extensions if they're being installed some other way
	 *
	 * @param   string   $type     The type of extension (component, etc)
	 * @param   string   $element  Unique element identifier (e.g. com_content)
	 * @param   string   $folder   The folder of the extension (plugins; e.g. system)
	 * @param   integer  $client   The client application (administrator or site)
	 *
	 * @return  object    Result of query
	 *
	 * @since   11.1
	 */
	public function cleanDiscoveredExtension($type, $element, $folder = '', $client = 0)
	{
		$dbo = JFactory::getDBO();
		$dbo->setQuery(
			'DELETE FROM #__extensions WHERE type = ' . $dbo->Quote($type) . ' AND element = ' . $dbo->Quote($element) . ' AND folder = ' .
				$dbo->Quote($folder) . ' AND client_id = ' . intval($client) . ' AND state = -1'
		);

		return $dbo->Query();
	}

	/**
	 * Compares two "files" entries to find deleted files/folders
	 *
	 * @param   array  $old_files  An array of JXMLElement objects that are the old files
	 * @param   array  $new_files  An array of JXMLElement objects that are the new files
	 *
	 * @return  array  An array with the delete files and folders in findDeletedFiles[files] and findDeletedFiles[folders] resepctively
	 *
	 * @since   11.1
	 */
	public function findDeletedFiles($old_files, $new_files)
	{
		// The magic find deleted files function!
		// The files that are new
		$files = Array();
		// The folders that are new
		$folders = Array();
		// The folders of the files that are new
		$containers = Array();
		// A list of files to delete
		$files_deleted = Array();
		// A list of folders to delete
		$folders_deleted = Array();

		foreach ($new_files as $file)
		{
			switch ($file->getName())
			{
				case 'folder':
					// Add any folders to the list
					$folders[] = (string) $file; // add any folders to the list
					break;

				case 'file':
				default:
					// Add any files to the list
					$files[] = (string) $file;
					// Now handle the folder part of the file to ensure we get any containers
					// Break up the parts of the directory
					$container_parts = explode('/', dirname((string) $file));
					// Make sure this is clean and empty
					$container = '';

					foreach ($container_parts as $part)
					{
						// Iterate through each part
						// Add a slash if its not empty
						if (!empty($container))
						{
							$container .= '/';
						}
						$container .= $part; // append the folder part
						if (!in_array($container, $containers))
						{
							$containers[] = $container; // add the container if it doesn't already exist
						}
					}
					break;
			}
		}

		foreach ($old_files as $file)
		{
			switch ($file->getName())
			{
				case 'folder':
					if (!in_array((string) $file, $folders))
					{
						// See whether the folder exists in the new list
						if (!in_array((string) $file, $containers))
						{
							// Check if the folder exists as a container in the new list
							// If it's not in the new list or a container then delete it
							$folders_deleted[] = (string) $file;
						}
					}
					break;

				case 'file':
				default:
					if (!in_array((string) $file, $files))
					{
						// look if the file exists in the new list
						if (!in_array(dirname((string) $file), $folders))
						{
							// look if the file is now potentially in a folder
							$files_deleted[] = (string) $file; // not in a folder, doesn't exist, wipe it out!
						}
					}
					break;
			}
		}

		return Array('files' => $files_deleted, 'folders' => $folders_deleted);
	}

	/**
	 * Loads an MD5SUMS file into an associative array
	 *
	 * @param   string  $filename  Filename to load
	 *
	 * @return  array  Associative array with filenames as the index and the MD5 as the value
	 *
	 * @since   11.1
	 */
	function loadMD5Sum($filename)
	{
		if (!file_exists($filename))
		{
			// Bail if the file doesn't exist
			return false;
		}

		$data = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		$retval = Array();

		foreach ($data as $row)
		{
			$results = explode('  ', $row); // split up the data
			$results[1] = str_replace('./', '', $results[1]); // cull any potential prefix
			$retval[$results[1]] = $results[0]; // throw into the array
		}

		return $retval;
	}
}
