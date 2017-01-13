<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

jimport('joomla.filesystem.folder');

/**
 * Language installer
 *
 * @since  3.1
 */
class JInstallerAdapterLanguage extends JInstallerAdapter
{
	/**
	 * Core language pack flag
	 *
	 * @var    boolean
	 * @since  12.1
	 */
	protected $core = false;

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
		// TODO - Refactor adapter to use common code
	}

	/**
	 * Method to do any prechecks and setup the install paths for the extension
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	protected function setupInstallPaths()
	{
		// TODO - Refactor adapter to use common code
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
		// TODO - Refactor adapter to use common code
	}

	/**
	 * Custom install method
	 *
	 * Note: This behaves badly due to hacks made in the middle of 1.5.x to add
	 * the ability to install multiple distinct packs in one install. The
	 * preferred method is to use a package to install multiple language packs.
	 *
	 * @return  boolean|integer  The extension ID on success, boolean false on failure
	 *
	 * @since   3.1
	 */
	public function install()
	{
		$source = $this->parent->getPath('source');

		if (!$source)
		{
			$this->parent
				->setPath(
				'source',
				($this->parent->extension->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE) . '/language/' . $this->parent->extension->element
			);
		}

		$this->setManifest($this->parent->getManifest());

		// Get the client application target
		if ($cname = (string) $this->getManifest()->attributes()->client)
		{
			// Attempt to map the client to a base path
			$client = JApplicationHelper::getClientInfo($cname, true);

			if ($client === null)
			{
				$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT', JText::sprintf('JLIB_INSTALLER_ERROR_UNKNOWN_CLIENT_TYPE', $cname)));

				return false;
			}

			$basePath = $client->path;
			$clientId = $client->id;
			$element  = $this->getManifest()->files;

			return $this->_install($cname, $basePath, $clientId, $element);
		}
		else
		{
			// No client attribute was found so we assume the site as the client
			$cname    = 'site';
			$basePath = JPATH_SITE;
			$clientId = 0;
			$element  = $this->getManifest()->files;

			return $this->_install($cname, $basePath, $clientId, $element);
		}
	}

	/**
	 * Install function that is designed to handle individual clients
	 *
	 * @param   string   $cname     Cname @todo: not used
	 * @param   string   $basePath  The base name.
	 * @param   integer  $clientId  The client id.
	 * @param   object   &$element  The XML element.
	 *
	 * @return  boolean|integer  The extension ID on success, boolean false on failure
	 *
	 * @since   3.1
	 */
	protected function _install($cname, $basePath, $clientId, &$element)
	{
		$this->setManifest($this->parent->getManifest());

		// Get the language name
		// Set the extensions name
		$name = JFilterInput::getInstance()->clean((string) $this->getManifest()->name, 'cmd');
		$this->set('name', $name);

		// Get the Language tag [ISO tag, eg. en-GB]
		$tag = (string) $this->getManifest()->tag;

		// Check if we found the tag - if we didn't, we may be trying to install from an older language package
		if (!$tag)
		{
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT', JText::_('JLIB_INSTALLER_ERROR_NO_LANGUAGE_TAG')));

			return false;
		}

		$this->set('tag', $tag);

		// Set the language installation path
		$this->parent->setPath('extension_site', $basePath . '/language/' . $tag);

		// Do we have a meta file in the file list?  In other words... is this a core language pack?
		if ($element && count($element->children()))
		{
			$files = $element->children();

			foreach ($files as $file)
			{
				if ((string) $file->attributes()->file == 'meta')
				{
					$this->core = true;
					break;
				}
			}
		}

		// If the language directory does not exist, let's create it
		$created = false;

		if (!file_exists($this->parent->getPath('extension_site')))
		{
			if (!$created = JFolder::create($this->parent->getPath('extension_site')))
			{
				$this->parent
					->abort(
					JText::sprintf(
						'JLIB_INSTALLER_ABORT',
						JText::sprintf('JLIB_INSTALLER_ERROR_CREATE_FOLDER_FAILED', $this->parent->getPath('extension_site'))
					)
				);

				return false;
			}
		}
		else
		{
			// Look for an update function or update tag
			$updateElement = $this->getManifest()->update;

			// Upgrade manually set or update tag detected
			if ($this->parent->isUpgrade() || $updateElement)
			{
				// Transfer control to the update function
				return $this->update();
			}
			elseif (!$this->parent->isOverwrite())
			{
				// Overwrite is set
				// We didn't have overwrite set, find an update function or find an update tag so lets call it safe
				if (file_exists($this->parent->getPath('extension_site')))
				{
					// If the site exists say so.
					JLog::add(
						JText::sprintf('JLIB_INSTALLER_ABORT', JText::sprintf('JLIB_INSTALLER_ERROR_FOLDER_IN_USE', $this->parent->getPath('extension_site'))),
						JLog::WARNING, 'jerror'
					);
				}
				else
				{
					// If the admin exists say so.
					JLog::add(
						JText::sprintf('JLIB_INSTALLER_ABORT', JText::sprintf('JLIB_INSTALLER_ERROR_FOLDER_IN_USE', $this->parent->getPath('extension_administrator'))),
						JLog::WARNING, 'jerror'
					);
				}

				return false;
			}
		}

		/*
		 * If we created the language directory we will want to remove it if we
		 * have to roll back the installation, so let's add it to the installation
		 * step stack
		 */
		if ($created)
		{
			$this->parent->pushStep(array('type' => 'folder', 'path' => $this->parent->getPath('extension_site')));
		}

		// Copy all the necessary files
		if ($this->parent->parseFiles($element) === false)
		{
			// Install failed, rollback changes
			$this->parent->abort();

			return false;
		}

		// Parse optional tags
		$this->parent->parseMedia($this->getManifest()->media);

		// Copy all the necessary font files to the common pdf_fonts directory
		$this->parent->setPath('extension_site', $basePath . '/language/pdf_fonts');
		$overwrite = $this->parent->setOverwrite(true);

		if ($this->parent->parseFiles($this->getManifest()->fonts) === false)
		{
			// Install failed, rollback changes
			$this->parent->abort();

			return false;
		}

		$this->parent->setOverwrite($overwrite);

		// Get the language description
		$description = (string) $this->getManifest()->description;

		if ($description)
		{
			$this->parent->set('message', JText::_($description));
		}
		else
		{
			$this->parent->set('message', '');
		}

		// Add an entry to the extension table with a whole heap of defaults
		$row = JTable::getInstance('extension');
		$row->set('name', $this->get('name'));
		$row->set('type', 'language');
		$row->set('element', $this->get('tag'));

		// There is no folder for languages
		$row->set('folder', '');
		$row->set('enabled', 1);
		$row->set('protected', 0);
		$row->set('access', 0);
		$row->set('client_id', $clientId);
		$row->set('params', $this->parent->getParams());
		$row->set('manifest_cache', $this->parent->generateManifestCache());

		if (!$row->store())
		{
			// Install failed, roll back changes
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT', $row->getError()));

			return false;
		}

		// Create an unpublished content language.
		if ((int) $clientId === 0)
		{
			// Load the site language manifest.
			$siteLanguageManifest = JLanguageHelper::parseXMLLanguageFile(JPATH_SITE . '/language/' . $this->tag . '/' . $this->tag . '.xml');

			// Set the content language title as the language metadata name.
			$contentLanguageTitle = $siteLanguageManifest['name'];

			// Set, as fallback, the content language native title to the language metadata name.
			$contentLanguageNativeTitle = $contentLanguageTitle;

			// If exist, load the native title from the language xml metadata.
			if (isset($siteLanguageMetadata['nativeName']) && $siteLanguageMetadata['nativeName'])
			{
				$contentLanguageNativeTitle = $siteLanguageMetadata['nativeName'];
			}

			// Try to load a language string from the installation language var. Will be removed in 4.0.
			if ($contentLanguageNativeTitle === $contentLanguageTitle)
			{
				if (file_exists(JPATH_INSTALLATION . '/language/' . $this->tag . '/' . $this->tag . '.xml'))
				{
					$installationLanguage = new JLanguage($this->tag);
					$installationLanguage->load('', JPATH_INSTALLATION);

					if ($installationLanguage->hasKey('INSTL_DEFAULTLANGUAGE_NATIVE_LANGUAGE_NAME'))
					{
						// Make sure it will not use the en-GB fallback.
						$defaultLanguage = new JLanguage('en-GB');
						$defaultLanguage->load('', JPATH_INSTALLATION);

						$defaultLanguageNativeTitle      = $defaultLanguage->_('INSTL_DEFAULTLANGUAGE_NATIVE_LANGUAGE_NAME');
						$installationLanguageNativeTitle = $installationLanguage->_('INSTL_DEFAULTLANGUAGE_NATIVE_LANGUAGE_NAME');

						if ($defaultLanguageNativeTitle != $installationLanguageNativeTitle)
						{
							$contentLanguageNativeTitle = $installationLanguage->_('INSTL_DEFAULTLANGUAGE_NATIVE_LANGUAGE_NAME');
						}
					}
				}
			}

			// Prepare language data for store.
			$languageData = array(
				'lang_id'      => 0,
				'lang_code'    => $this->tag,
				'title'        => $contentLanguageTitle,
				'title_native' => $contentLanguageNativeTitle,
				'sef'          => $this->getSefString($this->tag),
				'image'        => strtolower(str_replace('-', '_', $this->tag)),
				'published'    => 0,
				'ordering'     => 0,
				'access'       => (int) JFactory::getConfig()->get('access', 1),
				'description'  => '',
				'metakey'      => '',
				'metadesc'     => '',
				'sitename'     => '',
			);

			$tableLanguage = JTable::getInstance('language');

			if (!$tableLanguage->bind($languageData) || !$tableLanguage->check() || !$tableLanguage->store() || !$tableLanguage->reorder())
			{
				JLog::add(
					JText::sprintf('JLIB_INSTALLER_WARNING_UNABLE_TO_INSTALL_CONTENT_LANGUAGE', $siteLanguageManifest['name'], $tableLanguage->getError()),
					JLog::WARNING,
					'jerror'
				);
			}
		}

		// Clobber any possible pending updates
		$update = JTable::getInstance('update');
		$uid = $update->find(array('element' => $this->get('tag'), 'type' => 'language', 'folder' => ''));

		if ($uid)
		{
			$update->delete($uid);
		}

		// Clean installed languages cache.
		JFactory::getCache()->clean('com_languages');

		return $row->get('extension_id');
	}


	/**
	 * Gets a unique language SEF string.
	 *
	 * This function checks other existing language with the same code, if they exist provides a unique SEF name.
	 * For instance: en-GB, en-US and en-AU will share the same SEF code by default: www.mywebsite.com/en/
	 * To avoid this conflict, this function creates an specific SEF in case of existing conflict:
	 * For example: www.mywebsite.com/en-au/
	 *
	 * @param   string  $itemLanguageTag  Language Tag.
	 *
	 * @return  string
	 *
	 * @since   3.7.0
	 */
	protected function getSefString($itemLanguageTag)
	{
		$langs               = explode('-', $itemLanguageTag);
		$prefixToFind        = $langs[0];
		$numberPrefixesFound = 0;

		// Get the sef value of all current content languages.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('sef'))
			->from($db->qn('#__languages'));
		$db->setQuery($query);

		$siteLanguages = $db->loadObjectList();

		foreach ($siteLanguages as $siteLang)
		{
			if ($siteLang->sef === $prefixToFind)
			{
				$numberPrefixesFound++;
			}
		}

		return $numberPrefixesFound === 0 ? $prefixToFind : strtolower($itemLanguageTag);
	}

	/**
	 * Custom update method
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @since   3.1
	 */
	public function update()
	{
		$xml = $this->parent->getManifest();

		$this->setManifest($xml);

		$cname = $xml->attributes()->client;

		// Attempt to map the client to a base path
		$client = JApplicationHelper::getClientInfo($cname, true);

		if ($client === null || (empty($cname) && $cname !== 0))
		{
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT', JText::sprintf('JLIB_INSTALLER_ERROR_UNKNOWN_CLIENT_TYPE', $cname)));

			return false;
		}

		$basePath = $client->path;
		$clientId = $client->id;

		// Get the language name
		// Set the extensions name
		$name = (string) $this->getManifest()->name;
		$name = JFilterInput::getInstance()->clean($name, 'cmd');
		$this->set('name', $name);

		// Get the Language tag [ISO tag, eg. en-GB]
		$tag = (string) $xml->tag;

		// Check if we found the tag - if we didn't, we may be trying to install from an older language package
		if (!$tag)
		{
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT', JText::_('JLIB_INSTALLER_ERROR_NO_LANGUAGE_TAG')));

			return false;
		}

		$this->set('tag', $tag);

		// Set the language installation path
		$this->parent->setPath('extension_site', $basePath . '/language/' . $tag);

		// Do we have a meta file in the file list?  In other words... is this a core language pack?
		if (count($xml->files->children()))
		{
			foreach ($xml->files->children() as $file)
			{
				if ((string) $file->attributes()->file == 'meta')
				{
					$this->core = true;
					break;
				}
			}
		}

		// Copy all the necessary files
		if ($this->parent->parseFiles($xml->files) === false)
		{
			// Install failed, rollback changes
			$this->parent->abort();

			return false;
		}

		// Parse optional tags
		$this->parent->parseMedia($xml->media);

		// Copy all the necessary font files to the common pdf_fonts directory
		$this->parent->setPath('extension_site', $basePath . '/language/pdf_fonts');
		$overwrite = $this->parent->setOverwrite(true);

		if ($this->parent->parseFiles($xml->fonts) === false)
		{
			// Install failed, rollback changes
			$this->parent->abort();

			return false;
		}

		$this->parent->setOverwrite($overwrite);

		// Get the language description and set it as message
		$this->parent->set('message', (string) $xml->description);

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Finalization and Cleanup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Clobber any possible pending updates
		$update = JTable::getInstance('update');
		$uid = $update->find(array('element' => $this->get('tag'), 'type' => 'language', 'client_id' => $clientId));

		if ($uid)
		{
			$update->delete($uid);
		}

		// Update an entry to the extension table
		$row = JTable::getInstance('extension');
		$eid = $row->find(array('element' => strtolower($this->get('tag')), 'type' => 'language', 'client_id' => $clientId));

		if ($eid)
		{
			$row->load($eid);
		}
		else
		{
			// Set the defaults

			// There is no folder for language
			$row->set('folder', '');
			$row->set('enabled', 1);
			$row->set('protected', 0);
			$row->set('access', 0);
			$row->set('client_id', $clientId);
			$row->set('params', $this->parent->getParams());
		}

		$row->set('name', $this->get('name'));
		$row->set('type', 'language');
		$row->set('element', $this->get('tag'));
		$row->set('manifest_cache', $this->parent->generateManifestCache());

		// Clean installed languages cache.
		JFactory::getCache()->clean('com_languages');

		if (!$row->store())
		{
			// Install failed, roll back changes
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT', $row->getError()));

			return false;
		}

		return $row->get('extension_id');
	}

	/**
	 * Custom uninstall method
	 *
	 * @param   string  $eid  The tag of the language to uninstall
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.1
	 */
	public function uninstall($eid)
	{
		// Load up the extension details
		$extension = JTable::getInstance('extension');
		$extension->load($eid);

		// Grab a copy of the client details
		$client = JApplicationHelper::getClientInfo($extension->get('client_id'));

		// Check the element isn't blank to prevent nuking the languages directory...just in case
		$element = $extension->get('element');

		if (empty($element))
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_LANG_UNINSTALL_ELEMENT_EMPTY'), JLog::WARNING, 'jerror');

			return false;
		}

		// Check that the language is not protected, Normally en-GB.
		$protected = $extension->get('protected');

		if ($protected == 1)
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_LANG_UNINSTALL_PROTECTED'), JLog::WARNING, 'jerror');

			return false;
		}

		// Verify that it's not the default language for that client
		$params = JComponentHelper::getParams('com_languages');

		if ($params->get($client->name) == $element)
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_LANG_UNINSTALL_DEFAULT'), JLog::WARNING, 'jerror');

			return false;
		}

		/*
		 * Does this extension have a parent package?
		 * If so, check if the package disallows individual extensions being uninstalled if the package is not being uninstalled
		 */
		if ($extension->package_id && !$this->parent->isPackageUninstall() && !$this->canUninstallPackageChild($extension->package_id))
		{
			JLog::add(JText::sprintf('JLIB_INSTALLER_ERROR_CANNOT_UNINSTALL_CHILD_OF_PACKAGE', $extension->name), JLog::WARNING, 'jerror');

			return false;
		}

		// Construct the path from the client, the language and the extension element name
		$path = $client->path . '/language/' . $element;

		// Get the package manifest object and remove media
		$this->parent->setPath('source', $path);

		// We do findManifest to avoid problem when uninstalling a list of extension: getManifest cache its manifest file
		$this->parent->findManifest();
		$this->setManifest($this->parent->getManifest());
		$this->parent->removeFiles($this->getManifest()->media);

		// Check it exists
		if (!JFolder::exists($path))
		{
			// If the folder doesn't exist lets just nuke the row as well and presume the user killed it for us
			$extension->delete();
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_LANG_UNINSTALL_PATH_EMPTY'), JLog::WARNING, 'jerror');

			return false;
		}

		if (!JFolder::delete($path))
		{
			// If deleting failed we'll leave the extension entry in tact just in case
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_LANG_UNINSTALL_DIRECTORY'), JLog::WARNING, 'jerror');

			return false;
		}

		// Remove the extension table entry
		$extension->delete();

		// Setting the language of users which have this language as the default language
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->from('#__users')
			->select('*');
		$db->setQuery($query);
		$users = $db->loadObjectList();

		if ($client->name == 'administrator')
		{
			$param_name = 'admin_language';
		}
		else
		{
			$param_name = 'language';
		}

		$count = 0;

		foreach ($users as $user)
		{
			$registry = new Registry($user->params);

			if ($registry->get($param_name) == $element)
			{
				$registry->set($param_name, '');
				$query->clear()
					->update('#__users')
					->set('params=' . $db->quote($registry))
					->where('id=' . (int) $user->id);
				$db->setQuery($query);
				$db->execute();
				$count++;
			}
		}

		// Clean installed languages cache.
		JFactory::getCache()->clean('com_languages');

		if (!empty($count))
		{
			JLog::add(JText::plural('JLIB_INSTALLER_NOTICE_LANG_RESET_USERS', $count), JLog::NOTICE, 'jerror');
		}

		// All done!
		return true;
	}

	/**
	 * Custom discover method
	 * Finds language files
	 *
	 * @return  boolean  True on success
	 *
	 * @since  3.1
	 */
	public function discover()
	{
		$results = array();
		$site_languages = JFolder::folders(JPATH_SITE . '/language');
		$admin_languages = JFolder::folders(JPATH_ADMINISTRATOR . '/language');

		foreach ($site_languages as $language)
		{
			if (file_exists(JPATH_SITE . '/language/' . $language . '/' . $language . '.xml'))
			{
				$manifest_details = JInstaller::parseXMLInstallFile(JPATH_SITE . '/language/' . $language . '/' . $language . '.xml');
				$extension = JTable::getInstance('extension');
				$extension->set('type', 'language');
				$extension->set('client_id', 0);
				$extension->set('element', $language);
				$extension->set('folder', '');
				$extension->set('name', $language);
				$extension->set('state', -1);
				$extension->set('manifest_cache', json_encode($manifest_details));
				$extension->set('params', '{}');
				$results[] = $extension;
			}
		}

		foreach ($admin_languages as $language)
		{
			if (file_exists(JPATH_ADMINISTRATOR . '/language/' . $language . '/' . $language . '.xml'))
			{
				$manifest_details = JInstaller::parseXMLInstallFile(JPATH_ADMINISTRATOR . '/language/' . $language . '/' . $language . '.xml');
				$extension = JTable::getInstance('extension');
				$extension->set('type', 'language');
				$extension->set('client_id', 1);
				$extension->set('element', $language);
				$extension->set('folder', '');
				$extension->set('name', $language);
				$extension->set('state', -1);
				$extension->set('manifest_cache', json_encode($manifest_details));
				$extension->set('params', '{}');
				$results[] = $extension;
			}
		}

		return $results;
	}

	/**
	 * Custom discover install method
	 * Basically updates the manifest cache and leaves everything alone
	 *
	 * @return  integer  The extension id
	 *
	 * @since   3.1
	 */
	public function discover_install()
	{
		// Need to find to find where the XML file is since we don't store this normally
		$client = JApplicationHelper::getClientInfo($this->parent->extension->client_id);
		$short_element = $this->parent->extension->element;
		$manifestPath = $client->path . '/language/' . $short_element . '/' . $short_element . '.xml';
		$this->parent->manifest = $this->parent->isManifest($manifestPath);
		$this->parent->setPath('manifest', $manifestPath);
		$this->parent->setPath('source', $client->path . '/language/' . $short_element);
		$this->parent->setPath('extension_root', $this->parent->getPath('source'));
		$manifest_details = JInstaller::parseXMLInstallFile($this->parent->getPath('manifest'));
		$this->parent->extension->manifest_cache = json_encode($manifest_details);
		$this->parent->extension->state = 0;
		$this->parent->extension->name = $manifest_details['name'];
		$this->parent->extension->enabled = 1;

		// @todo remove code: $this->parent->extension->params = $this->parent->getParams();
		try
		{
			$this->parent->extension->store();
		}
		catch (RuntimeException $e)
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_LANG_DISCOVER_STORE_DETAILS'), JLog::WARNING, 'jerror');

			return false;
		}

		// Clean installed languages cache.
		JFactory::getCache()->clean('com_languages');

		return $this->parent->extension->get('extension_id');
	}

	/**
	 * Refreshes the extension table cache
	 *
	 * @return  boolean result of operation, true if updated, false on failure
	 *
	 * @since   3.1
	 */
	public function refreshManifestCache()
	{
		$client = JApplicationHelper::getClientInfo($this->parent->extension->client_id);
		$manifestPath = $client->path . '/language/' . $this->parent->extension->element . '/' . $this->parent->extension->element . '.xml';
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
}

/**
 * Deprecated class placeholder. You should use JInstallerAdapterLanguage instead.
 *
 * @since       3.1
 * @deprecated  4.0
 * @codeCoverageIgnore
 */
class JInstallerLanguage extends JInstallerAdapterLanguage
{
}
