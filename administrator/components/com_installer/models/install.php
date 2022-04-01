<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Extension Manager Install Model
 *
 * @since  1.5
 */
class InstallerModelInstall extends JModelLegacy
{
	/**
	 * @var object JTable object
	 */
	protected $_table = null;

	/**
	 * @var object JTable object
	 */
	protected $_url = null;

	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context = 'com_installer.install';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('administrator');

		$this->setState('message', $app->getUserState('com_installer.message'));
		$this->setState('extension_message', $app->getUserState('com_installer.extension_message'));
		$app->setUserState('com_installer.message', '');
		$app->setUserState('com_installer.extension_message', '');

		parent::populateState();
	}

	/**
	 * Install an extension from either folder, URL or upload.
	 *
	 * @return  boolean result of install.
	 *
	 * @since   1.5
	 */
	public function install()
	{
		$this->setState('action', 'install');

		// Set FTP credentials, if given.
		JClientHelper::setCredentialsFromRequest('ftp');
		$app = JFactory::getApplication();

		// Load installer plugins for assistance if required:
		JPluginHelper::importPlugin('installer');
		$dispatcher = JEventDispatcher::getInstance();

		$package = null;

		// This event allows an input pre-treatment, a custom pre-packing or custom installation.
		// (e.g. from a JSON description).
		$results = $dispatcher->trigger('onInstallerBeforeInstallation', array($this, &$package));

		if (in_array(true, $results, true))
		{
			return true;
		}

		if (in_array(false, $results, true))
		{
			return false;
		}

		$installType = $app->input->getWord('installtype');

		if ($package === null)
		{
			switch ($installType)
			{
				case 'folder':
					// Remember the 'Install from Directory' path.
					$app->getUserStateFromRequest($this->_context . '.install_directory', 'install_directory');
					$package = $this->_getPackageFromFolder();
					break;

				case 'upload':
					$package = $this->_getPackageFromUpload();
					break;

				case 'url':
					$package = $this->_getPackageFromUrl();
					break;

				default:
					$app->setUserState('com_installer.message', JText::_('COM_INSTALLER_NO_INSTALL_TYPE_FOUND'));

					return false;
					break;
			}
		}

		// This event allows a custom installation of the package or a customization of the package:
		$results = $dispatcher->trigger('onInstallerBeforeInstaller', array($this, &$package));

		if (in_array(true, $results, true))
		{
			return true;
		}

		if (in_array(false, $results, true))
		{
			if (in_array($installType, array('upload', 'url')))
			{
				JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
			}

			return false;
		}

		// Check if package was uploaded successfully.
		if (!\is_array($package))
		{
			$app->enqueueMessage(JText::_('COM_INSTALLER_UNABLE_TO_FIND_INSTALL_PACKAGE'), 'error');

			return false;
		}

		// Get an installer instance.
		$installer = JInstaller::getInstance();

		/*
		 * Check for a Joomla core package.
		 * To do this we need to set the source path to find the manifest (the same first step as JInstaller::install())
		 *
		 * This must be done before the unpacked check because JInstallerHelper::detectType() returns a boolean false since the manifest
		 * can't be found in the expected location.
		 */
		if (isset($package['dir']) && is_dir($package['dir']))
		{
			$installer->setPath('source', $package['dir']);

			if (!$installer->findManifest())
			{
				// If a manifest isn't found at the source, this may be a Joomla package; check the package directory for the Joomla manifest
				if (file_exists($package['dir'] . '/administrator/manifests/files/joomla.xml'))
				{
					// We have a Joomla package
					if (in_array($installType, array('upload', 'url')))
					{
						JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
					}

					$app->enqueueMessage(
						JText::sprintf('COM_INSTALLER_UNABLE_TO_INSTALL_JOOMLA_PACKAGE', JRoute::_('index.php?option=com_joomlaupdate')),
						'warning'
					);

					return false;
				}
			}
		}

		// Was the package unpacked?
		if (empty($package['type']))
		{
			if (in_array($installType, array('upload', 'url')))
			{
				JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
			}

			$app->enqueueMessage(JText::_('JLIB_INSTALLER_ABORT_DETECTMANIFEST'), 'error');

			return false;
		}

		// Install the package.
		if (!$installer->install($package['dir']))
		{
			// There was an error installing the package.
			$msg = JText::sprintf('COM_INSTALLER_INSTALL_ERROR', JText::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($package['type'])));
			$result = false;
			$msgType = 'error';
		}
		else
		{
			// Package installed successfully.
			$msg = JText::sprintf('COM_INSTALLER_INSTALL_SUCCESS', JText::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($package['type'])));
			$result = true;
			$msgType = 'message';
		}

		// This event allows a custom a post-flight:
		$dispatcher->trigger('onInstallerAfterInstaller', array($this, &$package, $installer, &$result, &$msg));

		// Set some model state values.
		$app = JFactory::getApplication();
		$app->enqueueMessage($msg, $msgType);
		$this->setState('name', $installer->get('name'));
		$this->setState('result', $result);
		$app->setUserState('com_installer.message', $installer->message);
		$app->setUserState('com_installer.extension_message', $installer->get('extension_message'));
		$app->setUserState('com_installer.redirect_url', $installer->get('redirect_url'));

		// Cleanup the install files.
		if (!is_file($package['packagefile']))
		{
			$config = JFactory::getConfig();
			$package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
		}

		JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

		// Clear the cached extension data and menu cache
		$this->cleanCache('_system', 0);
		$this->cleanCache('_system', 1);
		$this->cleanCache('com_modules', 0);
		$this->cleanCache('com_modules', 1);
		$this->cleanCache('com_plugins', 0);
		$this->cleanCache('com_plugins', 1);
		$this->cleanCache('mod_menu', 0);
		$this->cleanCache('mod_menu', 1);

		return $result;
	}

	/**
	 * Works out an installation package from a HTTP upload.
	 *
	 * @return package definition or false on failure.
	 */
	protected function _getPackageFromUpload()
	{
		// Get the uploaded file information.
		$input    = JFactory::getApplication()->input;

		// Do not change the filter type 'raw'. We need this to let files containing PHP code to upload. See JInputFiles::get.
		$userfile = $input->files->get('install_package', null, 'raw');

		// Make sure that file uploads are enabled in php.
		if (!(bool) ini_get('file_uploads'))
		{
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLFILE'));

			return false;
		}

		// Make sure that zlib is loaded so that the package can be unpacked.
		if (!extension_loaded('zlib'))
		{
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLZLIB'));

			return false;
		}

		// If there is no uploaded file, we have a problem...
		if (!is_array($userfile))
		{
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_NO_FILE_SELECTED'));

			return false;
		}

		// Is the PHP tmp directory missing?
		if ($userfile['error'] && ($userfile['error'] == UPLOAD_ERR_NO_TMP_DIR))
		{
			JError::raiseWarning(
				'',
				JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR') . '<br />' . JText::_('COM_INSTALLER_MSG_WARNINGS_PHPUPLOADNOTSET')
			);

			return false;
		}

		// Is the max upload size too small in php.ini?
		if ($userfile['error'] && ($userfile['error'] == UPLOAD_ERR_INI_SIZE))
		{
			JError::raiseWarning(
				'',
				JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR') . '<br />' . JText::_('COM_INSTALLER_MSG_WARNINGS_SMALLUPLOADSIZE')
			);

			return false;
		}

		// Check if there was a different problem uploading the file.
		if ($userfile['error'] || $userfile['size'] < 1)
		{
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR'));

			return false;
		}

		// Build the appropriate paths.
		$config   = JFactory::getConfig();
		$tmp_dest = $config->get('tmp_path') . '/' . $userfile['name'];
		$tmp_src  = $userfile['tmp_name'];

		// Move uploaded file.
		jimport('joomla.filesystem.file');
		JFile::upload($tmp_src, $tmp_dest, false, true);

		// Unpack the downloaded package file.
		$package = JInstallerHelper::unpack($tmp_dest, true);

		return $package;
	}

	/**
	 * Install an extension from a directory
	 *
	 * @return  array  Package details or false on failure
	 *
	 * @since   1.5
	 */
	protected function _getPackageFromFolder()
	{
		$input = JFactory::getApplication()->input;

		// Get the path to the package to install.
		$p_dir = $input->getString('install_directory');
		$p_dir = JPath::clean($p_dir);

		// Did you give us a valid directory?
		if (!is_dir($p_dir))
		{
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_PLEASE_ENTER_A_PACKAGE_DIRECTORY'));

			return false;
		}

		// Detect the package type
		$type = JInstallerHelper::detectType($p_dir);

		// Did you give us a valid package?
		if (!$type)
		{
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_PATH_DOES_NOT_HAVE_A_VALID_PACKAGE'));
		}

		$package['packagefile'] = null;
		$package['extractdir'] = null;
		$package['dir'] = $p_dir;
		$package['type'] = $type;

		return $package;
	}

	/**
	 * Install an extension from a URL.
	 *
	 * @return  Package details or false on failure.
	 *
	 * @since   1.5
	 */
	protected function _getPackageFromUrl()
	{
		$input = JFactory::getApplication()->input;

		// Get the URL of the package to install.
		$url = $input->getString('install_url');

		// Did you give us a URL?
		if (!$url)
		{
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL'));

			return false;
		}

		// We only allow http & https here
		$uri = new JUri($url);

		if (!in_array($uri->getScheme(), array('http', 'https')))
		{
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL_SCHEME'));

			return false;
		}

		// Handle updater XML file case:
		if (preg_match('/\.xml\s*$/', $url))
		{
			jimport('joomla.updater.update');
			$update = new JUpdate;
			$update->loadFromXml($url);
			$package_url = trim($update->get('downloadurl', false)->_data);

			if ($package_url)
			{
				$url = $package_url;
			}

			unset($update);
		}

		// Download the package at the URL given.
		$p_file = JInstallerHelper::downloadPackage($url);

		// Was the package downloaded?
		if (!$p_file)
		{
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL'));

			return false;
		}

		$config   = JFactory::getConfig();
		$tmp_dest = $config->get('tmp_path');

		// Unpack the downloaded package file.
		$package = JInstallerHelper::unpack($tmp_dest . '/' . $p_file, true);

		return $package;
	}
}
