<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Utilities\ArrayHelper;

jimport('joomla.filesystem.folder');

/**
 * Module installer
 *
 * @since  3.1
 */
class JInstallerAdapterModule extends JInstallerAdapter
{
	/**
	 * The install client ID
	 *
	 * @var    integer
	 * @since  3.4
	 */
	protected $clientId;

	/**
	 * `<scriptfile>` element of the extension manifest
	 *
	 * @var    object
	 * @since  3.1
	 */
	protected $scriptElement = null;

	/**
	 * Method to check if the extension is already present in the database
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	protected function checkExistingExtension()
	{
		try
		{
			$this->currentExtensionId = $this->extension->find(
				array(
					'element'   => $this->element,
					'type'      => $this->type,
					'client_id' => $this->clientId,
				)
			);
		}
		catch (RuntimeException $e)
		{
			// Install failed, roll back changes
			throw new RuntimeException(
				JText::sprintf(
					'JLIB_INSTALLER_ABORT_ROLLBACK',
					JText::_('JLIB_INSTALLER_' . $this->route),
					$e->getMessage()
				),
				$e->getCode(),
				$e
			);
		}
	}

	/**
	 * Method to copy the extension's base files from the `<files>` tag(s) and the manifest file
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	protected function copyBaseFiles()
	{
		// Copy all necessary files
		if ($this->parent->parseFiles($this->getManifest()->files, -1) === false)
		{
			throw new RuntimeException(JText::_('JLIB_INSTALLER_ABORT_MOD_COPY_FILES'));
		}

		// If there is a manifest script, let's copy it.
		if ($this->manifest_script)
		{
			$path['src']  = $this->parent->getPath('source') . '/' . $this->manifest_script;
			$path['dest'] = $this->parent->getPath('extension_root') . '/' . $this->manifest_script;

			if (!file_exists($path['dest']) || $this->parent->isOverwrite())
			{
				if (!$this->parent->copyFiles(array($path)))
				{
					// Install failed, rollback changes
					throw new RuntimeException(JText::_('JLIB_INSTALLER_ABORT_MOD_INSTALL_MANIFEST'));
				}
			}
		}
	}

	/**
	 * Method to finalise the installation processing
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	protected function finaliseInstall()
	{
		// Clobber any possible pending updates
		$update = JTable::getInstance('update');
		$uid    = $update->find(
			array(
				'element'   => $this->element,
				'type'      => 'module',
				'client_id' => $this->clientId,
			)
		);

		if ($uid)
		{
			$update->delete($uid);
		}

		// Lastly, we will copy the manifest file to its appropriate place.
		if ($this->route != 'discover_install')
		{
			if (!$this->parent->copyManifest(-1))
			{
				// Install failed, rollback changes
				throw new RuntimeException(JText::_('JLIB_INSTALLER_ABORT_MOD_INSTALL_COPY_SETUP'));
			}
		}
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
			if (count($this->getManifest()->files->children()))
			{
				foreach ($this->getManifest()->files->children() as $file)
				{
					if ((string) $file->attributes()->module)
					{
						$element = strtolower((string) $file->attributes()->module);

						break;
					}
				}
			}
		}

		return $element;
	}

	/**
	 * Custom loadLanguage method
	 *
	 * @param   string  $path  The path where we find language files
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function loadLanguage($path = null)
	{
		$source = $this->parent->getPath('source');
		$client = $this->parent->extension->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE;

		if (!$source)
		{
			$this->parent->setPath('source', $client . '/modules/' . $this->parent->extension->element);
		}

		$this->setManifest($this->parent->getManifest());

		if ($this->getManifest()->files)
		{
			$extension = $this->getElement();

			if ($extension)
			{
				$source = $path ?: ($this->parent->extension->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE) . '/modules/' . $extension;
				$folder = (string) $this->getManifest()->files->attributes()->folder;

				if ($folder && file_exists($path . '/' . $folder))
				{
					$source = $path . '/' . $folder;
				}

				$client = (string) $this->getManifest()->attributes()->client;
				$this->doLoadLanguage($extension, $source, constant('JPATH_' . strtoupper($client)));
			}
		}
	}

	/**
	 * Method to parse optional tags in the manifest
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	protected function parseOptionalTags()
	{
		// Parse optional tags
		$this->parent->parseMedia($this->getManifest()->media, $this->clientId);
		$this->parent->parseLanguages($this->getManifest()->languages, $this->clientId);
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
		$client = JApplicationHelper::getClientInfo($this->parent->extension->client_id);
		$manifestPath = $client->path . '/modules/' . $this->parent->extension->element . '/' . $this->parent->extension->element . '.xml';
		$this->parent->manifest = $this->parent->isManifest($manifestPath);
		$this->parent->setPath('manifest', $manifestPath);
		$this->setManifest($this->parent->getManifest());
	}

	/**
	 * Method to do any prechecks and setup the install paths for the extension
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	protected function setupInstallPaths()
	{
		// Get the target application
		$cname = (string) $this->getManifest()->attributes()->client;

		if ($cname)
		{
			// Attempt to map the client to a base path
			$client = JApplicationHelper::getClientInfo($cname, true);

			if ($client === false)
			{
				throw new RuntimeException(
					JText::sprintf(
						'JLIB_INSTALLER_ABORT_MOD_UNKNOWN_CLIENT',
						JText::_('JLIB_INSTALLER_' . $this->route),
						$client->name
					)
				);
			}

			$basePath = $client->path;
			$this->clientId = $client->id;
		}
		else
		{
			// No client attribute was found so we assume the site as the client
			$basePath = JPATH_SITE;
			$this->clientId = 0;
		}

		// Set the installation path
		if (empty($this->element))
		{
			throw new RuntimeException(
				JText::sprintf(
					'JLIB_INSTALLER_ABORT_MOD_INSTALL_NOFILE',
					JText::_('JLIB_INSTALLER_' . $this->route)
				)
			);
		}

		$this->parent->setPath('extension_root', $basePath . '/modules/' . $this->element);
	}

	/**
	 * Method to store the extension to the database
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	protected function storeExtension()
	{
		// Discover installs are stored a little differently
		if ($this->route == 'discover_install')
		{
			$manifest_details = JInstaller::parseXMLInstallFile($this->parent->getPath('manifest'));

			$this->extension->manifest_cache = json_encode($manifest_details);
			$this->extension->state = 0;
			$this->extension->name = $manifest_details['name'];
			$this->extension->enabled = 1;
			$this->extension->params = $this->parent->getParams();

			if (!$this->extension->store())
			{
				// Install failed, roll back changes
				throw new RuntimeException(JText::_('JLIB_INSTALLER_ERROR_MOD_DISCOVER_STORE_DETAILS'));
			}

			return;
		}

		// Was there a module already installed with the same name?
		if ($this->currentExtensionId)
		{
			if (!$this->parent->isOverwrite())
			{
				// Install failed, roll back changes
				throw new RuntimeException(
					JText::sprintf(
						'JLIB_INSTALLER_ABORT_MOD_INSTALL_ALLREADY_EXISTS',
						JText::_('JLIB_INSTALLER_' . $this->route),
						$this->name
					)
				);
			}

			// Load the entry and update the manifest_cache
			$this->extension->load($this->currentExtensionId);

			// Update name
			$this->extension->name = $this->name;

			// Update manifest
			$this->extension->manifest_cache = $this->parent->generateManifestCache();

			if (!$this->extension->store())
			{
				// Install failed, roll back changes
				throw new RuntimeException(
					JText::sprintf(
						'JLIB_INSTALLER_ABORT_MOD_ROLLBACK',
						JText::_('JLIB_INSTALLER_' . $this->route),
						$this->extension->getError()
					)
				);
			}
		}
		else
		{
			$this->extension->name    = $this->name;
			$this->extension->type    = 'module';
			$this->extension->element = $this->element;

			// There is no folder for modules
			$this->extension->folder    = '';
			$this->extension->enabled   = 1;
			$this->extension->protected = 0;
			$this->extension->access    = $this->clientId == 1 ? 2 : 0;
			$this->extension->client_id = $this->clientId;
			$this->extension->params    = $this->parent->getParams();

			// Custom data
			$this->extension->custom_data    = '';
			$this->extension->system_data    = '';
			$this->extension->manifest_cache = $this->parent->generateManifestCache();

			if (!$this->extension->store())
			{
				// Install failed, roll back changes
				throw new RuntimeException(
					JText::sprintf(
						'JLIB_INSTALLER_ABORT_MOD_ROLLBACK',
						JText::_('JLIB_INSTALLER_' . $this->route),
						$this->extension->getError()
					)
				);
			}

			// Since we have created a module item, we add it to the installation step stack
			// so that if we have to rollback the changes we can undo it.
			$this->parent->pushStep(
				array(
					'type' => 'extension',
					'extension_id' => $this->extension->extension_id,
				)
			);

			// Create unpublished module
			$name = preg_replace('#[\*?]#', '', JText::_($this->name));

			/** @var JTableModule $module */
			$module            = JTable::getInstance('module');
			$module->title     = $name;
			$module->content   = '';
			$module->module    = $this->element;
			$module->access    = '1';
			$module->showtitle = '1';
			$module->params    = '';
			$module->client_id = $this->clientId;
			$module->language  = '*';

			$module->store();
		}
	}

	/**
	 * Custom discover method
	 *
	 * @return  array  JExtension list of extensions available
	 *
	 * @since   3.1
	 */
	public function discover()
	{
		$results = array();
		$site_list = JFolder::folders(JPATH_SITE . '/modules');
		$admin_list = JFolder::folders(JPATH_ADMINISTRATOR . '/modules');
		$site_info = JApplicationHelper::getClientInfo('site', true);
		$admin_info = JApplicationHelper::getClientInfo('administrator', true);

		foreach ($site_list as $module)
		{
			if (file_exists(JPATH_SITE . "/modules/$module/$module.xml"))
			{
				$manifest_details = JInstaller::parseXMLInstallFile(JPATH_SITE . "/modules/$module/$module.xml");
				$extension = JTable::getInstance('extension');
				$extension->set('type', 'module');
				$extension->set('client_id', $site_info->id);
				$extension->set('element', $module);
				$extension->set('folder', '');
				$extension->set('name', $module);
				$extension->set('state', -1);
				$extension->set('manifest_cache', json_encode($manifest_details));
				$extension->set('params', '{}');
				$results[] = clone $extension;
			}
		}

		foreach ($admin_list as $module)
		{
			if (file_exists(JPATH_ADMINISTRATOR . "/modules/$module/$module.xml"))
			{
				$manifest_details = JInstaller::parseXMLInstallFile(JPATH_ADMINISTRATOR . "/modules/$module/$module.xml");
				$extension = JTable::getInstance('extension');
				$extension->set('type', 'module');
				$extension->set('client_id', $admin_info->id);
				$extension->set('element', $module);
				$extension->set('folder', '');
				$extension->set('name', $module);
				$extension->set('state', -1);
				$extension->set('manifest_cache', json_encode($manifest_details));
				$extension->set('params', '{}');
				$results[] = clone $extension;
			}
		}

		return $results;
	}

	/**
	 * Refreshes the extension table cache
	 *
	 * @return  boolean  Result of operation, true if updated, false on failure.
	 *
	 * @since   3.1
	 */
	public function refreshManifestCache()
	{
		$client = JApplicationHelper::getClientInfo($this->parent->extension->client_id);
		$manifestPath = $client->path . '/modules/' . $this->parent->extension->element . '/' . $this->parent->extension->element . '.xml';
		$this->parent->manifest = $this->parent->isManifest($manifestPath);
		$this->parent->setPath('manifest', $manifestPath);
		$manifest_details = JInstaller::parseXMLInstallFile($this->parent->getPath('manifest'));
		$this->parent->extension->manifest_cache = json_encode($manifest_details);
		$this->parent->extension->name = $manifest_details['name'];

		if ($this->parent->extension->store())
		{
			return true;
		}
		else
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_MOD_REFRESH_MANIFEST_CACHE'), JLog::WARNING, 'jerror');

			return false;
		}
	}

	/**
	 * Custom uninstall method
	 *
	 * @param   integer  $id  The id of the module to uninstall
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.1
	 */
	public function uninstall($id)
	{
		$retval = true;
		$db     = $this->db;

		// First order of business will be to load the module object table from the database.
		// This should give us the necessary information to proceed.
		if (!$this->extension->load((int) $id) || !strlen($this->extension->element))
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_MOD_UNINSTALL_ERRORUNKOWNEXTENSION'), JLog::WARNING, 'jerror');

			return false;
		}

		// Is the module we are trying to uninstall a core one?
		// Because that is not a good idea...
		if ($this->extension->protected)
		{
			JLog::add(JText::sprintf('JLIB_INSTALLER_ERROR_MOD_UNINSTALL_WARNCOREMODULE', $this->extension->name), JLog::WARNING, 'jerror');

			return false;
		}

		/*
		 * Does this extension have a parent package?
		 * If so, check if the package disallows individual extensions being uninstalled if the package is not being uninstalled
		 */
		if ($this->extension->package_id && !$this->parent->isPackageUninstall() && !$this->canUninstallPackageChild($this->extension->package_id))
		{
			JLog::add(JText::sprintf('JLIB_INSTALLER_ERROR_CANNOT_UNINSTALL_CHILD_OF_PACKAGE', $this->extension->name), JLog::WARNING, 'jerror');

			return false;
		}

		// Get the extension root path
		$element = $this->extension->element;
		$client  = JApplicationHelper::getClientInfo($this->extension->client_id);

		if ($client === false)
		{
			$this->parent->abort(
				JText::sprintf(
					'JLIB_INSTALLER_ERROR_MOD_UNINSTALL_UNKNOWN_CLIENT',
					$this->extension->client_id
				)
			);

			return false;
		}

		$this->parent->setPath('extension_root', $client->path . '/modules/' . $element);

		$this->parent->setPath('source', $this->parent->getPath('extension_root'));

		// Get the module's manifest objecct
		// We do findManifest to avoid problem when uninstalling a list of extensions: getManifest cache its manifest file.
		$this->parent->findManifest();
		$this->setManifest($this->parent->getManifest());

		// Attempt to load the language file; might have uninstall strings
		$this->loadLanguage(($this->extension->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE) . '/modules/' . $element);

		// If there is an manifest class file, let's load it
		$this->scriptElement = $this->getManifest()->scriptfile;
		$manifestScript      = (string) $this->getManifest()->scriptfile;

		if ($manifestScript)
		{
			$manifestScriptFile = $this->parent->getPath('extension_root') . '/' . $manifestScript;

			// Set the class name
			$classname = $element . 'InstallerScript';

			JLoader::register($classname, $manifestScriptFile);

			if (class_exists($classname))
			{
				// Create a new instance
				$this->parent->manifestClass = new $classname($this);

				// And set this so we can copy it later
				$this->set('manifest_script', $manifestScript);
			}
		}

		try
		{
			$this->triggerManifestScript('uninstall');
		}
		catch (RuntimeException $e)
		{
			// Ignore errors for now
		}

		if (!($this->getManifest() instanceof SimpleXMLElement))
		{
			// Make sure we delete the folders
			JFolder::delete($this->parent->getPath('extension_root'));
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_MOD_UNINSTALL_INVALID_NOTFOUND_MANIFEST'), JLog::WARNING, 'jerror');

			return false;
		}

		// Let's run the uninstall queries for the module
		try
		{
			$this->parseQueries();
		}
		catch (RuntimeException $e)
		{
			// Install failed, rollback changes
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
			$retval = false;
		}

		// Remove the schema version
		$query = $db->getQuery(true)
			->delete('#__schemas')
			->where('extension_id = ' . $this->extension->extension_id);
		$db->setQuery($query);
		$db->execute();

		// Remove other files
		$this->parent->removeFiles($this->getManifest()->media);
		$this->parent->removeFiles($this->getManifest()->languages, $this->extension->client_id);

		// Let's delete all the module copies for the type we are uninstalling
		$query->clear()
			->select($db->quoteName('id'))
			->from($db->quoteName('#__modules'))
			->where($db->quoteName('module') . ' = ' . $db->quote($this->extension->element))
			->where($db->quoteName('client_id') . ' = ' . (int) $this->extension->client_id);
		$db->setQuery($query);

		try
		{
			$modules = $db->loadColumn();
		}
		catch (RuntimeException $e)
		{
			$modules = array();
		}

		// Do we have any module copies?
		if (count($modules))
		{
			// Ensure the list is sane
			$modules = ArrayHelper::toInteger($modules);
			$modID = implode(',', $modules);

			// Wipe out any items assigned to menus
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__modules_menu'))
				->where($db->quoteName('moduleid') . ' IN (' . $modID . ')');
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				JLog::add(JText::sprintf('JLIB_INSTALLER_ERROR_MOD_UNINSTALL_EXCEPTION', $db->stderr(true)), JLog::WARNING, 'jerror');
				$retval = false;
			}

			// Wipe out any instances in the modules table
			/** @var JTableModule $module */
			$module = JTable::getInstance('Module');

			foreach ($modules as $modInstanceId)
			{
				$module->load($modInstanceId);

				if (!$module->delete())
				{
					JLog::add(JText::sprintf('JLIB_INSTALLER_ERROR_MOD_UNINSTALL_EXCEPTION', $module->getError()), JLog::WARNING, 'jerror');
					$retval = false;
				}
			}
		}

		// Now we will no longer need the module object, so let's delete it and free up memory
		$this->extension->delete($this->extension->extension_id);
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__modules'))
			->where($db->quoteName('module') . ' = ' . $db->quote($this->extension->element))
			->where($db->quote('client_id') . ' = ' . $this->extension->client_id);
		$db->setQuery($query);

		try
		{
			// Clean up any other ones that might exist as well
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			// Ignore the error...
		}

		// Remove the installation folder
		if (!JFolder::delete($this->parent->getPath('extension_root')))
		{
			// JFolder should raise an error
			$retval = false;
		}

		return $retval;
	}

	/**
	 * Custom rollback method
	 * - Roll back the menu item
	 *
	 * @param   array  $arg  Installation step to rollback
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.1
	 */
	protected function _rollback_menu($arg)
	{
		// Get database connector object
		$db = $this->parent->getDbo();

		// Remove the entry from the #__modules_menu table
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__modules_menu'))
			->where($db->quoteName('moduleid') . ' = ' . (int) $arg['id']);
		$db->setQuery($query);

		try
		{
			return $db->execute();
		}
		catch (RuntimeException $e)
		{
			return false;
		}
	}

	/**
	 * Custom rollback method
	 * - Roll back the module item
	 *
	 * @param   array  $arg  Installation step to rollback
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.1
	 */
	protected function _rollback_module($arg)
	{
		// Get database connector object
		$db = $this->parent->getDbo();

		// Remove the entry from the #__modules table
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__modules'))
			->where($db->quoteName('id') . ' = ' . (int) $arg['id']);
		$db->setQuery($query);

		try
		{
			return $db->execute();
		}
		catch (RuntimeException $e)
		{
			return false;
		}
	}
}
