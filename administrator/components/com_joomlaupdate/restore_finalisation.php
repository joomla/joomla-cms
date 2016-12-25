<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Require the restoration environment or fail cold. Prevents direct web access.
defined('_AKEEBA_RESTORATION') or die();

// Fake a miniature Joomla environment
if (!defined('_JEXEC'))
{
	define('_JEXEC', 1);
}

if (!function_exists('jimport'))
{
	/**
	 * We don't use it but the post-update script is using it anyway, so LET'S FAKE IT!
	 *
	 * @param   string  $path  A dot syntax path.
	 * @param   string  $base  Search this directory for the class.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	function jimport($path, $base = null)
	{
		// Do nothing
	}
}

// Fake the JFile class, mapping it to Restore's post-processing class
if (!class_exists('JFile'))
{
	/**
	 * JFile mock class proxing behaviour in the post-upgrade script to that of either native PHP or restore.php
	 *
	 * @since  3.5.1
	 */
	abstract class JFile
	{
		/**
		 * Proxies checking a folder exists to the native php version
		 *
		 * @param   string  $fileName  The path to the file to be checked
		 *
		 * @return  bool
		 *
		 * @since   3.5.1
		 */
		public static function exists($fileName)
		{
			return @file_exists($fileName);
		}

		/**
		 * Proxies deleting a file to the restore.php version
		 *
		 * @param   string  $fileName  The path to the file to be deleted
		 *
		 * @return  bool
		 *
		 * @since   3.5.1
		 */
		public static function delete($fileName)
		{
			$postproc = AKFactory::getPostProc();
			$postproc->unlink($fileName);
		}
	}
}

// Fake the JFolder class, mapping it to Restore's post-processing class
if (!class_exists('JFolder'))
{
	/**
	 * JFolder mock class proxing behaviour in the post-upgrade script to that of either native PHP or restore.php
	 *
	 * @since  3.5.1
	 */
	abstract class JFolder
	{
		/**
		 * Proxies checking a folder exists to the native php version
		 *
		 * @param   string  $folderName  The path to the folder to be checked
		 *
		 * @return  bool
		 *
		 * @since   3.5.1
		 */
		public static function exists($folderName)
		{
			return @is_dir($folderName);
		}

		/**
		 * Proxies deleting a folder to the restore.php version
		 *
		 * @param   string  $folderName  The path to the folder to be deleted
		 *
		 * @return  void
		 *
		 * @since   3.5.1
		 */
		public static function delete($folderName)
		{
			recursive_remove_directory($folderName);
		}
	}
}

// Fake the JText class - we aren't going to show errors to people anyhow
if (!class_exists('JText'))
{
	/**
	 * JText mock class proxing behaviour in the post-upgrade script to that of either native PHP or restore.php
	 *
	 * @since  3.5.1
	 */
	abstract class JText
	{
		/**
		 * No need for translations in a non-interactive script, so always return an empty string here
		 *
		 * @param   string  $text  A language constant
		 *
		 * @return  string
		 *
		 * @since   3.5.1
		 */
		public static function sprintf($text)
		{
			return '';
		}
	}
}

if (!function_exists('finalizeRestore'))
{
	/**
	 * Run part of the Joomla! finalisation script, namely the part that cleans up unused files/folders
	 *
	 * @param   string  $siteRoot     The root to the Joomla! site
	 * @param   string  $restorePath  The base path to restore.php
	 *
	 * @return  void
	 *
	 * @since   3.5.1
	 */
	function finalizeRestore($siteRoot, $restorePath)
	{
		if (!defined('JPATH_ROOT'))
		{
			define('JPATH_ROOT', $siteRoot);
		}

		$filePath = JPATH_ROOT . '/administrator/components/com_admin/script.php';

		if (file_exists($filePath))
		{
			require_once $filePath;
		}

		// Make sure Joomla!'s code can figure out which files exist and need be removed
		clearstatcache();

		// Remove obsolete files - prevents errors occuring in some system plugins
		if (class_exists('JoomlaInstallerScript'))
		{
			$script = new JoomlaInstallerScript;
			$script->deleteUnexistingFiles();
		}

		// Clear OPcache
		if (function_exists('opcache_reset'))
		{
			opcache_reset();
		}
		elseif (function_exists('apc_clear_cache'))
		{
			@apc_clear_cache();
		}
	}
}
