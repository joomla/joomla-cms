<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Scan;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Base\Exceptions\WarningException;
use Akeeba\Engine\Factory;
use DirectoryIterator;
use Exception;
use RuntimeException;

/* Windows system detection */
if (!defined('_AKEEBA_IS_WINDOWS'))
{
	$isWindows = DIRECTORY_SEPARATOR == '\\';

	if (function_exists('php_uname'))
	{
		$isWindows = stristr(php_uname(), 'windows');
	}

	define('_AKEEBA_IS_WINDOWS', $isWindows);
}

/**
 * A filesystem scanner which uses opendir() and is smart enough to make large directories
 * be scanned inside a step of their own.
 *
 * The idea is that if it's not the first operation of this step and the number of contained
 * directories AND files is more than double the number of allowed files per fragment, we should
 * break the step immediately.
 *
 */
class Smart extends Base
{
	public function getFiles($folder, &$position)
	{
		$registry = Factory::getConfiguration();
		// Was the breakflag set BEFORE starting? -- This workaround is required due to PHP5 defaulting to assigning variables by reference
		$breakflag_before_process = $registry->get('volatile.breakflag', false);

		// Reset break flag before continuing
		$breakflag = false;

		// Initialize variables
		$arr   = [];
		$false = false;

		if (!@is_dir($folder) && !@is_dir($folder . '/'))
		{
			return $false;
		}

		$counter    = 0;
		$registry   = Factory::getConfiguration();
		$maxCounter = $registry->get('engine.scan.smart.large_dir_threshold', 100);

		$allowBreakflag = ($registry->get('volatile.operation_counter', 0) != 0) && !$breakflag_before_process;

		if (!@is_dir($folder))
		{
			throw new WarningException('Cannot list contents of directory ' . $folder . ' -- PHP reports it as not a folder.');
		}

		if (!@is_readable($folder))
		{
			throw new WarningException('Cannot list contents of directory ' . $folder . ' -- PHP reports it as not readable.');
		}

		try
		{
			$di = new DirectoryIterator($folder);
		}
		catch (Exception $e)
		{
			throw new WarningException('Cannot list contents of directory ' . $folder . ' -- PHP\'s DirectoryIterator reports the path cannot be opened.', 0, $e);
		}

		if (!$di->valid())
		{
			throw new WarningException('Cannot list contents of directory ' . $folder . ' -- PHP\'s DirectoryIterator could open the folder but immediately reports itself as not valid. If this happens your server is about to die.');
		}

		$ds = ($folder == '') || ($folder == '/') || (@substr($folder, -1) == '/') || (@substr($folder, -1) == DIRECTORY_SEPARATOR) ? '' : DIRECTORY_SEPARATOR;

		/** @var DirectoryIterator $file */
		foreach ($di as $file)
		{
			if ($breakflag)
			{
				break;
			}

			/**
			 * If the directory entry is a link pointing somewhere outside the allowed directories per open_basedir we
			 * will get a RuntimeException (tested on PHP 5.3 onwards). Catching it lets us report the link as
			 * unreadable without suffering a PHP Fatal Error.
			 */
			try
			{
				$file->isLink();
			}
			catch (RuntimeException $e)
			{
				if (!in_array($di->getFilename(), ['.', '..']))
				{
					Factory::getLog()->warning(sprintf("Link %s is inaccessible. Check the open_basedir restrictions in your server's PHP configuration", $file->getPathname()));
				}

				continue;
			}

			if ($file->isDot())
			{
				continue;
			}

			if ($file->isDir())
			{
				continue;
			}

			$dir  = $folder . $ds . $file->getFilename();
			$data = $dir;

			if (_AKEEBA_IS_WINDOWS)
			{
				$data = Factory::getFilesystemTools()->TranslateWinPath($dir);
			}

			if ($data)
			{
				$arr[] = $data;
			}

			$counter++;

			if ($counter >= $maxCounter)
			{
				$breakflag = $allowBreakflag;
			}
		}

		// Save break flag status
		$registry->set('volatile.breakflag', $breakflag);

		return $arr;
	}

	public function getFolders($folder, &$position)
	{
		// Was the breakflag set BEFORE starting? -- This workaround is required due to PHP5 defaulting to assigning variables by reference
		$registry                 = Factory::getConfiguration();
		$breakflag_before_process = $registry->get('volatile.breakflag', false);

		// Reset break flag before continuing
		$breakflag = false;

		// Initialize variables
		$arr   = [];
		$false = false;

		if (!is_dir($folder) && !is_dir($folder . '/'))
		{
			throw new WarningException('Cannot list contents of directory ' . $folder . ' -- PHP reports it as not a folder.');
		}

		if (!@is_readable($folder))
		{
			throw new WarningException('Cannot list contents of directory ' . $folder . ' -- PHP reports it as not readable.');
		}

		$counter    = 0;
		$registry   = Factory::getConfiguration();
		$maxCounter = $registry->get('engine.scan.smart.large_dir_threshold', 100);

		$allowBreakflag = ($registry->get('volatile.operation_counter', 0) != 0) && !$breakflag_before_process;

		try
		{
			$di = new DirectoryIterator($folder);
		}
		catch (Exception $e)
		{
			throw new WarningException('Cannot list contents of directory ' . $folder . ' -- PHP\'s DirectoryIterator reports the path cannot be opened.', 0, $e);
		}

		if (!$di->valid())
		{
			throw new WarningException('Cannot list contents of directory ' . $folder . ' -- PHP\'s DirectoryIterator could open the folder but immediately reports itself as not valid. If this happens your server is about to die.');
		}

		$ds = ($folder == '') || ($folder == '/') || (@substr($folder, -1) == '/') || (@substr($folder, -1) == DIRECTORY_SEPARATOR) ? '' : DIRECTORY_SEPARATOR;

		/** @var DirectoryIterator $file */
		foreach ($di as $file)
		{
			if ($breakflag)
			{
				break;
			}

			/**
			 * If the directory entry is a link pointing somewhere outside the allowed directories per open_basedir we
			 * will get a RuntimeException (tested on PHP 5.3 onwards). Catching it lets us report the link as
			 * unreadable without suffering a PHP Fatal Error.
			 */
			try
			{
				$file->isLink();
			}
			catch (RuntimeException $e)
			{
				if (!in_array($di->getFilename(), ['.', '..']))
				{
					Factory::getLog()->warning(sprintf("Link %s is inaccessible. Check the open_basedir restrictions in your server's PHP configuration", $file->getPathname()));
				}

				continue;
			}

			if ($file->isDot())
			{
				continue;
			}

			if (!$file->isDir())
			{
				continue;
			}

			$dir  = $folder . $ds . $file->getFilename();
			$data = $dir;

			if (_AKEEBA_IS_WINDOWS)
			{
				$data = Factory::getFilesystemTools()->TranslateWinPath($dir);
			}

			if ($data)
			{
				$arr[] = $data;
			}

			$counter++;

			if ($counter >= $maxCounter)
			{
				$breakflag = $allowBreakflag;
			}
		}

		// Save break flag status
		$registry->set('volatile.breakflag', $breakflag);

		return $arr;
	}
}
