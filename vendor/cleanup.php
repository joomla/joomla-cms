<?php
/**
 * Composer cleanup script.
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Remove a folder.
 *
 * @param   string  $path  The folder path.
 *
 * @return  boolean
 *
 * @since   3.3
 */
function removeFolder($path)
{
	$iterator = new RecursiveDirectoryIterator($path);

	foreach (new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST) as $artifact)
	{
		/* @var $artifact SplFileInfo  */
		if ($artifact->isDir())
		{
			rmdir($artifact->getPathname());
		}
		else
		{
			unlink($artifact->getPathname());
		}
	}

    return rmdir($path);
}

/**
 * Remove a file.
 *
 * @param   string  $path  The folder path.
 *
 * @return  boolean
 *
 * @since   3.3
 */
function removeFile($path)
{
	return unlink($path);
}

echo "\nStarting package cleanup.";

// The paths to scan in which to clean up files and folders.
$paths = array(
	'/joomla/di/Joomla/DI',
);

// The folders to remove from the paths, if present.
$removeFolders = array(
	'/Tests',
	'/docs',
);

// The files to remove from the paths, if present.
$removeFiles = array(
	'/README.md',
	'/phpunit.xml.dist',
);

foreach ($paths as $path)
{
	foreach ($removeFolders as $artifact)
	{
		$fullPath = __DIR__ . $path . $artifact;

		if (is_dir($fullPath))
		{
			echo "\nRemoving `$fullPath`";
			echo removeFolder($fullPath) ? 'successful' : 'failed';
		}
	}

	foreach ($removeFiles as $artifact)
	{
		$fullPath = __DIR__ . $path . $artifact;

		if (file_exists($fullPath))
		{
			echo "\nRemoving `$fullPath` - ";
			echo removeFile($fullPath) ? 'successful' : 'failed';
		}
	}
}

echo "\nDone\n";
