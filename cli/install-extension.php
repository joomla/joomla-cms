<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Command-line installer for Joomla! extensions. Please note that this must be called
 * from the command line, not the web. For example:
 *
 *     /usr/bin/php /path/to/site/cli/install-extension.php
 *
 * This file is based on ideas from
 * https://github.com/alikon/joomla-platform-examples/blob/master/Joomla%20CLI%20App/cli/jeicli.php
 * and
 * https://github.com/akeeba/vagrant/master/vagrant/files/joomla/install-joomla-extension.php
 *
 * Error codes returned from this script are:
 * 0	Success
 * 1	Missing parameters
 * 2	Package file not found
 * 3	Could not find download URL in the XML manifest
 * 4	Could not download package
 * 5	Could not extract package
 * 250	Installation failed (package error, unwriteable directories, etc)
 */

// Set flag that this is a parent file.
const _JEXEC = 1;

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if ( !defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_LIBRARIES . '/import.legacy.php';
require_once JPATH_LIBRARIES . '/cms.php';

// Load the configuration
require_once JPATH_CONFIGURATION . '/configuration.php';

// Load the JApplicationCli class
JLoader::import('joomla.application.cli');

/**
 * A command line script to install extensions and extension updates from a folder, file, URL or update XML source
 *
 * @since  3.4
 */
class JoomlaExtensionInstallerCli extends JApplicationCli
{
	/**
	 * The installation method. One of folder, package, url, web
	 *
	 * @var    null|string
	 * @since  3.4
	 */
	private $installationMethod = null;

	/**
	 * The installation source. It can be a folder, file or URL depending on the installationMethod.
	 *
	 * @var    null|string
	 * @since  3.4
	 */
	private $installationSource = null;

	/**
	 * The path to the temporary package file downloaded from the web. Only used with url and web installation methods.
	 *
	 * @var    null|string
	 * @since  3.4
	 */
	private $temporaryPackage = null;

	/**
	 * The path to the temporary folder where the package is extracted. Not used with the folder installation method.
	 *
	 * @var    null|string
	 * @since  3.4
	 */
	private $temporaryFolder = null;

	/**
	 * Get the metadata of the possible options
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	private function getOptionsMeta()
	{
		return array(
			array(
				'short_name'     => 'f',
				'long_name'      => 'folder',
				'filter'         => 'raw',
				'help_parameter' => 'CLI_INSTALL_EXTENSION_HELP_OPTION_FOLDER_PARAM',
				'help_text'      => 'CLI_INSTALL_EXTENSION_HELP_OPTION_FOLDER'
			),
			array(
				'short_name'     => 'p',
				'long_name'      => 'package',
				'filter'         => 'raw',
				'help_parameter' => 'CLI_INSTALL_EXTENSION_HELP_OPTION_PACKAGE_PARAM',
				'help_text'      => 'CLI_INSTALL_EXTENSION_HELP_OPTION_PACKAGE'
			),
			array(
				'short_name'     => 'u',
				'long_name'      => 'url',
				'filter'         => 'raw',
				'help_parameter' => 'CLI_INSTALL_EXTENSION_HELP_OPTION_URL_PARAM',
				'help_text'      => 'CLI_INSTALL_EXTENSION_HELP_OPTION_URL'
			),
			array(
				'short_name'     => 'w',
				'long_name'      => 'web',
				'filter'         => 'raw',
				'help_parameter' => 'CLI_INSTALL_EXTENSION_HELP_OPTION_WEB_PARAM',
				'help_text'      => 'CLI_INSTALL_EXTENSION_HELP_OPTION_WEB'
			),
		);
	}

	/**
	 * Shows the usage instructions of this script
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	private function showUsage()
	{
		$phpPath = defined('PHP_BINARY') ? PHP_BINARY : '/usr/bin/php';
		$this->out(JText::sprintf('CLI_INSTALL_EXTENSION_USAGE', $phpPath, basename(__FILE__)));
		$this->out('');
		$this->out(JText::_('CLI_INSTALL_EXTENSION_OPTIONS'));
		$this->out('');

		foreach ($this->getOptionsMeta() as $optionDef)
		{
			$this->out('-' . $optionDef['short_name'], false);
			$this->out(' ' . JText::_($optionDef['help_parameter']), false);
			$this->out(' | ', false);
			$this->out('--' . $optionDef['long_name'], false);
			$this->out('=' . JText::_($optionDef['help_parameter']), false);
			$this->out();
			$this->out("\t" . JText::_($optionDef['help_text']));
			$this->out();
		}
	}

	/**
	 * Checks if at least one of the installation options is set and populates the installationMethod and
	 * installationSource properties.
	 *
	 * @return  bool  True if there was an installation method and source specified
	 *
	 * @since  3.4
	 */
	private function getAndValidateParameters()
	{
		foreach ($this->getOptionsMeta() as $optionDef)
		{
			$value = $this->input->get($optionDef['short_name'], null, $optionDef['filter']);

			if (empty($value))
			{
				$value = $this->input->get($optionDef['long_name'], null, $optionDef['filter']);
			}

			if ( !empty($value))
			{
				$this->installationMethod = $optionDef['long_name'];
				$this->installationSource = $value;

				return true;
			}
		}

		return false;
	}

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function execute()
	{
		// Load dependencies
		JLoader::import('cms.component.helper');
		JLoader::import('joomla.application.component.helper');
		JLoader::import('joomla.updater.update');
		JLoader::import('joomla.filesystem.file');
		JLoader::import('joomla.filesystem.folder');

		// Load the language files for the Joomla! library (lib_joomla)
		$jlang = JFactory::getLanguage();
		$jlang->load('lib_joomla', JPATH_SITE, 'en-GB', true);
		$jlang->load('lib_joomla', JPATH_SITE, null, true);

		// Load the language files for the extensions installer (com_installer). IMPORTANT: These langauge files are
		// located in the back-end of the site, hence JPATH_ADMINISTRATOR.
		$jlang->load('com_installer', JPATH_ADMINISTRATOR, 'en-GB', true);
		$jlang->load('com_installer', JPATH_ADMINISTRATOR, null, true);

		// Load the language files for this CLI APP
		$jlang->load('cli_install_extension', JPATH_SITE, 'en-GB', true);
		$jlang->load('cli_install_extension', JPATH_SITE, null, true);

		// Add a logger
		JLog::addLogger(
			array(
				// Set the name of the log file.
				'text_file' => 'installer_cli.php',
			), JLog::DEBUG
		);

		// Show the application banner
		$jVersion = new JVersion;
		$this->out(JText::sprintf('CLI_INSTALL_EXTENSION', $jVersion->getShortVersion()));
		$this->out($jVersion->COPYRIGHT);
		$this->out(str_repeat('=', 79));
		$this->out();

		// Verify the command-line options
		if (!$this->getAndValidateParameters())
		{
			$this->showUsage();
			$this->close(1);
		}

		$this->out(JText::sprintf('CLI_INSTALL_EXTENSION_INSTALL_WITH_METHOD', $this->installationMethod));
		$this->out();

		// Find the package file to extract
		$packageFile = null;

		switch ($this->installationMethod)
		{
			case 'folder' :
				$packageFile = null;

			case 'file' :
				$packageFile = $this->installationSource;
				break;

			case 'web' :
			case 'url' :
				$url = $this->installationSource;

				if ($this->installationMethod == 'web')
				{
					$this->out(JText::sprintf('CLI_INSTALL_EXTENSION_FIND_DOWNLOAD_FROM_XML', $this->installationSource));
					$url = $this->getDownloadUrlFromXML($this->installationSource);

					if ($url === false)
					{
						$this->out(JText::sprintf('CLI_INSTALL_EXTENSION_ERR_XML_PROVIDES_NO_URL', $this->installationSource));
						$this->close(3);
					}
				}

				// Download the package
				$this->out(JText::sprintf('CLI_INSTALL_EXTENSION_DOWNLOAD_FROM_URL', $url));
				$this->temporaryPackage = JInstallerHelper::downloadPackage($url);

				if ($this->temporaryPackage === false)
				{
					$this->out(JText::sprintf('CLI_INSTALL_EXTENSION_ERR_CANT_DOWNLOAD', $url));
					$this->close(4);
				}

				$this->temporaryPackage = JFactory::getConfig()->get('tmp_path') . '/' . $this->temporaryPackage;
				$packageFile            = $this->temporaryPackage;

				break;

			default :
				$this->showUsage();
				$this->close(1);
				break;
		}

		// Make sure the package file exists
		if (!is_null($packageFile) && !file_exists($packageFile))
		{
			$this->out(JText::sprintf('CLI_INSTALL_EXTENSION_ERR_PACKAGE_NOT_EXISTS', $packageFile));
			$this->close(2);
		}

		// Extract the package file, if required
		$extensionDirectory = null;

		if ($this->installationMethod == 'folder')
		{
			$extensionDirectory = $this->installationSource;
		}

		if (!is_null($packageFile))
		{
			$this->out(JText::sprintf('CLI_INSTALL_EXTENSION_EXTRACTING_PACKAGE', $packageFile));
			$package = JInstallerHelper::unpack($packageFile);

			if ($package === false)
			{
				$this->cleanUp();

				$this->out(JText::sprintf('CLI_INSTALL_EXTENSION_ERR_CANNOT_EXTRACT', $packageFile));
				$this->close(5);
			}

			$this->temporaryFolder = $package['extractdir'];
			$extensionDirectory    = $this->temporaryFolder;
		}

		// Try installing the extension
		$this->out(JText::sprintf('CLI_INSTALL_EXTENSION_INSTALLING_FROM', $extensionDirectory));
		$installer = new JInstaller;
		$installed = $installer->install($extensionDirectory);

		// Remove the temporary folders and files
		$this->cleanUp();

		// Print a message
		if ($installed)
		{
			$this->out(JText::_('CLI_INSTALL_EXTENSION_MSG_SUCCESS'));
			$this->close(0);
		}
		else
		{
			$this->out(JText::_('CLI_INSTALL_EXTENSION_MSG_FAIL'));
			$this->close(250);
		}
	}

	/**
	 * Gets the download URL of an extension from an Update XML source
	 *
	 * @param   string  $url  The URL to the update XML source
	 *
	 * @return  string|bool  The download URL or false if it's not found
	 *
	 * @since   3.4
	 */
	private function getDownloadUrlFromXML($url)
	{
		jimport('joomla.updater.update');

		$update = new JUpdate;
		$update->loadFromXML($url);
		$package_url = trim($update->get('downloadurl', false)->_data);

		return $package_url;
	}

	/**
	 * Flush the media version to refresh versionable assets. Called by the extensions installation code.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function flushAssets()
	{
		$version = new JVersion;
		$version->refreshMediaVersion();
	}

	/**
	 * Gets the name of the current template. Called by the extensions installation code.
	 *
	 * @param   boolean  $params  An optional associative array of configuration settings
	 *
	 * @return  mixed  System is the fallback.
	 *
	 * @since       3.4
	 *
	 * @deprecated  4.0
	 */
	public function getTemplate($params = false)
	{
		$template           = new stdClass;
		$template->template = 'system';
		$template->params   = new Registry;

		if ($params)
		{
			return $template;
		}

		return $template->template;
	}

	/**
	 * This method is called by the extensions installation code. Since we're in the context of a CLI application we
	 * cannot set HTTP headers, so we'll simply ignore any call to this method.
	 *
	 * @param   string   $name     Ignored
	 * @param   string   $value    Ignored
	 * @param   boolean  $replace  Ignored
	 *
	 * @return  $this
	 *
	 * @since   3.4
	 */
	public function setHeader($name, $value, $replace = false)
	{
		return $this;
	}

	/**
	 * Cleans up temporary files and folders used in the installation
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	private function cleanUp()
	{
		if (!empty($this->temporaryFolder) && JFolder::exists($this->temporaryFolder))
		{
			JFolder::delete($this->temporaryFolder);
		}

		if (!empty($this->temporaryPackage) && JFile::exists($this->temporaryPackage))
		{
			JFile::delete($this->temporaryPackage);
		}
	}
}

// Instantiate the application object
$app = JApplicationCli::getInstance('JoomlaExtensionInstallerCli');

// The installation code assumes that JFactory::getApplication returns a valid reference. We must not disappoint it!
JFactory::$application = $app;

// Execute the CLI extensions installer application
$app->execute();
