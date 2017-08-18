<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installer;

use Joomla\CMS\Installer\Manifest\PackageManifest;
use Joomla\CMS\Table\Extension;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\TableInterface;

defined('JPATH_PLATFORM') or die;

\JLoader::import('joomla.base.adapterinstance');

/**
 * Abstract adapter for the installer.
 *
 * @method         Installer  getParent()  Retrieves the parent object.
 * @property-read  Installer  $parent      Parent object
 *
 * @since  3.4
 * @note   As of 4.0, this class will no longer extend from JAdapterInstance
 */
abstract class InstallerAdapter extends \JAdapterInstance
{
	/**
	 * ID for the currently installed extension if present
	 *
	 * @var    integer
	 * @since  3.4
	 */
	protected $currentExtensionId = null;

	/**
	 * The unique identifier for the extension (e.g. mod_login)
	 *
	 * @var    string
	 * @since  3.4
	 * */
	protected $element = null;

	/**
	 * Extension object.
	 *
	 * @var    Extension
	 * @since  3.4
	 * */
	protected $extension = null;

	/**
	 * Messages rendered by custom scripts
	 *
	 * @var    string
	 * @since  3.4
	 */
	protected $extensionMessage = '';

	/**
	 * Copy of the XML manifest file.
	 *
	 * Making this object public allows extensions to customize the manifest in custom scripts.
	 *
	 * @var    string
	 * @since  3.4
	 */
	public $manifest = null;

	/**
	 * A path to the PHP file that the scriptfile declaration in the manifest refers to.
	 *
	 * @var    string
	 * @since  3.4
	 */
	protected $manifest_script = null;

	/**
	 * Name of the extension
	 *
	 * @var    string
	 * @since  3.4
	 */
	protected $name = null;

	/**
	 * Install function routing
	 *
	 * @var    string
	 * @since  3.4
	 */
	protected $route = 'install';

	/**
	 * Flag if the adapter supports discover installs
	 *
	 * Adapters should override this and set to false if discover install is unsupported
	 *
	 * @var    boolean
	 * @since  3.4
	 */
	protected $supportsDiscoverInstall = true;

	/**
	 * The type of adapter in use
	 *
	 * @var    string
	 * @since  3.4
	 */
	protected $type;

	/**
	 * Constructor
	 *
	 * @param   Installer         $parent   Parent object
	 * @param   \JDatabaseDriver  $db       Database object
	 * @param   array             $options  Configuration Options
	 *
	 * @since   3.4
	 */
	public function __construct(Installer $parent, \JDatabaseDriver $db, array $options = array())
	{
		parent::__construct($parent, $db, $options);

		// Get a generic TableExtension instance for use if not already loaded
		if (!($this->extension instanceof TableInterface))
		{
			$this->extension = Table::getInstance('extension');
		}

		// Sanity check, make sure the type is set by taking the adapter name from the class name
		if (!$this->type)
		{
			// This assumes the adapter short class name in its namespace is `<foo>Adapter`, replace this logic in subclasses if needed
			$reflection = new \ReflectionClass(get_called_class());
			$this->type = strtolower(str_replace('Adapter', '', $reflection->getShortName()));
		}
	}

	/**
	 * Check if a package extension allows its child extensions to be uninstalled individually
	 *
	 * @param   integer  $packageId  The extension ID of the package to check
	 *
	 * @return  boolean
	 *
	 * @since   3.7.0
	 * @note    This method defaults to true to emulate the behavior of 3.6 and earlier which did not support this lookup
	 */
	protected function canUninstallPackageChild($packageId)
	{
		$package = Table::getInstance('extension');

		// If we can't load this package ID, we have a corrupt database
		if (!$package->load((int) $packageId))
		{
			return true;
		}

		$manifestFile = JPATH_MANIFESTS . '/packages/' . $package->element . '.xml';

		$xml = $this->parent->isManifest($manifestFile);

		// If the manifest doesn't exist, we've got some major issues
		if (!$xml)
		{
			return true;
		}

		$manifest = new PackageManifest($manifestFile);

		return $manifest->blockChildUninstall === false;
	}

	/**
	 * Method to check if the extension is already present in the database
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @throws  \RuntimeException
	 */
	protected function checkExistingExtension()
	{
		try
		{
			$this->currentExtensionId = $this->extension->find(
				array('element' => $this->element, 'type' => $this->type)
			);

			// If it does exist, load it
			if ($this->currentExtensionId)
			{
				$this->extension->load(array('element' => $this->element, 'type' => $this->type));
			}
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			throw new \RuntimeException(
				\JText::sprintf(
					'JLIB_INSTALLER_ABORT_ROLLBACK',
					\JText::_('JLIB_INSTALLER_' . $this->route),
					$e->getMessage()
				),
				$e->getCode(),
				$e
			);
		}
	}

	/**
	 * Method to check if the extension is present in the filesystem, flags the route as update if so
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @throws  \RuntimeException
	 */
	protected function checkExtensionInFilesystem()
	{
		if (file_exists($this->parent->getPath('extension_root')) && (!$this->parent->isOverwrite() || $this->parent->isUpgrade()))
		{
			// Look for an update function or update tag
			$updateElement = $this->getManifest()->update;

			// Upgrade manually set or update function available or update tag detected
			if ($updateElement || $this->parent->isUpgrade()
				|| ($this->parent->manifestClass && method_exists($this->parent->manifestClass, 'update')))
			{
				// Force this one
				$this->parent->setOverwrite(true);
				$this->parent->setUpgrade(true);

				if ($this->currentExtensionId)
				{
					// If there is a matching extension mark this as an update
					$this->setRoute('update');
				}
			}
			elseif (!$this->parent->isOverwrite())
			{
				// We didn't have overwrite set, find an update function or find an update tag so lets call it safe
				throw new \RuntimeException(
					\JText::sprintf(
						'JLIB_INSTALLER_ABORT_DIRECTORY',
						\JText::_('JLIB_INSTALLER_' . $this->route),
						$this->type,
						$this->parent->getPath('extension_root')
					)
				);
			}
		}
	}

	/**
	 * Method to copy the extension's base files from the `<files>` tag(s) and the manifest file
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @throws  \RuntimeException
	 */
	abstract protected function copyBaseFiles();

	/**
	 * Method to create the extension root path if necessary
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @throws  \RuntimeException
	 */
	protected function createExtensionRoot()
	{
		// If the extension directory does not exist, lets create it
		$created = false;

		if (!file_exists($this->parent->getPath('extension_root')))
		{
			if (!$created = \JFolder::create($this->parent->getPath('extension_root')))
			{
				throw new \RuntimeException(
					\JText::sprintf(
						'JLIB_INSTALLER_ABORT_CREATE_DIRECTORY',
						\JText::_('JLIB_INSTALLER_' . $this->route),
						$this->parent->getPath('extension_root')
					)
				);
			}
		}

		/*
		 * Since we created the extension directory and will want to remove it if
		 * we have to roll back the installation, let's add it to the
		 * installation step stack
		 */

		if ($created)
		{
			$this->parent->pushStep(
				array(
					'type' => 'folder',
					'path' => $this->parent->getPath('extension_root'),
				)
			);
		}
	}

	/**
	 * Generic discover_install method for extensions
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.4
	 */
	public function discover_install()
	{
		// Get the extension's description
		$description = (string) $this->getManifest()->description;

		if ($description)
		{
			$this->parent->message = \JText::_($description);
		}
		else
		{
			$this->parent->message = '';
		}

		// Set the extension's name and element
		$this->name    = $this->getName();
		$this->element = $this->getElement();

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Extension Precheck and Setup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Setup the install paths and perform other prechecks as necessary
		try
		{
			$this->setupInstallPaths();
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Installer Trigger Loading
		 * ---------------------------------------------------------------------------------------------
		 */

		$this->setupScriptfile();

		try
		{
			$this->triggerManifestScript('preflight');
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Database Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		try
		{
			$this->storeExtension();
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		try
		{
			$this->parseQueries();
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		// Run the custom install method
		try
		{
			$this->triggerManifestScript('install');
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Finalization and Cleanup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		try
		{
			$this->finaliseInstall();
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		// And now we run the postflight
		try
		{
			$this->triggerManifestScript('postflight');
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		return $this->extension->extension_id;
	}

	/**
	 * Method to handle database transactions for the installer
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.4
	 * @throws  \RuntimeException
	 */
	protected function doDatabaseTransactions()
	{
		$route = $this->route === 'discover_install' ? 'install' : $this->route;

		// Let's run the install queries for the component
		if (isset($this->getManifest()->{$route}->sql))
		{
			$result = $this->parent->parseSQLFiles($this->getManifest()->{$route}->sql);

			if ($result === false)
			{
				// Only rollback if installing
				if ($route === 'install')
				{
					throw new \RuntimeException(
						\JText::sprintf(
							'JLIB_INSTALLER_ABORT_SQL_ERROR',
							\JText::_('JLIB_INSTALLER_' . strtoupper($this->route)),
							$this->parent->getDbo()->stderr(true)
						)
					);
				}

				return false;
			}

			// If installing with success and there is an uninstall script, add a installer rollback step to rollback if needed
			if ($route === 'install' && isset($this->getManifest()->uninstall->sql))
			{
				$this->parent->pushStep(array('type' => 'query', 'script' => $this->getManifest()->uninstall->sql));
			}
		}

		return true;
	}

	/**
	 * Load language files
	 *
	 * @param   string  $extension  The name of the extension
	 * @param   string  $source     Path to the extension
	 * @param   string  $base       Base path for the extension language
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	protected function doLoadLanguage($extension, $source, $base = JPATH_ADMINISTRATOR)
	{
		$lang = \JFactory::getLanguage();
		$lang->load($extension . '.sys', $source, null, false, true) || $lang->load($extension . '.sys', $base, null, false, true);
	}

	/**
	 * Checks if the adapter supports discover_install
	 *
	 * @return  boolean
	 *
	 * @since   3.4
	 */
	public function getDiscoverInstallSupported()
	{
		return $this->supportsDiscoverInstall;
	}

	/**
	 * Get the filtered extension element from the manifest
	 *
	 * @param   string  $element  Optional element name to be converted
	 *
	 * @return  string  The filtered element
	 *
	 * @since   3.4
	 */
	public function getElement($element = null)
	{
		if (!$element)
		{
			// Ensure the element is a string
			$element = (string) $this->getManifest()->element;
		}

		if (!$element)
		{
			$element = $this->getName();
		}

		// Filter the name for illegal characters
		return strtolower(\JFilterInput::getInstance()->clean($element, 'cmd'));
	}

	/**
	 * Get the manifest object.
	 *
	 * @return  \SimpleXMLElement  Manifest object
	 *
	 * @since   3.4
	 */
	public function getManifest()
	{
		return $this->manifest;
	}

	/**
	 * Get the filtered component name from the manifest
	 *
	 * @return  string  The filtered name
	 *
	 * @since   3.4
	 */
	public function getName()
	{
		// Ensure the name is a string
		$name = (string) $this->getManifest()->name;

		// Filter the name for illegal characters
		$name = \JFilterInput::getInstance()->clean($name, 'string');

		return $name;
	}

	/**
	 * Get the install route being followed
	 *
	 * @return  string  The install route
	 *
	 * @since   3.4
	 */
	public function getRoute()
	{
		return $this->route;
	}

	/**
	 * Get the class name for the install adapter script.
	 *
	 * @return  string  The class name.
	 *
	 * @since   3.4
	 */
	protected function getScriptClassName()
	{
		// Support element names like 'en-GB'
		$className = \JFilterInput::getInstance()->clean($this->element, 'cmd') . 'InstallerScript';

		// Cannot have - in class names
		$className = str_replace('-', '', $className);

		return $className;
	}

	/**
	 * Generic install method for extensions
	 *
	 * @return  boolean|integer  The extension ID on success, boolean false on failure
	 *
	 * @since   3.4
	 */
	public function install()
	{
		// Get the extension's description
		$description = (string) $this->getManifest()->description;

		if ($description)
		{
			$this->parent->message = \JText::_($description);
		}
		else
		{
			$this->parent->message = '';
		}

		// Set the extension's name and element
		$this->name    = $this->getName();
		$this->element = $this->getElement();

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Extension Precheck and Setup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Setup the install paths and perform other prechecks as necessary
		try
		{
			$this->setupInstallPaths();
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		// Check to see if an extension by the same name is already installed.
		try
		{
			$this->checkExistingExtension();
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		// Check if the extension is present in the filesystem
		try
		{
			$this->checkExtensionInFilesystem();
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		// If we are on the update route, run any custom setup routines
		if ($this->route === 'update')
		{
			try
			{
				$this->setupUpdates();
			}
			catch (\RuntimeException $e)
			{
				// Install failed, roll back changes
				$this->parent->abort($e->getMessage());

				return false;
			}
		}

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Installer Trigger Loading
		 * ---------------------------------------------------------------------------------------------
		 */

		$this->setupScriptfile();

		try
		{
			$this->triggerManifestScript('preflight');
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Filesystem Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// If the extension directory does not exist, lets create it
		try
		{
			$this->createExtensionRoot();
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		// Copy all necessary files
		try
		{
			$this->copyBaseFiles();
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		// Parse optional tags
		$this->parseOptionalTags();

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Database Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		try
		{
			$this->storeExtension();
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		try
		{
			$this->parseQueries();
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		// Run the custom method based on the route
		try
		{
			$this->triggerManifestScript($this->route);
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Finalization and Cleanup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		try
		{
			$this->finaliseInstall();
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		// And now we run the postflight
		try
		{
			$this->triggerManifestScript('postflight');
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort($e->getMessage());

			return false;
		}

		return $this->extension->extension_id;
	}

	/**
	 * Method to parse the queries specified in the `<sql>` tags
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @throws  \RuntimeException
	 */
	protected function parseQueries()
	{
		// Let's run the queries for the extension
		if (in_array($this->route, array('install', 'discover_install', 'uninstall')))
		{
			// This method may throw an exception, but it is caught by the parent caller
			if (!$this->doDatabaseTransactions())
			{
				throw new \RuntimeException(
					\JText::sprintf(
						'JLIB_INSTALLER_ABORT_SQL_ERROR',
						\JText::_('JLIB_INSTALLER_' . strtoupper($this->route)),
						$this->db->stderr(true)
					)
				);
			}

			// Set the schema version to be the latest update version
			if ($this->getManifest()->update)
			{
				$this->parent->setSchemaVersion($this->getManifest()->update->schemas, $this->extension->extension_id);
			}
		}
		elseif ($this->route === 'update')
		{
			if ($this->getManifest()->update)
			{
				$result = $this->parent->parseSchemaUpdates($this->getManifest()->update->schemas, $this->extension->extension_id);

				if ($result === false)
				{
					// Install failed, rollback changes
					throw new \RuntimeException(
						\JText::sprintf(
							'JLIB_INSTALLER_ABORT_SQL_ERROR',
							\JText::_('JLIB_INSTALLER_' . strtoupper($this->route)),
							$this->db->stderr(true)
						)
					);
				}
			}
		}
	}

	/**
	 * Method to parse optional tags in the manifest
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function parseOptionalTags()
	{
		// Some extensions may not have optional tags
	}

	/**
	 * Prepares the adapter for a discover_install task
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function prepareDiscoverInstall()
	{
		// Adapters may not support discover install or may have overridden the default task and aren't using this
	}

	/**
	 * Set the manifest object.
	 *
	 * @param   object  $manifest  The manifest object
	 *
	 * @return  InstallerAdapter  Instance of this class to support chaining
	 *
	 * @since   3.4
	 */
	public function setManifest($manifest)
	{
		$this->manifest = $manifest;

		return $this;
	}

	/**
	 * Set the install route being followed
	 *
	 * @param   string  $route  The install route being followed
	 *
	 * @return  InstallerAdapter  Instance of this class to support chaining
	 *
	 * @since   3.4
	 */
	public function setRoute($route)
	{
		$this->route = $route;

		return $this;
	}

	/**
	 * Method to do any prechecks and setup the install paths for the extension
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	abstract protected function setupInstallPaths();

	/**
	 * Setup the manifest script file for those adapters that use it.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	protected function setupScriptfile()
	{
		// If there is a manifest class file, lets load it; we'll copy it later (don't have dest yet)
		$manifestScript = (string) $this->getManifest()->scriptfile;

		if ($manifestScript)
		{
			$manifestScriptFile = $this->parent->getPath('source') . '/' . $manifestScript;

			$classname = $this->getScriptClassName();

			\JLoader::register($classname, $manifestScriptFile);

			if (class_exists($classname))
			{
				// Create a new instance
				$this->parent->manifestClass = new $classname($this);

				// And set this so we can copy it later
				$this->manifest_script = $manifestScript;
			}
		}
	}

	/**
	 * Method to setup the update routine for the adapter
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	protected function setupUpdates()
	{
		// Some extensions may not have custom setup routines for updates
	}

	/**
	 * Method to store the extension to the database
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @throws  \RuntimeException
	 */
	abstract protected function storeExtension();

	/**
	 * Executes a custom install script method
	 *
	 * @param   string  $method  The install method to execute
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.4
	 * @throws  \RuntimeException
	 */
	protected function triggerManifestScript($method)
	{
		ob_start();
		ob_implicit_flush(false);

		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass, $method))
		{
			switch ($method)
			{
				// The preflight and postflight take the route as a param
				case 'preflight' :
				case 'postflight' :
					if ($this->parent->manifestClass->$method($this->route, $this) === false)
					{
						if ($method !== 'postflight')
						{
							// Clean and close the output buffer
							ob_end_clean();

							// The script failed, rollback changes
							throw new \RuntimeException(
								\JText::sprintf(
									'JLIB_INSTALLER_ABORT_INSTALL_CUSTOM_INSTALL_FAILURE',
									\JText::_('JLIB_INSTALLER_' . $this->route)
								)
							);
						}
					}
					break;

				// The install, uninstall, and update methods only pass this object as a param
				case 'install' :
				case 'uninstall' :
				case 'update' :
					if ($this->parent->manifestClass->$method($this) === false)
					{
						if ($method !== 'uninstall')
						{
							// Clean and close the output buffer
							ob_end_clean();

							// The script failed, rollback changes
							throw new \RuntimeException(
								\JText::sprintf(
									'JLIB_INSTALLER_ABORT_INSTALL_CUSTOM_INSTALL_FAILURE',
									\JText::_('JLIB_INSTALLER_' . $this->route)
								)
							);
						}
					}
					break;
			}
		}

		// Append to the message object
		$this->extensionMessage .= ob_get_clean();

		// If in postflight or uninstall, set the message for display
		if (($method === 'uninstall' || $method === 'postflight') && $this->extensionMessage !== '')
		{
			$this->parent->set('extension_message', $this->extensionMessage);
		}

		return true;
	}

	/**
	 * Generic update method for extensions
	 *
	 * @return  boolean|integer  The extension ID on success, boolean false on failure
	 *
	 * @since   3.4
	 */
	public function update()
	{
		// Set the overwrite setting
		$this->parent->setOverwrite(true);
		$this->parent->setUpgrade(true);

		// And make sure the route is set correctly
		$this->setRoute('update');

		// Now jump into the install method to run the update
		return $this->install();
	}
}
