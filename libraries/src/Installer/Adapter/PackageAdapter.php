<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installer\Adapter;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Installer\Manifest\PackageManifest;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\Update;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\ParameterType;
use Joomla\Event\Event;

/**
 * Package installer
 *
 * @since  3.1
 */
class PackageAdapter extends InstallerAdapter
{
	/**
	 * An array of extension IDs for each installed extension
	 *
	 * @var    array
	 * @since  3.7.0
	 */
	protected $installedIds = array();

	/**
	 * The results of each installed extensions
	 *
	 * @var    array
	 * @since  3.1
	 */
	protected $results = array();

	/**
	 * Flag if the adapter supports discover installs
	 *
	 * Adapters should override this and set to false if discover install is unsupported
	 *
	 * @var    boolean
	 * @since  3.4
	 */
	protected $supportsDiscoverInstall = false;

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
		// If the package manifest already exists, then we will assume that the package is already installed.
		if (file_exists(JPATH_MANIFESTS . '/packages/' . basename($this->parent->getPath('manifest'))))
		{
			// Look for an update function or update tag
			$updateElement = $this->manifest->update;

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
					Text::sprintf(
						'JLIB_INSTALLER_ABORT_DIRECTORY',
						Text::_('JLIB_INSTALLER_' . $this->route),
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
	protected function copyBaseFiles()
	{
		$folder = (string) $this->getManifest()->files->attributes()->folder;
		$source = $this->parent->getPath('source');

		if ($folder)
		{
			$source .= '/' . $folder;
		}

		// Install all necessary files
		if (!\count($this->getManifest()->files->children()))
		{
			throw new \RuntimeException(
				Text::sprintf('JLIB_INSTALLER_ABORT_PACK_INSTALL_NO_FILES',
					Text::_('JLIB_INSTALLER_' . strtoupper($this->route))
				)
			);
		}

		$dispatcher = Factory::getApplication()->getDispatcher();

		// Add a callback for the `onExtensionAfterInstall` event so we can receive the installed extension ID
		if (!$dispatcher->hasListener([$this, 'onExtensionAfterInstall'], 'onExtensionAfterInstall'))
		{
			$dispatcher->addListener('onExtensionAfterInstall', [$this, 'onExtensionAfterInstall']);
		}

		foreach ($this->getManifest()->files->children() as $child)
		{
			$file = $source . '/' . (string) $child;

			if (is_dir($file))
			{
				// If it's actually a directory then fill it up
				$package = array();
				$package['dir'] = $file;
				$package['type'] = InstallerHelper::detectType($file);
			}
			else
			{
				// If it's an archive
				$package = InstallerHelper::unpack($file);
			}

			$tmpInstaller  = new Installer;
			$installResult = $tmpInstaller->install($package['dir']);

			if (!$installResult)
			{
				throw new \RuntimeException(
					Text::sprintf(
						'JLIB_INSTALLER_ABORT_PACK_INSTALL_ERROR_EXTENSION',
						Text::_('JLIB_INSTALLER_' . strtoupper($this->route)),
						basename($file)
					)
				);
			}

			$this->results[] = array(
				'name'   => (string) $tmpInstaller->manifest->name,
				'result' => $installResult,
			);
		}
	}

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
		/*
		 * For packages, we only need the extension root if copying manifest files; this step will be handled
		 * at that point if necessary
		 */
	}

	/**
	 * Method to finalise the installation processing
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @throws  \RuntimeException
	 */
	protected function finaliseInstall()
	{
		// Clobber any possible pending updates
		/** @var Update $update */
		$update = Table::getInstance('update');
		$uid = $update->find(
			array(
				'element' => $this->element,
				'type' => $this->type,
			)
		);

		if ($uid)
		{
			$update->delete($uid);
		}

		// Set the package ID for each of the installed extensions to track the relationship
		if (!empty($this->installedIds))
		{
			$db = $this->db;
			$query = $db->getQuery(true)
				->update($db->quoteName('#__extensions'))
				->set($db->quoteName('package_id') . ' = :id')
				->whereIn($db->quoteName('extension_id'), $this->installedIds)
				->bind(':id', $this->extension->extension_id, ParameterType::INTEGER);

			try
			{
				$db->setQuery($query)->execute();
			}
			catch (ExecutionFailureException $e)
			{
				Log::add(Text::_('JLIB_INSTALLER_ERROR_PACK_SETTING_PACKAGE_ID'), Log::WARNING, 'jerror');
			}
		}

		// Lastly, we will copy the manifest file to its appropriate place.
		$manifest = array();
		$manifest['src'] = $this->parent->getPath('manifest');
		$manifest['dest'] = JPATH_MANIFESTS . '/packages/' . basename($this->parent->getPath('manifest'));

		if (!$this->parent->copyFiles(array($manifest), true))
		{
			// Install failed, rollback changes
			throw new \RuntimeException(
				Text::sprintf(
					'JLIB_INSTALLER_ABORT_COPY_SETUP',
					Text::_('JLIB_INSTALLER_' . strtoupper($this->route))
				)
			);
		}

		// If there is a manifest script, let's copy it.
		if ($this->manifest_script)
		{
			// First, we have to create a folder for the script if one isn't present
			if (!file_exists($this->parent->getPath('extension_root')))
			{
				if (!Folder::create($this->parent->getPath('extension_root')))
				{
					throw new \RuntimeException(
						Text::sprintf(
							'JLIB_INSTALLER_ABORT_CREATE_DIRECTORY',
							Text::_('JLIB_INSTALLER_' . $this->route),
							$this->parent->getPath('extension_root')
						)
					);
				}

				/*
				 * Since we created the extension directory and will want to remove it if
				 * we have to roll back the installation, let's add it to the
				 * installation step stack
				 */

				$this->parent->pushStep(
					array(
						'type' => 'folder',
						'path' => $this->parent->getPath('extension_root'),
					)
				);
			}

			$path['src'] = $this->parent->getPath('source') . '/' . $this->manifest_script;
			$path['dest'] = $this->parent->getPath('extension_root') . '/' . $this->manifest_script;

			if ($this->parent->isOverwrite() || !file_exists($path['dest']))
			{
				if (!$this->parent->copyFiles(array($path)))
				{
					// Install failed, rollback changes
					throw new \RuntimeException(
						Text::sprintf(
							'JLIB_INSTALLER_ABORT_MANIFEST',
							Text::_('JLIB_INSTALLER_' . strtoupper($this->route))
						)
					);
				}
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

		// Remove the schema version
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__schemas'))
			->where($db->quoteName('extension_id') . ' = :extension_id')
			->bind(':extension_id', $this->extension->extension_id, ParameterType::INTEGER);
		$db->setQuery($query);
		$db->execute();

		// Clobber any possible pending updates
		$update = Table::getInstance('update');
		$uid    = $update->find(
			[
				'element' => $this->extension->element,
				'type'    => $this->type,
			]
		);

		if ($uid)
		{
			$update->delete($uid);
		}

		File::delete(JPATH_MANIFESTS . '/packages/' . $this->extension->element . '.xml');

		$folder = $this->parent->getPath('extension_root');

		if (Folder::exists($folder))
		{
			Folder::delete($folder);
		}

		$this->extension->delete();

		return true;
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
			$element = (string) $this->getManifest()->packagename;

			// Filter the name for illegal characters
			$element = 'pkg_' . InputFilter::getInstance()->clean($element, 'cmd');
		}

		return $element;
	}

	/**
	 * Load language from a path
	 *
	 * @param   string  $path  The path of the language.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function loadLanguage($path)
	{
		$this->doLoadLanguage($this->getElement(), $path);
	}

	/**
	 * Handler for the `onExtensionAfterInstall` event
	 *
	 * @param   Event  $event  The event
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	public function onExtensionAfterInstall(Event $event)
	{
		if ($event->getArgument('eid', false) !== false)
		{
			$this->installedIds[] = $event->getArgument('eid');
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
		$this->parent->parseLanguages($this->getManifest()->languages);
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
		$manifest = new PackageManifest(JPATH_MANIFESTS . '/packages/' . $this->extension->element . '.xml');
		$error = false;

		foreach ($manifest->filelist as $extension)
		{
			$tmpInstaller = new Installer;
			$tmpInstaller->setPackageUninstall(true);

			$id = $this->_getExtensionId($extension->type, $extension->id, $extension->client, $extension->group);

			if ($id)
			{
				if (!$tmpInstaller->uninstall($extension->type, $id))
				{
					$error = true;
					Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_NOT_PROPER', basename($extension->filename)), Log::WARNING, 'jerror');
				}
			}
			else
			{
				Log::add(Text::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_UNKNOWN_EXTENSION'), Log::WARNING, 'jerror');
			}
		}

		// Remove any language files
		$this->parent->removeFiles($this->getManifest()->languages);

		// Clean up manifest file after we're done if there were no errors
		if ($error)
		{
			throw new \RuntimeException(Text::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_MANIFEST_NOT_REMOVED'));
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
		$packagepath = (string) $this->getManifest()->packagename;

		if (empty($packagepath))
		{
			throw new \RuntimeException(
				Text::sprintf(
					'JLIB_INSTALLER_ABORT_PACK_INSTALL_NO_PACK',
					Text::_('JLIB_INSTALLER_' . strtoupper($this->route))
				)
			);
		}

		$this->parent->setPath('extension_root', JPATH_MANIFESTS . '/packages/' . $packagepath);
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
		$manifestFile = JPATH_MANIFESTS . '/packages/' . $this->extension->element . '.xml';
		$manifest = new PackageManifest($manifestFile);

		// Set the package root path
		$this->parent->setPath('extension_root', JPATH_MANIFESTS . '/packages/' . $manifest->packagename);

		// Set the source path for compatibility with the API
		$this->parent->setPath('source', $this->parent->getPath('extension_root'));

		// Because packages may not have their own folders we cannot use the standard method of finding an installation manifest
		if (!file_exists($manifestFile))
		{
			throw new \RuntimeException(Text::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_MISSINGMANIFEST'));
		}

		$xml = simplexml_load_file($manifestFile);

		if (!$xml)
		{
			throw new \RuntimeException(Text::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_LOAD_MANIFEST'));
		}

		// Check for a valid XML root tag.
		if ($xml->getName() !== 'extension')
		{
			throw new \RuntimeException(Text::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_INVALID_MANIFEST'));
		}

		$this->setManifest($xml);

		// Attempt to load the language file; might have uninstall strings
		$this->loadLanguage(JPATH_SITE);
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
		if ($this->currentExtensionId)
		{
			if (!$this->parent->isOverwrite())
			{
				// Install failed, roll back changes
				throw new \RuntimeException(
					Text::sprintf(
						'JLIB_INSTALLER_ABORT_ALREADY_EXISTS',
						Text::_('JLIB_INSTALLER_' . $this->route),
						$this->name
					)
				);
			}

			$this->extension->load($this->currentExtensionId);
			$this->extension->name = $this->name;
		}
		else
		{
			$this->extension->name         = $this->name;
			$this->extension->type         = 'package';
			$this->extension->element      = $this->element;
			$this->extension->changelogurl = $this->changelogurl;

			// There is no folder for packages
			$this->extension->folder    = '';
			$this->extension->enabled   = 1;
			$this->extension->protected = 0;
			$this->extension->access    = 1;
			$this->extension->client_id = 0;
			$this->extension->params    = $this->parent->getParams();
		}

		// Update the manifest cache for the entry
		$this->extension->manifest_cache = $this->parent->generateManifestCache();

		if (!$this->extension->store())
		{
			// Install failed, roll back changes
			throw new \RuntimeException(
				Text::sprintf(
					'JLIB_INSTALLER_ABORT_PACK_INSTALL_ROLLBACK',
					$this->extension->getError()
				)
			);
		}

		// Since we have created a package item, we add it to the installation step stack
		// so that if we have to rollback the changes we can undo it.
		$this->parent->pushStep(array('type' => 'extension', 'id' => $this->extension->extension_id));
	}

	/**
	 * Executes a custom install script method
	 *
	 * @param   string  $method  The install method to execute
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.4
	 */
	protected function triggerManifestScript($method)
	{
		ob_start();
		ob_implicit_flush(false);

		if ($this->parent->manifestClass && method_exists($this->parent->manifestClass, $method))
		{
			switch ($method)
			{
				// The preflight method takes the route as a param
				case 'preflight':
					if ($this->parent->manifestClass->$method($this->route, $this) === false)
					{
						// The script failed, rollback changes
						throw new \RuntimeException(
							Text::sprintf(
								'JLIB_INSTALLER_ABORT_INSTALL_CUSTOM_INSTALL_FAILURE',
								Text::_('JLIB_INSTALLER_' . $this->route)
							)
						);
					}

					break;

				// The postflight method takes the route and a results array as params
				case 'postflight':
					$this->parent->manifestClass->$method($this->route, $this, $this->results);

					break;

				// The install, uninstall, and update methods only pass this object as a param
				case 'install':
				case 'uninstall':
				case 'update':
					if ($this->parent->manifestClass->$method($this) === false)
					{
						if ($method !== 'uninstall')
						{
							// The script failed, rollback changes
							throw new \RuntimeException(
								Text::sprintf(
									'JLIB_INSTALLER_ABORT_INSTALL_CUSTOM_INSTALL_FAILURE',
									Text::_('JLIB_INSTALLER_' . $this->route)
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
	 * Gets the extension id.
	 *
	 * @param   string   $type    The extension type.
	 * @param   string   $id      The name of the extension (the element field).
	 * @param   integer  $client  The application id (0: Joomla CMS site; 1: Joomla CMS administrator).
	 * @param   string   $group   The extension group (mainly for plugins).
	 *
	 * @return  integer
	 *
	 * @since   3.1
	 */
	protected function _getExtensionId($type, $id, $client, $group)
	{
		$db = $this->parent->getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'))
			->where(
				[
					$db->quoteName('type') . ' = :type',
					$db->quoteName('element') . ' = :element',
				]
			)
			->bind(':type', $type)
			->bind(':element', $id);

		switch ($type)
		{
			case 'plugin':
				// Plugins have a folder but not a client
				$query->where('folder = :folder')
					->bind(':folder', $group);

				break;

			case 'library':
			case 'package':
			case 'component':
				// Components, packages and libraries don't have a folder or client.
				// Included for completeness.
				break;

			case 'language':
			case 'module':
			case 'template':
				// Languages, modules and templates have a client but not a folder
				$clientId = ApplicationHelper::getClientInfo($client, true)->id;

				$query->where('client_id = :client_id')
					->bind(':client_id', $clientId, ParameterType::INTEGER);

				break;
		}

		$db->setQuery($query);

		// Note: For templates, libraries and packages their unique name is their key.
		// This means they come out the same way they came in.

		return $db->loadResult();
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
		$manifestPath = JPATH_MANIFESTS . '/packages/' . $this->parent->extension->element . '.xml';
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
			Log::add(Text::_('JLIB_INSTALLER_ERROR_PACK_REFRESH_MANIFEST_CACHE'), Log::WARNING, 'jerror');

			return false;
		}
	}
}
