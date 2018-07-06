<?php
/**
 * Script used to build Joomla distribution archive packages
 * Builds packages in tmp/packages folder (for example, 'build/tmp/packages')
 *
 * Note: the new package must be tagged in your git repository BEFORE doing this
 * It uses the git tag for the new version, not trunk.
 *
 * This script is designed to be run in CLI on Linux or Mac OS X.
 * Make sure your default umask is 022 to create archives with correct permissions.
 *
 * Steps:
 * 1. Tag new release in the local git repository (for example, "git tag 2.5.1")
 * 2. Set the $version and $release variables for the new version.
 * 3. Run from CLI as: 'php build.php" from build directory.
 * 4. Check the archives in the tmp directory.
 *
 * @package    Joomla.Build
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Version;

const PHP_TAB = "\t";

function usage($command)
{
	echo PHP_EOL;
	echo 'Usage: php ' . $command . ' [options]' . PHP_EOL;
	echo PHP_TAB . '[options]:'.PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--remote <remote>:' . PHP_TAB . 'The git remote reference to build from (ex: `tags/3.8.6`, `4.0-dev`), defaults to the most recent tag for the repository' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--exclude-zip:' . PHP_TAB . PHP_TAB . 'Exclude the generation of .zip packages' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--exclude-gzip:' . PHP_TAB . PHP_TAB . 'Exclude the generation of .tar.gz packages' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--exclude-bzip2:' . PHP_TAB . 'Exclude the generation of .tar.bz2 packages' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--help:' . PHP_TAB . PHP_TAB . PHP_TAB . 'Show this help output' . PHP_EOL;
	echo PHP_EOL;
}

if (version_compare(PHP_VERSION, '5.4', '<'))
{
	echo "The build script requires PHP 5.4.\n";

	exit(1);
}

$time = time();

// Set path to git binary (e.g., /usr/local/git/bin/git or /usr/bin/git)
ob_start();
passthru('which git', $systemGit);
$systemGit = trim(ob_get_clean());

// Make sure file and folder permissions are set correctly
umask(022);

// Shortcut the paths to the repository root and build folder
$repo = dirname(__DIR__);
$here = __DIR__;

// Set paths for the build packages
$tmp      = $here . '/tmp';
$fullpath = $tmp . '/' . $time;

// Parse input options
$options = getopt('', ['help', 'remote::', 'exclude-zip', 'exclude-gzip', 'exclude-bzip2']);

$remote       = isset($options['remote']) ? $options['remote'] : false;
$excludeZip   = isset($options['exclude-zip']);
$excludeGzip  = isset($options['exclude-gzip']);
$excludeBzip2 = isset($options['exclude-bzip2']);
$showHelp     = isset($options['help']);

if ($showHelp)
{
	usage($argv[0]);
	die;
}

// If not given a remote, assume we are looking for the latest local tag
if (!$remote)
{
	chdir($repo);
	$tagVersion = system($systemGit . ' describe --tags `' . $systemGit . ' rev-list --tags --max-count=1`', $tagVersion);
	$remote = 'tags/' . $tagVersion;
	chdir($here);
}

echo "Start build for remote $remote.\n";
echo "Delete old release folder.\n";
system('rm -rf ' . $tmp);
mkdir($tmp);
mkdir($fullpath);

echo "Copy the files from the git repository.\n";
chdir($repo);
system($systemGit . ' archive ' . $remote . ' | tar -x -C ' . $fullpath);

// Import the version class to set the version information
define('JPATH_PLATFORM', 1);
require_once $fullpath . '/libraries/src/Version.php';

// Set version information for the build
$version     = Version::MAJOR_VERSION . '.' . Version::MINOR_VERSION;
$release     = Version::PATCH_VERSION;
$fullVersion = (new Version)->getShortVersion();

chdir($tmp);
system('mkdir diffdocs');
system('mkdir diffconvert');
system('mkdir packages');

echo "Create list of changed files from git repository for version $fullVersion.\n";

/*
 * Here we force add every top-level directory and file in our diff archive, even if they haven't changed.
 * This allows us to install these files from the Extension Manager.
 * So we add the index file for each top-level directory.
 * Note: If we add new top-level directories or files, be sure to include them here.
 */
$filesArray = array(
	"administrator/index.php\n" => true,
	"cache/index.html\n" => true,
	"cli/index.html\n" => true,
	"components/index.html\n" => true,
	"images/index.html\n" => true,
	"includes/index.html\n" => true,
	"language/index.html\n" => true,
	"layouts/index.html\n" => true,
	"libraries/index.html\n" => true,
	"media/index.html\n" => true,
	"modules/index.html\n" => true,
	"plugins/index.html\n" => true,
	"templates/index.html\n" => true,
	"tmp/index.html\n" => true,
	"htaccess.txt\n" => true,
	"index.php\n" => true,
	"LICENSE.txt\n" => true,
	"README.txt\n" => true,
	"robots.txt.dist\n" => true,
	"web.config.txt\n" => true
);

/*
 * Here we set the files/folders which should not be packaged at any time
 * These paths are from the repository root without the leading slash
 */
$doNotPackage = array(
	'dev',
	'.appveyor.yml',
	'.drone.yml',
	'.eslintignore',
	'.eslint',
	'.github',
	'.gitignore',
	'.hound.yml',
	'.php_cs',
	'.travis.yml',
	'README.md',
	'acceptance.suite.yml',
	'appveyor-phpunit.xml',
	'build',
	'build.xml',
	'build.js',
	'composer.json',
	'composer.lock',
	'Gemfile',
	'grunt-settings.yaml',
	'grunt-readme.md',
	'Gruntfile.js',
	'karma.conf.js',
	'phpunit.xml.dist',
	'scss-lint-report.xml',
	'scss-lint.yml',
	'stubs.php',
	'tests',
	'travisci-phpunit.xml',
	'drone-package.json',
	'package.json',
	'codeception.yml',
	'Jenkinsfile',
	'jenkins-phpunit.xml',
	'RoboFile.php',
	'RoboFile.dist.ini',
	// Remove the testing sample data from all packages
	'installation/sql/mysql/sample_testing.sql',
	'installation/sql/postgresql/sample_testing.sql',
);

/*
 * Here we set the files/folders which should not be packaged with patch packages only
 * These paths are from the repository root without the leading slash
 */
$doNotPatch = array(
	'administrator/cache',
	'administrator/logs',
	'installation',
	'images',
);

/*
 * This array will contain the checksums for all files which are created by this script.
 * This is an associative array with the following structure:
 * array(
 *   'filename' => array(
 *     'type1' => 'hash',
 *     'type2' => 'hash',
 *   ),
 * )
 */
$checksums = array();

// For the packages, replace spaces in stability (RC) with underscores
$packageStability = str_replace(' ', '_', Version::DEV_STATUS);

// Count down starting with the latest release and add diff files to this array
for ($num = $release - 1; $num >= 0; $num--)
{
	echo "Create version $num update packages.\n";

	// Here we get a list of all files that have changed between the two references ($previousTag and $remote) and save in diffdocs
	$previousTag = $version . '.' . $num;
	$command     = $systemGit . ' diff tags/' . $previousTag . ' ' . $remote . ' --name-status > diffdocs/' . $version . '.' . $num;

	system($command);

	// $filesArray will hold the array of files to include in diff package
	$deletedFiles = array();
	$files        = file('diffdocs/' . $version . '.' . $num);

	// Loop through and add all files except: tests, installation, build, .git, .travis, travis, phpunit, .md, or images
	foreach ($files as $file)
	{
		$fileName   = substr($file, 2);
		$folderPath = explode('/', $fileName);
		$baseFolderName = $folderPath[0];

		$doNotPackageFile = in_array(trim($fileName), $doNotPackage);
		$doNotPatchFile = in_array(trim($fileName), $doNotPatch);
		$doNotPackageBaseFolder = in_array($baseFolderName, $doNotPackage);
		$doNotPatchBaseFolder = in_array($baseFolderName, $doNotPatch);

		if ($doNotPackageFile || $doNotPatchFile || $doNotPackageBaseFolder || $doNotPatchBaseFolder)
		{
			continue;
		}

		// Act on the file based on the action
		switch (substr($file, 0, 1))
		{
			// This is a new case with git 2.9 to handle renamed files
			case 'R':
				// Explode the file on the tab character; key 0 is the action (rename), key 1 is the old filename, and key 2 is the new filename
				$renamedFileData = explode("\t", $file);

				// Add the new file for packaging
				$filesArray[$renamedFileData[2]] = true;

				// And flag the old file as deleted
				$deletedFiles[] = $renamedFileData[1];

				break;

			// Deleted files
			case 'D':
				$deletedFiles[] = $fileName;

				break;

			// Regular additions and modifications
			default:
				$filesArray[$fileName] = true;

				break;
		}
	}

	// Write the file list to a text file.
	$filePut = array_keys($filesArray);
	sort($filePut);
	file_put_contents('diffconvert/' . $version . '.' . $num, implode('', $filePut));
	file_put_contents('diffconvert/' . $version . '.' . $num . '-deleted', $deletedFiles);

	// Only create archives for 0 and most recent versions. Skip other update versions.
	if ($num != 0 && ($num != $release - 1))
	{
		echo "Skipping patch archive for version $version.$num\n";

		continue;
	}

	$fromName = $num == 0 ? 'x' : $num;

	// Create the diff archive packages using the file name list.
	if (!$excludeBzip2)
	{
		$packageName = 'Joomla_' . $version . '.' . $fromName . '_to_' . $fullVersion . '-' . $packageStability . '-Patch_Package.tar.bz2';
		system('tar --create --bzip2 --no-recursion --directory ' . $time . ' --file packages/' . $packageName . ' --files-from diffconvert/' . $version . '.' . $num . '> /dev/null');
		$checksums[$packageName] = array();
	}

	if (!$excludeGzip)
	{
		$packageName = 'Joomla_' . $version . '.' . $fromName . '_to_' . $fullVersion . '-' . $packageStability . '-Patch_Package.tar.gz';
		system('tar --create --gzip  --no-recursion --directory ' . $time . ' --file packages/' . $packageName . ' --files-from diffconvert/' . $version . '.' . $num . '> /dev/null');
		$checksums[$packageName] = array();
	}

	if (!$excludeZip)
	{
		$packageName = 'Joomla_' . $version . '.' . $fromName . '_to_' . $fullVersion . '-' . $packageStability . '-Patch_Package.zip';
		chdir($time);
		system('zip ../packages/' . $packageName . ' -@ < ../diffconvert/' . $version . '.' . $num . '> /dev/null');
		chdir('..');
		$checksums[$packageName] = array();
	}
}

// Delete the files and folders we exclude from the packages (tests, docs, build, etc.).
echo "Delete folders not included in packages.\n";

foreach ($doNotPackage as $removeFile)
{
	system('rm -rf ' . $time . '/' . $removeFile);
}

echo "Build full package files.\n";
chdir($time);

// Create full archive packages.
if (!$excludeBzip2)
{
	$packageName = 'Joomla_' . $fullVersion . '-' . $packageStability . '-Full_Package.tar.bz2';
	system('tar --create --bzip2 --file ../packages/' . $packageName . ' * > /dev/null');
	$checksums[$packageName] = array();
}

if (!$excludeGzip)
{
	$packageName = 'Joomla_' . $fullVersion . '-' . $packageStability . '-Full_Package.tar.gz';
	system('tar --create --gzip --file ../packages/' . $packageName . ' * > /dev/null');
	$checksums[$packageName] = array();
}

if (!$excludeZip)
{
	$packageName = 'Joomla_' . $fullVersion . '-' . $packageStability . '-Full_Package.zip';
	system('zip -r ../packages/' . $packageName . ' * > /dev/null');
	$checksums[$packageName] = array();
}

// Create full update file without the default logs directory, installation folder, or sample images.
echo "Build full update package.\n";
system('rm -r administrator/logs');
system('rm -r installation');
system('rm -r images/banners');
system('rm -r images/headers');
system('rm -r images/sampledata');
system('rm images/joomla_black.png');
system('rm images/powered_by.png');

if (!$excludeBzip2)
{
	$packageName = 'Joomla_' . $fullVersion . '-' . $packageStability . '-Update_Package.tar.bz2';
	system('tar --create --bzip2 --file ../packages/' . $packageName . ' * > /dev/null');
	$checksums[$packageName] = array();
}

if (!$excludeGzip)
{
	$packageName = 'Joomla_' . $fullVersion . '-' . $packageStability . '-Update_Package.tar.gz';
	system('tar --create --gzip --file ../packages/' . $packageName . ' * > /dev/null');
	$checksums[$packageName] = array();
}

if (!$excludeZip)
{
	$packageName = 'Joomla_' . $fullVersion . '-' . $packageStability . '-Update_Package.zip';
	system('zip -r ../packages/' . $packageName . ' * > /dev/null');
	$checksums[$packageName] = array();
}

chdir('..');

foreach (array_keys($checksums) as $packageName)
{
	echo "Generating checksums for $packageName\n";

	foreach (array('md5', 'sha1') as $hash)
	{
		if (file_exists('packages/' . $packageName))
		{
			$checksums[$packageName][$hash] = hash_file($hash, 'packages/' . $packageName);
		}
		else
		{
			echo "Package $packageName not found in build directories\n";
		}
	}
}

echo "Generating checksums.txt file\n";

$checksumsContent = '';

foreach ($checksums as $packageName => $packageHashes)
{
	$checksumsContent .= "Filename: $packageName\n";

	foreach ($packageHashes as $hashType => $hash)
	{
		$checksumsContent .= "$hashType: $hash\n";
	}

	$checksumsContent .= "\n";
}

file_put_contents('checksums.txt', $checksumsContent);

echo "Build of version $fullVersion complete!\n";
