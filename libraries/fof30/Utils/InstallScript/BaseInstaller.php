<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Utils\InstallScript;

defined('_JEXEC') || die;

use DirectoryIterator;
use Exception;
use FOFTemplateUtils;
use JLoader;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Log\Log;

class BaseInstaller
{
	/**
	 * The minimum PHP version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumPHPVersion = '7.1.0';

	/**
	 * The minimum Joomla! version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumJoomlaVersion = '3.3.0';

	/**
	 * The maximum Joomla! version this extension can be installed on
	 *
	 * @var   string
	 */
	protected $maximumJoomlaVersion = '4.0.99';

	/**
	 * Post-installation message definitions for Joomla! 3.2 or later.
	 *
	 * This array contains the message definitions for the Post-installation Messages component added in Joomla! 3.2 and
	 * later versions. Each element is also a hashed array. For the keys used in these message definitions please see
	 * addPostInstallationMessage
	 *
	 * @var   array
	 */
	protected $postInstallationMessages = [];

	/**
	 * Recursively copy a bunch of files, but only if the source and target file have a different size.
	 *
	 * @param   string  $source   Path to copy FROM
	 * @param   string  $dest     Path to copy TO
	 * @param   array   $ignored  List of entries to ignore (first level entries are taken into account)
	 *
	 * @return  void
	 */
	protected function recursiveConditionalCopy($source, $dest, $ignored = [])
	{
		// Make sure source and destination exist
		if (!@is_dir($source))
		{
			return;
		}

		if (!@is_dir($dest))
		{
			if (!@mkdir($dest, 0755))
			{
				Folder::create($dest, 0755);
			}
		}

		if (!@is_dir($dest))
		{
			$this->log(__CLASS__ . ": Cannot create folder $dest");

			return;
		}

		// List the contents of the source folder
		try
		{
			$di = new DirectoryIterator($source);
		}
		catch (Exception $e)
		{
			return;
		}

		// Process each entry
		foreach ($di as $entry)
		{
			// Ignore dot dirs (. and ..)
			if ($entry->isDot())
			{
				continue;
			}

			$sourcePath = $entry->getPathname();
			$fileName   = $entry->getFilename();

			// Do not copy ignored files
			if (!empty($ignored) && in_array($fileName, $ignored))
			{
				continue;
			}

			// If it's a directory do a recursive copy
			if ($entry->isDir())
			{
				$this->recursiveConditionalCopy($sourcePath, $dest . DIRECTORY_SEPARATOR . $fileName);

				continue;
			}

			// If it's a file check if it's missing or identical
			$mustCopy   = false;
			$targetPath = $dest . DIRECTORY_SEPARATOR . $fileName;

			if (!@is_file($targetPath))
			{
				$mustCopy = true;
			}
			else
			{
				$sourceSize = @filesize($sourcePath);
				$targetSize = @filesize($targetPath);

				$mustCopy = $sourceSize != $targetSize;
			}

			if (!$mustCopy)
			{
				continue;
			}

			if (!@copy($sourcePath, $targetPath))
			{
				if (!File::copy($sourcePath, $targetPath))
				{
					$this->log(__CLASS__ . ": Cannot copy $sourcePath to $targetPath");
				}
			}
		}
	}

	/**
	 * Try to log a warning / error with Joomla
	 *
	 * @param   string  $message   The message to write to the log
	 * @param   bool    $error     Is this an error? If not, it's a warning. (default: false)
	 * @param   string  $category  Log category, default jerror
	 *
	 * @return  void
	 */
	protected function log($message, $error = false, $category = 'jerror')
	{
		// Just in case...
		if (!class_exists('JLog', true))
		{
			return;
		}

		$priority = $error ? Log::ERROR : Log::WARNING;

		try
		{
			Log::add($message, $priority, $category);
		}
		catch (Exception $e)
		{
			// Swallow the exception.
		}
	}

	/**
	 * Check that the server meets the minimum PHP version requirements.
	 *
	 * @return  bool
	 */
	protected function checkPHPVersion()
	{
		if (!empty($this->minimumPHPVersion))
		{
			if (defined('PHP_VERSION'))
			{
				$version = PHP_VERSION;
			}
			elseif (function_exists('phpversion'))
			{
				$version = phpversion();
			}
			else
			{
				$version = '5.0.0'; // all bets are off!
			}

			if (!version_compare($version, $this->minimumPHPVersion, 'ge'))
			{
				$msg = "<p>You need PHP $this->minimumPHPVersion or later to install this extension</p>";

				$this->log($msg);

				return false;
			}
		}

		return true;
	}

	/**
	 * Check the minimum and maximum Joomla! versions for this extension
	 *
	 * @return  bool
	 */
	protected function checkJoomlaVersion()
	{
		if (!empty($this->minimumJoomlaVersion) && !version_compare(JVERSION, $this->minimumJoomlaVersion, 'ge'))
		{
			$msg = "<p>You need Joomla! $this->minimumJoomlaVersion or later to install this extension</p>";

			$this->log($msg);

			return false;
		}

		// Check the maximum Joomla! version
		if (!empty($this->maximumJoomlaVersion) && !version_compare(JVERSION, $this->maximumJoomlaVersion, 'le'))
		{
			$msg = "<p>You need Joomla! $this->maximumJoomlaVersion or earlier to install this extension</p>";

			$this->log($msg);

			return false;
		}

		return true;
	}

	/**
	 * Clear PHP opcode caches
	 *
	 * @return  void
	 */
	protected function clearOpcodeCaches()
	{
		// Always reset the OPcache if it's enabled. Otherwise there's a good chance the server will not know we are
		// replacing .php scripts. This is a major concern since PHP 5.5 included and enabled OPcache by default.
		if (function_exists('opcache_reset'))
		{
			opcache_reset();
		}
		// Also do that for APC cache
		elseif (function_exists('apc_clear_cache'))
		{
			@apc_clear_cache();
		}
	}

	/**
	 * Get the dependencies for a package from the #__akeeba_common table
	 *
	 * @param   string  $package  The package
	 *
	 * @return  array  The dependencies
	 */
	protected function getDependencies($package)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select($db->qn('value'))
			->from($db->qn('#__akeeba_common'))
			->where($db->qn('key') . ' = ' . $db->q($package));

		try
		{
			$dependencies = $db->setQuery($query)->loadResult();
			$dependencies = json_decode($dependencies, true);

			if (empty($dependencies))
			{
				$dependencies = [];
			}
		}
		catch (Exception $e)
		{
			$dependencies = [];
		}

		return $dependencies;
	}

	/**
	 * Sets the dependencies for a package into the #__akeeba_common table
	 *
	 * @param   string  $package       The package
	 * @param   array   $dependencies  The dependencies list
	 */
	protected function setDependencies($package, array $dependencies)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->delete('#__akeeba_common')
			->where($db->qn('key') . ' = ' . $db->q($package));

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			// Do nothing if the old key wasn't found
		}

		$object = (object) [
			'key'   => $package,
			'value' => json_encode($dependencies),
		];

		try
		{
			$db->insertObject('#__akeeba_common', $object, 'key');
		}
		catch (Exception $e)
		{
			// Do nothing if the old key wasn't found
		}
	}

	/**
	 * Adds a package dependency to #__akeeba_common
	 *
	 * @param   string  $package     The package
	 * @param   string  $dependency  The dependency to add
	 */
	protected function addDependency($package, $dependency)
	{
		$dependencies = $this->getDependencies($package);

		if (!in_array($dependency, $dependencies))
		{
			$dependencies[] = $dependency;

			$this->setDependencies($package, $dependencies);
		}
	}

	/**
	 * Removes a package dependency from #__akeeba_common
	 *
	 * @param   string  $package     The package
	 * @param   string  $dependency  The dependency to remove
	 */
	protected function removeDependency($package, $dependency)
	{
		$dependencies = $this->getDependencies($package);

		if (in_array($dependency, $dependencies))
		{
			$index = array_search($dependency, $dependencies);
			unset($dependencies[$index]);

			$this->setDependencies($package, $dependencies);
		}
	}

	/**
	 * Do I have a dependency for a package in #__akeeba_common
	 *
	 * @param   string  $package     The package
	 * @param   string  $dependency  The dependency to check for
	 *
	 * @return bool
	 */
	protected function hasDependency($package, $dependency)
	{
		$dependencies = $this->getDependencies($package);

		return in_array($dependency, $dependencies);
	}

	/**
	 * Adds or updates a post-installation message (PIM) definition for Joomla! 3.2 or later. You can use this in your
	 * post-installation script using this code:
	 *
	 * The $options array contains the following mandatory keys:
	 *
	 * extension_id        The numeric ID of the extension this message is for (see the #__extensions table)
	 *
	 * type                One of message, link or action. Their meaning is:
	 *                    message        Informative message. The user can dismiss it.
	 *                    link        The action button links to a URL. The URL is defined in the action parameter.
	 *                  action      A PHP action takes place when the action button is clicked. You need to specify the
	 *                              action_file (RAD path to the PHP file) and action (PHP function name) keys. See
	 *                              below for more information.
	 *
	 * title_key        The JText language key for the title of this PIM
	 *                    Example: COM_FOOBAR_POSTINSTALL_MESSAGEONE_TITLE
	 *
	 * description_key    The JText language key for the main body (description) of this PIM
	 *                    Example: COM_FOOBAR_POSTINSTALL_MESSAGEONE_DESCRIPTION
	 *
	 * action_key        The JText language key for the action button. Ignored and not required when type=message
	 *                    Example: COM_FOOBAR_POSTINSTALL_MESSAGEONE_ACTION
	 *
	 * language_extension    The extension name which holds the language keys used above. For example, com_foobar,
	 *                    mod_something, plg_system_whatever, tpl_mytemplate
	 *
	 * language_client_id   Should we load the front-end (0) or back-end (1) language keys?
	 *
	 * version_introduced   Which was the version of your extension where this message appeared for the first time?
	 *                        Example: 3.2.1
	 *
	 * enabled              Must be 1 for this message to be enabled. If you omit it, it defaults to 1.
	 *
	 * condition_file        The RAD path to a PHP file containing a PHP function which determines whether this message
	 *                        should be shown to the user. @param   array  $options  See description
	 *
	 * @return  void
	 *
	 * @throws Exception
	 * @see                   Template::parsePath() for RAD path format. Joomla! will include this file
	 *                        before calling the function defined in the action key below.
	 *                        Example:   admin://components/com_foobar/helpers/postinstall.php
	 *
	 * action                The name of a PHP function which will be used to run the action of this PIM. This must be
	 * a
	 *                      simple PHP user function (not a class method, static method etc) which returns no result.
	 *                        Example: com_foobar_postinstall_messageone_action
	 *
	 * @see                   Template::parsePath() for RAD path format. Joomla!
	 *                        will include this file before calling the condition_method.
	 *                      Example:   admin://components/com_foobar/helpers/postinstall.php
	 *
	 * condition_method     The name of a PHP function which will be used to determine whether to show this message to
	 *                      the user. This must be a simple PHP user function (not a class method, static method etc)
	 *                        which returns true to show the message and false to hide it. This function is defined in
	 *                        the condition_file. Example: com_foobar_postinstall_messageone_condition
	 *
	 * When type=message no additional keys are required.
	 *
	 * When type=link the following additional keys are required:
	 *
	 * action                The URL which will open when the user clicks on the PIM's action button
	 *                        Example:    index.php?option=com_foobar&view=tools&task=installSampleData
	 *
	 * Then type=action the following additional keys are required:
	 *
	 * action_file            The RAD path to a PHP file containing a PHP function which performs the action of this
	 * PIM.
	 *
	 */
	protected function addPostInstallationMessage(array $options)
	{
		// Make sure there are options set
		if (!is_array($options))
		{
			throw new Exception('Post-installation message definitions must be of type array', 500);
		}

		// Initialise array keys
		$defaultOptions = [
			'extension_id'       => '',
			'type'               => '',
			'title_key'          => '',
			'description_key'    => '',
			'action_key'         => '',
			'language_extension' => '',
			'language_client_id' => '',
			'action_file'        => '',
			'action'             => '',
			'condition_file'     => '',
			'condition_method'   => '',
			'version_introduced' => '',
			'enabled'            => '1',
		];

		$options = array_merge($defaultOptions, $options);

		// Array normalisation. Removes array keys not belonging to a definition.
		$defaultKeys = array_keys($defaultOptions);
		$allKeys     = array_keys($options);
		$extraKeys   = array_diff($allKeys, $defaultKeys);

		if (!empty($extraKeys))
		{
			foreach ($extraKeys as $key)
			{
				unset($options[$key]);
			}
		}

		// Normalisation of integer values
		$options['extension_id']       = (int) $options['extension_id'];
		$options['language_client_id'] = (int) $options['language_client_id'];
		$options['enabled']            = (int) $options['enabled'];

		// Normalisation of 0/1 values
		foreach (['language_client_id', 'enabled'] as $key)
		{
			$options[$key] = $options[$key] ? 1 : 0;
		}

		// Make sure there's an extension_id
		if (!(int) $options['extension_id'])
		{
			throw new Exception('Post-installation message definitions need an extension_id', 500);
		}

		// Make sure there's a valid type
		if (!in_array($options['type'], ['message', 'link', 'action']))
		{
			throw new Exception('Post-installation message definitions need to declare a type of message, link or action', 500);
		}

		// Make sure there's a title key
		if (empty($options['title_key']))
		{
			throw new Exception('Post-installation message definitions need a title key', 500);
		}

		// Make sure there's a description key
		if (empty($options['description_key']))
		{
			throw new Exception('Post-installation message definitions need a description key', 500);
		}

		// If the type is anything other than message you need an action key
		if (($options['type'] != 'message') && empty($options['action_key']))
		{
			throw new Exception('Post-installation message definitions need an action key when they are of type "' . $options['type'] . '"', 500);
		}

		// You must specify the language extension
		if (empty($options['language_extension']))
		{
			throw new Exception('Post-installation message definitions need to specify which extension contains their language keys', 500);
		}

		// The action file and method are only required for the "action" type
		if ($options['type'] == 'action')
		{
			if (empty($options['action_file']))
			{
				throw new Exception('Post-installation message definitions need an action file when they are of type "action"', 500);
			}

			$file_path = FOFTemplateUtils::parsePath($options['action_file'], true);

			if (!@is_file($file_path))
			{
				throw new Exception('The action file ' . $options['action_file'] . ' of your post-installation message definition does not exist', 500);
			}

			if (empty($options['action']))
			{
				throw new Exception('Post-installation message definitions need an action (function name) when they are of type "action"', 500);
			}
		}

		if ($options['type'] == 'link')
		{
			if (empty($options['link']))
			{
				throw new Exception('Post-installation message definitions need an action (URL) when they are of type "link"', 500);
			}
		}

		// The condition file and method are only required when the type is not "message"
		if ($options['type'] != 'message')
		{
			if (empty($options['condition_file']))
			{
				throw new Exception('Post-installation message definitions need a condition file when they are of type "' . $options['type'] . '"', 500);
			}

			$file_path = FOFTemplateUtils::parsePath($options['condition_file'], true);

			if (!@is_file($file_path))
			{
				throw new Exception('The condition file ' . $options['condition_file'] . ' of your post-installation message definition does not exist', 500);
			}

			if (empty($options['condition_method']))
			{
				throw new Exception('Post-installation message definitions need a condition method (function name) when they are of type "' . $options['type'] . '"', 500);
			}
		}

		// Check if the definition exists
		$tableName = '#__postinstall_messages';

		$db          = Factory::getDbo();
		$query       = $db->getQuery(true)
			->select('*')
			->from($db->qn($tableName))
			->where($db->qn('extension_id') . ' = ' . $db->q($options['extension_id']))
			->where($db->qn('type') . ' = ' . $db->q($options['type']))
			->where($db->qn('title_key') . ' = ' . $db->q($options['title_key']));
		$existingRow = $db->setQuery($query)->loadAssoc();

		// Is the existing definition the same as the one we're trying to save (ignore the enabled flag)?
		if (!empty($existingRow))
		{
			$same = true;

			foreach ($options as $k => $v)
			{
				if ($k == 'enabled')
				{
					continue;
				}

				if ($existingRow[$k] != $v)
				{
					$same = false;
					break;
				}
			}

			// Trying to add the same row as the existing one; quit
			if ($same)
			{
				return;
			}

			// Otherwise it's not the same row. Remove the old row before insert a new one.
			$query = $db->getQuery(true)
				->delete($db->qn($tableName))
				->where($db->q('extension_id') . ' = ' . $db->q($options['extension_id']))
				->where($db->q('type') . ' = ' . $db->q($options['type']))
				->where($db->q('title_key') . ' = ' . $db->q($options['title_key']));
			$db->setQuery($query)->execute();
		}

		// Insert the new row
		$options = (object) $options;
		$db->insertObject($tableName, $options);
	}

	/**
	 * Applies the post-installation messages for Joomla! 3.2 or later
	 *
	 * @return  void
	 */
	protected function _applyPostInstallationMessages()
	{
		// Make sure it's Joomla! 3.2.0 or later
		if (!version_compare(JVERSION, '3.2.0', 'ge'))
		{
			return;
		}

		// Make sure there are post-installation messages
		if (empty($this->postInstallationMessages))
		{
			return;
		}

		// Get the extension ID for our component
		$db    = Factory::getDbo();
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

		if (empty($ids))
		{
			return;
		}

		$extension_id = array_shift($ids);

		foreach ($this->postInstallationMessages as $message)
		{
			$message['extension_id'] = $extension_id;
			$this->addPostInstallationMessage($message);
		}
	}

	/**
	 * Uninstalls the post-installation messages for Joomla! 3.2 or later
	 *
	 * @return  void
	 */
	protected function uninstallPostInstallationMessages()
	{
		// Make sure it's Joomla! 3.2.0 or later
		if (!version_compare(JVERSION, '3.2.0', 'ge'))
		{
			return;
		}

		// Make sure there are post-installation messages
		if (empty($this->postInstallationMessages))
		{
			return;
		}

		// Get the extension ID for our component
		$db    = Factory::getDbo();
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

		if (empty($ids))
		{
			return;
		}

		$extension_id = array_shift($ids);

		$query = $db->getQuery(true)
			->delete($db->qn('#__postinstall_messages'))
			->where($db->qn('extension_id') . ' = ' . $db->q($extension_id));

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			return;
		}
	}
}
