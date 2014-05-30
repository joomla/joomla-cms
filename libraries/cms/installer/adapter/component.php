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
jimport('joomla.filesystem.folder');

/**
 * Component installer
 *
 * @package     Joomla.Libraries
 * @subpackage  Installer
 * @since       3.1
 */
class JInstallerAdapterComponent extends JAdapterInstance
{
	/**
	 * Copy of the XML manifest file
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $manifest = null;

	/**
	 * Name of the extension
	 *
	 * @var    string
	 * @since  3.1
	 * */
	protected $name = null;

	/**
	 * The unique identifier for the extension (e.g. mod_login)
	 *
	 * @var    string
	 * @since  3.1
	 * */
	protected $element = null;

	/**
	 * The list of current files fo the Joomla! CMS administrator that are installed and is read
	 * from the manifest on disk in the update area to handle doing a diff
	 * and deleting files that are in the old files list and not in the new
	 * files list.
	 *
	 * @var    array
	 * @since  3.1
	 * */
	protected $oldAdminFiles = null;

	/**
	 * The list of current files that are installed and is read
	 * from the manifest on disk in the update area to handle doing a diff
	 * and deleting files that are in the old files list and not in the new
	 * files list.
	 *
	 * @var    array
	 * @since  3.1
	 * */
	protected $oldFiles = null;

	/**
	 * A path to the PHP file that the scriptfile declaration in
	 * the manifest refers to.
	 *
	 * @var    string
	 * @since  3.1
	 * */
	protected $manifest_script = null;

	/**
	 * For legacy installations this is a path to the PHP file that the scriptfile declaration in the
	 * manifest refers to.
	 *
	 * @var    string
	 * @since  3.1
	 * */
	protected $install_script = null;

	/**
	 * Custom loadLanguage method
	 *
	 * @param   string  $path  The path language files are on.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function loadLanguage($path = null)
	{
		$source = $this->parent->getPath('source');

		if (!$source)
		{
			$this->parent
				->setPath(
				'source',
				($this->parent->extension->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE) .
				'/components/' . $this->parent->extension->element
			);
		}

		$this->manifest = $this->parent->getManifest();
		$name = strtolower(JFilterInput::getInstance()->clean((string) $this->manifest->name, 'cmd'));

		if (substr($name, 0, 4) == 'com_')
		{
			$extension = $name;
		}
		else
		{
			$extension = 'com_' . $name;
		}

		$lang = JFactory::getLanguage();
		$source = $path ? $path : ($this->parent->extension->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE) . '/components/' . $extension;

		if ($this->manifest->administration->files)
		{
			$element = $this->manifest->administration->files;
		}
		elseif ($this->manifest->files)
		{
			$element = $this->manifest->files;
		}
		else
		{
			$element = null;
		}

		if ($element)
		{
			$folder = (string) $element->attributes()->folder;

			if ($folder && file_exists($path . '/' . $folder))
			{
				$source = $path . '/' . $folder;
			}
		}

		$lang->load($extension . '.sys', $source, null, false, true) || $lang->load($extension . '.sys', JPATH_ADMINISTRATOR, null, false, true);
	}

	/**
	 * Custom install method for components
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.1
	 */
	public function install()
	{
		// Get a database connector object
		$db = $this->parent->getDbo();

		// Get the extension manifest object
		$this->manifest = $this->parent->getManifest();

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Manifest Document Setup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Set the extension's name
		$name = strtolower(JFilterInput::getInstance()->clean((string) $this->manifest->name, 'cmd'));

		if (substr($name, 0, 4) == 'com_')
		{
			$element = $name;
		}
		else
		{
			$element = 'com_' . $name;
		}

		$this->set('name', $name);
		$this->set('element', $element);

		// Get the component description
		$this->parent->set('message', JText::_((string) $this->manifest->description));

		// Set the installation target paths
		$this->parent->setPath('extension_site', JPath::clean(JPATH_SITE . '/components/' . $this->get('element')));
		$this->parent->setPath('extension_administrator', JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $this->get('element')));

		// Copy the admin path as it's used as a common base
		$this->parent->setPath('extension_root', $this->parent->getPath('extension_administrator'));

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Basic Checks Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Make sure that we have an admin element
		if (!$this->manifest->administration)
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_COMP_INSTALL_ADMIN_ELEMENT'), JLog::WARNING, 'jerror');

			return false;
		}

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Filesystem Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		/*
		 * If the component site or admin directory already exists, then we will assume that the component is already
		 * installed or another component is using that directory.
		 */

		if (file_exists($this->parent->getPath('extension_site')) || file_exists($this->parent->getPath('extension_administrator')))
		{
			// Look for an update function or update tag
			$updateElement = $this->manifest->update;

			// Upgrade manually set or update function available or update tag detected
			if ($this->parent->isUpgrade() || ($this->parent->manifestClass && method_exists($this->parent->manifestClass, 'update'))
				|| $updateElement)
			{
				// Transfer control to the update function
				return $this->update();
			}
			elseif (!$this->parent->isOverwrite())
			{
				// Overwrite is set.
				// We didn't have overwrite set, find an update function or find an update tag so lets call it safe
				if (file_exists($this->parent->getPath('extension_site')))
				{
					// If the site exists say so.
					JLog::add(
						JText::sprintf('JLIB_INSTALLER_ERROR_COMP_INSTALL_DIR_SITE', $this->parent->getPath('extension_site')),
						JLog::WARNING, 'jerror'
					);
				}
				else
				{
					// If the admin exists say so
					JLog::add(
						JText::sprintf('JLIB_INSTALLER_ERROR_COMP_INSTALL_DIR_ADMIN', $this->parent->getPath('extension_administrator')),
						JLog::WARNING, 'jerror'
					);
				}

				return false;
			}
		}

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Installer Trigger Loading
		 * ---------------------------------------------------------------------------------------------
		 */

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

			// Set the class name
			$classname = $this->get('element') . 'InstallerScript';

			if (class_exists($classname))
			{
				// Create a new instance
				$this->parent->manifestClass = new $classname($this);

				// And set this so we can copy it later
				$this->set('manifest_script', $manifestScript);
			}
		}

		// Run preflight if possible (since we know we're not an update)
		ob_start();
		ob_implicit_flush(false);

		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass, 'preflight'))
		{
			if ($this->parent->manifestClass->preflight('install', $this) === false)
			{
				// Install failed, rollback changes
				$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_COMP_INSTALL_CUSTOM_INSTALL_FAILURE'));

				return false;
			}
		}

		// Create msg object; first use here
		$msg = ob_get_contents();
		ob_end_clean();

		// If the component directory does not exist, let's create it
		$created = false;

		if (!file_exists($this->parent->getPath('extension_site')))
		{
			if (!$created = JFolder::create($this->parent->getPath('extension_site')))
			{
				JLog::add(
					JText::sprintf('JLIB_INSTALLER_ERROR_COMP_INSTALL_FAILED_TO_CREATE_DIRECTORY_SITE', $this->parent->getPath('extension_site')),
					JLog::WARNING, 'jerror'
				);

				return false;
			}
		}

		/*
		 * Since we created the component directory and we will want to remove it if we have to roll back
		 * the installation, let's add it to the installation step stack
		 */
		if ($created)
		{
			$this->parent->pushStep(array('type' => 'folder', 'path' => $this->parent->getPath('extension_site')));
		}

		// If the component admin directory does not exist, let's create it
		$created = false;

		if (!file_exists($this->parent->getPath('extension_administrator')))
		{
			if (!$created = JFolder::create($this->parent->getPath('extension_administrator')))
			{
				JLog::add(
					JText::sprintf('JLIB_INSTALLER_ERROR_COMP_INSTALL_FAILED_TO_CREATE_DIRECTORY_ADMIN', $this->parent->getPath('extension_administrator')),
					JLog::WARNING, 'jerror'
				);

				// Install failed, rollback any changes
				$this->parent->abort();

				return false;
			}
		}

		/*
		 * Since we created the component admin directory and we will want to remove it if we have to roll
		 * back the installation, let's add it to the installation step stack
		 */
		if ($created)
		{
			$this->parent->pushStep(array('type' => 'folder', 'path' => $this->parent->getPath('extension_administrator')));
		}

		// Copy site files
		if ($this->manifest->files)
		{
			if ($this->parent->parseFiles($this->manifest->files) === false)
			{
				// Install failed, rollback any changes
				$this->parent->abort();

				return false;
			}
		}

		// Copy admin files
		if ($this->manifest->administration->files)
		{
			if ($this->parent->parseFiles($this->manifest->administration->files, 1) === false)
			{
				// Install failed, rollback any changes
				$this->parent->abort();

				return false;
			}
		}

		// Parse optional tags
		$this->parent->parseMedia($this->manifest->media);
		$this->parent->parseLanguages($this->manifest->languages);
		$this->parent->parseLanguages($this->manifest->administration->languages, 1);

		// If there is a manifest script, let's copy it.
		if ($this->get('manifest_script'))
		{
			$path['src'] = $this->parent->getPath('source') . '/' . $this->get('manifest_script');
			$path['dest'] = $this->parent->getPath('extension_administrator') . '/' . $this->get('manifest_script');

			if (!file_exists($path['dest']) || $this->parent->isOverwrite())
			{
				if (!$this->parent->copyFiles(array($path)))
				{
					// Install failed, rollback changes
					$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_COMP_INSTALL_MANIFEST'));

					return false;
				}
			}
		}

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Database Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Run the install queries for the component
		if (isset($this->manifest->install->sql))
		{
			$result = $this->parent->parseSQLFiles($this->manifest->install->sql);

			if ($result === false)
			{
				// Install failed, rollback changes
				$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_COMP_INSTALL_SQL_ERROR', $db->stderr(true)));

				return false;
			}
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Custom Installation Script Section
		 * ---------------------------------------------------------------------------------------------
		 */

		/*
		 * If we have an install script, let's include it, execute the custom
		 * install method, and append the return value from the custom install
		 * method to the installation message.
		 */
		ob_start();
		ob_implicit_flush(false);

		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass, 'install'))
		{
			if ($this->parent->manifestClass->install($this) === false)
			{
				// Install failed, rollback changes
				$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_COMP_INSTALL_CUSTOM_INSTALL_FAILURE'));

				return false;
			}
		}

		// Append messages
		$msg .= ob_get_contents();
		ob_end_clean();

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Finalization and Cleanup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Add an entry to the extension table with a whole heap of defaults
		$row = JTable::getInstance('extension');
		$row->set('name', $this->get('name'));
		$row->set('type', 'component');
		$row->set('element', $this->get('element'));

		// There is no folder for components
		$row->set('folder', '');
		$row->set('enabled', 1);
		$row->set('protected', 0);
		$row->set('access', 0);
		$row->set('client_id', 1);
		$row->set('params', $this->parent->getParams());
		$row->set('manifest_cache', $this->parent->generateManifestCache());

		if (!$row->store())
		{
			// Install failed, roll back changes
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_COMP_INSTALL_ROLLBACK', $db->stderr(true)));

			return false;
		}

		$eid = $row->extension_id;

		// Clobber any possible pending updates
		$update = JTable::getInstance('update');
		$uid = $update->find(array('element' => $this->get('element'), 'type' => 'component', 'client_id' => 1, 'folder' => ''));

		if ($uid)
		{
			$update->delete($uid);
		}

		// We will copy the manifest file to its appropriate place.
		if (!$this->parent->copyManifest())
		{
			// Install failed, rollback changes
			$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_COMP_INSTALL_COPY_SETUP'));

			return false;
		}

		// Time to build the admin menus
		if (!$this->_buildAdminMenus($row->extension_id))
		{
			JLog::add(JText::_('JLIB_INSTALLER_ABORT_COMP_BUILDADMINMENUS_FAILED'), JLog::WARNING, 'jerror');

			// @todo remove code: $this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_COMP_INSTALL_ROLLBACK', $db->stderr(true)));
			// @todo remove code: return false;
		}

		// Set the schema version to be the latest update version
		if ($this->manifest->update)
		{
			$this->parent->setSchemaVersion($this->manifest->update->schemas, $eid);
		}

		// Register the component container just under root in the assets table.
		$asset = JTable::getInstance('Asset');
		$asset->name = $row->element;
		$asset->parent_id = 1;
		$asset->rules = '{}';
		$asset->title = $row->name;
		$asset->setLocation(1, 'last-child');

		if (!$asset->store())
		{
			// Install failed, roll back changes
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_COMP_INSTALL_ROLLBACK', $db->stderr(true)));

			return false;
		}

		// And now we run the postflight
		ob_start();
		ob_implicit_flush(false);

		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass, 'postflight'))
		{
			$this->parent->manifestClass->postflight('install', $this);
		}

		// Append messages
		$msg .= ob_get_contents();
		ob_end_clean();

		if ($msg != '')
		{
			$this->parent->set('extension_message', $msg);
		}

		return $row->extension_id;
	}

	/**
	 * Custom update method for components
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.1
	 */
	public function update()
	{
		// Get a database connector object
		$db = $this->parent->getDbo();

		// Set the overwrite setting
		$this->parent->setOverwrite(true);

		// Get the extension manifest object
		$this->manifest = $this->parent->getManifest();

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Manifest Document Setup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Set the extension's name
		$name = strtolower(JFilterInput::getInstance()->clean((string) $this->manifest->name, 'cmd'));

		if (substr($name, 0, 4) == 'com_')
		{
			$element = $name;
		}
		else
		{
			$element = 'com_' . $name;
		}

		$this->set('name', $name);
		$this->set('element', $element);

		// Get the component description
		$description = (string) $this->manifest->description;

		if ($description)
		{
			$this->parent->set('message', JText::_($description));
		}
		else
		{
			$this->parent->set('message', '');
		}

		// Set the installation target paths
		$this->parent->setPath('extension_site', JPath::clean(JPATH_SITE . '/components/' . $this->get('element')));
		$this->parent->setPath('extension_administrator', JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $this->get('element')));

		// Copy the admin path as it's used as a common base
		$this->parent->setPath('extension_root', $this->parent->getPath('extension_administrator'));

		// Hunt for the original XML file
		$old_manifest = null;

		// Create a new installer because findManifest sets stuff
		// Look in the administrator first
		$tmpInstaller = new JInstaller;
		$tmpInstaller->setPath('source', $this->parent->getPath('extension_administrator'));

		if (!$tmpInstaller->findManifest())
		{
			// Then the site
			$tmpInstaller->setPath('source', $this->parent->getPath('extension_site'));

			if ($tmpInstaller->findManifest())
			{
				$old_manifest = $tmpInstaller->getManifest();
			}
		}
		else
		{
			$old_manifest = $tmpInstaller->getManifest();
		}

		// Should do this above perhaps?
		if ($old_manifest)
		{
			$this->oldAdminFiles = $old_manifest->administration->files;
			$this->oldFiles = $old_manifest->files;
		}
		else
		{
			$this->oldAdminFiles = null;
			$this->oldFiles = null;
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Basic Checks Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Make sure that we have an admin element
		if (!$this->manifest->administration)
		{
			JLog::add(JText::_('JLIB_INSTALLER_ABORT_COMP_UPDATE_ADMIN_ELEMENT'), JLog::WARNING, 'jerror');

			return false;
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Installer Trigger Loading
		 * ---------------------------------------------------------------------------------------------
		 */

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

			// Set the class name
			$classname = $element . 'InstallerScript';

			if (class_exists($classname))
			{
				// Create a new instance
				$this->parent->manifestClass = new $classname($this);

				// And set this so we can copy it later
				$this->set('manifest_script', $manifestScript);
			}
		}

		// Run preflight if possible (since we know we're not an update)
		ob_start();
		ob_implicit_flush(false);

		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass, 'preflight'))
		{
			if ($this->parent->manifestClass->preflight('update', $this) === false)
			{
				// Install failed, rollback changes
				$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_COMP_INSTALL_CUSTOM_INSTALL_FAILURE'));

				return false;
			}
		}

		// Create msg object; first use here
		$msg = ob_get_contents();
		ob_end_clean();

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Filesystem Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// If the component directory does not exist, let's create it
		$created = false;

		if (!file_exists($this->parent->getPath('extension_site')))
		{
			if (!$created = JFolder::create($this->parent->getPath('extension_site')))
			{
				JLog::add(
					JText::sprintf('JLIB_INSTALLER_ERROR_COMP_UPDATE_FAILED_TO_CREATE_DIRECTORY_SITE', $this->parent->getPath('extension_site')),
					JLog::WARNING, 'jerror'
				);

				return false;
			}
		}

		/*
		 * Since we created the component directory and will want to remove it if we have to roll back
		 * the installation, lets add it to the installation step stack
		 */
		if ($created)
		{
			$this->parent->pushStep(array('type' => 'folder', 'path' => $this->parent->getPath('extension_site')));
		}

		// If the component admin directory does not exist, let's create it
		$created = false;

		if (!file_exists($this->parent->getPath('extension_administrator')))
		{
			if (!$created = JFolder::create($this->parent->getPath('extension_administrator')))
			{
				JLog::add(
					JText::sprintf('JLIB_INSTALLER_ERROR_COMP_UPDATE_FAILED_TO_CREATE_DIRECTORY_ADMIN', $this->parent->getPath('extension_administrator')),
					JLog::WARNING, 'jerror'
				);

				// Install failed, rollback any changes
				$this->parent->abort();

				return false;
			}
		}

		/*
		 * Since we created the component admin directory and we will want to remove it if we have to roll
		 * back the installation, let's add it to the installation step stack
		 */
		if ($created)
		{
			$this->parent->pushStep(array('type' => 'folder', 'path' => $this->parent->getPath('extension_administrator')));
		}

		// Find files to copy
		if ($this->manifest->files)
		{
			if ($this->parent->parseFiles($this->manifest->files, 0, $this->oldFiles) === false)
			{
				// Install failed, rollback any changes
				$this->parent->abort();

				return false;
			}
		}

		if ($this->manifest->administration->files)
		{
			if ($this->parent->parseFiles($this->manifest->administration->files, 1, $this->oldAdminFiles) === false)
			{
				// Install failed, rollback any changes
				$this->parent->abort();

				return false;
			}
		}

		// Parse optional tags
		$this->parent->parseMedia($this->manifest->media);
		$this->parent->parseLanguages($this->manifest->languages);
		$this->parent->parseLanguages($this->manifest->administration->languages, 1);

		// If there is a manifest script, let's copy it.
		if ($this->get('manifest_script'))
		{
			$path['src'] = $this->parent->getPath('source') . '/' . $this->get('manifest_script');
			$path['dest'] = $this->parent->getPath('extension_administrator') . '/' . $this->get('manifest_script');

			if (!file_exists($path['dest']) || $this->parent->isOverwrite())
			{
				if (!$this->parent->copyFiles(array($path)))
				{
					// Install failed, rollback changes
					$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_COMP_UPDATE_MANIFEST'));

					return false;
				}
			}
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Database Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Let's run the update queries for the component
		$row = JTable::getInstance('extension');
		$eid = $row->find(array('element' => strtolower($this->get('element')), 'type' => 'component'));

		if ($this->manifest->update)
		{
			$result = $this->parent->parseSchemaUpdates($this->manifest->update->schemas, $eid);

			if ($result === false)
			{
				// Install failed, rollback changes
				$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_COMP_UPDATE_SQL_ERROR', $db->stderr(true)));

				return false;
			}
		}

		// Time to build the admin menus
		if (!$this->_buildAdminMenus($eid))
		{
			JLog::add(JText::_('JLIB_INSTALLER_ABORT_COMP_BUILDADMINMENUS_FAILED'), JLog::WARNING, 'jerror');

			// $this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_COMP_INSTALL_ROLLBACK', $db->stderr(true)));
			// Return false;
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Custom Installation Script Section
		 * ---------------------------------------------------------------------------------------------
		 */

		/*
		 * If we have an install script, let's include it, execute the custom
		 * update method, and append the return value from the custom update
		 * method to the installation message.
		 */
		ob_start();
		ob_implicit_flush(false);

		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass, 'update'))
		{
			if ($this->parent->manifestClass->update($this) === false)
			{
				// Install failed, rollback changes
				$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_COMP_INSTALL_CUSTOM_INSTALL_FAILURE'));

				return false;
			}
		}

		// Append messages
		$msg .= ob_get_contents();
		ob_end_clean();

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Finalization and Cleanup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Clobber any possible pending updates
		$update = JTable::getInstance('update');
		$uid = $update->find(array('element' => $this->get('element'), 'type' => 'component', 'client_id' => 1, 'folder' => ''));

		if ($uid)
		{
			$update->delete($uid);
		}

		// Update an entry to the extension table
		if ($eid)
		{
			$row->load($eid);
		}
		else
		{
			// Set the defaults
			// There is no folder for components
			$row->folder = '';
			$row->enabled = 1;
			$row->protected = 0;
			$row->access = 1;
			$row->client_id = 1;
			$row->params = $this->parent->getParams();
		}

		$row->name = $this->get('name');
		$row->type = 'component';
		$row->element = $this->get('element');
		$row->manifest_cache = $this->parent->generateManifestCache();

		if (!$row->store())
		{
			// Install failed, roll back changes
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_COMP_UPDATE_ROLLBACK', $db->stderr(true)));

			return false;
		}

		// We will copy the manifest file to its appropriate place.
		if (!$this->parent->copyManifest())
		{
			// Install failed, rollback changes
			$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_COMP_UPDATE_COPY_SETUP'));

			return false;
		}

		// And now we run the postflight
		ob_start();
		ob_implicit_flush(false);

		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass, 'postflight'))
		{
			$this->parent->manifestClass->postflight('update', $this);
		}

		// Append messages
		$msg .= ob_get_contents();
		ob_end_clean();

		if ($msg != '')
		{
			$this->parent->set('extension_message', $msg);
		}

		return $row->extension_id;
	}

	/**
	 * Custom uninstall method for components
	 *
	 * @param   integer  $id  The unique extension id of the component to uninstall
	 *
	 * @return  mixed  Return value for uninstall method in component uninstall file
	 *
	 * @since   3.1
	 */
	public function uninstall($id)
	{
		$db = $this->parent->getDbo();
		$row = null;
		$retval = true;

		// First order of business will be to load the component object table from the database.
		// This should give us the necessary information to proceed.
		$row = JTable::getInstance('extension');

		if (!$row->load((int) $id))
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_COMP_UNINSTALL_ERRORUNKOWNEXTENSION'), JLog::WARNING, 'jerror');

			return false;
		}

		// Is the component we are trying to uninstall a core one?
		// Because that is not a good idea...
		if ($row->protected)
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_COMP_UNINSTALL_WARNCORECOMPONENT'), JLog::WARNING, 'jerror');

			return false;
		}

		// Get the admin and site paths for the component
		$this->parent->setPath('extension_administrator', JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $row->element));
		$this->parent->setPath('extension_site', JPath::clean(JPATH_SITE . '/components/' . $row->element));

		// Copy the admin path as it's used as a common base
		$this->parent->setPath('extension_root', $this->parent->getPath('extension_administrator'));

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Manifest Document Setup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Find and load the XML install file for the component
		$this->parent->setPath('source', $this->parent->getPath('extension_administrator'));

		// Get the package manifest object
		// We do findManifest to avoid problem when uninstalling a list of extension: getManifest cache its manifest file
		$this->parent->findManifest();
		$this->manifest = $this->parent->getManifest();

		if (!$this->manifest)
		{
			// Make sure we delete the folders if no manifest exists
			JFolder::delete($this->parent->getPath('extension_administrator'));
			JFolder::delete($this->parent->getPath('extension_site'));

			// Remove the menu
			$this->_removeAdminMenus($row);

			// Raise a warning
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_COMP_UNINSTALL_ERRORREMOVEMANUALLY'), JLog::WARNING, 'jerror');

			// Return
			return false;
		}

		// Set the extensions name
		$name = strtolower(JFilterInput::getInstance()->clean((string) $this->manifest->name, 'cmd'));

		if (substr($name, 0, 4) == 'com_')
		{
			$element = $name;
		}
		else
		{
			$element = 'com_' . $name;
		}

		$this->set('name', $name);
		$this->set('element', $element);

		// Attempt to load the admin language file; might have uninstall strings
		$this->loadLanguage(JPATH_ADMINISTRATOR . '/components/' . $element);

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Installer Trigger Loading and Uninstall
		 * ---------------------------------------------------------------------------------------------
		 */

		// If there is an manifest class file, lets load it; we'll copy it later (don't have dest yet)
		$scriptFile = (string) $this->manifest->scriptfile;

		if ($scriptFile)
		{
			$manifestScriptFile = $this->parent->getPath('source') . '/' . $scriptFile;

			if (is_file($manifestScriptFile))
			{
				// Load the file
				include_once $manifestScriptFile;
			}

			// Set the class name
			$classname = $row->element . 'InstallerScript';

			if (class_exists($classname))
			{
				// Create a new instance
				$this->parent->manifestClass = new $classname($this);

				// And set this so we can copy it later
				$this->set('manifest_script', $scriptFile);
			}
		}

		ob_start();
		ob_implicit_flush(false);

		// Run uninstall if possible
		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass, 'uninstall'))
		{
			$this->parent->manifestClass->uninstall($this);
		}

		$msg = ob_get_contents();
		ob_end_clean();

		if ($msg != '')
		{
			$this->parent->set('extension_message', $msg);
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Database Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Let's run the uninstall queries for the component
		if (isset($this->manifest->uninstall->sql))
		{
			$result = $this->parent->parseSQLFiles($this->manifest->uninstall->sql);

			if ($result === false)
			{
				// Install failed, rollback changes
				JLog::add(JText::sprintf('JLIB_INSTALLER_ERROR_COMP_UNINSTALL_SQL_ERROR', $db->stderr(true)), JLog::WARNING, 'jerror');
				$retval = false;
			}
		}

		$this->_removeAdminMenus($row);

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Filesystem Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Let's remove those language files and media in the JROOT/images/ folder that are
		// associated with the component we are uninstalling
		$this->parent->removeFiles($this->manifest->media);
		$this->parent->removeFiles($this->manifest->languages);
		$this->parent->removeFiles($this->manifest->administration->languages, 1);

		// Remove the schema version
		$query = $db->getQuery(true)
			->delete('#__schemas')
			->where('extension_id = ' . $id);
		$db->setQuery($query);
		$db->execute();

		// Remove the component container in the assets table.
		$asset = JTable::getInstance('Asset');

		if ($asset->loadByName($element))
		{
			$asset->delete();
		}

		// Remove categories for this component
		$query->clear()
			->delete('#__categories')
			->where('extension=' . $db->quote($element), 'OR')
			->where('extension LIKE ' . $db->quote($element . '.%'));
		$db->setQuery($query);
		$db->execute();

		// Clobber any possible pending updates
		$update = JTable::getInstance('update');
		$uid = $update->find(array('element' => $row->element, 'type' => 'component', 'client_id' => 1, 'folder' => ''));

		if ($uid)
		{
			$update->delete($uid);
		}

		// Now we need to delete the installation directories. This is the final step in uninstalling the component.
		if (trim($row->element))
		{
			// Delete the component site directory
			if (is_dir($this->parent->getPath('extension_site')))
			{
				if (!JFolder::delete($this->parent->getPath('extension_site')))
				{
					JLog::add(JText::_('JLIB_INSTALLER_ERROR_COMP_UNINSTALL_FAILED_REMOVE_DIRECTORY_SITE'), JLog::WARNING, 'jerror');
					$retval = false;
				}
			}

			// Delete the component admin directory
			if (is_dir($this->parent->getPath('extension_administrator')))
			{
				if (!JFolder::delete($this->parent->getPath('extension_administrator')))
				{
					JLog::add(JText::_('JLIB_INSTALLER_ERROR_COMP_UNINSTALL_FAILED_REMOVE_DIRECTORY_ADMIN'), JLog::WARNING, 'jerror');
					$retval = false;
				}
			}

			// Now we will no longer need the extension object, so let's delete it and free up memory
			$row->delete($row->extension_id);
			unset($row);

			return $retval;
		}
		else
		{
			// No component option defined... cannot delete what we don't know about
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_COMP_UNINSTALL_NO_OPTION'), JLog::WARNING, 'jerror');

			return false;
		}
	}

	/**
	 * Method to build menu database entries for a component
	 *
	 * @return  boolean  True if successful
	 *
	 * @since   3.1
	 */
	protected function _buildAdminMenus()
	{
		$db = $this->parent->getDbo();
		$table = JTable::getInstance('menu');
		$option = $this->get('element');

		// If a component exists with this option in the table then we don't need to add menus
		$query = $db->getQuery(true)
			->select('m.id, e.extension_id')
			->from('#__menu AS m')
			->join('LEFT', '#__extensions AS e ON m.component_id = e.extension_id')
			->where('m.parent_id = 1')
			->where('m.client_id = 1')
			->where('e.element = ' . $db->quote($option));

		$db->setQuery($query);

		$componentrow = $db->loadObject();

		// Check if menu items exist
		if ($componentrow)
		{
			// Don't do anything if overwrite has not been enabled
			if (!$this->parent->isOverwrite())
			{
				return true;
			}

			// Remove existing menu items if overwrite has been enabled
			if ($option)
			{
				// If something goes wrong, there's no way to rollback TODO: Search for better solution
				$this->_removeAdminMenus($componentrow);
			}

			$component_id = $componentrow->extension_id;
		}
		else
		{
			// Lets find the extension id
			$query->clear()
				->select('e.extension_id')
				->from('#__extensions AS e')
				->where('e.element = ' . $db->quote($option));

			$db->setQuery($query);

			// TODO Find Some better way to discover the component_id
			$component_id = $db->loadResult();
		}

		// Ok, now its time to handle the menus.  Start with the component root menu, then handle submenus.
		$menuElement = $this->manifest->administration->menu;

		if ($menuElement)
		{
			$data = array();
			$data['menutype'] = 'main';
			$data['client_id'] = 1;
			$data['title'] = (string) trim($menuElement);
			$data['alias'] = (string) $menuElement;
			$data['link'] = 'index.php?option=' . $option;
			$data['type'] = 'component';
			$data['published'] = 0;
			$data['parent_id'] = 1;
			$data['component_id'] = $component_id;
			$data['img'] = ((string) $menuElement->attributes()->img) ? (string) $menuElement->attributes()->img : 'class:component';
			$data['home'] = 0;

			try
			{
				$table->setLocation(1, 'last-child');
			}
			catch (InvalidArgumentException $e)
			{
				JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

				return false;
			}

			if (!$table->bind($data) || !$table->check() || !$table->store())
			{
				// The menu item already exists. Delete it and retry instead of throwing an error.
				$query->clear()
					->select('id')
					->from('#__menu')
					->where('menutype = ' . $db->quote('main'))
					->where('client_id = 1')
					->where('link = ' . $db->quote('index.php?option=' . $option))
					->where('type = ' . $db->quote('component'))
					->where('parent_id = 1')
					->where('home = 0');

				$db->setQuery($query);
				$menu_id = $db->loadResult();

				if (!$menu_id)
				{
					// Oops! Could not get the menu ID. Go back and rollback changes.
					JError::raiseWarning(1, $table->getError());

					return false;
				}
				else
				{
					// Remove the old menu item
					$query->clear()
						->delete('#__menu')
						->where('id = ' . (int) $menu_id);

					$db->setQuery($query);
					$db->query();

					// Retry creating the menu item
					$table->setLocation(1, 'last-child');

					if (!$table->bind($data) || !$table->check() || !$table->store())
					{
						// Install failed, warn user and rollback changes
						JError::raiseWarning(1, $table->getError());

						return false;
					}
				}
			}

			/*
			 * Since we have created a menu item, we add it to the installation step stack
			 * so that if we have to rollback the changes we can undo it.
			 */
			$this->parent->pushStep(array('type' => 'menu', 'id' => $component_id));
		}
		// No menu element was specified, Let's make a generic menu item
		else
		{
			$data = array();
			$data['menutype'] = 'main';
			$data['client_id'] = 1;
			$data['title'] = $option;
			$data['alias'] = $option;
			$data['link'] = 'index.php?option=' . $option;
			$data['type'] = 'component';
			$data['published'] = 0;
			$data['parent_id'] = 1;
			$data['component_id'] = $component_id;
			$data['img'] = 'class:component';
			$data['home'] = 0;

			try
			{
				$table->setLocation(1, 'last-child');
			}
			catch (InvalidArgumentException $e)
			{
				JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

				return false;
			}

			if (!$table->bind($data) || !$table->check() || !$table->store())
			{
				// Install failed, warn user and rollback changes
				JLog::add($table->getError(), JLog::WARNING, 'jerror');

				return false;
			}

			/*
			 * Since we have created a menu item, we add it to the installation step stack
			 * so that if we have to rollback the changes we can undo it.
			 */
			$this->parent->pushStep(array('type' => 'menu', 'id' => $component_id));
		}

		/*
		 * Process SubMenus
		 */

		if (!$this->manifest->administration->submenu)
		{
			return true;
		}

		$parent_id = $table->id;

		foreach ($this->manifest->administration->submenu->menu as $child)
		{
			$data = array();
			$data['menutype'] = 'main';
			$data['client_id'] = 1;
			$data['title'] = (string) trim($child);
			$data['alias'] = (string) $child;
			$data['type'] = 'component';
			$data['published'] = 0;
			$data['parent_id'] = $parent_id;
			$data['component_id'] = $component_id;
			$data['img'] = ((string) $child->attributes()->img) ? (string) $child->attributes()->img : 'class:component';
			$data['home'] = 0;

			// Set the sub menu link
			if ((string) $child->attributes()->link)
			{
				$data['link'] = 'index.php?' . $child->attributes()->link;
			}
			else
			{
				$request = array();

				if ((string) $child->attributes()->act)
				{
					$request[] = 'act=' . $child->attributes()->act;
				}

				if ((string) $child->attributes()->task)
				{
					$request[] = 'task=' . $child->attributes()->task;
				}

				if ((string) $child->attributes()->controller)
				{
					$request[] = 'controller=' . $child->attributes()->controller;
				}

				if ((string) $child->attributes()->view)
				{
					$request[] = 'view=' . $child->attributes()->view;
				}

				if ((string) $child->attributes()->layout)
				{
					$request[] = 'layout=' . $child->attributes()->layout;
				}

				if ((string) $child->attributes()->sub)
				{
					$request[] = 'sub=' . $child->attributes()->sub;
				}

				$qstring = (count($request)) ? '&' . implode('&', $request) : '';
				$data['link'] = 'index.php?option=' . $option . $qstring;
			}

			$table = JTable::getInstance('menu');

			try
			{
				$table->setLocation($parent_id, 'last-child');
			}
			catch (InvalidArgumentException $e)
			{
				return false;
			}

			if (!$table->bind($data) || !$table->check() || !$table->store())
			{
				// Install failed, rollback changes
				return false;
			}

			/*
			 * Since we have created a menu item, we add it to the installation step stack
			 * so that if we have to rollback the changes we can undo it.
			 */
			$this->parent->pushStep(array('type' => 'menu', 'id' => $component_id));
		}

		return true;
	}

	/**
	 * Method to remove admin menu references to a component
	 *
	 * @param   object  &$row  Component table object.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @since   3.1
	 */
	protected function _removeAdminMenus(&$row)
	{
		$db = $this->parent->getDbo();
		$table = JTable::getInstance('menu');
		$id = $row->extension_id;

		// Get the ids of the menu items
		$query = $db->getQuery(true)
			->select('id')
			->from('#__menu')
			->where($db->quoteName('client_id') . ' = 1')
			->where($db->quoteName('component_id') . ' = ' . (int) $id);

		$db->setQuery($query);

		$ids = $db->loadColumn();

		// Check for error
		if (!empty($ids))
		{
			// Iterate the items to delete each one.
			foreach ($ids as $menuid)
			{
				if (!$table->delete((int) $menuid))
				{
					$this->setError($table->getError());

					return false;
				}
			}
			// Rebuild the whole tree
			$table->rebuild();
		}

		return true;
	}

	/**
	 * Custom rollback method
	 * - Roll back the component menu item
	 *
	 * @param   array  $step  Installation step to rollback.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.1
	 */
	protected function _rollback_menu($step)
	{
		return $this->_removeAdminMenus((object) array('extension_id' => $step['id']));
	}

	/**
	 * Discover unregistered extensions.
	 *
	 * @return  array  A list of extensions.
	 *
	 * @since   3.1
	 */
	public function discover()
	{
		$results = array();
		$site_components = JFolder::folders(JPATH_SITE . '/components');
		$admin_components = JFolder::folders(JPATH_ADMINISTRATOR . '/components');

		foreach ($site_components as $component)
		{
			if (file_exists(JPATH_SITE . '/components/' . $component . '/' . str_replace('com_', '', $component) . '.xml'))
			{
				$manifest_details = JInstaller::parseXMLInstallFile(
					JPATH_SITE . '/components/' . $component . '/' . str_replace('com_', '', $component) . '.xml'
				);
				$extension = JTable::getInstance('extension');
				$extension->set('type', 'component');
				$extension->set('client_id', 0);
				$extension->set('element', $component);
				$extension->set('folder', '');
				$extension->set('name', $component);
				$extension->set('state', -1);
				$extension->set('manifest_cache', json_encode($manifest_details));
				$extension->set('params', '{}');
				$results[] = $extension;
			}
		}

		foreach ($admin_components as $component)
		{
			if (file_exists(JPATH_ADMINISTRATOR . '/components/' . $component . '/' . str_replace('com_', '', $component) . '.xml'))
			{
				$manifest_details = JInstaller::parseXMLInstallFile(
					JPATH_ADMINISTRATOR . '/components/' . $component . '/' . str_replace('com_', '', $component) . '.xml'
				);
				$extension = JTable::getInstance('extension');
				$extension->set('type', 'component');
				$extension->set('client_id', 1);
				$extension->set('element', $component);
				$extension->set('folder', '');
				$extension->set('name', $component);
				$extension->set('state', -1);
				$extension->set('manifest_cache', json_encode($manifest_details));
				$extension->set('params', '{}');
				$results[] = $extension;
			}
		}

		return $results;
	}

	/**
	 * Install unregistered extensions that have been discovered.
	 *
	 * @return  mixed
	 *
	 * @since   3.1
	 */
	public function discover_install()
	{
		// Need to find to find where the XML file is since we don't store this normally
		$client = JApplicationHelper::getClientInfo($this->parent->extension->client_id);
		$short_element = str_replace('com_', '', $this->parent->extension->element);
		$manifestPath = $client->path . '/components/' . $this->parent->extension->element . '/' . $short_element . '.xml';
		$this->parent->manifest = $this->parent->isManifest($manifestPath);
		$this->parent->setPath('manifest', $manifestPath);
		$this->parent->setPath('source', $client->path . '/components/' . $this->parent->extension->element);
		$this->parent->setPath('extension_root', $this->parent->getPath('source'));

		$manifest_details = JInstaller::parseXMLInstallFile($this->parent->getPath('manifest'));
		$this->parent->extension->manifest_cache = json_encode($manifest_details);
		$this->parent->extension->state = 0;
		$this->parent->extension->name = $manifest_details['name'];
		$this->parent->extension->enabled = 1;
		$this->parent->extension->params = $this->parent->getParams();

		try
		{
			$this->parent->extension->store();
		}
		catch (RuntimeException $e)
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_COMP_DISCOVER_STORE_DETAILS'), JLog::WARNING, 'jerror');

			return false;
		}

		// Now we need to run any SQL it has, languages, media or menu stuff

		// Get a database connector object
		$db = $this->parent->getDbo();

		// Get the extension manifest object
		$this->manifest = $this->parent->getManifest();

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Manifest Document Setup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Set the extensions name
		$name = strtolower(JFilterInput::getInstance()->clean((string) $this->manifest->name, 'cmd'));

		if (substr($name, 0, 4) == 'com_')
		{
			$element = $name;
		}
		else
		{
			$element = 'com_' . $name;
		}

		$this->set('name', $name);
		$this->set('element', $element);

		// Get the component description
		$description = (string) $this->manifest->description;

		if ($description)
		{
			$this->parent->set('message', JText::_((string) $description));
		}
		else
		{
			$this->parent->set('message', '');
		}

		// Set the installation target paths
		$this->parent->setPath('extension_site', JPath::clean(JPATH_SITE . '/components/' . $this->get('element')));
		$this->parent->setPath('extension_administrator', JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $this->get('element')));

		// Copy the admin path as it's used as a common base
		$this->parent->setPath('extension_root', $this->parent->getPath('extension_administrator'));

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Basic Checks Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Make sure that we have an admin element
		if (!$this->manifest->administration)
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_COMP_INSTALL_ADMIN_ELEMENT'), JLog::WARNING, 'jerror');

			return false;
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Installer Trigger Loading
		 * ---------------------------------------------------------------------------------------------
		 */
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

			// Set the class name
			$classname = $element . 'InstallerScript';

			if (class_exists($classname))
			{
				// Create a new instance
				$this->parent->manifestClass = new $classname($this);

				// And set this so we can copy it later
				$this->set('manifest_script', $manifestScript);
			}
		}

		// Run preflight if possible (since we know we're not an update)
		ob_start();
		ob_implicit_flush(false);

		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass, 'preflight'))
		{
			if ($this->parent->manifestClass->preflight('discover_install', $this) === false)
			{
				// Install failed, rollback changes
				$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_COMP_INSTALL_CUSTOM_INSTALL_FAILURE'));

				return false;
			}
		}

		// Create msg object; first use here
		$msg = ob_get_contents();
		ob_end_clean();

		/*
		 *
		 * Normally we would copy files and create directories, lets skip to the optional files
		 * Note: need to dereference things!
		 * Parse optional tags
		 * @todo remove code: $this->parent->parseMedia($this->manifest->media);
		 *
		 * We don't do language because 1.6 suggests moving to extension based languages
		 * @todo remove code: $this->parent->parseLanguages($this->manifest->languages);
		 * @todo remove code: $this->parent->parseLanguages($this->manifest->administration->languages, 1);
		 */

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Database Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Let's run the install queries for the component
		if (isset($this->manifest->install->sql))
		{
			$utfresult = $this->parent->parseSQLFiles($this->manifest->install->sql);

			if ($utfresult === false)
			{
				// Install failed, rollback changes
				$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_COMP_INSTALL_SQL_ERROR', $db->stderr(true)));

				return false;
			}
		}

		// Time to build the admin menus
		if (!$this->_buildAdminMenus($this->parent->extension->extension_id))
		{
			JLog::add(JText::_('JLIB_INSTALLER_ABORT_COMP_BUILDADMINMENUS_FAILED'), JLog::WARNING, 'jerror');

			// @todo remove code: $this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_COMP_INSTALL_ROLLBACK', $db->stderr(true)));

			// @todo remove code: return false;
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Custom Installation Script Section
		 * ---------------------------------------------------------------------------------------------
		 */

		/*
		 * If we have an install script, lets include it, execute the custom
		 * discover_install method, and append the return value from the custom discover_install
		 * method to the installation message.
		 */
		ob_start();
		ob_implicit_flush(false);

		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass, 'install'))
		{
			if ($this->parent->manifestClass->install($this) === false)
			{
				// Install failed, rollback changes
				$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_COMP_INSTALL_CUSTOM_INSTALL_FAILURE'));

				return false;
			}
		}

		// Append messages
		$msg .= ob_get_contents();
		ob_end_clean();

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Finalization and Cleanup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Clobber any possible pending updates
		$update = JTable::getInstance('update');
		$uid = $update->find(array('element' => $this->get('element'), 'type' => 'component', 'client_id' => 1, 'folder' => ''));

		if ($uid)
		{
			$update->delete($uid);
		}

		// And now we run the postflight
		ob_start();
		ob_implicit_flush(false);

		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass, 'postflight'))
		{
			$this->parent->manifestClass->postflight('discover_install', $this);
		}

		// Append messages
		$msg .= ob_get_contents();
		ob_end_clean();

		if ($msg != '')
		{
			$this->parent->set('extension_message', $msg);
		}

		return $this->parent->extension->extension_id;
	}

	/**
	 * Refreshes the extension table cache
	 *
	 * @return  boolean  Result of operation, true if updated, false on failure
	 *
	 * @since   3.1
	 */
	public function refreshManifestCache()
	{
		// Need to find to find where the XML file is since we don't store this normally
		$client = JApplicationHelper::getClientInfo($this->parent->extension->client_id);
		$short_element = str_replace('com_', '', $this->parent->extension->element);
		$manifestPath = $client->path . '/components/' . $this->parent->extension->element . '/' . $short_element . '.xml';
		$this->parent->manifest = $this->parent->isManifest($manifestPath);
		$this->parent->setPath('manifest', $manifestPath);

		$manifest_details = JInstaller::parseXMLInstallFile($this->parent->getPath('manifest'));
		$this->parent->extension->manifest_cache = json_encode($manifest_details);
		$this->parent->extension->name = $manifest_details['name'];

		try
		{
			return $this->parent->extension->store();
		}
		catch (RuntimeException $e)
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_COMP_REFRESH_MANIFEST_CACHE'), JLog::WARNING, 'jerror');

			return false;
		}
	}
}

/**
 * Deprecated class placeholder. You should use JInstallerAdapterComponent instead.
 *
 * @package     Joomla.Libraries
 * @subpackage  Installer
 * @since       3.1
 * @deprecated  4.0
 * @codeCoverageIgnore
 */
class JInstallerComponent extends JInstallerAdapterComponent
{
}
