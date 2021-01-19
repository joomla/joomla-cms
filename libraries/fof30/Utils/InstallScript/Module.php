<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Utils\InstallScript;

defined('_JEXEC') || die;

use Exception;
use FOF30\Database\Installer;
use JLoader;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Adapter\ComponentAdapter;
use Joomla\CMS\Log\Log;

// In case FOF's autoloader is not present yet, e.g. new installation
if (!class_exists('FOF30\\Utils\\InstallScript\\BaseInstaller', true))
{
	require_once __DIR__ . '/BaseInstaller.php';
}

/**
 * A helper class which you can use to create module installation scripts.
 *
 * Example usage: class Mod_ExampleInstallerScript extends FOF30\Utils\InstallScript\Module
 *
 * This namespace contains more classes for creating installation scripts for other kinds of Joomla! extensions as well.
 * Do keep in mind that only components, modules and plugins could have post-installation scripts before Joomla! 3.3.
 */
class Module extends BaseInstaller
{
	/**
	 * Which side of the site is this module installed in? Use 'site' or 'administrator'.
	 *
	 * @var   string
	 */
	protected $moduleClient = 'site';

	/**
	 * The modules's name, e.g. mod_foobar. Auto-filled from the class name.
	 *
	 * @var   string
	 */
	protected $moduleName = '';

	/**
	 * The path where the schema XML files are stored. The path is relative to the folder which contains the extension's
	 * files.
	 *
	 * @var string
	 */
	protected $schemaXmlPath = 'sql/xml';


	/**
	 * Module installer script constructor.
	 */
	public function __construct()
	{
		// Get the plugin name and folder from the class name (it's always plgFolderPluginInstallerScript) if necessary.
		if (empty($this->moduleName))
		{
			$class      = get_class($this);
			$words      = preg_replace('/(\s)+/', '_', $class);
			$words      = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $words));
			$classParts = explode('_', $words);

			$this->moduleName = 'mod_' . $classParts[2];
		}
	}

	/**
	 * Joomla! pre-flight event. This runs before Joomla! installs or updates the component. This is our last chance to
	 * tell Joomla! if it should abort the installation.
	 *
	 * @param   string            $type                                  Installation type (install, update,
	 *                                                                   discover_install)
	 * @param   ComponentAdapter  $parent                                Parent object
	 *
	 * @return  boolean  True to let the installation proceed, false to halt the installation
	 */
	public function preflight($type, $parent)
	{
		// Check the minimum PHP version
		if (!$this->checkPHPVersion())
		{
			return false;
		}

		// Check the minimum Joomla! version
		if (!$this->checkJoomlaVersion())
		{
			return false;
		}

		// Clear op-code caches to prevent any cached code issues
		$this->clearOpcodeCaches();

		return true;
	}

	/**
	 * Runs after install, update or discover_update. In other words, it executes after Joomla! has finished installing
	 * or updating your component. This is the last chance you've got to perform any additional installations, clean-up,
	 * database updates and similar housekeeping functions.
	 *
	 * @param   string            $type    install, update or discover_update
	 * @param   ComponentAdapter  $parent  Parent object
	 *
	 * @return  void
	 * @throws Exception
	 *
	 */
	public function postflight($type, $parent)
	{
		/**
		 * We are not doing dependency tracking for modules and plugins because of the way Joomla package uninstallation
		 * works. FOF's uninstall() method would get called before the extensions are uninstalled, therefore its
		 * uninstallation would fail and make the entire package uninstallation to fail (the package is impossible to
		 * uninstall).
		 */
		// Add ourselves to the list of extensions depending on FOF30
		// $this->addDependency('fof30', $this->getDependencyName());
		// Install or update database
		$schemaPath = $parent->getParent()->getPath('source') . '/' . $this->schemaXmlPath;

		if (@is_dir($schemaPath))
		{
			$dbInstaller = new Installer(Factory::getDbo(), $schemaPath);
			$dbInstaller->updateSchema();
		}

		// Make sure everything is copied properly
		$this->bugfixFilesNotCopiedOnUpdate($parent);

		// Add post-installation messages on Joomla! 3.2 and later
		$this->_applyPostInstallationMessages();

		// Clear the opcode caches again - in case someone accessed the extension while the files were being upgraded.
		$this->clearOpcodeCaches();
	}

	/**
	 * Runs on uninstallation
	 *
	 * @param   ComponentAdapter  $parent  The parent object
	 */
	public function uninstall($parent)
	{
		// Uninstall database
		$schemaPath = $parent->getParent()->getPath('source') . '/' . $this->schemaXmlPath;

		// Uninstall database
		if (@is_dir($schemaPath))
		{
			$dbInstaller = new Installer(Factory::getDbo(), $schemaPath);
			$dbInstaller->removeSchema();
		}

		// Uninstall post-installation messages on Joomla! 3.2 and later
		$this->uninstallPostInstallationMessages();

		/**
		 * We are not doing dependency tracking for modules and plugins because of the way Joomla package uninstallation
		 * works. FOF's uninstall() method would get called before the extensions are uninstalled, therefore its
		 * uninstallation would fail and make the entire package uninstallation to fail (the package is impossible to
		 * uninstall).
		 */
		// Remove ourselves from the list of extensions depending on FOF30
		// $this->removeDependency('fof30', $this->getDependencyName());
	}

	/**
	 * Fix for Joomla bug: sometimes files are not copied on update.
	 *
	 * We have observed that ever since Joomla! 1.5.5, when Joomla! is performing an extension update some files /
	 * folders are not copied properly. This seems to be a bit random and seems to be more likely to happen the more
	 * added / modified files and folders you have. We are trying to work around it by retrying the copy operation
	 * ourselves WITHOUT going through the manifest, based entirely on the conventions we follow for Akeeba Ltd's
	 * extensions.
	 *
	 * @param   ComponentAdapter  $parent
	 */
	protected function bugfixFilesNotCopiedOnUpdate($parent)
	{
		Log::add("Joomla! extension update workaround for $this->moduleClient module $this->moduleName", Log::INFO, 'fof3_extension_installation');

		$temporarySource = $parent->getParent()->getPath('source');
		$rootFolder      = ($this->moduleClient == 'site') ? JPATH_SITE : JPATH_ADMINISTRATOR;

		$copyMap = [
			// Module files
			$temporarySource               => $rootFolder . '/modules/' . $this->moduleName,
			// Language
			$temporarySource . '/language' => $rootFolder . '/language',
			// Media files
			$temporarySource . '/media'    => JPATH_ROOT . '/media/' . $this->moduleName,
		];

		foreach ($copyMap as $source => $target)
		{
			Log::add(__CLASS__ . ":: Conditional copy $source to $target", Log::DEBUG, 'fof3_extension_installation');

			$ignored = [];

			if ($source == $temporarySource)
			{
				$ignored = [
					'index.html', 'index.htm', 'LICENSE.txt', 'license.txt', 'readme.htm', 'readme.html', 'README.md',
					'script.php', 'language', 'media',
				];

			}

			$this->recursiveConditionalCopy($source, $target, $ignored);
		}
	}

	/**
	 * Get the extension name for FOF dependency tracking, e.g. mod_site_foobar
	 *
	 * @return  string
	 */
	protected function getDependencyName()
	{
		return 'mod_' . strtolower($this->moduleClient) . '_' . substr($this->moduleName, 4);
	}
}
