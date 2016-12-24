<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.folder');

/**
 * Template installer
 *
 * @since  3.1
 */
class JInstallerAdapterTemplate extends JInstallerAdapter
{
	/**
	 * The install client ID
	 *
	 * @var    integer
	 * @since  3.4
	 */
	protected $clientId;

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
		// Copy all the necessary files
		if ($this->parent->parseFiles($this->getManifest()->files, -1) === false)
		{
			throw new RuntimeException(
				JText::sprintf(
					'JLIB_INSTALLER_ABORT_TPL_INSTALL_COPY_FILES',
					'files'
				)
			);
		}

		if ($this->parent->parseFiles($this->getManifest()->images, -1) === false)
		{
			throw new RuntimeException(
				JText::sprintf(
					'JLIB_INSTALLER_ABORT_TPL_INSTALL_COPY_FILES',
					'images'
				)
			);
		}

		if ($this->parent->parseFiles($this->getManifest()->css, -1) === false)
		{
			throw new RuntimeException(
				JText::sprintf(
					'JLIB_INSTALLER_ABORT_TPL_INSTALL_COPY_FILES',
					'css'
				)
			);
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
					throw new RuntimeException(
						JText::sprintf(
							'JLIB_INSTALLER_ABORT_MANIFEST',
							JText::_('JLIB_INSTALLER_' . strtoupper($this->getRoute()))
						)
					);
				}
			}
		}
	}

	/**
	 * Method to finalise the installation processing
	 *
	 * @return  void
	 *
	 * @since   3.1
	 * @throws  RuntimeException
	 */
	protected function finaliseInstall()
	{
		// Clobber any possible pending updates
		/** @var JTableUpdate $update */
		$update = JTable::getInstance('update');

		$uid = $update->find(
			array(
				'element'   => $this->element,
				'type'      => $this->type,
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
				throw new RuntimeException(JText::_('JLIB_INSTALLER_ABORT_TPL_INSTALL_COPY_SETUP'));
			}
		}
	}

	/**
	 * Custom loadLanguage method
	 *
	 * @param   string  $path  The path where to find language files.
	 *
	 * @return  JInstallerTemplate
	 *
	 * @since   3.1
	 */
	public function loadLanguage($path = null)
	{
		$source   = $this->parent->getPath('source');
		$basePath = $this->parent->extension->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE;

		if (!$source)
		{
			$this->parent->setPath('source', $basePath . '/templates/' . $this->parent->extension->element);
		}

		$this->setManifest($this->parent->getManifest());

		$client = (string) $this->getManifest()->attributes()->client;

		// Load administrator language if not set.
		if (!$client)
		{
			$client = 'ADMINISTRATOR';
		}

		$base = constant('JPATH_' . strtoupper($client));
		$extension = 'tpl_' . $this->getName();
		$source    = $path ? $path : $base . '/templates/' . $this->getName();

		$this->doLoadLanguage($extension, $source, $base);
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
		$this->parent->parseMedia($this->getManifest()->media);
		$this->parent->parseLanguages($this->getManifest()->languages, $this->clientId);
	}

	/**
	 * Overloaded method to parse queries for template installations
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	protected function parseQueries()
	{
		if (in_array($this->route, array('install', 'discover_install')))
		{
			$db    = $this->db;
			$lang  = JFactory::getLanguage();
			$debug = $lang->setDebug(false);

			$columns = array(
				$db->quoteName('template'),
				$db->quoteName('client_id'),
				$db->quoteName('home'),
				$db->quoteName('title'),
				$db->quoteName('params'),
			);

			$values = array(
				$db->quote($this->extension->element), $this->extension->client_id, $db->quote(0),
				$db->quote(JText::sprintf('JLIB_INSTALLER_DEFAULT_STYLE', JText::_($this->extension->name))),
				$db->quote($this->extension->params),
			);

			$lang->setDebug($debug);

			// Insert record in #__template_styles
			$query = $db->getQuery(true);
			$query->insert($db->quoteName('#__template_styles'))
				->columns($columns)
				->values(implode(',', $values));

			// There is a chance this could fail but we don't care...
			$db->setQuery($query)->execute();
		}
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
		$client = JApplicationHelper::getClientInfo($this->extension->client_id);
		$manifestPath = $client->path . '/templates/' . $this->extension->element . '/templateDetails.xml';
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
		// Get the client application target
		$cname = (string) $this->getManifest()->attributes()->client;

		if ($cname)
		{
			// Attempt to map the client to a base path
			$client = JApplicationHelper::getClientInfo($cname, true);

			if ($client === false)
			{
				throw new RuntimeException(JText::sprintf('JLIB_INSTALLER_ABORT_TPL_INSTALL_UNKNOWN_CLIENT', $cname));
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

		// Set the template root path
		if (empty($this->element))
		{
			throw new RuntimeException(
				JText::sprintf(
					'JLIB_INSTALLER_ABORT_MOD_INSTALL_NOFILE',
					JText::_('JLIB_INSTALLER_' . strtoupper($this->route))
				)
			);
		}

		$this->parent->setPath('extension_root', $basePath . '/templates/' . $this->element);
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
				throw new RuntimeException(JText::_('JLIB_INSTALLER_ERROR_TPL_DISCOVER_STORE_DETAILS'));
			}

			return;
		}

		// If extension already exists, load the entry.
		if ($this->currentExtensionId)
		{
			// If we are not allowed to overwrite on update.
			if (!$this->parent->isOverwrite())
			{
				throw new RuntimeException(
					JText::sprintf('JLIB_INSTALLER_ABORT_STORE_EXTENSION_ALREADY_EXISTS',
						JText::_('JLIB_INSTALLER_EXTENSION_TYPE_' . strtoupper($this->type)),
						JText::_('JLIB_INSTALLER_' . $this->route),
						$this->name
						)
				);
			}

			// Load the entry and update the manifest_cache
			$this->extension->load($this->currentExtensionId);
		}
		// If extension doesn't exist, add an entry to the extension table with defaults.
		else
		{
			$this->extension->type        = $this->type;
			$this->extension->element     = $this->element;
			$this->extension->folder      = '';
			$this->extension->enabled     = 1;
			$this->extension->protected   = 0;
			$this->extension->access      = 1;
			$this->extension->ordering    = 0;
			$this->extension->client_id   = $this->clientId;
			$this->extension->params      = $this->parent->getParams();
			$this->extension->system_data = '';
			$this->extension->custom_data = '';
		}

		// On install or update refresh name and manifest cache.
		$this->extension->name           = $this->name;
		$this->extension->manifest_cache = $this->parent->generateManifestCache();

		// If store extension failed, abort and throw and extension.
		if (!$this->extension->store())
		{
			throw new RuntimeException(
				JText::sprintf('JLIB_INSTALLER_ABORT_STORE_EXTENSION_FAILED',
					JText::_('JLIB_INSTALLER_EXTENSION_TYPE_' . strtoupper($this->type)),
					JText::_('JLIB_INSTALLER_' . $this->route),
					$this->name,
					$this->extension->getError()
				)
			);
		}

		// Add a installer rollback step to the installation step stack so we can rollback the changes if we need.
		$this->addStepToInstaller(array('type' => 'extension', 'id' => $this->extension->extension_id));
	}

	/**
	 * Custom uninstall method
	 *
	 * @param   integer  $id  The extension ID
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.1
	 */
	public function uninstall($id)
	{
		// First order of business will be to load the template object table from the database.
		// This should give us the necessary information to proceed.
		$row = JTable::getInstance('extension');

		if (!$row->load((int) $id) || !strlen($row->element))
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_ERRORUNKOWNEXTENSION'), JLog::WARNING, 'jerror');

			return false;
		}

		// Is the template we are trying to uninstall a core one?
		// Because that is not a good idea...
		if ($row->protected)
		{
			JLog::add(JText::sprintf('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_WARNCORETEMPLATE', $row->name), JLog::WARNING, 'jerror');

			return false;
		}

		/*
		 * Does this extension have a parent package?
		 * If so, check if the package disallows individual extensions being uninstalled if the package is not being uninstalled
		 */
		if ($row->package_id && !$this->parent->isPackageUninstall() && !$this->canUninstallPackageChild($row->package_id))
		{
			JLog::add(JText::sprintf('JLIB_INSTALLER_ERROR_CANNOT_UNINSTALL_CHILD_OF_PACKAGE', $row->name), JLog::WARNING, 'jerror');

			return false;
		}

		$name = $row->element;
		$clientId = $row->client_id;

		// For a template the id will be the template name which represents the subfolder of the templates folder that the template resides in.
		if (!$name)
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_TEMPLATE_ID_EMPTY'), JLog::WARNING, 'jerror');

			return false;
		}

		// Deny remove default template
		$db = $this->parent->getDbo();
		$query = "SELECT COUNT(*) FROM #__template_styles WHERE home = '1' AND template = " . $db->quote($name);
		$db->setQuery($query);

		if ($db->loadResult() != 0)
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_TEMPLATE_DEFAULT'), JLog::WARNING, 'jerror');

			return false;
		}

		// Get the template root path
		$client = JApplicationHelper::getClientInfo($clientId);

		if (!$client)
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_INVALID_CLIENT'), JLog::WARNING, 'jerror');

			return false;
		}

		$this->parent->setPath('extension_root', $client->path . '/templates/' . strtolower($name));
		$this->parent->setPath('source', $this->parent->getPath('extension_root'));

		// We do findManifest to avoid problem when uninstalling a list of extensions: getManifest cache its manifest file
		$this->parent->findManifest();
		$manifest = $this->parent->getManifest();

		if (!($manifest instanceof SimpleXMLElement))
		{
			// Kill the extension entry
			$row->delete($row->extension_id);
			unset($row);

			// Make sure we delete the folders
			JFolder::delete($this->parent->getPath('extension_root'));
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_INVALID_NOTFOUND_MANIFEST'), JLog::WARNING, 'jerror');

			return false;
		}

		// Remove files
		$this->parent->removeFiles($manifest->media);
		$this->parent->removeFiles($manifest->languages, $clientId);

		// Delete the template directory
		if (JFolder::exists($this->parent->getPath('extension_root')))
		{
			$retval = JFolder::delete($this->parent->getPath('extension_root'));
		}
		else
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_TEMPLATE_DIRECTORY'), JLog::WARNING, 'jerror');
			$retval = false;
		}

		// Set menu that assigned to the template back to default template
		$query = 'UPDATE #__menu'
			. ' SET template_style_id = 0'
			. ' WHERE template_style_id in ('
			. '	SELECT s.id FROM #__template_styles s'
			. ' WHERE s.template = ' . $db->quote(strtolower($name)) . ' AND s.client_id = ' . $clientId . ')';

		$db->setQuery($query);
		$db->execute();

		$query = $db->getQuery(true)
			->delete($db->quoteName('#__template_styles'))
			->where($db->quoteName('template') . ' = ' . $db->quote($name))
			->where($db->quoteName('client_id') . ' = ' . $clientId);
		$db->setQuery($query);
		$db->execute();

		$row->delete($row->extension_id);
		unset($row);

		return $retval;
	}

	/**
	 * Discover existing but uninstalled templates
	 *
	 * @return  array  JExtensionTable list
	 */
	public function discover()
	{
		$results = array();
		$site_list = JFolder::folders(JPATH_SITE . '/templates');
		$admin_list = JFolder::folders(JPATH_ADMINISTRATOR . '/templates');
		$site_info = JApplicationHelper::getClientInfo('site', true);
		$admin_info = JApplicationHelper::getClientInfo('administrator', true);

		foreach ($site_list as $template)
		{
			if (file_exists(JPATH_SITE . "/templates/$template/templateDetails.xml"))
			{
				if ($template == 'system')
				{
					// Ignore special system template
					continue;
				}

				$manifest_details = JInstaller::parseXMLInstallFile(JPATH_SITE . "/templates/$template/templateDetails.xml");
				$extension = JTable::getInstance('extension');
				$extension->set('type', 'template');
				$extension->set('client_id', $site_info->id);
				$extension->set('element', $template);
				$extension->set('folder', '');
				$extension->set('name', $template);
				$extension->set('state', -1);
				$extension->set('manifest_cache', json_encode($manifest_details));
				$extension->set('params', '{}');
				$results[] = $extension;
			}
		}

		foreach ($admin_list as $template)
		{
			if (file_exists(JPATH_ADMINISTRATOR . "/templates/$template/templateDetails.xml"))
			{
				if ($template == 'system')
				{
					// Ignore special system template
					continue;
				}

				$manifest_details = JInstaller::parseXMLInstallFile(JPATH_ADMINISTRATOR . "/templates/$template/templateDetails.xml");
				$extension = JTable::getInstance('extension');
				$extension->set('type', 'template');
				$extension->set('client_id', $admin_info->id);
				$extension->set('element', $template);
				$extension->set('folder', '');
				$extension->set('name', $template);
				$extension->set('state', -1);
				$extension->set('manifest_cache', json_encode($manifest_details));
				$extension->set('params', '{}');
				$results[] = $extension;
			}
		}

		return $results;
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
		// Need to find to find where the XML file is since we don't store this normally.
		$client = JApplicationHelper::getClientInfo($this->parent->extension->client_id);
		$manifestPath = $client->path . '/templates/' . $this->parent->extension->element . '/templateDetails.xml';
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
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_TPL_REFRESH_MANIFEST_CACHE'), JLog::WARNING, 'jerror');

			return false;
		}
	}
}

/**
 * Deprecated class placeholder. You should use JInstallerAdapterTemplate instead.
 *
 * @since       3.1
 * @deprecated  4.0
 * @codeCoverageIgnore
 */
class JInstallerTemplate extends JInstallerAdapterTemplate
{
}
