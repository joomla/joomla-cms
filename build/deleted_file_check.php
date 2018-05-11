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
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Detect the native operating system type.
$os = strtoupper(substr(PHP_OS, 0, 3));

if ($os === 'WIN')
{
	echo 'Sorry, this script is not supported on Windows.' . PHP_EOL;

	exit(1);
}

/*
 * Constants
 */

const PHP_TAB = "\t";

/*
 * Globals
 */

global $currentDir;
$currentDir = getcwd();

global $changedFiles;
$changedFiles = array();

global $deletedFiles;
$deletedFiles = array();

global $gitBinary;
ob_start();
passthru('which git', $gitBinary);
$gitBinary = trim(ob_get_clean());

/*
 * Functions
 */

function run_git_command($command)
{
	global $currentDir;

	chdir(dirname(__DIR__));

	ob_start();
	passthru($command);
	$return = trim(ob_get_clean());

	chdir($currentDir);

	return $return;
}

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
	echo 'Missing starting commit reference' . PHP_EOL;

	usage($argv[0]);

	exit(1);
}

// Missing the to reference?  No problem, grab the current HEAD
if (empty($options['to']))
{
	$options['to'] = run_git_command("$gitBinary rev-parse HEAD");
}

// Parse the git diff to know what files have been added, removed, or renamed
$fileDiff = explode("\n", run_git_command("$gitBinary diff --name-status {$options['from']} {$options['to']}"));

$deletedFiles = array();

foreach ($fileDiff as $file)
{
	$fileName = substr($file, 2);

	// Act on the file based on the action
	switch (substr($file, 0, 1))
	{
		// This is a new case with git 2.9 to handle renamed files
		case 'R':
			// Explode the file on the tab character; key 0 is the action (rename), key 1 is the old filename, and key 2 is the new filename
			$renamedFileData = explode("\t", $file);

			// And flag the old file as deleted
			$deletedFiles[] = "'/{$renamedFileData[1]}',";

			break;

		case 'D':
			$deletedFiles[] = "'/$fileName',";

			break;

		default:
			// Ignore file additions and modifications
			break;
	}
}

asort($deletedFiles);

// Write the deleted files list to a file for later reference
file_put_contents(__DIR__ . '/deleted_files.txt', implode("\n", $deletedFiles));

echo PHP_EOL;
echo 'There are ' . count($deletedFiles) . ' deleted files in comparison to "' . $options['from'] . '"' . PHP_EOL;
