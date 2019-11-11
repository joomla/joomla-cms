<?php
/**
 * This file is used to build the list of deleted files between two reference points.
 *
 * This script requires one parameter:
 *
 * --from - The git commit reference to use as the starting point for the comparison.
 *
 * This script has one additional optional parameter:
 *
 * --to - The git commit reference to use as the ending point for the comparison.
 *
 * The reference parameters may be any valid identifier (i.e. a branch, tag, or commit SHA)
 *
 * @package    Joomla.Build
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/*
 * Constants
 */
const PHP_TAB = "\t";

function usage($command)
{
	echo PHP_EOL;
	echo 'Usage: php ' . $command . ' [options]' . PHP_EOL;
	echo PHP_TAB . '--from <ref>:' . PHP_TAB . 'Starting commit reference (branch/tag)' . PHP_EOL;
	echo PHP_TAB . '--to <ref>:' . PHP_TAB . 'Ending commit reference (branch/tag) [optional]' . PHP_EOL;
	echo PHP_EOL;
}

/*
 * This is where the magic happens
 */

$options = getopt('', array('from:', 'to::'));

// We need the from reference, otherwise we're doomed to fail
if (empty($options['from']))
{
	echo PHP_EOL;
	echo 'Missing starting directory' . PHP_EOL;

	usage($argv[0]);

	exit(1);
}

// Missing the to reference?  No problem, grab the current HEAD
if (empty($options['to']))
{
	echo PHP_EOL;
	echo 'Missing ending directory' . PHP_EOL;

	usage($argv[0]);

	exit(1);
}

// Directories to skip for the check (needs to include anything from J3 we want to keep)
$previousReleaseExclude = [
	$options['from'] . '/administrator/components/com_search',
	$options['from'] . '/components/com_search',
	$options['from'] . '/plugins/search',
	$options['from'] . '/installation'
];

/**
 * @param   SplFileInfo                      $file      The file being checked
 * @param   mixed                            $key       ?
 * @param   RecursiveCallbackFilterIterator  $iterator  The iterator being processed
 *
 * @return bool True if you need to recurse or if the item is acceptable
 */
$previousReleaseFilter = function ($file, $key, $iterator) use ($previousReleaseExclude) {
	if ($iterator->hasChildren() && !in_array($file->getPathname(), $previousReleaseExclude))
	{
		return true;
	}

	return $file->isFile();
};

// Directories to skip for the check
$newReleaseExclude = [
	$options['to'] . '/installation'
];

/**
 * @param   SplFileInfo                      $file      The file being checked
 * @param   mixed                            $key       ?
 * @param   RecursiveCallbackFilterIterator  $iterator  The iterator being processed
 *
 * @return bool True if you need to recurse or if the item is acceptable
 */
$newReleaseFilter = function ($file, $key, $iterator) use ($newReleaseExclude) {
	if ($iterator->hasChildren() && !in_array($file->getPathname(), $newReleaseExclude))
	{
		return true;
	}

	return $file->isFile();
};

$previousReleaseDirIterator = new RecursiveDirectoryIterator($options['from'], RecursiveDirectoryIterator::SKIP_DOTS);
$previousReleaseIterator = new RecursiveIteratorIterator(
	new RecursiveCallbackFilterIterator($previousReleaseDirIterator, $previousReleaseFilter),
	RecursiveIteratorIterator::SELF_FIRST
);
$previousReleaseFiles = [];
$previousReleaseFolders = [];

foreach ($previousReleaseIterator as $info)
{
	if ($info->isDir())
	{
		$previousReleaseFolders[] = "'" . str_replace($options['from'], '', $info->getPathname()) . "',";
		continue;
	}

	$previousReleaseFiles[] = "'" . str_replace($options['from'], '', $info->getPathname()) . "',";
}

$newReleaseDirIterator = new RecursiveDirectoryIterator($options['to'], RecursiveDirectoryIterator::SKIP_DOTS);
$newReleaseIterator = new RecursiveIteratorIterator(
	new RecursiveCallbackFilterIterator($newReleaseDirIterator, $newReleaseFilter),
	RecursiveIteratorIterator::SELF_FIRST
);
$newReleaseFiles = [];
$newReleaseFolders = [];

foreach ($newReleaseIterator as $info)
{
	if ($info->isDir())
	{
		$newReleaseFolders[] = "'" . str_replace($options['to'], '', $info->getPathname()) . "',";
		continue;
	}

	$newReleaseFiles[] = "'" . str_replace($options['to'], '', $info->getPathname()) . "',";
}

$filesDifference = array_diff($previousReleaseFiles, $newReleaseFiles);

$foldersDifference = array_diff($previousReleaseFolders, $newReleaseFolders);

asort($filesDifference);
asort($foldersDifference);

// Write the deleted files list to a file for later reference
file_put_contents(__DIR__ . '/deleted_files.txt', implode("\n", $filesDifference));
file_put_contents(__DIR__ . '/deleted_folders.txt', implode("\n", $foldersDifference));

echo PHP_EOL;
echo 'There are ' . count($filesDifference) . ' deleted files and ' . count($foldersDifference) .  ' deleted folders in comparison to "' . $options['from'] . '"' . PHP_EOL;
