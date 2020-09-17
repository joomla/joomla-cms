<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Utils\InstallScript;

defined('_JEXEC') || die;

use Exception;
use FOF30\Database\Installer as DatabaseInstaller;
use InvalidArgumentException;
use JDatabaseDriver;
use JError;
use JLoader;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\Adapter\ComponentAdapter;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Table\Menu;
use Joomla\CMS\Table\Table;
use SimpleXMLElement;
use stdClass;

// In case FOF's autoloader is not present yet, e.g. new installation
if (!class_exists('FOF30\\Utils\\InstallScript\\BaseInstaller', true))
{
	require_once __DIR__ . '/BaseInstaller.php';
}

/**
 * A helper class which you can use to create component installation scripts.
 *
 * Example usage: class Com_ExampleInstallerScript extends FOF30\Utils\InstallScript\Component
 *
 * This namespace contains more classes for creating installation scripts for other kinds of Joomla! extensions as well.
 * Do keep in mind that only components, modules and plugins could have post-installation scripts before Joomla! 3.3.
 */
class Component extends BaseInstaller
{
	/**
	 * The component's name. Auto-filled from the class name.
	 *
	 * @var   string
	 */
	protected $componentName = '';

	/**
	 * The title of the component (printed on installation and uninstallation messages)
	 *
	 * @var string
	 */
	protected $componentTitle = 'Foobar Component';

	/**
	 * The list of obsolete extra modules and plugins to uninstall on component upgrade / installation.
	 *
	 * @var array
	 */
	protected $uninstallation_queue = [
		// modules => { (folder) => { (module) }* }*
		'modules' => [
			'admin' => [],
			'site'  => [],
		],
		// plugins => { (folder) => { (element) }* }*
		'plugins' => [
			'system' => [],
		],
	];

	/**
	 * Obsolete files and folders to remove from the free version only. This is used when you move a feature from the
	 * free version of your extension to its paid version. If you don't have such a distinction you can ignore this.
	 *
	 * @var   array
	 */
	protected $removeFilesFree = [
		'files'   => [
			// Use pathnames relative to your site's root, e.g.
			// 'administrator/components/com_foobar/helpers/whatever.php'
		],
		'folders' => [
			// Use pathnames relative to your site's root, e.g.
			// 'administrator/components/com_foobar/baz'
		],
	];

	/**
	 * Obsolete files and folders to remove from both paid and free releases. This is used when you refactor code and
	 * some files inevitably become obsolete and need to be removed.
	 *
	 * @var   array
	 */
	protected $removeFilesAllVersions = [
		'files'   => [
			// Use pathnames relative to your site's root, e.g.
			// 'administrator/components/com_foobar/helpers/whatever.php'
		],
		'folders' => [
			// Use pathnames relative to your site's root, e.g.
			// 'administrator/components/com_foobar/baz'
		],
	];

	/**
	 * A list of scripts to be copied to the "cli" directory of the site
	 *
	 * @var   array
	 */
	protected $cliScriptFiles = [
		// Use just the filename, e.g.
		// 'my-cron-script.php'
	];

	/**
	 * The path inside your package where cli scripts are stored
	 *
	 * @var   string
	 */
	protected $cliSourcePath = 'cli';

	/**
	 * Is the schemaXmlPath class variable a relative path? If set to true the schemaXmlPath variable contains a path
	 * relative to the component's back-end directory. If set to false the schemaXmlPath variable contains an absolute
	 * filesystem path.
	 *
	 * @var   boolean
	 */
	protected $schemaXmlPathRelative = true;

	/**
	 * The path where the schema XML files are stored. Its contents depend on the schemaXmlPathRelative variable above
	 * true        => schemaXmlPath contains a path relative to the component's back-end directory
	 * false    => schemaXmlPath contains an absolute filesystem path
	 *
	 * @var string
	 */
	protected $schemaXmlPath = 'sql/xml';

	/**
	 * Is this the paid version of the extension? This only determines which files / extensions will be removed.
	 *
	 * @var   boolean
	 */
	protected $isPaid = false;

	/**
	 * Module installer script constructor.
	 */
	public function __construct()
	{
		// Get the plugin name and folder from the class name (it's always plgFolderPluginInstallerScript) if necessary.
		if (empty($this->componentName))
		{
			$class      = get_class($this);
			$words      = preg_replace('/(\s)+/', '_', $class);
			$words      = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $words));
			$classParts = explode('_', $words);

			$this->componentName = 'com_' . $classParts[2];
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

		// Workarounds for JInstaller issues.
		if (in_array($type, ['install', 'discover_install']))
		{
			// Bug fix for "Database function returned no error"
			$this->bugfixDBFunctionReturnedNoError();
		}
		else
		{
			// Bug fix for "Can not build admin menus"
			$this->bugfixCantBuildAdminMenus();
		}

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
		// Add ourselves to the list of extensions depending on FOF30
		$this->addDependency('fof30', $this->componentName);

		// Install or update database
		$dbInstaller = new DatabaseInstaller(Factory::getDbo(),
			($this->schemaXmlPathRelative ? JPATH_ADMINISTRATOR . '/components/' . $this->componentName : '') . '/' .
			$this->schemaXmlPath
		);
		$dbInstaller->updateSchema();

		// These workarounds are only needed, and only work, on Joomla! 3.x
		if (strpos(JVERSION, '3.') === 0)
		{
			// Make sure menu items are installed
			$this->_createAdminMenus($parent);

			// Make sure menu items are published
			$this->_reallyPublishAdminMenuItems($parent);
		}

		// Which files should I remove?
		if ($this->isPaid)
		{
			// This is the paid version, only remove the removeFilesAllVersions files
			$removeFiles = $this->removeFilesAllVersions;
		}
		else
		{
			// This is the free version, remove the removeFilesAllVersions and removeFilesFree files
			$removeFiles = ['files' => [], 'folders' => []];

			if (isset($this->removeFilesAllVersions['files']))
			{
				if (isset($this->removeFilesFree['files']))
				{
					$removeFiles['files'] = array_merge($this->removeFilesAllVersions['files'], $this->removeFilesFree['files']);
				}
				else
				{
					$removeFiles['files'] = $this->removeFilesAllVersions['files'];
				}
			}
			elseif (isset($this->removeFilesFree['files']))
			{
				$removeFiles['files'] = $this->removeFilesFree['files'];
			}

			if (isset($this->removeFilesAllVersions['folders']))
			{
				if (isset($this->removeFilesFree['folders']))
				{
					$removeFiles['folders'] = array_merge($this->removeFilesAllVersions['folders'], $this->removeFilesFree['folders']);
				}
				else
				{
					$removeFiles['folders'] = $this->removeFilesAllVersions['folders'];
				}
			}
			elseif (isset($this->removeFilesFree['folders']))
			{
				$removeFiles['folders'] = $this->removeFilesFree['folders'];
			}
		}

		// Remove obsolete files and folders
		$this->removeFilesAndFolders($removeFiles);

		// Make sure everything is copied properly
		$this->bugfixFilesNotCopiedOnUpdate($parent);

		// Copy the CLI files (if any)
		$this->copyCliFiles($parent);

		// Show the post-installation page
		$this->renderPostInstallation($parent);

		// Uninstall obsolete sub-extensions
		$this->uninstallObsoleteSubextensions($parent);

		// Clear the FOF cache
		$false = false;
		$cache = Factory::getCache('fof', '');
		$cache->store($false, 'cache', 'fof');

		// Make sure the Joomla! menu structure is correct
		$this->_rebuildMenu();

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
		$dbInstaller = new DatabaseInstaller(Factory::getDbo(),
			($this->schemaXmlPathRelative ? JPATH_ADMINISTRATOR . '/components/' . $this->componentName : '') . '/' .
			$this->schemaXmlPath
		);

		$dbInstaller->removeSchema();

		// Uninstall post-installation messages on Joomla! 3.2 and later
		$this->uninstallPostInstallationMessages();

		// Remove ourselves from the list of extensions depending on FOF30
		$this->removeDependency('fof30', $this->componentName);

		// Show the post-uninstallation page
		$this->renderPostUninstallation($parent);
	}

	/**
	 * Copies the CLI scripts into Joomla!'s cli directory
	 *
	 * @param   ComponentAdapter  $parent
	 */
	protected function copyCliFiles($parent)
	{
		$src = $parent->getParent()->getPath('source');

		foreach ($this->cliScriptFiles as $script)
		{
			if (is_file(JPATH_ROOT . '/cli/' . $script))
			{
				File::delete(JPATH_ROOT . '/cli/' . $script);
			}

			if (is_file($src . '/' . $this->cliSourcePath . '/' . $script))
			{
				File::copy($src . '/' . $this->cliSourcePath . '/' . $script, JPATH_ROOT . '/cli/' . $script);
			}
		}
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
		Log::add("Joomla! extension update workaround for component $this->componentName", Log::INFO, 'fof3_extension_installation');

		$temporarySource = $parent->getParent()->getPath('source');

		$copyMap = [
			// Backend component files
			'backend'           => JPATH_ADMINISTRATOR . '/components/' . $this->componentName,
			'admin'             => JPATH_ADMINISTRATOR . '/components/' . $this->componentName,
			// Frontend component files
			'frontend'          => JPATH_SITE . '/components/' . $this->componentName,
			'site'              => JPATH_SITE . '/components/' . $this->componentName,
			// Backend language
			'language/backend'  => JPATH_ADMINISTRATOR . '/language',
			'language/admin'    => JPATH_ADMINISTRATOR . '/language',
			// Frontend language
			'language/frontend' => JPATH_SITE . '/language',
			'language/site'     => JPATH_SITE . '/language',
			// Media files
			'media'             => JPATH_ROOT . '/media/' . $this->componentName,
		];

		foreach ($copyMap as $partialSource => $target)
		{
			$source = $temporarySource . '/' . $partialSource;

			Log::add(__CLASS__ . ":: Conditional copy $source to $target", Log::DEBUG, 'fof3_extension_installation');

			$this->recursiveConditionalCopy($source, $target);
		}
	}

	/**
	 * Override this method to display a custom component installation message if you so wish
	 *
	 * @param   ComponentAdapter  $parent  Parent class calling us
	 */
	protected function renderPostInstallation($parent)
	{
		echo "<h3>$this->componentName has been installed</h3>";
	}

	/**
	 * Override this method to display a custom component uninstallation message if you so wish
	 *
	 * @param   ComponentAdapter  $parent  Parent class calling us
	 */
	protected function renderPostUninstallation($parent)
	{
		echo "<h3>$this->componentName has been uninstalled</h3>";
	}

	/**
	 * Bug fix for "DB function returned no error"
	 */
	protected function bugfixDBFunctionReturnedNoError()
	{
		$db = Factory::getDbo();

		try
		{
			// Fix broken #__assets records
			$this->deleteComponentAssetRecords($db);

			// Fix broken #__extensions records
			$this->deleteComponentExtensionRecord($db);

			/**
			 * Fix broken #__menu records
			 *
			 * Only run on Joomla! versions lower than 3.7. Joomla! 3.7 introduced a backend menu manager which
			 * lets the user create missing menu items. Moreover, it lets them create custom links to the component
			 * which means that our menu deleting code would break them! So we don't run this code in newer Joomla!
			 * versions any more.
			 */
			if (version_compare(JVERSION, '3.6.9999', 'le'))
			{
				$this->deleteComponentMenuRecord($db);
			}
		}
		catch (Exception $exc)
		{
			return;
		}
	}

	/**
	 * Joomla! 1.6+ bug fix for "Can not build admin menus"
	 */
	protected function bugfixCantBuildAdminMenus()
	{
		$db = Factory::getDbo();

		// If there are multiple #__extensions record, keep one of them
		$query = $db->getQuery(true);
		$query->select('extension_id')
			->from('#__extensions')
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->where($db->qn('element') . ' = ' . $db->q($this->componentName));
		$db->setQuery($query);

		try
		{
			$ids = $db->loadColumn();
		}
		catch (Exception $exc)
		{
			return;
		}


		if (count($ids) > 1)
		{
			asort($ids);
			$extension_id = array_shift($ids); // Keep the oldest id

			foreach ($ids as $id)
			{
				$query = $db->getQuery(true);
				$query->delete('#__extensions')
					->where($db->qn('extension_id') . ' = ' . $db->q($id));
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (Exception $exc)
				{
					// Nothing
				}
			}
		}

		// If there are multiple assets records, delete all except the oldest one
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__assets')
			->where($db->qn('name') . ' = ' . $db->q($this->componentName));
		$db->setQuery($query);
		$ids = $db->loadObjectList();

		if (count($ids) > 1)
		{
			asort($ids);
			$asset_id = array_shift($ids); // Keep the oldest id

			foreach ($ids as $id)
			{
				$query = $db->getQuery(true);
				$query->delete('#__assets')
					->where($db->qn('id') . ' = ' . $db->q($id));
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (Exception $exc)
				{
					// Nothing
				}
			}
		}

		// Remove #__menu records for good measure! –– I think this is not necessary and causes the menu item to
		// disappear on extension update.
		/**
		 * $query = $db->getQuery(true);
		 * $query->select('id')
		 * ->from('#__menu')
		 * ->where($db->qn('type') . ' = ' . $db->q('component'))
		 * ->where($db->qn('menutype') . ' = ' . $db->q('main'))
		 * ->where($db->qn('link') . ' LIKE ' . $db->q('index.php?option=' . $this->componentName));
		 * $db->setQuery($query);
		 *
		 * try
		 * {
		 * $ids1 = $db->loadColumn();
		 * }
		 * catch (Exception $exc)
		 * {
		 * $ids1 = array();
		 * }
		 *
		 * if (empty($ids1))
		 * {
		 * $ids1 = array();
		 * }
		 *
		 * $query = $db->getQuery(true);
		 * $query->select('id')
		 * ->from('#__menu')
		 * ->where($db->qn('type') . ' = ' . $db->q('component'))
		 * ->where($db->qn('menutype') . ' = ' . $db->q('main'))
		 * ->where($db->qn('link') . ' LIKE ' . $db->q('index.php?option=' . $this->componentName . '&%'));
		 * $db->setQuery($query);
		 *
		 * try
		 * {
		 * $ids2 = $db->loadColumn();
		 * }
		 * catch (Exception $exc)
		 * {
		 * $ids2 = array();
		 * }
		 *
		 * if (empty($ids2))
		 * {
		 * $ids2 = array();
		 * }
		 *
		 * $ids = array_merge($ids1, $ids2);
		 *
		 * if (!empty($ids))
		 * {
		 * foreach ($ids as $id)
		 * {
		 * $query = $db->getQuery(true);
		 * $query->delete('#__menu')
		 * ->where($db->qn('id') . ' = ' . $db->q($id));
		 * $db->setQuery($query);
		 *
		 * try
		 * {
		 * $db->execute();
		 * }
		 * catch (Exception $exc)
		 * {
		 * // Nothing
		 * }
		 * }
		 * }
		 * /**/
	}

	/**
	 * Removes obsolete files and folders
	 *
	 * @param   array  $removeList  The files and directories to remove
	 */
	protected function removeFilesAndFolders($removeList)
	{
		// Remove files
		if (isset($removeList['files']) && !empty($removeList['files']))
		{
			foreach ($removeList['files'] as $file)
			{
				$f = JPATH_ROOT . '/' . $file;

				if (!is_file($f))
				{
					continue;
				}

				File::delete($f);
			}
		}

		// Remove folders
		if (isset($removeList['folders']) && !empty($removeList['folders']))
		{
			foreach ($removeList['folders'] as $folder)
			{
				$f = JPATH_ROOT . '/' . $folder;

				if (!is_dir($f))
				{
					continue;
				}

				Folder::delete($f);
			}
		}
	}

	/**
	 * Uninstalls obsolete sub-extensions (modules, plugins) bundled with the main extension
	 *
	 * @param   ComponentAdapter  $parent  The parent object
	 *
	 * @return  stdClass The sub-extension uninstallation status
	 */
	protected function uninstallObsoleteSubextensions($parent)
	{
		$db = Factory::getDBO();

		$status          = new stdClass();
		$status->modules = [];
		$status->plugins = [];

		// Modules uninstallation
		if (isset($this->uninstallation_queue['modules']) && count($this->uninstallation_queue['modules']))
		{
			foreach ($this->uninstallation_queue['modules'] as $folder => $modules)
			{
				if (count($modules))
				{
					foreach ($modules as $module)
					{
						// Find the module ID
						$sql = $db->getQuery(true)
							->select($db->qn('extension_id'))
							->from($db->qn('#__extensions'))
							->where($db->qn('element') . ' = ' . $db->q('mod_' . $module))
							->where($db->qn('type') . ' = ' . $db->q('module'));
						$db->setQuery($sql);
						$id = $db->loadResult();
						// Uninstall the module
						if ($id)
						{
							$installer         = new Installer;
							$result            = $installer->uninstall('module', $id, 1);
							$status->modules[] = [
								'name'   => 'mod_' . $module,
								'client' => $folder,
								'result' => $result,
							];
						}
					}
				}
			}
		}

		// Plugins uninstallation
		if (isset($this->uninstallation_queue['plugins']) && count($this->uninstallation_queue['plugins']))
		{
			foreach ($this->uninstallation_queue['plugins'] as $folder => $plugins)
			{
				if (count($plugins))
				{
					foreach ($plugins as $plugin)
					{
						$sql = $db->getQuery(true)
							->select($db->qn('extension_id'))
							->from($db->qn('#__extensions'))
							->where($db->qn('type') . ' = ' . $db->q('plugin'))
							->where($db->qn('element') . ' = ' . $db->q($plugin))
							->where($db->qn('folder') . ' = ' . $db->q($folder));
						$db->setQuery($sql);

						$id = $db->loadResult();
						if ($id)
						{
							$installer         = new Installer;
							$result            = $installer->uninstall('plugin', $id, 1);
							$status->plugins[] = [
								'name'   => 'plg_' . $plugin,
								'group'  => $folder,
								'result' => $result,
							];
						}
					}
				}
			}
		}

		return $status;
	}

	/**
	 * @param   ComponentAdapter  $parent
	 *
	 * @return bool
	 *
	 * @throws Exception When the Joomla! menu is FUBAR
	 */
	private function _createAdminMenus($parent)
	{
		$db = $parent->getParent()->getDbo();
		/** @var Menu $table */
		$table  = Table::getInstance('menu');
		$option = $parent->get('element');

		// If a component exists with this option in the table then we don't need to add menus
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->qn('#__menu') . ' AS ' . $db->qn('m'))
			->leftJoin($db->qn('#__extensions', 'e') . ' ON ' .
				$db->qn('m.component_id') . ' = ' . $db->qn('e.extension_id'))
			->where($db->qn('m.parent_id') . ' = ' . $db->q(1))
			->where($db->qn('m.client_id') . ' = ' . $db->q(1))
			->where($db->qn('e.type') . ' = ' . $db->q('component'))
			->where($db->qn('e.element') . ' = ' . $db->q($option));

		$db->setQuery($query);

		$existingMenus = $db->loadResult();

		if ($existingMenus)
		{
			return true;
		}

		// Let's find the extension id
		$query->clear()
			->select($db->qn('e.extension_id'))
			->from($db->qn('#__extensions', 'e'))
			->where($db->qn('e.type') . ' = ' . $db->q('component'))
			->where($db->qn('e.element') . ' = ' . $db->q($option));
		$db->setQuery($query);
		$componentId = $db->loadResult();

		// Ok, now its time to handle the menus.  Start with the component root menu, then handle submenus.
		if (method_exists($parent, 'getManifest'))
		{
			$menuElement = $parent->getManifest()->administration->menu;
		}
		else
		{
			$menuElement = $parent->get('manifest')->administration->menu;
		}

		// We need to insert the menu item as the last child of Joomla!'s menu root node. First let's make sure that
		// it exists. Normally it should be the menu item with ID = 1.
		$query      = $db->getQuery(true)
			->select($db->qn('id'))
			->from($db->qn('#__menu'))
			->where($db->qn('id') . ' = ' . $db->q(1));
		$rootItemId = $db->setQuery($query)->loadResult();

		// If we didn't find the item with ID=1 something has screwed up the menu table, e.g. a bad upgrade script. In
		// this case we can try to find the root node by title.
		if (is_null($rootItemId))
		{
			$rootItemId = null;
			$query      = $db->getQuery(true)
				->select($db->qn('id'))
				->from($db->qn('#__menu'))
				->where($db->qn('title') . ' = ' . $db->q('Menu_Item_Root'));
			$rootItemId = $db->setQuery($query, 0, 1)->loadResult();
		}

		// So, someone changed the title of the menu item too?! Let's find it by alias.
		if (is_null($rootItemId))
		{
			$rootItemId = null;
			$query      = $db->getQuery(true)
				->select($db->qn('id'))
				->from($db->qn('#__menu'))
				->where($db->qn('alias') . ' = ' . $db->q('root'));
			$rootItemId = $db->setQuery($query, 0, 1)->loadResult();
		}

		// For crying out loud, they changed the alias too? Fine! Find it by component ID.
		if (is_null($rootItemId))
		{
			$rootItemId = null;
			$query      = $db->getQuery(true)
				->select($db->qn('id'))
				->from($db->qn('#__menu'))
				->where($db->qn('component_id') . ' = ' . $db->q('0'));
			$rootItemId = $db->setQuery($query, 0, 1)->loadResult();
		}

		// Um, OK. Still no go. Let's try with minimum lft value.
		if (is_null($rootItemId))
		{
			$rootItemId = null;
			$query      = $db->getQuery(true)
				->select($db->qn('id'))
				->from($db->qn('#__menu'))
				->order($db->qn('lft') . ' ASC');
			$rootItemId = $db->setQuery($query, 0, 1)->loadResult();
		}

		// I quit. Your site's menu structure is broken. I'll just throw an error.
		if (is_null($rootItemId))
		{
			throw new Exception("Your site is broken. There is no root menu item. As a result it is impossible to create menu items. The installation of this component has failed. Please fix your database and retry!", 500);
		}

		/** @var SimpleXMLElement $menuElement */
		if ($menuElement)
		{
			$data                 = [];
			$data['menutype']     = 'main';
			$data['client_id']    = 1;
			$data['title']        = (string) trim($menuElement);
			$data['alias']        = (string) $menuElement;
			$data['link']         = 'index.php?option=' . $option;
			$data['type']         = 'component';
			$data['published']    = 0;
			$data['parent_id']    = 1;
			$data['component_id'] = $componentId;
			$data['img']          = (string) $menuElement->attributes()->img ?: 'class:component';
			$data['home']         = 0;
			$data['path']         = '';
			$data['params']       = '';
		}
		// No menu element was specified, Let's make a generic menu item
		else
		{
			$data                 = [];
			$data['menutype']     = 'main';
			$data['client_id']    = 1;
			$data['title']        = $option;
			$data['alias']        = $option;
			$data['link']         = 'index.php?option=' . $option;
			$data['type']         = 'component';
			$data['published']    = 0;
			$data['parent_id']    = 1;
			$data['component_id'] = $componentId;
			$data['img']          = 'class:component';
			$data['home']         = 0;
			$data['path']         = '';
			$data['params']       = '';
		}

		try
		{
			$table->setLocation($rootItemId, 'last-child');
		}
		catch (InvalidArgumentException $e)
		{
			$this->log($e->getMessage());

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
			$menu_ids_level1 = $db->loadColumn();

			if (empty($menu_ids_level1))
			{
				// Oops! Could not get the menu ID. Go back and rollback changes.
				JError::raiseWarning(1, $table->getError());

				return false;
			}
			else
			{
				$ids = implode(',', $menu_ids_level1);

				$query->clear()
					->select('id')
					->from('#__menu')
					->where('menutype = ' . $db->quote('main'))
					->where('client_id = 1')
					->where('type = ' . $db->quote('component'))
					->where('parent_id in (' . $ids . ')')
					->where('level = 2')
					->where('home = 0');

				$db->setQuery($query);
				$menu_ids_level2 = $db->loadColumn();

				$ids = implode(',', array_merge($menu_ids_level1, $menu_ids_level2));

				// Remove the old menu item
				$query->clear()
					->delete('#__menu')
					->where('id in (' . $ids . ')');

				$db->setQuery($query);
				$db->execute();

				// Retry creating the menu item
				$table->setLocation($rootItemId, 'last-child');

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
		$parent->getParent()->pushStep(['type' => 'menu', 'id' => $componentId]);

		/*
		 * Process SubMenus
		 */

		if (method_exists($parent, 'getManifest'))
		{
			$submenu = $parent->getManifest()->administration->submenu;
		}
		else
		{
			$submenu = $parent->get('manifest')->administration->submenu;
		}

		if (!$submenu)
		{
			return true;
		}

		$parent_id = $table->id;

		/** @var SimpleXMLElement $child */
		foreach ($submenu->menu as $child)
		{
			$data                 = [];
			$data['menutype']     = 'main';
			$data['client_id']    = 1;
			$data['title']        = (string) trim($child);
			$data['alias']        = (string) $child;
			$data['type']         = 'component';
			$data['published']    = 0;
			$data['parent_id']    = $parent_id;
			$data['component_id'] = $componentId;
			$data['img']          = (string) $child->attributes()->img ?: 'class:component';
			$data['home']         = 0;

			// Set the sub menu link
			if ((string) $child->attributes()->link)
			{
				$data['link'] = 'index.php?' . $child->attributes()->link;
			}
			else
			{
				$request = [];

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

				$qstring      = (count($request)) ? '&' . implode('&', $request) : '';
				$data['link'] = 'index.php?option=' . $option . $qstring;
			}

			$table = Table::getInstance('menu');

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
			$parent->getParent()->pushStep(['type' => 'menu', 'id' => $componentId]);
		}

		return true;
	}

	/**
	 * Make sure the Component menu items are really published!
	 *
	 * @param   ComponentAdapter  $parent
	 *
	 * @return bool
	 */
	private function _reallyPublishAdminMenuItems($parent)
	{
		$db     = $parent->getParent()->getDbo();
		$option = $parent->get('element');

		$query = $db->getQuery(true)
			->update('#__menu AS m')
			->join('LEFT', '#__extensions AS e ON m.component_id = e.extension_id')
			->set($db->qn('published') . ' = ' . $db->q(1))
			->where('m.parent_id = 1')
			->where('m.client_id = 1')
			->where('e.type = ' . $db->quote('component'))
			->where('e.element = ' . $db->quote($option));

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			// If it fails, it fails. Who cares.
		}
	}

	/**
	 * Tells Joomla! to rebuild its menu structure to make triple-sure that the Components menu items really do exist
	 * in the correct place and can really be rendered.
	 */
	private function _rebuildMenu()
	{
		/** @var Menu $table */
		$table = Table::getInstance('menu');
		$db    = $table->getDbo();

		// We need to rebuild the menu based on its root item. By default this is the menu item with ID=1. However, some
		// crappy upgrade scripts enjoy screwing it up. Hey, ho, the workaround way I go.
		$query      = $db->getQuery(true)
			->select($db->qn('id'))
			->from($db->qn('#__menu'))
			->where($db->qn('id') . ' = ' . $db->q(1));
		$rootItemId = $db->setQuery($query)->loadResult();

		if (is_null($rootItemId))
		{
			// Guess what? The Problem has happened. Let's find the root node by title.
			$rootItemId = null;
			$query      = $db->getQuery(true)
				->select($db->qn('id'))
				->from($db->qn('#__menu'))
				->where($db->qn('title') . ' = ' . $db->q('Menu_Item_Root'));
			$rootItemId = $db->setQuery($query, 0, 1)->loadResult();
		}

		if (is_null($rootItemId))
		{
			// Did they change the title too?! Let's find it by alias.
			$rootItemId = null;
			$query      = $db->getQuery(true)
				->select($db->qn('id'))
				->from($db->qn('#__menu'))
				->where($db->qn('alias') . ' = ' . $db->q('root'));
			$rootItemId = $db->setQuery($query, 0, 1)->loadResult();
		}

		if (is_null($rootItemId))
		{
			// Woah! The alias is borked, too?! Find it by component ID.
			$rootItemId = null;
			$query      = $db->getQuery(true)
				->select($db->qn('id'))
				->from($db->qn('#__menu'))
				->where($db->qn('component_id') . ' = ' . $db->q('0'));
			$rootItemId = $db->setQuery($query, 0, 1)->loadResult();
		}

		if (is_null($rootItemId))
		{
			// Your site is more of a "shite" than a "site". Let's try with minimum lft value.
			$rootItemId = null;
			$query      = $db->getQuery(true)
				->select($db->qn('id'))
				->from($db->qn('#__menu'))
				->order($db->qn('lft') . ' ASC');
			$rootItemId = $db->setQuery($query, 0, 1)->loadResult();
		}

		if (is_null($rootItemId))
		{
			// I quit. Your site is broken.
			return false;
		}

		$table->rebuild($rootItemId);
	}

	/**
	 * Deletes the assets table records for the component
	 *
	 * @param   JDatabaseDriver  $db
	 *
	 * @return  void
	 *
	 * @since   3.0.18
	 */
	private function deleteComponentAssetRecords($db)
	{
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__assets')
			->where($db->qn('name') . ' = ' . $db->q($this->componentName));
		$db->setQuery($query);

		$ids = $db->loadColumn();

		if (empty($ids))
		{
			return;
		}

		foreach ($ids as $id)
		{
			$query = $db->getQuery(true);
			$query->delete('#__assets')
				->where($db->qn('id') . ' = ' . $db->q($id));
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $exc)
			{
				// Nothing
			}
		}
	}

	/**
	 * Deletes the extensions table records for the component
	 *
	 * @param   JDatabaseDriver  $db
	 *
	 * @return  void
	 *
	 * @since   3.0.18
	 */
	private function deleteComponentExtensionRecord($db)
	{
		$query = $db->getQuery(true);
		$query->select('extension_id')
			->from('#__extensions')
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->where($db->qn('element') . ' = ' . $db->q($this->componentName));
		$db->setQuery($query);
		$ids = $db->loadColumn();

		if (empty($ids))
		{
			return;
		}

		foreach ($ids as $id)
		{
			$query = $db->getQuery(true);
			$query->delete('#__extensions')
				->where($db->qn('extension_id') . ' = ' . $db->q($id));
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $exc)
			{
				// Nothing
			}
		}
	}

	/**
	 * Deletes the menu table records for the component
	 *
	 * @param   JDatabaseDriver  $db
	 *
	 * @return  void
	 *
	 * @since   3.0.18
	 */
	private function deleteComponentMenuRecord($db)
	{
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__menu')
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->where($db->qn('menutype') . ' = ' . $db->q('main'))
			->where($db->qn('link') . ' LIKE ' . $db->q('index.php?option=' . $this->componentName));
		$db->setQuery($query);
		$ids = $db->loadColumn();

		if (empty($ids))
		{
			return;
		}

		foreach ($ids as $id)
		{
			$query = $db->getQuery(true);
			$query->delete('#__menu')
				->where($db->qn('id') . ' = ' . $db->q($id));
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $exc)
			{
				// Nothing
			}
		}
	}
}
