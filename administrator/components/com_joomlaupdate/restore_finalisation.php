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
	/** We don't use it but the post-update script is using it anyway, so LET'S FAKE IT! */
	function jimport($foo = null, $bar = null)
	{
		// Do nothing
	}
}

// Fake the JFile class, mapping it to Restore's post-processing class
if (!class_exists('JFile'))
{
	abstract class JFile
	{
		public static function exists($filename)
		{
			return @file_exists($filename);
		}

		public static function delete($filename)
		{
			$postproc = AKFactory::getPostProc();
			$postproc->unlink($filename);
		}
	}
}

// Fake the JFolder class, mapping it to Restore's post-processing class
if (!class_exists('JFolder'))
{
	abstract class JFolder
	{
		public static function exists($filename)
		{
			return @is_dir($filename);
		}

		public static function delete($filename)
		{
			recursive_remove_directory($filename);
		}
	}
}

// Fake the JText class - we aren't going to show errors to people anyhow
if (!class_exists('JText'))
{
	abstract class JText
	{
		// No need for translations in a non-interactive script.
		public static function sprintf($foobar)
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
			require_once ($filePath);
		}

		// Make sure Joomla!'s code can figure out which files exist and need be removed
		clearstatcache();

		// Remove obsolete files - prevents errors occuring in some system plugins
		if (class_exists('JoomlaInstallerScript'))
		{
			$script = new JoomlaInstallerScript();
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
