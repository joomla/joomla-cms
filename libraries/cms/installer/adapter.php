<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.base.adapterinstance');

/**
 * Abstract adapter for the installer.
 *
 * @method         JInstaller  getParent()  Retrieves the parent object.
 * @property-read  JInstaller  $parent      Parent object
 *
 * @since  3.4
 * @note   As of 4.0, this class will no longer extend from JAdapterInstance
 */
abstract class JInstallerAdapter extends JAdapterInstance
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
	 * JTableExtension object.
	 *
	 * @var    JTableExtension
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
	 * Constructor
	 *
	 * @param   JInstaller       $parent   Parent object
	 * @param   JDatabaseDriver  $db       Database object
	 * @param   array            $options  Configuration Options
	 *
	 * @since   3.4
	 */
	public function __construct(JInstaller $parent, $db, $options = array())
	{
		parent::__construct($parent, $db, $options);

		// Get a generic JTableExtension instance for use if not already loaded
		if (!($this->extension instanceof JTableInterface))
		{
			$this->extension = JTable::getInstance('extension');
		}
	}

	/**
	 * Method to handle database transactions for the installer
	 *
	 * @param   string  $route  The installation route
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	protected function doDatabaseTransactions($route)
	{
		// Get a database connector object
		$db = $this->parent->getDbo();

		// Let's run the install queries for the component
		if (isset($this->manifest->{$route}->sql))
		{
			$result = $this->parent->parseSQLFiles($this->manifest->{$route}->sql);

			if ($result === false)
			{
				// Only rollback if installing
				if ($route == 'install')
				{
					throw new RuntimeException(
						JText::sprintf(
							'JLIB_INSTALLER_ABORT_SQL_ERROR',
							JText::_('JLIB_INSTALLER_' . strtoupper($route)),
							$db->stderr(true)
						)
					);
				}

				return false;
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
		$lang = JFactory::getLanguage();
		$lang->load($extension . '.sys', $source, null, false, true) || $lang->load($extension . '.sys', $base, null, false, true);
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
		$element = JFilterInput::getInstance()->clean($element, 'cmd');

		return $element;
	}

	/**
	 * Get the manifest object.
	 *
	 * @return  object  Manifest object
	 *
	 * @since   3.4
	 */
	public function getManifest()
	{
		if (!$this->manifest)
		{
			// We are trying to find manifest for the installed extension.
			$this->manifest = $this->parent->getManifest();
		}

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
		$name = JFilterInput::getInstance()->clean($name, 'string');

		return $name;
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
		$className = JFilterInput::getInstance()->clean($this->element, 'cmd') . 'InstallerScript';

		// Cannot have - in class names
		$className = str_replace('-', '', $className);

		return $className;
	}

	/**
	 * Method to parse the queries specified in the <sql> tags
	 *
	 * @param   string  $route  The installation route
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	protected function parseQueries($route)
	{
		// Let's run the queries for the plugin
		if ($route == 'install')
		{
			if (!$this->doDatabaseTransactions('install'))
			{
				// TODO: Exception
				return false;
			}

			// Set the schema version to be the latest update version
			if ($this->manifest->update)
			{
				$this->parent->setSchemaVersion($this->manifest->update->schemas, $this->extension->extension_id);
			}
		}
		elseif ($route == 'update')
		{
			if ($this->manifest->update)
			{
				$result = $this->parent->parseSchemaUpdates($this->manifest->update->schemas, $this->extension->extension_id);

				if ($result === false)
				{
					// Install failed, rollback changes
					throw new RuntimeException(JText::sprintf('JLIB_INSTALLER_ABORT_PLG_UPDATE_SQL_ERROR', $this->db->stderr(true)));
				}
			}
		}
	}

	/**
	 * Setup the manifest script file for those adapters that use it.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	protected function setupScriptfile()
	{
		// If there is an manifest class file, lets load it; we'll copy it later (don't have dest yet)
		$manifestScript = (string) $this->manifest->scriptfile;

		if ($manifestScript)
		{
			$manifestScriptFile = $this->parent->getPath('source') . '/' . $manifestScript;

			if (is_file($manifestScriptFile))
			{
				// Load the file
				include_once $manifestScriptFile;
			}

			$classname = $this->getScriptClassName();

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
	 * Executes a custom install script method
	 *
	 * @param   string  $method  The install method to execute
	 * @param   string  $route   The route for preflight/postflight scripts
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	protected function triggerManifestScript($method, $route = null)
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
					if ($this->parent->manifestClass->$method($route, $this) === false)
					{
						if ($method != 'postflight')
						{
							// The script failed, rollback changes
							throw new RuntimeException(JText::_('JLIB_INSTALLER_ABORT_INSTALL_CUSTOM_INSTALL_FAILURE'));
						}
					}
					break;

				// The install, uninstall, and update methods only pass this object as a param
				case 'install' :
				case 'uninstall' :
				case 'update' :
					if ($this->parent->manifestClass->$method($this) === false)
					{
						if ($method != 'uninstall')
						{
							// The script failed, rollback changes
							throw new RuntimeException(JText::_('JLIB_INSTALLER_ABORT_INSTALL_CUSTOM_INSTALL_FAILURE'));
						}
					}
					break;
			}
		}

		// Append to the message object
		$this->extensionMessage .= ob_get_clean();

		// If in postflight or uninstall, set the message for display
		if (($method == 'uninstall' || $method == 'postflight') && $this->extensionMessage != '')
		{
			$this->parent->set('extension_message', $this->extensionMessage);
		}

		return true;
	}
}
