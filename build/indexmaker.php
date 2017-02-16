#!/usr/bin/php
<?php

/**
 * This file will be removed in Joomla! CMS version 4.0. Developers should supply their own copy of this file if needed.
 */

if (php_sapi_name() != "cli")
{
	echo "Error: phptidy has to be run on command line with CLI SAPI\n";
	exit(1);
}

function getDirectory($path = '.', $level = 0)
{
	$iterator  = new RecursiveDirectoryIterator($path, FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS);
	$flattened = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);

	foreach ($flattened as $path => $dir)
	{
		if (!$dir->isDir())
		{
			continue;
		}

		// Add an index.html if neither an index.html nor an index.php exist
		if (!(file_exists($path . '/index.html') || file_exists($path . '/index.php')))
		{
			file_put_contents($path . '/index.html', '<!DOCTYPE html><title></title>' . "\n");
		}
	}
}

$work = $_SERVER['argv'][1];

echo "Working on directory " . $work . "\n";

getDirectory($_SERVER['argv'][1]);
