<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Important Notes:
 * - Unlike other files, this file requires multiple namespace declarations in order to overload core classes during the update process
 * - Also unlike other files, the normal constant defined checks must be within the global namespace declaration and can't be outside of it
 */

namespace
{
	// Require the restoration environment or fail cold. Prevents direct web access.
	\defined('_AKEEBA_RESTORATION') or die();

	// Fake a miniature Joomla environment
	if (!\defined('_JEXEC'))
	{
		\define('_JEXEC', 1);
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
		 * @since   1.7.0
		 */
		function jimport($path, $base = null)
		{
			// Do nothing
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
			if (!\defined('JPATH_ROOT'))
			{
				\define('JPATH_ROOT', $siteRoot);
			}

			$filePath = JPATH_ROOT . '/administrator/components/com_admin/script.php';

			if (file_exists($filePath))
			{
				require_once $filePath;
			}

			// Make sure Joomla!'s code can figure out which files exist and need be removed
			clearstatcache();

			// Remove obsolete files - prevents errors occurring in some system plugins
			if (class_exists('JoomlaInstallerScript'))
			{
				(new JoomlaInstallerScript)->deleteUnexistingFiles();
			}
		}
	}
}

namespace Joomla\CMS\Filesystem
{
	// Fake the JFile class, mapping it to Restore's post-processing class
	if (!class_exists('\Joomla\CMS\Filesystem\File'))
	{
		/**
		 * JFile mock class proxying behaviour in the post-upgrade script to that of either native PHP or restore.php
		 *
		 * @since  3.5.1
		 */
		abstract class File
		{
			/**
			 * Proxies checking a file exists to the native php version
			 *
			 * @param   string  $fileName  The path to the file to be checked
			 *
			 * @return  boolean
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
			 * @return  boolean
			 *
			 * @since   3.5.1
			 */
			public static function delete($fileName)
			{
				/** @var \AKPostprocDirect $postproc */
				$postproc = \AKFactory::getPostProc();
				$postproc->unlink($fileName);
			}
			/**
			 * Proxies moving a file to the restore.php version
			 *
			 * @param   string  $src   The path to the source file
			 * @param   string  $dest  The path to the destination file
			 *
			 * @return  boolean  True on success
			 *
			 * @since   4.0.1
			 */
			public static function move($src, $dest)
			{
				/** @var \AKPostprocDirect $postproc */
				$postproc = \AKFactory::getPostProc();
				$postproc->rename($src, $dest);
			}

			/**
			 * Invalidate opcache for a newly written/deleted file immediately, if opcache* functions exist and if this was a PHP file.
			 *
			 * @param   string  $filepath   The path to the file just written to, to flush from opcache
			 * @param   boolean $force      If set to true, the script will be invalidated regardless of whether invalidation is necessary
			 *
			 * @return  boolean TRUE if the opcode cache for script was invalidated/nothing to invalidate,
			 *                  or FALSE if the opcode cache is disabled or other conditions returning
			 *                  FALSE from opcache_invalidate (like file not found).
			 *
			 * @since  4.0.2
			 */
			public static function invalidateFileCache($filepath, $force = true)
			{
				if ('.php' === strtolower(substr($filepath, -4)))
				{
					$postproc = \AKFactory::getPostProc();
					$postproc->clearFileInOPCache($filepath);
				}

				return false;
			}

		}
	}

	// Fake the Folder class, mapping it to Restore's post-processing class
	if (!class_exists('\Joomla\CMS\Filesystem\Folder'))
	{
		/**
		 * Folder mock class proxying behaviour in the post-upgrade script to that of either native PHP or restore.php
		 *
		 * @since  3.5.1
		 */
		abstract class Folder
		{
			/**
			 * Proxies checking a folder exists to the native php version
			 *
			 * @param   string  $folderName  The path to the folder to be checked
			 *
			 * @return  boolean
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
}

namespace Joomla\CMS\Language
{
	// Fake the Text class - we aren't going to show errors to people anyhow
	if (!class_exists('\Joomla\CMS\Language\Text'))
	{
		/**
		 * Text mock class proxying behaviour in the post-upgrade script to that of either native PHP or restore.php
		 *
		 * @since  3.5.1
		 */
		abstract class Text
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
}
