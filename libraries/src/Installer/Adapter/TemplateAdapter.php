<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installer\Adapter;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\Update;

\JLoader::import('joomla.filesystem.folder');

/**
 * Template installer
 *
 * @since  3.1
 */
class TemplateAdapter extends InstallerAdapter
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
	 * @throws  \RuntimeException
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
	 * Method to copy the extension's base files from the `<files>` tag(s) and the manifest file
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @throws  \RuntimeException
	 */
	protected function copyBaseFiles()
	{
		// Copy all the necessary files
		if ($this->parent->parseFiles($this->getManifest()->files, -1) === false)
		{
			throw new \RuntimeException(
				\JText::sprintf(
					'JLIB_INSTALLER_ABORT_TPL_INSTALL_COPY_FILES',
					'files'
				)
			);
		}

		if ($this->parent->parseFiles($this->getManifest()->images, -1) === false)
		{
			throw new \RuntimeException(
				\JText::sprintf(
					'JLIB_INSTALLER_ABORT_TPL_INSTALL_COPY_FILES',
					'images'
				)
			);
		}

		if ($this->parent->parseFiles($this->getManifest()->css, -1) === false)
		{
			throw new \RuntimeException(
				\JText::sprintf(
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

			if ($this->parent->isOverwrite() || !file_exists($path['dest']))
			{
				if (!$this->parent->copyFiles(array($path)))
				{
					throw new \RuntimeException(
						\JText::sprintf(
							'JLIB_INSTALLER_ABORT_MANIFEST',
							\JText::_('JLIB_INSTALLER_' . strtoupper($this->getRoute()))
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
	 * @throws  \RuntimeException
	 */
	protected function finaliseInstall()
	{
		// Clobber any possible pending updates
		/** @var Update $update */
		$update = Table::getInstance('update');

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
		if ($this->route !== 'discover_install')
		{
			if (!$this->parent->copyManifest(-1))
			{
				// Install failed, rollback changes
				throw new \RuntimeException(\JText::_('JLIB_INSTALLER_ABORT_TPL_INSTALL_COPY_SETUP'));
			}
		}
	}

	/**
	 * Method to finalise the uninstallation processing
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 * @throws  \RuntimeException
	 */
	protected function finaliseUninstall(): bool
	{
		$db = $this->parent->getDbo();

		// Set menu that assigned to the template back to default template
		$subQuery = $db->getQuery(true)
			->select('s.id')
			->from($db->quoteName('#__template_styles', 's'))
			->where('s.template = ' . $db->quote(strtolower($this->extension->element)))
			->where('s.client_id = ' . (int) $this->extension->client_id);

		$query = $db->getQuery(true)
			->update('#__menu')
			->set('template_style_id = 0')
			->where('template_style_id IN (' . (string) $subQuery . ')');

		$db->setQuery($query);
		$db->execute();

		// Remove the template's styles
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__template_styles'))
			->where($db->quoteName('template') . ' = ' . $db->quote($this->extension->element))
			->where($db->quoteName('client_id') . ' = ' . (int) $this->extension->client_id);
		$db->setQuery($query);
		$db->execute();

		// Remove the schema version
		$query = $db->getQuery(true)
			->delete('#__schemas')
			->where('extension_id = ' . $this->extension->extension_id);
		$db->setQuery($query);
		$db->execute();

		// Clobber any possible pending updates
		$update = Table::getInstance('update');
		$uid    = $update->find(
			[
				'element'   => $this->extension->element,
				'type'      => $this->type,
				'client_id' => $this->extension->client_id,
			]
		);

		if ($uid)
		{
			$update->delete($uid);
		}

		$this->extension->delete();

		return true;
	}

	/**
	 * Custom loadLanguage method
	 *
	 * @param   string  $path  The path where to find language files.
	 *
	 * @return  void
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
		$source    = $path ?: $base . '/templates/' . $this->getName();

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
	 * @throws  \RuntimeException
	 */
	protected function parseQueries()
	{
		if (in_array($this->route, array('install', 'discover_install')))
		{
			$db    = $this->db;
			$lang  = \JFactory::getLanguage();
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
				$db->quote(\JText::sprintf('JLIB_INSTALLER_DEFAULT_STYLE', \JText::_($this->extension->name))),
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
		$client = ApplicationHelper::getClientInfo($this->extension->client_id);
		$manifestPath = $client->path . '/templates/' . $this->extension->element . '/templateDetails.xml';
		$this->parent->manifest = $this->parent->isManifest($manifestPath);
		$this->parent->setPath('manifest', $manifestPath);
		$this->setManifest($this->parent->getManifest());
	}

	/**
	 * Removes this extension's files
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 * @throws  \RuntimeException
	 */
	protected function removeExtensionFiles()
	{
		// Remove files
		$this->parent->removeFiles($this->getManifest()->media);
		$this->parent->removeFiles($this->getManifest()->languages, $this->extension->client_id);

		// Delete the template directory
		if (\JFolder::exists($this->parent->getPath('extension_root')))
		{
			\JFolder::delete($this->parent->getPath('extension_root'));
		}
		else
		{
			\JLog::add(\JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_TEMPLATE_DIRECTORY'), \JLog::WARNING, 'jerror');
		}
	}

	/**
	 * Method to do any prechecks and setup the install paths for the extension
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @throws  \RuntimeException
	 */
	protected function setupInstallPaths()
	{
		// Get the client application target
		$cname = (string) $this->getManifest()->attributes()->client;

		if ($cname)
		{
			// Attempt to map the client to a base path
			$client = ApplicationHelper::getClientInfo($cname, true);

			if ($client === false)
			{
				throw new \RuntimeException(\JText::sprintf('JLIB_INSTALLER_ABORT_TPL_INSTALL_UNKNOWN_CLIENT', $cname));
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
			throw new \RuntimeException(
				\JText::sprintf(
					'JLIB_INSTALLER_ABORT_MOD_INSTALL_NOFILE',
					\JText::_('JLIB_INSTALLER_' . strtoupper($this->route))
				)
			);
		}

		$this->parent->setPath('extension_root', $basePath . '/templates/' . $this->element);
	}

	/**
	 * Method to do any prechecks and setup the uninstall job
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function setupUninstall()
	{
		$this->parent->extension = $this->extension;

		$name     = $this->extension->element;
		$clientId = $this->extension->client_id;

		// For a template the id will be the template name which represents the subfolder of the templates folder that the template resides in.
		if (!$name)
		{
			throw new \RuntimeException(\JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_TEMPLATE_ID_EMPTY'));
		}

		// Deny remove default template
		$db = $this->parent->getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__template_styles')
			->where('home = 1')
			->where('template = ' . $db->quote($name));
		$db->setQuery($query);

		if ($db->loadResult() != 0)
		{
			throw new \RuntimeException(\JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_TEMPLATE_DEFAULT'));
		}

		// Get the template root path
		$client = ApplicationHelper::getClientInfo($clientId);

		if (!$client)
		{
			throw new \RuntimeException(\JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_INVALID_CLIENT'));
		}

		$this->parent->setPath('extension_root', $client->path . '/templates/' . strtolower($name));
		$this->parent->setPath('source', $this->parent->getPath('extension_root'));

		// We do findManifest to avoid problem when uninstalling a list of extensions: getManifest cache its manifest file
		$this->parent->findManifest();
		$manifest = $this->parent->getManifest();

		if (!($manifest instanceof \SimpleXMLElement))
		{
			// Kill the extension entry
			$this->extension->delete($this->extension->extension_id);

			// Make sure we delete the folders
			\JFolder::delete($this->parent->getPath('extension_root'));

			throw new \RuntimeException(\JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_INVALID_NOTFOUND_MANIFEST'));
		}

		// Attempt to load the language file; might have uninstall strings
		$this->loadLanguage();
	}

	/**
	 * Method to store the extension to the database
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @throws  \RuntimeException
	 */
	protected function storeExtension()
	{
		// Discover installs are stored a little differently
		if ($this->route === 'discover_install')
		{
			$manifest_details = Installer::parseXMLInstallFile($this->parent->getPath('manifest'));

			$this->extension->manifest_cache = json_encode($manifest_details);
			$this->extension->state = 0;
			$this->extension->name = $manifest_details['name'];
			$this->extension->enabled = 1;
			$this->extension->params = $this->parent->getParams();

			if (!$this->extension->store())
			{
				// Install failed, roll back changes
				throw new \RuntimeException(\JText::_('JLIB_INSTALLER_ERROR_TPL_DISCOVER_STORE_DETAILS'));
			}

			return;
		}

		// Was there a template already installed with the same name?
		if ($this->currentExtensionId)
		{
			if (!$this->parent->isOverwrite())
			{
				// Install failed, roll back changes
				throw new \RuntimeException(
					\JText::_('JLIB_INSTALLER_ABORT_TPL_INSTALL_ALREADY_INSTALLED')
				);
			}

			// Load the entry and update the manifest_cache
			$this->extension->load($this->currentExtensionId);
		}
		else
		{
			$this->extension->type    = 'template';
			$this->extension->element = $this->element;

			// There is no folder for templates
			$this->extension->folder    = '';
			$this->extension->enabled   = 1;
			$this->extension->protected = 0;
			$this->extension->access    = 1;
			$this->extension->client_id = $this->clientId;
			$this->extension->params    = $this->parent->getParams();
		}

		// Name might change in an update
		$this->extension->name = $this->name;

		// Update the manifest cache for the entry
		$this->extension->manifest_cache = $this->parent->generateManifestCache();

		if (!$this->extension->store())
		{
			// Install failed, roll back changes
			throw new \RuntimeException(
				\JText::sprintf(
					'JLIB_INSTALLER_ABORT_ROLLBACK',
					\JText::_('JLIB_INSTALLER_' . strtoupper($this->route)),
					$this->extension->getError()
				)
			);
		}
	}

	/**
	 * Discover existing but uninstalled templates
	 *
	 * @return  array  Extension list
	 */
	public function discover()
	{
		$results = array();
		$site_list = \JFolder::folders(JPATH_SITE . '/templates');
		$admin_list = \JFolder::folders(JPATH_ADMINISTRATOR . '/templates');
		$site_info = ApplicationHelper::getClientInfo('site', true);
		$admin_info = ApplicationHelper::getClientInfo('administrator', true);

		foreach ($site_list as $template)
		{
			if (file_exists(JPATH_SITE . "/templates/$template/templateDetails.xml"))
			{
				if ($template === 'system')
				{
					// Ignore special system template
					continue;
				}

				$manifest_details = Installer::parseXMLInstallFile(JPATH_SITE . "/templates/$template/templateDetails.xml");
				$extension = Table::getInstance('extension');
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
				if ($template === 'system')
				{
					// Ignore special system template
					continue;
				}

				$manifest_details = Installer::parseXMLInstallFile(JPATH_ADMINISTRATOR . "/templates/$template/templateDetails.xml");
				$extension = Table::getInstance('extension');
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
		$client = ApplicationHelper::getClientInfo($this->parent->extension->client_id);
		$manifestPath = $client->path . '/templates/' . $this->parent->extension->element . '/templateDetails.xml';
		$this->parent->manifest = $this->parent->isManifest($manifestPath);
		$this->parent->setPath('manifest', $manifestPath);

		$manifest_details = Installer::parseXMLInstallFile($this->parent->getPath('manifest'));
		$this->parent->extension->manifest_cache = json_encode($manifest_details);
		$this->parent->extension->name = $manifest_details['name'];

		try
		{
			return $this->parent->extension->store();
		}
		catch (\RuntimeException $e)
		{
			\JLog::add(\JText::_('JLIB_INSTALLER_ERROR_TPL_REFRESH_MANIFEST_CACHE'), \JLog::WARNING, 'jerror');

			return false;
		}
	}
}
